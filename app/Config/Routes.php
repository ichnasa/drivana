<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', static fn () => redirect()->to('login'));

// Public: sign in / sign up, demo reset, photo credits
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::doLogin');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::doRegister');
$routes->get('mitra/login', 'Auth::mitraLogin');
$routes->post('mitra/login', 'Auth::doMitraLogin');
$routes->get('mitra/register', 'Auth::mitraRegister');
$routes->post('mitra/register', 'Auth::doMitraRegister');
$routes->get('login/otp', 'Auth::otp/customer');
$routes->post('login/otp', 'Auth::doOtp/customer');
$routes->get('login/otp/kode', 'Auth::otpKode/customer');
$routes->post('login/otp/kode', 'Auth::doOtpKode/customer');
$routes->get('mitra/login/otp', 'Auth::otp/mitra');
$routes->post('mitra/login/otp', 'Auth::doOtp/mitra');
$routes->get('mitra/login/otp/kode', 'Auth::otpKode/mitra');
$routes->post('mitra/login/otp/kode', 'Auth::doOtpKode/mitra');
$routes->post('logout', 'Auth::logout');
$routes->post('demo/reset', 'Demo::reset');
$routes->get('kredit', 'Pages::kredit');

// Any signed-in user: private documents + payment receipts + the payment gateway
$routes->group('', ['filter' => 'auth:any'], static function ($routes) {
    $routes->get('berkas/(:num)', 'Files::document/$1');
    $routes->get('bukti/(:segment)', 'Orders::bukti/$1');
    $routes->get('gateway/(:segment)', 'Orders::gateway/$1');
    $routes->post('gateway/(:segment)/(berhasil|batal)', 'Orders::gatewayResult/$1/$2');
});

// ───────── Customer ─────────
$routes->group('', ['filter' => 'auth:customer'], static function ($routes) {
    $routes->get('verifikasi', 'Auth::verifikasi');
    $routes->post('verifikasi', 'Auth::doVerifikasi');

    $routes->get('beranda', 'Customer::beranda');
    $routes->get('cari', 'Customer::cari');
    $routes->get('filter', 'Customer::filter');
    $routes->get('mobil/(:num)', 'Customer::detail/$1');
    $routes->get('booking/(:num)', 'Customer::booking/$1');
    $routes->post('booking/(:num)', 'Customer::doBooking/$1');

    $routes->get('bayar/(:segment)', 'Orders::checkout/$1');
    $routes->post('bayar/(:segment)', 'Orders::pay/$1');
    $routes->get('pesanan', 'Orders::index');
    $routes->get('pesanan/(:segment)', 'Orders::show/$1');
    $routes->post('pesanan/(:segment)/batal', 'Orders::cancel/$1');
    $routes->post('pesanan/(:segment)/ulasan', 'Orders::review/$1');

    $routes->get('chat', 'Chat::index');
    $routes->get('chat/mulai/(:num)', 'Chat::start/$1');
    $routes->get('chat/pengemudi/(:segment)', 'Chat::startDriver/$1');
    $routes->get('chat/(:num)', 'Chat::show/$1');
    $routes->post('chat/(:num)/kirim', 'Chat::send/$1');
    $routes->get('chat/(:num)/pesan', 'Chat::poll/$1');

    $routes->get('profil', 'Account::profil');
    $routes->get('profil/edit', 'Account::edit');
    $routes->post('profil/edit', 'Account::update');
    $routes->get('profil/dokumen', 'Account::dokumen');
    $routes->post('profil/dokumen/(:segment)', 'Account::uploadDokumen/$1');
    $routes->get('profil/keamanan', 'Account::keamanan');
    $routes->post('profil/keamanan', 'Account::ubahPassword');
    $routes->get('notifikasi', 'Account::notifikasi');
    $routes->get('bantuan', 'Account::bantuan');
    $routes->post('bantuan/keluhan', 'Account::keluhan');
});

// ───────── Mitra ─────────
$routes->group('mitra', ['filter' => 'auth:mitra'], static function ($routes) {
    $routes->get('verifikasi', 'Auth::mitraVerifikasi');
    $routes->post('verifikasi', 'Auth::doMitraVerifikasi');

    $routes->get('dashboard', 'Mitra::dashboard');
    $routes->get('armada', 'Mitra::armada');
    $routes->get('armada/tambah', 'Mitra::tambah');
    $routes->get('armada/(:num)/edit', 'Mitra::edit/$1');
    $routes->post('armada/simpan', 'Mitra::simpan');
    $routes->post('armada/(:num)/status', 'Mitra::status/$1');
    $routes->post('armada/(:num)/hapus', 'Mitra::hapus/$1');
    $routes->get('reservasi', 'Mitra::reservasi');
    $routes->get('reservasi/(:segment)', 'Mitra::reservasiDetail/$1');
    $routes->post('reservasi/(:segment)/(konfirmasi|tolak|mulai|selesai)', 'Mitra::aksi/$1/$2');

    $routes->get('pesan', 'Chat::index');
    $routes->get('pesan/mulai/(:num)', 'Chat::startMitra/$1');
    $routes->get('pesan/(:num)', 'Chat::show/$1');
    $routes->post('pesan/(:num)/kirim', 'Chat::send/$1');
    $routes->get('pesan/(:num)/pesan', 'Chat::poll/$1');

    $routes->get('langganan', 'MitraPremium::langganan');
    $routes->post('langganan/beli', 'MitraPremium::beliLangganan');
    $routes->get('pendapatan', 'MitraPremium::pendapatan');

    $routes->get('profil', 'Account::profil');
    $routes->get('profil/edit', 'Account::edit');
    $routes->post('profil/edit', 'Account::update');
    $routes->get('profil/dokumen', 'Account::dokumen');
    $routes->post('profil/dokumen/(:segment)', 'Account::uploadDokumen/$1');
    $routes->get('profil/keamanan', 'Account::keamanan');
    $routes->post('profil/keamanan', 'Account::ubahPassword');
    $routes->get('notifikasi', 'Account::notifikasi');
    $routes->get('bantuan', 'Account::bantuan');
    $routes->post('bantuan/keluhan', 'Account::keluhan');
});

// Mitra premium features (need an active subscription)
$routes->group('mitra', ['filter' => ['auth:mitra', 'premium']], static function ($routes) {
    $routes->get('boost', 'MitraPremium::boost');
    $routes->post('boost/beli', 'MitraPremium::beliBoost');
    $routes->get('rekomendasi-harga', 'MitraPremium::harga');
    $routes->post('rekomendasi-harga/terapkan', 'MitraPremium::terapkanHarga');
    $routes->get('analitik', 'MitraPremium::analitik');
});

// ───────── Admin ─────────
$routes->group('admin', ['filter' => 'auth:admin'], static function ($routes) {
    $routes->get('dashboard', 'Admin::dashboard');
    $routes->get('verifikasi', 'Admin::verifikasi');
    $routes->post('verifikasi/(:num)/setuju', 'Admin::setujuiDokumen/$1');
    $routes->post('verifikasi/(:num)/tolak', 'Admin::tolakDokumen/$1');
    $routes->get('mobil', 'Admin::mobil');
    $routes->post('mobil/(:num)/setujui', 'Admin::setujuiMobil/$1');
    $routes->post('mobil/(:num)/tolak', 'Admin::tolakMobil/$1');
    $routes->get('transaksi', 'Admin::transaksi');
    $routes->get('transaksi/(:segment)', 'Admin::transaksiDetail/$1');
    $routes->get('pengguna', 'Admin::pengguna');
    $routes->post('pengguna/(:num)/status', 'Admin::statusPengguna/$1');
    $routes->get('keluhan', 'Admin::keluhan');
    $routes->post('keluhan/(:num)/balas', 'Admin::balasKeluhan/$1');
    $routes->get('log', 'Admin::log');
    $routes->get('notifikasi', 'Account::notifikasi');
});
