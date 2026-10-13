<?php

namespace App\Controllers;

use App\Libraries\BookingService;
use App\Libraries\CarSearch;
use App\Libraries\Drivana;
use App\Libraries\FormRules;
use App\Libraries\Store;

/** Customer: home, search, car detail, booking form. */
class Customer extends BaseController
{
    public static function activePromo(): ?array
    {
        $now = Drivana::now();
        $promos = Store::where('promos', fn ($p) => $p['active'] && (! $p['expires_at'] || $p['expires_at'] > $now));
        usort($promos, fn ($a, $b) => $b['percent'] <=> $a['percent']);

        return $promos[0] ?? null;
    }

    public function beranda(): string
    {
        return view('customer/beranda', [
            'title' => 'Beranda', 'active' => 'beranda', 'me' => $this->me(), 'promo' => self::activePromo(),
            'cars' => CarSearch::search([], 1, 6)['items'], 'types' => Drivana::CAR_TYPES,
        ]);
    }

    /** Parse + validate the search filters from the query string. Returns [filters, errors]. */
    private function filters(): array
    {
        $get = $this->request->getGet() ?: [];
        if (! $this->validateData($get, FormRules::filter())) {
            return [[], $this->validator->getErrors()];
        }
        $f = array_intersect_key($get, array_flip(['q', 'type', 'seats', 'min', 'max', 'driver', 'area', 'start', 'end', 'sort', 'page']));
        $f = array_filter($f, fn ($v) => $v !== '' && $v !== null);
        $err = [];
        if (isset($f['min'], $f['max']) && (int) $f['min'] > (int) $f['max']) {
            $err['max'] = 'Harga max harus lebih besar dari harga min.';
        }
        if (isset($f['start']) xor isset($f['end'])) {
            $err[isset($f['start']) ? 'end' : 'start'] = 'Isi tanggal mulai dan selesai sekaligus.';
        } elseif (isset($f['start'], $f['end'])) {
            if ($f['start'] < date('Y-m-d')) {
                $err['start'] = 'Tanggal mulai tidak boleh sebelum hari ini.';
            }
            if ($f['end'] <= $f['start']) {
                $err['end'] = 'Tanggal selesai harus setelah tanggal mulai.';
            }
        }

        return [$f, $err];
    }

    /** type/seats/driver come from buttons, so a bad value there means a tampered request: one message. */
    private function tamperedAsForm(array $errors): array
    {
        foreach (['type', 'seats', 'driver', 'sort', 'page', 'area', 'q'] as $k) {
            if (isset($errors[$k])) {
                $errors['form'] = $errors[$k];
            }
        }

        return $errors;
    }

    public function cari()
    {
        [$f, $err] = $this->filters();
        if ($err) {
            return $this->backWith('filter', $this->tamperedAsForm($err));
        }
        $page = (int) ($f['page'] ?? 1);

        return view('customer/cari', [
            'title' => 'Cari mobil', 'active' => 'cari', 'f' => $f, 'res' => CarSearch::search($f, $page, 8), 'page' => $page,
            'types' => array_merge(['Semua'], Drivana::CAR_TYPES), 'type' => $f['type'] ?? 'Semua',
        ]);
    }

    public function filter(): string
    {
        return view('customer/filter', ['title' => 'Filter mobil', 'nav' => false]);
    }

    public function detail(int $id): string
    {
        $car = CarSearch::detail($id) ?? $this->notFound();
        $get = $this->request->getGet() ?: [];
        $check = null;
        $start = $get['start'] ?? '';
        $end = $get['end'] ?? '';
        if ($start !== '' && $end !== '' && $start >= date('Y-m-d') && $end > $start) {
            $check = (new BookingService())->isAvailable($id, $start, $end) && $car['ops_status'] === 'tersedia';
        }

        return view('customer/detail', [
            'title' => $car['name'], 'car' => $car, 'reviews' => CarSearch::reviews($id), 'busy' => CarSearch::busyRanges($id),
            'me' => $this->me(), 'check' => $check, 'start' => $start, 'end' => $end, 'nav' => false,
        ]);
    }

    public function booking(int $id)
    {
        $car = CarSearch::detail($id) ?? $this->notFound();
        if ($car['ops_status'] !== 'tersedia') {
            return redirect()->to('mobil/' . $id)->with('error', 'Mobil ini sedang tidak tersedia.');
        }
        $get = $this->request->getGet() ?: [];

        return view('customer/booking', [
            'title' => 'Form booking', 'car' => $car, 'me' => $this->me(), 'nav' => false,
            'start' => $get['start'] ?? date('Y-m-d', strtotime('+1 day')), 'end' => $get['end'] ?? date('Y-m-d', strtotime('+3 days')),
        ]);
    }

    public function doBooking(int $id)
    {
        $car = CarSearch::detail($id) ?? $this->notFound();
        $to = 'booking/' . $id;
        if (! $this->validate(FormRules::booking())) {
            return $this->backWith($to, $this->validator->getErrors());
        }
        $res = (new BookingService())->create($this->me(), $car, [
            'start' => $this->request->getPost('start'), 'end' => $this->request->getPost('end'),
            'with_driver' => $this->request->getPost('driver') === '1', 'area' => $this->request->getPost('area'),
            'address' => trim((string) $this->request->getPost('address')), 'note' => trim((string) $this->request->getPost('note')),
        ]);

        return $res['ok'] ? redirect()->to('bayar/' . $res['booking']['code']) : $this->backWith($to, $res['errors']);
    }
}
