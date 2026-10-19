<?php

namespace App\Controllers;

use App\Libraries\BookingService;
use App\Libraries\Drivana;
use App\Libraries\FormRules;
use App\Libraries\Notifier;
use App\Libraries\PaymentService;
use App\Libraries\Store;

/** Mitra: dashboard, fleet, reservations. */
class Mitra extends BaseController
{
    private function uid(): int
    {
        return (int) session('user')['id'];
    }

    private function myCars(): array
    {
        return Store::where('cars', fn ($c) => (int) $c['mitra_id'] === $this->uid() && ! $c['deleted_at']);
    }

    private function myCar(int $id): array
    {
        $c = Store::get('cars', $id);

        return ($c && (int) $c['mitra_id'] === $this->uid() && ! $c['deleted_at']) ? $c : $this->notFound();
    }

    private function myBooking(string $code): array
    {
        $svc = new BookingService();
        $svc->expireStale();
        $b = $svc->findByCode($code);

        return ($b && (int) $b['mitra_id'] === $this->uid()) ? $b : $this->notFound();
    }

    /** Net revenue (completed bookings, minus the platform fee) finished within [from, to). */
    private function net(string $from, ?string $to = null): int
    {
        return array_sum(array_map(fn ($b) => $b['total'] - $b['service_fee'], Store::where('bookings', function ($b) use ($from, $to) {
            $d = $b['finished_at'] ?: $b['end_date'];

            return (int) $b['mitra_id'] === $this->uid() && $b['status'] === 'selesai' && $d >= $from && ($to === null || $d < $to);
        })));
    }

    /* ───────── dashboard ───────── */

    public function dashboard(): string
    {
        $uid = $this->uid();
        $cars = $this->myCars();
        $rented = array_column(Store::where('bookings', fn ($b) => (int) $b['mitra_id'] === $uid && $b['status'] === 'aktif'), 'car_id');
        $approved = array_filter($cars, fn ($c) => $c['listing_status'] === 'approved');
        $fleet = [
            'Tersedia' => count(array_filter($approved, fn ($c) => $c['ops_status'] === 'tersedia' && ! in_array($c['id'], $rented, true))),
            'Disewa' => count(array_filter($approved, fn ($c) => in_array($c['id'], $rented, true))),
            'Servis' => count(array_filter($approved, fn ($c) => $c['ops_status'] === 'servis')),
        ];
        $pending = Store::where('bookings', fn ($b) => (int) $b['mitra_id'] === $uid && $b['status'] === 'menunggu');
        usort($pending, fn ($a, $b) => strcmp($a['created_at'], $b['created_at']));
        $pending = array_map(fn ($b) => $b + ['car_name' => Store::get('cars', (int) $b['car_id'])['name'], 'customer_name' => Store::get('users', (int) $b['customer_id'])['name']], array_slice($pending, 0, 3));

        return view('mitra/dashboard', [
            'title' => 'Dashboard', 'role' => 'mitra', 'active' => 'dashboard', 'me' => $this->me(),
            'sub' => (new PaymentService())->activeSubscription($uid),
            'active_now' => Store::count('bookings', fn ($b) => (int) $b['mitra_id'] === $uid && $b['status'] === 'aktif'),
            'to_confirm' => count(Store::where('bookings', fn ($b) => (int) $b['mitra_id'] === $uid && $b['status'] === 'menunggu')),
            'revenue' => $this->net(date('Y-m-01')), 'revenue_prev' => $this->net(date('Y-m-01', strtotime('-1 month')), date('Y-m-01')),
            'fleet' => $fleet, 'pending' => $pending, 'review_cars' => count(array_filter($cars, fn ($c) => $c['listing_status'] === 'pending')),
        ]);
    }

    /* ───────── fleet ───────── */

    public function armada(): string
    {
        $cars = array_map(function ($c) {
            $bk = Store::where('bookings', fn ($b) => (int) $b['car_id'] === (int) $c['id']);
            $rt = array_map(fn ($r) => $r['rating'], Store::where('reviews', fn ($r) => (int) $r['car_id'] === (int) $c['id']));

            return $c + [
                'renting_now' => count(array_filter($bk, fn ($b) => $b['status'] === 'aktif')),
                'upcoming' => count(array_filter($bk, fn ($b) => in_array($b['status'], ['menunggu', 'dikonfirmasi'], true))),
                'rating' => $rt ? round(array_sum($rt) / count($rt), 1) : null,
            ];
        }, $this->myCars());
        usort($cars, fn ($a, $b) => $b['id'] <=> $a['id']);

        return view('mitra/armada', ['title' => 'Kelola Armada', 'role' => 'mitra', 'active' => 'armada', 'cars' => $cars, 'me' => $this->me()]);
    }

    public function tambah()
    {
        if ($this->me()['verification_status'] !== 'verified') {
            return redirect()->to('mitra/armada')->with('error', 'Akunmu belum terverifikasi. Selesaikan verifikasi dulu untuk memasang mobil.');
        }

        return view('mitra/armada_form', ['title' => 'Tambah Data Kendaraan', 'role' => 'mitra', 'car' => null, 'me' => $this->me(), 'nav' => false]);
    }

    public function edit(int $id): string
    {
        return view('mitra/armada_form', ['title' => 'Edit Data Kendaraan', 'role' => 'mitra', 'car' => $this->myCar($id), 'me' => $this->me(), 'nav' => false]);
    }

    public function simpan()
    {
        $id = (int) $this->request->getPost('id');
        $back = $id ? "mitra/armada/{$id}/edit" : 'mitra/armada/tambah';
        $car = $id ? $this->myCar($id) : null;
        if (! $car && $this->me()['verification_status'] !== 'verified') {
            return redirect()->to('mitra/armada')->with('error', 'Akunmu belum terverifikasi.');
        }

        $rules = FormRules::fleet();
        $image = $this->request->getFile('image');
        $hasImage = $image && $image->getError() !== UPLOAD_ERR_NO_FILE;
        if ($hasImage) {
            $rules['image'] = ['label' => 'Gambar', 'rules' => 'is_image[image]|max_size[image,5120]|mime_in[image,image/jpeg,image/png]', 'errors' => [
                'is_image' => 'Gambar harus berformat JPG atau PNG.', 'mime_in' => 'Gambar harus berformat JPG atau PNG.', 'max_size' => 'Gambar maksimal 5 MB.']];
        } elseif (! $car) {
            $rules['image'] = ['label' => 'Gambar', 'rules' => 'uploaded[image]', 'errors' => ['uploaded' => 'Gambar kendaraan wajib diunggah.']];
        }
        if (! $this->validate($rules)) {
            return $this->backWith($back, $this->validator->getErrors());
        }

        $drv = (int) $this->request->getPost('driver');   // 0 none · 1 optional · 2 driver only
        $data = [
            'name' => trim((string) $this->request->getPost('name')), 'year' => (int) $this->request->getPost('year'), 'type' => $this->request->getPost('type'),
            'seats' => (int) $this->request->getPost('seats'), 'transmission' => $this->request->getPost('transmission'), 'fuel' => $this->request->getPost('fuel'),
            'plate' => strtoupper(preg_replace('/\s+/', ' ', trim((string) $this->request->getPost('plate')))), 'price' => (int) $this->request->getPost('price'),
            'with_driver' => $drv >= 1 ? 1 : 0, 'driver_only' => $drv === 2 ? 1 : 0, 'area' => $this->request->getPost('area'),
            'description' => trim((string) $this->request->getPost('description')) ?: null, 'updated_at' => Drivana::now(),
        ];
        if ($hasImage) {
            $dir = FCPATH . 'uploads/cars/';
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $name = bin2hex(random_bytes(8)) . '.' . $image->guessExtension();
            $image->move($dir, $name);
            $data += ['image' => 'uploads/cars/' . $name, 'image_credit' => null, 'image_credit_url' => null];
            if ($car && str_starts_with($car['image'], 'uploads/')) {
                @unlink(FCPATH . $car['image']);
            }
        }

        if ($car) {
            $resubmit = $car['listing_status'] === 'rejected';
            if ($resubmit) {
                $data += ['listing_status' => 'pending', 'reject_reason' => null];
                Notifier::admins('Mobil diajukan ulang', $data['name'], 'admin/mobil');
            }
            Store::update('cars', $id, $data);
            $msg = $resubmit ? 'Perubahan disimpan dan diajukan ulang untuk ditinjau.' : 'Perubahan disimpan.';
        } else {
            Store::insert('cars', $data + ['mitra_id' => $this->uid(), 'listing_status' => 'pending', 'reject_reason' => null, 'ops_status' => 'tersedia', 'service_until' => null,
                'boosted_until' => null, 'deleted_at' => null, 'image_credit' => $data['image_credit'] ?? null, 'image_credit_url' => $data['image_credit_url'] ?? null, 'created_at' => Drivana::now()]);
            Notifier::admins('Mobil baru perlu ditinjau', $this->me()['business_name'] . ' menambahkan ' . $data['name'] . '.', 'admin/mobil');
            Notifier::log($this->uid(), 'info', 'Mobil baru menunggu tinjauan: ' . $data['name'], $this->me()['business_name']);
            $msg = 'Kendaraan ditambahkan. Admin akan meninjaunya sebelum tampil di pencarian.';
        }

        return redirect()->to('mitra/armada')->with('success', $msg);
    }

    public function status(int $id)
    {
        $car = $this->myCar($id);
        $to = 'mitra/armada';
        $new = $this->request->getPost('status');
        if (! in_array($new, ['tersedia', 'servis'], true)) {
            return redirect()->to($to)->with('error', 'Status tidak valid.');
        }
        $until = null;
        if ($new === 'servis') {
            $until = (string) $this->request->getPost('until');
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $until) || $until < date('Y-m-d')) {
                return redirect()->to($to)->with('error', 'Isi estimasi selesai servis (tanggal hari ini atau setelahnya).');
            }
            if (Store::count('bookings', fn ($b) => (int) $b['car_id'] === $id && in_array($b['status'], ['menunggu', 'dikonfirmasi', 'aktif'], true))) {
                return redirect()->to($to)->with('error', 'Masih ada pesanan aktif atau mendatang untuk mobil ini.');
            }
        }
        Store::update('cars', $id, ['ops_status' => $new, 'service_until' => $until, 'updated_at' => Drivana::now()]);

        return redirect()->to($to)->with('success', $car['name'] . ($new === 'servis' ? ' ditandai sedang servis.' : ' tersedia lagi.'));
    }

    public function hapus(int $id)
    {
        $car = $this->myCar($id);
        if (Store::count('bookings', fn ($b) => (int) $b['car_id'] === $id && in_array($b['status'], ['pending_payment', 'menunggu', 'dikonfirmasi', 'aktif'], true))) {
            return redirect()->to('mitra/armada')->with('error', 'Tidak bisa dihapus: masih ada pesanan aktif atau mendatang.');
        }
        Store::update('cars', $id, ['deleted_at' => Drivana::now(), 'boosted_until' => null]);

        return redirect()->to('mitra/armada')->with('success', $car['name'] . ' dihapus dari armada.');
    }

    /* ───────── reservations ───────── */

    public function reservasi(): string
    {
        (new BookingService())->expireStale();
        $tab = $this->request->getGet('tab') ?: 'semua';
        $groups = ['konfirmasi' => ['menunggu'], 'aktif' => ['dikonfirmasi', 'aktif'], 'selesai' => ['selesai', 'dibatalkan', 'ditolak']];
        $rows = Store::where('bookings', fn ($b) => (int) $b['mitra_id'] === $this->uid() && ! in_array($b['status'], ['pending_payment', 'kedaluwarsa'], true)
            && (! isset($groups[$tab]) || in_array($b['status'], $groups[$tab], true)));
        $rank = ['menunggu' => 0, 'aktif' => 1, 'dikonfirmasi' => 2];
        usort($rows, fn ($a, $b) => [$rank[$a['status']] ?? 3, $b['start_date']] <=> [$rank[$b['status']] ?? 3, $a['start_date']]);
        $rows = array_map(function ($b) {
            $car = Store::get('cars', (int) $b['car_id']);

            return $b + ['car_name' => $car['name'], 'car_image' => $car['image'], 'customer_name' => Store::get('users', (int) $b['customer_id'])['name']];
        }, array_slice($rows, 0, 100));

        return view('mitra/reservasi', ['title' => 'Pesanan masuk', 'role' => 'mitra', 'active' => 'reservasi', 'items' => $rows, 'tab' => $tab]);
    }

    public function reservasiDetail(string $code): string
    {
        $b = $this->myBooking($code);
        $b['car'] = Store::get('cars', (int) $b['car_id']);
        $b['customer'] = Store::get('users', (int) $b['customer_id']);
        $b['review'] = Store::first('reviews', fn ($r) => (int) $r['booking_id'] === (int) $b['id']);
        $b['conversation'] = Store::first('conversations', fn ($c) => (int) $c['customer_id'] === (int) $b['customer_id'] && (int) $c['mitra_id'] === $this->uid() && ($c['kind'] ?? 'mitra') === 'mitra');

        return view('mitra/reservasi_detail', ['title' => 'Pesanan ' . $b['code'], 'role' => 'mitra', 'b' => $b, 'nav' => false, 'receipt' => (new \App\Libraries\PaymentService())->forBooking((int) $b['id'])]);
    }

    public function aksi(string $code, string $action)
    {
        $b = $this->myBooking($code);
        $svc = new BookingService();
        $to = 'mitra/reservasi/' . $code;
        switch ($action) {
            case 'konfirmasi':
                $ok = $svc->confirm((int) $b['id']);
                $msg = 'Pesanan dikonfirmasi.';
                break;
            case 'tolak':
                if (! $this->validate(FormRules::reason('Alasan penolakan'))) {
                    return $this->backWith($to, $this->validator->getErrors());
                }
                $ok = $svc->reject((int) $b['id'], trim((string) $this->request->getPost('reason')));
                $msg = 'Pesanan ditolak. Dana dikembalikan ke penyewa.';
                break;
            case 'mulai':
                $ok = $svc->start((int) $b['id']);
                $msg = 'Sewa dimulai.';
                break;
            case 'selesai':
                $ok = $svc->finish((int) $b['id']);
                $msg = 'Sewa selesai. Pendapatan masuk ke laporanmu.';
                break;
            default:
                $this->notFound();
        }

        return redirect()->to($to)->with($ok ? 'success' : 'error', $ok ? $msg : 'Status pesanan sudah berubah. Muat ulang halaman.');
    }
}
