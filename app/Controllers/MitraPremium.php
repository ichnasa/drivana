<?php

namespace App\Controllers;

use App\Libraries\Drivana;
use App\Libraries\FormRules;
use App\Libraries\Insights;
use App\Libraries\PaymentService;
use App\Libraries\Store;

/** Subscription, boost and premium analytics for Mitra. */
class MitraPremium extends BaseController
{
    private function uid(): int
    {
        return (int) session('user')['id'];
    }

    private function period(): string
    {
        $p = (string) $this->request->getGet('periode');

        return isset(Insights::PERIODS[$p]) ? $p : 'minggu';
    }

    private function approvedCars(): array
    {
        $cars = Store::where('cars', fn ($c) => (int) $c['mitra_id'] === $this->uid() && ! $c['deleted_at'] && $c['listing_status'] === 'approved');
        usort($cars, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $cars;
    }

    /* ───────── subscription ───────── */

    public function langganan(): string
    {
        $history = Store::where('subscriptions', fn ($s) => (int) $s['mitra_id'] === $this->uid());
        usort($history, fn ($a, $b) => $b['id'] <=> $a['id']);

        return view('mitra/langganan', [
            'title' => 'Langganan Premium', 'role' => 'mitra', 'nav' => false, 'me' => $this->me(),
            'subscription' => (new PaymentService())->activeSubscription($this->uid()), 'history' => array_slice($history, 0, 5),
        ]);
    }

    public function beliLangganan()
    {
        if (! $this->validate(FormRules::subscription())) {
            return $this->backWith('mitra/langganan', $this->validator->getErrors());
        }
        if ($this->me()['verification_status'] !== 'verified') {
            return redirect()->to('mitra/langganan')->with('error', 'Verifikasi akunmu dulu sebelum berlangganan.');
        }
        $plan = (string) $this->request->getPost('plan');
        $p = (new PaymentService())->create($this->uid(), 'subscription', Drivana::PLANS[$plan]['price'], (string) $this->request->getPost('method'), ['plan' => $plan]);

        return redirect()->to('gateway/' . $p['ref']);
    }

    /* ───────── boost ───────── */

    public function boost(): string
    {
        return view('mitra/boost', ['title' => 'Boost listing', 'role' => 'mitra', 'nav' => false, 'cars' => $this->approvedCars(), 'preselect' => (int) $this->request->getGet('mobil')]);
    }

    public function beliBoost()
    {
        if (! $this->validate(FormRules::boost())) {
            return $this->backWith('mitra/boost', $this->validator->getErrors());
        }
        $carId = (int) $this->request->getPost('car');
        $car = Store::get('cars', $carId);
        if (! $car || (int) $car['mitra_id'] !== $this->uid() || $car['deleted_at'] || $car['listing_status'] !== 'approved') {
            return $this->backWith('mitra/boost', ['car' => 'Mobil tidak ditemukan atau belum disetujui admin.']);
        }
        $days = (int) $this->request->getPost('days');
        $p = (new PaymentService())->create($this->uid(), 'boost', Drivana::BOOSTS[$days], (string) $this->request->getPost('method'), ['car_id' => $carId, 'days' => $days]);

        return redirect()->to('gateway/' . $p['ref']);
    }

    /* ───────── premium analytics ───────── */

    public function harga(): string
    {
        $cars = $this->approvedCars();
        $sel = null;
        foreach ($cars as $c) {
            if ((int) $c['id'] === (int) $this->request->getGet('mobil')) {
                $sel = $c;
            }
        }
        $sel ??= $cars[0] ?? null;

        return view('mitra/harga', ['title' => 'Rekomendasi harga', 'role' => 'mitra', 'nav' => false, 'cars' => $cars, 'car' => $sel, 'rec' => $sel ? Insights::price($sel) : null]);
    }

    public function terapkanHarga()
    {
        $carId = (int) $this->request->getPost('car');
        $price = (int) $this->request->getPost('price');
        $car = Store::get('cars', $carId);
        if (! $car || (int) $car['mitra_id'] !== $this->uid() || $car['deleted_at'] || $price < 50000 || $price > 99999999) {
            return redirect()->to('mitra/rekomendasi-harga')->with('error', 'Harga tidak valid.');
        }
        Store::update('cars', $carId, ['price' => $price, 'updated_at' => Drivana::now()]);

        return redirect()->to('mitra/rekomendasi-harga?mobil=' . $carId)->with('success', 'Harga ' . $car['name'] . ' diperbarui ke ' . rupiah($price) . '.');
    }

    public function analitik(): string
    {
        $period = $this->period();

        return view('mitra/analitik', ['title' => 'Analitik rental', 'role' => 'mitra', 'nav' => false, 'period' => $period, 'periods' => Insights::PERIODS, 'a' => Insights::analytics($this->uid(), $period)]);
    }

    /** Earnings history is available to every verified mitra (not premium). */
    public function pendapatan(): string
    {
        $uid = $this->uid();
        $recent = Store::where('bookings', fn ($b) => (int) $b['mitra_id'] === $uid && $b['status'] === 'selesai');
        usort($recent, fn ($a, $b) => strcmp((string) $b['finished_at'], (string) $a['finished_at']));
        $recent = array_map(fn ($b) => $b + ['car_name' => Store::get('cars', (int) $b['car_id'])['name']], array_slice($recent, 0, 15));

        return view('mitra/pendapatan', ['title' => 'Riwayat pendapatan', 'role' => 'mitra', 'nav' => false, 'months' => Insights::earnings($uid, 6), 'recent' => $recent]);
    }
}
