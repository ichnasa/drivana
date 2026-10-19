<?php

namespace App\Controllers;

use App\Libraries\Drivana;
use App\Libraries\FormRules;
use App\Libraries\Notifier;
use App\Libraries\Store;
use App\Libraries\VerificationService;

/** Admin: dashboard, document verification, car review, transactions, users, complaints, activity log. */
class Admin extends BaseController
{
    private function aid(): int
    {
        return (int) session('user')['id'];
    }

    private function user(int $id): array
    {
        return Store::get('users', $id) ?? ['name' => 'Pengguna', 'email' => '', 'phone' => '', 'role' => '', 'business_name' => ''];
    }

    private function nav(string $active): array
    {
        return ['role' => 'admin', 'active' => $active];
    }

    /* ───────── dashboard ───────── */

    public function dashboard(): string
    {
        $month = date('Y-m-01');
        $people = Store::where('users', fn ($u) => $u['role'] !== 'admin');
        $by = fn (string $st) => count(array_filter($people, fn ($u) => $u['verification_status'] === $st));

        // platform revenue this month: service fees of live bookings + subscription/boost purchases
        $fees = array_sum(array_map(fn ($b) => $b['service_fee'], Store::where('bookings', fn ($b) => $b['created_at'] >= $month && in_array($b['status'], ['menunggu', 'dikonfirmasi', 'aktif', 'selesai'], true))));
        $sales = array_sum(array_map(fn ($p) => $p['amount'], Store::where('payments', fn ($p) => in_array($p['kind'], ['subscription', 'boost'], true) && $p['status'] === 'paid' && $p['paid_at'] >= $month)));

        // documents reviewed per day, last 7 days
        $trend = [];
        $names = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $trend[$names[(int) date('w', strtotime($d))]] = Store::count('documents', fn ($x) => $x['reviewed_at'] && substr($x['reviewed_at'], 0, 10) === $d);
        }
        $peak = array_keys($trend, max($trend))[0];

        $pendingDocs = Store::where('documents', fn ($d) => $d['status'] === 'pending');
        usort($pendingDocs, fn ($a, $b) => strcmp($a['created_at'], $b['created_at']));
        $seen = [];
        $attention = [];
        foreach ($pendingDocs as $d) {
            if (isset($seen[$d['user_id']])) {
                continue;
            }
            $seen[$d['user_id']] = true;
            $u = $this->user((int) $d['user_id']);
            $attention[] = ['name' => $u['name'], 'role' => $u['role'], 'task' => 'Verifikasi ' . Drivana::DOC_LABELS[$d['type']], 'meta' => 'Menunggu ' . str_replace(' lalu', '', Drivana::ago($d['created_at'])), 'urgent' => (time() - strtotime($d['created_at'])) > 1800];
            if (count($attention) === 3) {
                break;
            }
        }
        $logs = Store::all('activity_logs');
        usort($logs, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return view('admin/dashboard', [
            'title' => 'Dashboard', 'me' => $this->me(), 'live' => Store::count('bookings', fn ($b) => $b['status'] === 'aktif'),
            'bookings_month' => Store::count('bookings', fn ($b) => $b['created_at'] >= $month && in_array($b['status'], ['menunggu', 'dikonfirmasi', 'aktif', 'selesai'], true)),
            'revenue' => $fees + $sales, 'fees' => $fees, 'sales' => $sales,
            'users' => ['total' => count($people), 'pending' => $by('pending'), 'verified' => $by('verified'), 'rejected' => $by('rejected'), 'unverified' => $by('unverified')],
            'trend' => $trend, 'peak' => $peak, 'attention' => $attention, 'logs' => array_slice($logs, 0, 5),
            'cars_pending' => Store::count('cars', fn ($c) => $c['listing_status'] === 'pending' && ! $c['deleted_at']),
            'complaints_open' => Store::count('complaints', fn ($c) => $c['status'] === 'open'),
        ] + $this->nav('dashboard'));
    }

    /* ───────── document verification ───────── */

    public function verifikasi(): string
    {
        $status = $this->request->getGet('status') ?: 'menunggu';
        $q = mb_strtolower(trim((string) $this->request->getGet('q')));
        $map = ['menunggu' => 'pending', 'disetujui' => 'approved', 'ditolak' => 'rejected'];
        $all = Store::all('documents');
        $counts = ['semua' => count($all)];
        foreach ($map as $k => $v) {
            $counts[$k] = count(array_filter($all, fn ($d) => $d['status'] === $v));
        }
        $rows = array_filter($all, fn ($d) => (! isset($map[$status]) || $d['status'] === $map[$status]));
        $rows = array_map(fn ($d) => $d + ['user' => $this->user((int) $d['user_id'])], $rows);
        if ($q !== '') {
            $rows = array_filter($rows, fn ($d) => str_contains(mb_strtolower($d['user']['name'] . ' ' . $d['user']['email'] . ' ' . Drivana::DOC_LABELS[$d['type']]), $q));
        }
        $sort = in_array($this->request->getGet('urut'), ['terlama', 'nama'], true) ? $this->request->getGet('urut') : 'terbaru';
        usort($rows, match ($sort) {
            'terlama' => fn ($a, $b) => strcmp($a['created_at'], $b['created_at']),
            'nama'    => fn ($a, $b) => strcmp(mb_strtolower($a['user']['name']), mb_strtolower($b['user']['name'])),
            default   => fn ($a, $b) => strcmp($b['created_at'], $a['created_at']),
        });

        return view('admin/verifikasi', ['title' => 'Verifikasi Dokumen', 'docs' => array_values($rows), 'counts' => $counts, 'status' => $status, 'q' => $q, 'sort' => $sort] + $this->nav('verifikasi'));
    }

    public function setujuiDokumen(int $id)
    {
        Store::get('documents', $id) ?? $this->notFound();
        VerificationService::review($id, $this->aid(), true);

        return redirect()->back()->with('success', 'Dokumen disetujui.');
    }

    public function tolakDokumen(int $id)
    {
        Store::get('documents', $id) ?? $this->notFound();
        if (! $this->validate(FormRules::reason('Alasan penolakan'))) {
            return $this->backWith('admin/verifikasi?status=menunggu', ['reason' => $this->validator->getError('reason'), 'doc' => (string) $id]);
        }
        VerificationService::review($id, $this->aid(), false, trim((string) $this->request->getPost('reason')));

        return redirect()->back()->with('success', 'Dokumen ditolak. Pengguna diberi tahu.');
    }

    /* ───────── car listings ───────── */

    public function mobil(): string
    {
        $status = $this->request->getGet('status') ?: 'pending';
        $cars = Store::where('cars', fn ($c) => ! $c['deleted_at']);
        $counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
        foreach ($cars as $c) {
            $counts[$c['listing_status']]++;
        }
        $rows = array_filter($cars, fn ($c) => $c['listing_status'] === $status);
        usort($rows, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));
        $rows = array_map(fn ($c) => $c + ['mitra' => $this->user((int) $c['mitra_id'])], $rows);

        return view('admin/mobil', ['title' => 'Tinjau mobil', 'cars' => array_values($rows), 'counts' => $counts, 'status' => $status] + $this->nav('verifikasi'));
    }

    public function setujuiMobil(int $id)
    {
        $c = Store::get('cars', $id) ?? $this->notFound();
        Store::update('cars', $id, ['listing_status' => 'approved', 'reject_reason' => null, 'updated_at' => Drivana::now()]);
        Notifier::to((int) $c['mitra_id'], 'Mobil disetujui', $c['name'] . ' sekarang tampil di pencarian.', 'mitra/armada');
        Notifier::log($this->aid(), 'success', 'Mobil ' . $c['name'] . ' disetujui', $this->user((int) $c['mitra_id'])['business_name']);

        return redirect()->back()->with('success', $c['name'] . ' disetujui dan tampil di pencarian.');
    }

    public function tolakMobil(int $id)
    {
        $c = Store::get('cars', $id) ?? $this->notFound();
        if (! $this->validate(FormRules::reason('Alasan penolakan'))) {
            return $this->backWith('admin/mobil', ['reason' => $this->validator->getError('reason'), 'car' => (string) $id]);
        }
        $reason = trim((string) $this->request->getPost('reason'));
        Store::update('cars', $id, ['listing_status' => 'rejected', 'reject_reason' => $reason, 'updated_at' => Drivana::now()]);
        Notifier::to((int) $c['mitra_id'], 'Mobil ditolak', $c['name'] . ': ' . $reason, 'mitra/armada/' . $id . '/edit');
        Notifier::log($this->aid(), 'danger', 'Mobil ' . $c['name'] . ' ditolak', $reason);

        return redirect()->back()->with('success', $c['name'] . ' ditolak. Mitra diberi tahu.');
    }

    /* ───────── transactions ───────── */

    public function transaksi(): string
    {
        $tab = $this->request->getGet('tab') === 'langganan' ? 'langganan' : 'booking';
        $status = (string) $this->request->getGet('status');
        $q = mb_strtolower(trim((string) $this->request->getGet('q')));
        $date = fn (string $k) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $this->request->getGet($k)) ? (string) $this->request->getGet($k) : '';
        [$from, $to] = [$date('dari'), $date('sampai')];
        $inRange = fn (array $r) => ($from === '' || substr($r['created_at'], 0, 10) >= $from) && ($to === '' || substr($r['created_at'], 0, 10) <= $to);

        if ($tab === 'booking') {
            $rows = Store::where('bookings', fn ($b) => ($status === '' || $b['status'] === $status) && $inRange($b));
            $rows = array_map(fn ($b) => $b + ['customer' => $this->user((int) $b['customer_id'])['name'], 'mitra' => $this->user((int) $b['mitra_id'])['business_name'], 'car' => Store::get('cars', (int) $b['car_id'])['name'], 'pay' => $this->lastPayment((int) $b['id'])['status'] ?? null], $rows);
            if ($q !== '') {
                $rows = array_filter($rows, fn ($b) => str_contains(mb_strtolower($b['code'] . ' ' . $b['customer'] . ' ' . $b['mitra'] . ' ' . $b['car']), $q));
            }
        } else {
            $rows = Store::where('payments', fn ($p) => in_array($p['kind'], ['subscription', 'boost'], true) && ($status === '' || $p['status'] === $status) && $inRange($p));
            $rows = array_map(fn ($p) => $p + ['mitra' => $this->user((int) $p['user_id'])['business_name'], 'car' => $p['car_id'] ? Store::get('cars', (int) $p['car_id'])['name'] : null], $rows);
            if ($q !== '') {
                $rows = array_filter($rows, fn ($p) => str_contains(mb_strtolower($p['ref'] . ' ' . $p['mitra']), $q));
            }
        }
        $rows = array_values($rows);
        usort($rows, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return view('admin/transaksi', ['title' => 'Transaksi', 'tab' => $tab, 'status' => $status, 'q' => $q, 'from' => $from, 'to' => $to, 'rows' => array_slice($rows, 0, 60), 'total' => count($rows)] + $this->nav('aktivitas'));
    }

    private function lastPayment(int $bookingId): ?array
    {
        $rows = Store::where('payments', fn ($p) => (int) $p['booking_id'] === $bookingId);
        usort($rows, fn ($a, $b) => $b['id'] <=> $a['id']);

        return $rows[0] ?? null;
    }

    public function transaksiDetail(string $code): string
    {
        $b = Store::first('bookings', fn ($x) => $x['code'] === $code) ?? $this->notFound();

        return view('admin/transaksi_detail', [
            'title' => 'Transaksi ' . $b['code'], 'b' => $b, 'car' => Store::get('cars', (int) $b['car_id']),
            'customer' => $this->user((int) $b['customer_id']), 'mitra' => $this->user((int) $b['mitra_id']), 'payment' => $this->lastPayment((int) $b['id']),
            'review' => Store::first('reviews', fn ($r) => (int) $r['booking_id'] === (int) $b['id']),
        ] + $this->nav('aktivitas'));
    }

    /* ───────── users ───────── */

    public function pengguna(): string
    {
        $role = (string) $this->request->getGet('peran');
        $q = mb_strtolower(trim((string) $this->request->getGet('q')));
        $rows = Store::where('users', fn ($u) => $u['role'] !== 'admin' && ($role === '' || $u['role'] === $role)
            && ($q === '' || str_contains(mb_strtolower($u['name'] . ' ' . $u['email'] . ' ' . $u['business_name']), $q)));
        usort($rows, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return view('admin/pengguna', ['title' => 'Pengguna', 'users' => $rows, 'peran' => $role, 'q' => $q] + $this->nav('pengguna'));
    }

    public function statusPengguna(int $id)
    {
        $u = Store::get('users', $id) ?? $this->notFound();
        if ($u['role'] === 'admin' || $id === $this->aid()) {
            return redirect()->to('admin/pengguna')->with('error', 'Akun admin tidak bisa dinonaktifkan.');
        }
        $new = $u['status'] === 'active' ? 'inactive' : 'active';
        Store::update('users', $id, ['status' => $new, 'updated_at' => Drivana::now()]);
        Notifier::log($this->aid(), $new === 'inactive' ? 'danger' : 'success', 'Akun ' . $u['name'] . ($new === 'inactive' ? ' dinonaktifkan' : ' diaktifkan kembali'), null);

        return redirect()->back()->with('success', $u['name'] . ($new === 'inactive' ? ' dinonaktifkan.' : ' diaktifkan kembali.'));
    }

    /* ───────── complaints + activity ───────── */

    public function keluhan(): string
    {
        $rows = Store::all('complaints');
        usort($rows, fn ($a, $b) => [$a['status'] === 'open' ? 0 : 1, $b['created_at']] <=> [$b['status'] === 'open' ? 0 : 1, $a['created_at']]);
        $rows = array_map(fn ($c) => $c + ['user' => $this->user((int) $c['user_id']), 'code' => $c['booking_id'] ? (Store::get('bookings', (int) $c['booking_id'])['code'] ?? null) : null], $rows);

        return view('admin/keluhan', ['title' => 'Keluhan', 'rows' => $rows] + $this->nav('aktivitas'));
    }

    public function balasKeluhan(int $id)
    {
        $c = Store::get('complaints', $id) ?? $this->notFound();
        if (! $this->validate(FormRules::reply())) {
            return $this->backWith('admin/keluhan', ['reply' => $this->validator->getError('reply'), 'complaint' => (string) $id]);
        }
        Store::update('complaints', $id, ['admin_reply' => trim((string) $this->request->getPost('reply')), 'status' => 'resolved', 'replied_at' => Drivana::now()]);
        Notifier::to((int) $c['user_id'], 'Keluhanmu dibalas', 'Admin membalas: ' . $c['subject'], (($this->user((int) $c['user_id'])['role'] === 'mitra') ? 'mitra/' : '') . 'bantuan');
        Notifier::log($this->aid(), 'success', 'Keluhan dibalas: ' . $c['subject'], $this->user((int) $c['user_id'])['name']);

        return redirect()->to('admin/keluhan')->with('success', 'Balasan terkirim.');
    }

    public function log(): string
    {
        $rows = Store::all('activity_logs');
        usort($rows, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return view('admin/log', ['title' => 'Aktivitas', 'rows' => array_slice($rows, 0, 100)] + $this->nav('aktivitas'));
    }

    public function pengaturan(): string
    {
        return view('admin/pengaturan', [
            'title' => 'Pengaturan', 'me' => $this->me(),
            'cars_pending' => Store::count('cars', fn ($c) => $c['listing_status'] === 'pending' && ! $c['deleted_at']),
            'complaints_open' => Store::count('complaints', fn ($c) => $c['status'] === 'open'),
        ] + $this->nav('pengaturan'));
    }
}
