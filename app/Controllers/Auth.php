<?php

namespace App\Controllers;

use App\Libraries\Drivana;
use App\Libraries\FormRules;
use App\Libraries\Notifier;
use App\Libraries\Store;
use App\Libraries\VerificationService;

/** Sign in / sign up / identity verification for Customer, Mitra and Admin. */
class Auth extends BaseController
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 60;

    /* ───────── helpers ───────── */

    private function signIn(array $u)
    {
        session()->regenerate(true);   // new session id on privilege change
        session()->set('user', ['id' => (int) $u['id'], 'role' => $u['role']]);
        session()->remove(['login_fail', 'login_first']);
        Store::update('users', (int) $u['id'], ['last_login_at' => Drivana::now()]);
    }

    private function lockedFor(): int
    {
        $fails = (int) session('login_fail');
        $first = (int) session('login_first');
        if ($fails >= self::MAX_ATTEMPTS) {
            $left = $first + self::LOCK_SECONDS - time();
            if ($left > 0) {
                return $left;
            }
            session()->remove(['login_fail', 'login_first']);
        }

        return 0;
    }

    private function recordFailure(): void
    {
        session()->set('login_first', session('login_first') ?: time());
        session()->set('login_fail', (int) session('login_fail') + 1);
    }

    private function guest()
    {
        $u = session('user');

        return $u ? redirect()->to(Drivana::home($u['role'])) : null;
    }

    private function findByIdentity(string $identity): ?array
    {
        $identity = strtolower(trim($identity));
        $phone = Drivana::normalizePhone($identity);
        $byPhone = preg_match('/^\+?[0-9]/', $identity);

        return Store::first('users', fn ($u) => strtolower($u['email']) === $identity || ($byPhone && $u['phone'] === $phone));
    }

    private function attemptLogin(array $roles, string $form)
    {
        if (! $this->validate(FormRules::login())) {
            return $this->backWith($form, $this->validator->getErrors());
        }
        if ($wait = $this->lockedFor()) {
            return $this->backWith($form, ['form' => "Terlalu banyak percobaan. Coba lagi dalam {$wait} detik."]);
        }

        $u = $this->findByIdentity((string) $this->request->getPost('identity'));
        // always run a hash check so unknown users and wrong passwords take the same time
        $ok = password_verify((string) $this->request->getPost('password'), $u['password_hash'] ?? '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ01234');

        if (! $u || ! $ok || ! in_array($u['role'], $roles, true)) {
            $this->recordFailure();

            return $this->backWith($form, ['form' => 'Email/No. HP atau password salah.']);
        }
        if ($u['status'] !== 'active') {
            return $this->backWith($form, ['form' => 'Akun kamu dinonaktifkan. Hubungi admin Drivana.']);
        }

        $this->signIn($u);

        return redirect()->to(Drivana::home($u['role']));
    }

    private function createUser(array $data): int
    {
        $now = Drivana::now();

        return Store::insert('users', $data + [
            'address' => null, 'area' => null, 'business_name' => null, 'business_address' => null, 'whatsapp' => null, 'bank_account' => null,
            'status' => 'active', 'verification_status' => 'unverified', 'verification_note' => null, 'last_login_at' => null, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function emailTaken(string $email): bool
    {
        return Store::count('users', fn ($u) => strtolower($u['email']) === strtolower(trim($email))) > 0;
    }

    private function phoneTaken(string $phone): bool
    {
        return Store::count('users', fn ($u) => $u['phone'] === Drivana::normalizePhone($phone)) > 0;
    }

    /** Shared email/phone uniqueness check for both sign-up forms. */
    private function uniqueErrors(string $email, string $phone): array
    {
        $err = [];
        if ($this->emailTaken($email)) {
            $err['email'] = 'Email ini sudah terdaftar. Masuk saja.';
        }
        if ($this->phoneTaken($phone)) {
            $err['phone'] = 'Nomor HP ini sudah terdaftar.';
        }

        return $err;
    }

    /* ───────── Customer (and admin) ───────── */

    public function login()
    {
        return $this->guest() ?? view('auth/login', ['title' => 'Masuk', 'demo' => [
            'customer' => ['maulida@drivana.test', 'Customer#123'],
            'customer baru (belum verifikasi)' => ['rani@drivana.test', 'Customer#123'],
            'admin' => ['admin@drivana.test', 'Admin#123'],
        ]]);
    }

    public function doLogin()
    {
        return $this->attemptLogin(['customer', 'admin'], 'login');
    }

    public function register()
    {
        return $this->guest() ?? view('auth/register', ['title' => 'Daftar']);
    }

    public function doRegister()
    {
        if (! $this->validate(FormRules::register())) {
            return $this->backWith('register', $this->validator->getErrors());
        }
        $email = trim((string) $this->request->getPost('email'));
        $phone = Drivana::normalizePhone((string) $this->request->getPost('phone'));
        if ($err = $this->uniqueErrors($email, $phone)) {
            return $this->backWith('register', $err);
        }

        $name = trim((string) $this->request->getPost('name'));
        $id = $this->createUser(['role' => 'customer', 'name' => $name, 'email' => strtolower($email), 'phone' => $phone,
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_BCRYPT)]);
        Notifier::log($id, 'info', 'Pengguna baru mendaftar', $name);
        $this->signIn(['id' => $id, 'role' => 'customer']);

        return redirect()->to('verifikasi');
    }

    public function verifikasi()
    {
        return $this->me()['verification_status'] === 'verified'
            ? redirect()->to('beranda')
            : view('auth/verifikasi', ['title' => 'Verifikasi Identitas']);
    }

    public function doVerifikasi()
    {
        if (! $this->validate(FormRules::verifikasi())) {
            return $this->backWith('verifikasi', $this->validator->getErrors());
        }
        $id = (int) session('user')['id'];
        foreach (['ktp', 'sim', 'selfie'] as $t) {
            VerificationService::store($id, $t, $this->request->getFile($t));
        }

        return redirect()->to('beranda')->with('success', 'Dokumen terkirim. Admin akan memeriksanya.');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('login');
    }

    /* ───────── Mitra ───────── */

    public function mitraLogin()
    {
        return $this->guest() ?? view('auth/mitra_login', ['title' => 'Masuk Mitra', 'demo' => [
            'mitra (Premium aktif)' => ['budi@jayaabadi.test', 'Mitra#123'],
            'mitra tanpa langganan' => ['sari@sarimotor.test', 'Mitra#123'],
            'mitra baru (dokumen diperiksa)' => ['citra@citrarent.test', 'Mitra#123'],
        ]]);
    }

    public function doMitraLogin()
    {
        return $this->attemptLogin(['mitra'], 'mitra/login');
    }

    public function mitraRegister()
    {
        return $this->guest() ?? view('auth/mitra_register', ['title' => 'Daftar Mitra']);
    }

    public function doMitraRegister()
    {
        if (! $this->validate(FormRules::mitraRegister())) {
            return $this->backWith('mitra/register', $this->validator->getErrors());
        }
        $email = trim((string) $this->request->getPost('email'));
        $phone = Drivana::normalizePhone((string) $this->request->getPost('phone'));
        if ($err = $this->uniqueErrors($email, $phone)) {
            return $this->backWith('mitra/register', $err);
        }

        $biz = trim((string) $this->request->getPost('business'));
        $id = $this->createUser(['role' => 'mitra', 'name' => $biz, 'business_name' => $biz, 'email' => strtolower($email), 'phone' => $phone, 'whatsapp' => $phone,
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_BCRYPT)]);
        VerificationService::store($id, 'business', $this->request->getFile('document'));
        Notifier::log($id, 'info', 'Mitra baru mendaftar', $biz);
        $this->signIn(['id' => $id, 'role' => 'mitra']);

        return redirect()->to('mitra/verifikasi');
    }

    public function mitraVerifikasi()
    {
        return $this->me()['verification_status'] === 'verified'
            ? redirect()->to('mitra/dashboard')
            : view('auth/mitra_verifikasi', ['title' => 'Verifikasi Mitra']);
    }

    public function doMitraVerifikasi()
    {
        if (! $this->validate(FormRules::mitraVerifikasi())) {
            return $this->backWith('mitra/verifikasi', $this->validator->getErrors());
        }
        $id = (int) session('user')['id'];
        foreach (['ktp', 'sim', 'selfie'] as $t) {
            VerificationService::store($id, $t, $this->request->getFile($t));
        }
        Store::update('users', $id, [
            'business_name' => trim((string) $this->request->getPost('rental')), 'business_address' => trim((string) $this->request->getPost('address')),
            'whatsapp' => Drivana::normalizePhone((string) $this->request->getPost('whatsapp')), 'bank_account' => (string) $this->request->getPost('account'), 'updated_at' => Drivana::now(),
        ]);

        return redirect()->to('mitra/dashboard')->with('success', 'Data terkirim. Tim Drivana akan memeriksanya.');
    }
}
