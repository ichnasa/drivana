<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="logo-box"><img src="<?= base_url('assets/img/logo.png') ?>" alt="Drivana"></div>

<h1 class="title">Sewa mobil online, mudah dan aman</h1>
<p class="lead">Drivana menghubungkan penyewa dengan mitra rental mobil terpercaya. Cari mobil, booking, dan bayar secara online.</p>

<div class="stack" style="margin:24px 0">
    <div class="notice"><?= icon('search', 18) ?> Cari mobil sesuai kapasitas, tipe, harga, dan pilihan pengemudi.</div>
    <div class="notice"><?= icon('shield-check', 18) ?> Pengguna dan mitra terverifikasi oleh tim Drivana.</div>
    <div class="notice"><?= icon('credit-card', 18) ?> Bayar lewat transfer bank, e-wallet, atau kartu.</div>
    <div class="notice"><?= icon('message-square', 18) ?> Chat langsung dengan mitra rental.</div>
</div>

<a class="btn btn-primary" href="<?= site_url('cari') ?>"><span>Cari Mobil</span></a>
<a class="btn btn-secondary" style="margin-top:12px" href="<?= site_url('login') ?>">Masuk</a>
<a class="btn btn-secondary" style="margin-top:12px" href="<?= site_url('register') ?>">Daftar Sekarang</a>

<div class="divider">atau</div>
<a class="btn btn-secondary" href="<?= site_url('mitra/register') ?>"><?= icon('store', 20) ?> Gabung Sebagai Mitra</a>

<?= $this->endSection() ?>
