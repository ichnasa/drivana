<?php

namespace App\Controllers;

use App\Libraries\Drivana;
use App\Libraries\FormRules;
use App\Libraries\Notifier;
use App\Libraries\Store;
use App\Libraries\VerificationService;

/** Profile, documents, password, notifications and help — shared by Customer and Mitra (notifications also by Admin). */
class Account extends BaseController
{
    private function nav(): array
    {
        return ['role' => $this->role() === 'mitra' ? 'mitra' : 'customer', 'active' => 'profil'];
    }

    private function prefix(): string
    {
        return $this->role() === 'mitra' ? 'mitra/' : '';
    }

    public function profil(): string
    {
        $me = $this->me();
        $uid = (int) $me['id'];
        if ($me['role'] === 'mitra') {
            $cars = array_column(Store::where('cars', fn ($c) => (int) $c['mitra_id'] === $uid), 'id');
            $ratings = array_map(fn ($r) => $r['rating'], Store::where('reviews', fn ($r) => in_array($r['car_id'], $cars, true)));
            $stats = [
                'Total sewa' => Store::count('bookings', fn ($b) => (int) $b['mitra_id'] === $uid && $b['status'] === 'selesai'),
                'Aktif' => Store::count('bookings', fn ($b) => (int) $b['mitra_id'] === $uid && in_array($b['status'], ['aktif', 'dikonfirmasi'], true)),
                'Rating' => $ratings ? number_format(array_sum($ratings) / count($ratings), 1) : '–',
            ];
        } else {
            $stats = [
                'Total sewa' => Store::count('bookings', fn ($b) => (int) $b['customer_id'] === $uid && $b['status'] === 'selesai'),
                'Aktif' => Store::count('bookings', fn ($b) => (int) $b['customer_id'] === $uid && in_array($b['status'], ['aktif', 'dikonfirmasi'], true)),
                'Ulasan' => Store::count('reviews', fn ($r) => (int) $r['customer_id'] === $uid),
            ];
        }

        return view('account/profil', ['title' => 'Profil', 'me' => $me, 'stats' => $stats, 'p' => $this->prefix()] + $this->nav());
    }

    public function edit(): string
    {
        return view('account/edit', ['title' => 'Edit profil', 'me' => $this->me(), 'nav' => false, 'p' => $this->prefix(), 'role' => $this->role()]);
    }

    public function update()
    {
        $mitra = $this->role() === 'mitra';
        $to = $this->prefix() . 'profil/edit';
        if (! $this->validate($mitra ? FormRules::mitraProfile() : FormRules::profile())) {
            return $this->backWith($to, $this->validator->getErrors());
        }
        $me = $this->me();
        $phone = Drivana::normalizePhone((string) $this->request->getPost('phone'));
        if (Store::count('users', fn ($u) => $u['phone'] === $phone && (int) $u['id'] !== (int) $me['id'])) {
            return $this->backWith($to, ['phone' => 'Nomor HP ini sudah dipakai akun lain.']);
        }
        $data = ['name' => trim((string) $this->request->getPost('name')), 'phone' => $phone, 'address' => trim((string) $this->request->getPost('address')) ?: null,
                 'area' => $this->request->getPost('area') ?: null, 'updated_at' => Drivana::now()];
        if ($mitra) {
            $data += ['business_name' => trim((string) $this->request->getPost('business_name')), 'business_address' => trim((string) $this->request->getPost('business_address')),
                      'whatsapp' => Drivana::normalizePhone((string) $this->request->getPost('whatsapp')), 'bank_account' => (string) $this->request->getPost('bank_account')];
        }
        Store::update('users', (int) $me['id'], $data);

        return redirect()->to($this->prefix() . 'profil')->with('success', 'Profil diperbarui.');
    }

    /* ───────── saved addresses (display only, dummy data) ───────── */

    public function alamat(): string
    {
        return view('account/alamat', ['title' => 'Alamat tersimpan', 'list' => Drivana::DUMMY_ADDRESSES, 'nav' => false, 'p' => $this->prefix(), 'role' => $this->role()]);
    }

    /* ───────── documents ───────── */

    public function dokumen(): string
    {
        $me = $this->me();
        $docs = [];
        foreach (Store::where('documents', fn ($d) => (int) $d['user_id'] === (int) $me['id']) as $d) {
            $docs[$d['type']] = $d;
        }

        return view('account/dokumen', ['title' => 'Dokumen saya', 'me' => $me, 'docs' => $docs, 'required' => Drivana::REQUIRED_DOCS[$me['role']], 'nav' => false, 'p' => $this->prefix(), 'role' => $this->role()]);
    }

    public function uploadDokumen(string $type)
    {
        $me = $this->me();
        $to = $this->prefix() . 'profil/dokumen';
        if (! in_array($type, Drivana::REQUIRED_DOCS[$me['role']], true)) {
            $this->notFound();
        }
        $rules = FormRules::document('file');
        if ($type === 'business') {
            $rules = ['file' => ['label' => 'Dokumen', 'rules' => 'uploaded[file]|max_size[file,5120]|ext_in[file,png,jpg,jpeg,pdf]', 'errors' => ['uploaded' => 'Dokumen wajib diunggah.', 'ext_in' => 'Dokumen harus berformat JPG, PNG, atau PDF.', 'max_size' => 'Dokumen maksimal 5 MB.']]];
        }
        if (! $this->validate($rules)) {
            return redirect()->to($to)->with('errors', ['doc_' . $type => $this->validator->getError('file')]);
        }
        $existing = Store::first('documents', fn ($d) => (int) $d['user_id'] === (int) $me['id'] && $d['type'] === $type);
        if ($existing && $existing['status'] === 'approved') {
            return redirect()->to($to)->with('error', 'Dokumen ini sudah disetujui dan tidak perlu diunggah ulang.');
        }
        VerificationService::store((int) $me['id'], $type, $this->request->getFile('file'));

        return redirect()->to($to)->with('success', Drivana::DOC_LABELS[$type] . ' terkirim. Admin akan memeriksanya.');
    }

    /* ───────── password ───────── */

    public function keamanan(): string
    {
        return view('account/keamanan', ['title' => 'Keamanan', 'nav' => false, 'p' => $this->prefix(), 'role' => $this->role()]);
    }

    public function ubahPassword()
    {
        $to = $this->prefix() . 'profil/keamanan';
        if (! $this->validate(FormRules::password())) {
            return $this->backWith($to, $this->validator->getErrors());
        }
        $me = $this->me();
        if (! password_verify((string) $this->request->getPost('current'), $me['password_hash'])) {
            return $this->backWith($to, ['current' => 'Password saat ini salah.']);
        }
        if (password_verify((string) $this->request->getPost('password'), $me['password_hash'])) {
            return $this->backWith($to, ['password' => 'Password baru tidak boleh sama dengan yang lama.']);
        }
        Store::update('users', (int) $me['id'], ['password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_BCRYPT), 'updated_at' => Drivana::now()]);
        session()->regenerate(true);

        return redirect()->to($this->prefix() . 'profil')->with('success', 'Password diganti.');
    }

    /* ───────── notifications ───────── */

    public function notifikasi(): string
    {
        $uid = (int) session('user')['id'];
        $rows = Store::where('notifications', fn ($n) => (int) $n['user_id'] === $uid);
        usort($rows, fn ($a, $b) => $b['id'] <=> $a['id']);
        $rows = array_slice($rows, 0, 50);
        $html = view('account/notifikasi', ['title' => 'Notifikasi', 'list' => $rows, 'role' => $this->role(), 'nav' => false]);
        foreach ($rows as $n) {   // mark as read after rendering, so unread items still show as new this visit
            if ($n['read_at'] === null) {
                Store::update('notifications', (int) $n['id'], ['read_at' => Drivana::now()]);
            }
        }

        return $html;
    }

    /* ───────── help + complaints ───────── */

    public function bantuan(): string
    {
        $uid = (int) session('user')['id'];
        $mine = Store::where('complaints', fn ($c) => (int) $c['user_id'] === $uid);
        usort($mine, fn ($a, $b) => $b['id'] <=> $a['id']);
        $col = $this->role() === 'mitra' ? 'mitra_id' : 'customer_id';
        $orders = Store::where('bookings', fn ($b) => (int) $b[$col] === $uid);
        usort($orders, fn ($a, $b) => $b['id'] <=> $a['id']);

        return view('account/bantuan', ['title' => 'Bantuan', 'mine' => $mine, 'orders' => array_slice($orders, 0, 20), 'nav' => false, 'p' => $this->prefix(), 'role' => $this->role()]);
    }

    public function keluhan()
    {
        $to = $this->prefix() . 'bantuan';
        if (! $this->validate(FormRules::complaint())) {
            return $this->backWith($to, $this->validator->getErrors());
        }
        $uid = (int) session('user')['id'];
        $code = trim((string) $this->request->getPost('booking'));
        $booking = null;
        if ($code !== '') {
            $col = $this->role() === 'mitra' ? 'mitra_id' : 'customer_id';
            $booking = Store::first('bookings', fn ($b) => $b['code'] === $code && (int) $b[$col] === $uid);
            if (! $booking) {
                return $this->backWith($to, ['booking' => 'Kode booking tidak ditemukan di akunmu.']);
            }
        }
        $subject = trim((string) $this->request->getPost('subject'));
        Store::insert('complaints', ['user_id' => $uid, 'booking_id' => $booking['id'] ?? null, 'subject' => $subject, 'message' => trim((string) $this->request->getPost('message')),
            'status' => 'open', 'admin_reply' => null, 'replied_at' => null, 'created_at' => Drivana::now()]);
        Notifier::admins('Keluhan baru', $this->me()['name'] . ': ' . $subject, 'admin/keluhan');
        Notifier::log($uid, 'info', 'Keluhan baru dari ' . $this->me()['name'], $subject);

        return redirect()->to($to)->with('success', 'Keluhan terkirim. Admin akan membalas lewat notifikasi.');
    }
}
