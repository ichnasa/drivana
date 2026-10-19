<?php

namespace App\Controllers;

use App\Libraries\BookingService;
use App\Libraries\Drivana;
use App\Libraries\FormRules;
use App\Libraries\Notifier;
use App\Libraries\PaymentService;
use App\Libraries\Store;

/** Customer orders, checkout and the simulated payment gateway (also used by Mitra purchases). */
class Orders extends BaseController
{
    private function mine(string $code): array
    {
        $svc = new BookingService();
        $svc->expireStale();
        $b = $svc->findByCode($code);

        return ($b && (int) $b['customer_id'] === (int) session('user')['id']) ? $b : $this->notFound();
    }

    private function withCar(array $b): array
    {
        $b['car'] = Store::get('cars', (int) $b['car_id']);
        $b['mitra'] = Store::get('users', (int) $b['mitra_id']);
        $b['review'] = Store::first('reviews', fn ($r) => (int) $r['booking_id'] === (int) $b['id']);

        return $b;
    }

    /* ───────── orders ───────── */

    public function index(): string
    {
        (new BookingService())->expireStale();
        $tab = $this->request->getGet('tab') ?: 'semua';
        $uid = (int) session('user')['id'];
        $groups = ['aktif' => ['dikonfirmasi', 'aktif'], 'menunggu' => ['pending_payment', 'menunggu'], 'selesai' => ['selesai', 'dibatalkan', 'ditolak']];

        $rows = Store::where('bookings', fn ($b) => (int) $b['customer_id'] === $uid && $b['status'] !== 'kedaluwarsa' && (! isset($groups[$tab]) || in_array($b['status'], $groups[$tab], true)));
        usort($rows, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));
        $rows = array_map(function ($b) {
            $car = Store::get('cars', (int) $b['car_id']);

            return $b + ['car_name' => $car['name'], 'car_image' => $car['image'], 'business_name' => Store::get('users', (int) $b['mitra_id'])['business_name'] ?? '',
                'reviewed' => Store::count('reviews', fn ($r) => (int) $r['booking_id'] === (int) $b['id']) > 0];
        }, $rows);

        return view('customer/pesanan', ['title' => 'Pesanan Saya', 'active' => 'pesanan', 'orders' => $rows, 'tab' => $tab]);
    }

    public function show(string $code): string
    {
        $b = $this->withCar($this->mine($code));

        return view('customer/pesanan_detail', ['title' => 'Pesanan ' . $b['code'], 'b' => $b, 'nav' => false, 'receipt' => (new PaymentService())->forBooking((int) $b['id'])]);
    }

    public function cancel(string $code)
    {
        $b = $this->mine($code);
        if (! $this->validate(FormRules::cancel())) {
            return $this->backWith('pesanan/' . $code, $this->validator->getErrors());
        }
        $reason = trim((string) $this->request->getPost('reason')) ?: 'Dibatalkan oleh penyewa';
        if (! (new BookingService())->cancel((int) $b['id'], $reason)) {
            return redirect()->to('pesanan/' . $code)->with('error', 'Pesanan ini sudah tidak bisa dibatalkan.');
        }

        return redirect()->to('pesanan/' . $code)->with('success', $b['status'] === 'pending_payment' ? 'Pesanan dibatalkan.' : 'Pesanan dibatalkan. Dana dikembalikan.');
    }

    public function review(string $code)
    {
        $b = $this->mine($code);
        $to = 'pesanan/' . $code;
        if ($b['status'] !== 'selesai') {
            return redirect()->to($to)->with('error', 'Ulasan hanya bisa diberikan setelah sewa selesai.');
        }
        if (Store::first('reviews', fn ($r) => (int) $r['booking_id'] === (int) $b['id'])) {
            return redirect()->to($to)->with('error', 'Kamu sudah memberi ulasan untuk pesanan ini.');
        }
        if (! $this->validate(FormRules::review())) {
            return $this->backWith($to, $this->validator->getErrors());
        }
        $rating = (int) $this->request->getPost('rating');
        Store::insert('reviews', ['booking_id' => (int) $b['id'], 'car_id' => (int) $b['car_id'], 'customer_id' => (int) $b['customer_id'], 'rating' => $rating,
            'comment' => trim((string) $this->request->getPost('comment')) ?: null, 'created_at' => Drivana::now()]);
        Notifier::to((int) $b['mitra_id'], 'Ulasan baru', 'Penyewa memberi ' . $rating . ' bintang untuk pesanan ' . $b['code'] . '.', 'mitra/reservasi/' . $b['code']);

        return redirect()->to($to)->with('success', 'Terima kasih atas ulasanmu!');
    }

    /** "Pembayaran Berhasil" page shown right after a booking payment succeeds. */
    public function berhasil(string $ref): string
    {
        $p = $this->payment($ref);
        if ($p['kind'] !== 'booking' || $p['status'] !== 'paid') {
            $this->notFound();
        }
        $b = $this->withCar(Store::get('bookings', (int) $p['booking_id']));

        return view('customer/bayar_berhasil', ['title' => 'Pembayaran berhasil', 'p' => $p, 'b' => $b, 'nav' => false]);
    }

    /* ───────── digital receipt ───────── */

    /** Receipt for a paid payment: its payer, the booking's mitra, or an admin. */
    public function bukti(string $ref): string
    {
        $p = (new PaymentService())->find($ref);
        $me = (int) session('user')['id'];
        $b = ($p && $p['booking_id']) ? Store::get('bookings', (int) $p['booking_id']) : null;
        $allowed = $p && in_array($p['status'], ['paid', 'refunded'], true)
            && ((int) $p['user_id'] === $me || ($b && (int) $b['mitra_id'] === $me) || $this->role() === 'admin');
        if (! $allowed) {
            $this->notFound();
        }

        $back = match ($this->role()) {
            'admin'  => 'admin/transaksi' . ($b ? '' : '?tab=langganan'),
            'mitra'  => $b ? 'mitra/reservasi/' . $b['code'] : ($p['kind'] === 'boost' ? 'mitra/boost' : 'mitra/langganan'),
            default  => 'pesanan/' . $b['code'],
        };

        return view('customer/bukti', [
            'title' => 'Bukti pembayaran', 'p' => $p, 'b' => $b, 'back' => $back, 'nav' => false, 'role' => $this->role(),
            'car'   => Store::get('cars', (int) ($b['car_id'] ?? $p['car_id'] ?? 0)),
            'payer' => Store::get('users', (int) $p['user_id']),
            'method' => array_values(array_filter(Drivana::PAYMENT_METHODS, fn ($m) => $m['key'] === $p['method']))[0]['name'] ?? $p['method'],
        ]);
    }

    /* ───────── checkout ───────── */

    private function promo(string $code): ?array
    {
        $code = strtoupper(trim($code));
        $now = Drivana::now();

        return $code === '' ? null : Store::first('promos', fn ($p) => $p['code'] === $code && $p['active'] && (! $p['expires_at'] || $p['expires_at'] > $now));
    }

    public function checkout(string $code)
    {
        $b = $this->withCar($this->mine($code));
        if ($b['status'] !== 'pending_payment') {
            return redirect()->to('pesanan/' . $code);
        }

        return view('customer/checkout', ['title' => 'Pembayaran', 'b' => $b, 'methods' => Drivana::PAYMENT_METHODS, 'nav' => false, 'promo' => $this->promo((string) $b['promo_code']), 'offer' => Customer::activePromo()]);
    }

    public function pay(string $code)
    {
        $b = $this->mine($code);
        $to = 'bayar/' . $code;
        if ($b['status'] !== 'pending_payment') {
            return redirect()->to('pesanan/' . $code);
        }
        if (! $this->validate(FormRules::checkout())) {
            return $this->backWith($to, $this->validator->getErrors());
        }
        $promoCode = strtoupper(trim((string) $this->request->getPost('promo')));
        $promo = $this->promo($promoCode);
        if ($promoCode !== '' && ! $promo) {
            return $this->backWith($to, ['promo' => 'Kode promo tidak valid atau sudah berakhir.']);
        }
        $b = (new BookingService())->applyPromo($b, $promo);
        $p = (new PaymentService())->create((int) $b['customer_id'], 'booking', (int) $b['total'], (string) $this->request->getPost('method'), ['booking_id' => $b['id']]);

        return redirect()->to('gateway/' . $p['ref']);
    }

    /* ───────── simulated gateway (shared by booking, subscription and boost) ───────── */

    private function payment(string $ref): array
    {
        $p = (new PaymentService())->find($ref);

        return ($p && (int) $p['user_id'] === (int) session('user')['id']) ? $p : $this->notFound();
    }

    private function afterPay(array $p, bool $ok): string
    {
        if ($p['kind'] === 'booking') {
            $code = Store::get('bookings', (int) $p['booking_id'])['code'];

            return $ok ? 'pembayaran/berhasil/' . $p['ref'] : 'bayar/' . $code;
        }

        return $p['kind'] === 'subscription' ? 'mitra/langganan' : 'mitra/boost';
    }

    public function gateway(string $ref)
    {
        $p = $this->payment($ref);
        if ($p['status'] !== 'pending') {
            return redirect()->to($this->afterPay($p, $p['status'] === 'paid'));
        }

        return view('customer/gateway', [
            'title' => 'Pembayaran', 'p' => $p, 'nav' => false, 'role' => $this->role(),
            'b' => $p['booking_id'] ? Store::get('bookings', (int) $p['booking_id']) : null,
            'method' => array_values(array_filter(Drivana::PAYMENT_METHODS, fn ($m) => $m['key'] === $p['method']))[0] ?? Drivana::PAYMENT_METHODS[0],
        ]);
    }

    public function gatewayResult(string $ref, string $result)
    {
        $p = $this->payment($ref);
        $svc = new PaymentService();
        $ok = $result === 'berhasil';
        if ($p['status'] === 'pending') {
            $ok ? $svc->succeed($ref) : $svc->cancel($ref);
        }

        $flash = $ok ? ($p['kind'] === 'booking' ? null : 'Pembayaran berhasil.') : 'Pembayaran dibatalkan.';
        $to = redirect()->to($this->afterPay($p, $ok));

        return $flash ? $to->with('success', $flash) : $to;
    }
}
