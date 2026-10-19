<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="logo-box"><img src="<?= base_url('assets/img/logo.png') ?>" alt="Drivana"></div>
<span class="badge badge-black" style="margin-top:24px;align-self:flex-start">Portal Mitra</span>
<h1 class="title" style="margin-top:12px">Drivana</h1>
<p class="lead">Masuk ke akun mitra kamu</p>

<?= partial('components/alerts') ?>

<form action="<?= site_url('mitra/login') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <?= partial('components/field', ['label' => 'Email Bisnis', 'name' => 'identity', 'icon' => 'mail', 'placeholder' => 'email@bisnis.com', 'attrs' => [
        'autocomplete' => 'username', 'maxlength' => 100,
        'pattern' => '([^\s@]+@[^\s@]+\.[^\s@]+)|((\+62|62|0)8[0-9]{8,12})', 'data-error' => 'Isi email atau nomor HP yang valid.',
    ]]) ?>
    <?= partial('components/field', ['label' => 'Password', 'name' => 'password', 'icon' => 'lock', 'type' => 'password', 'placeholder' => 'Masukkan password', 'toggle' => true, 'attrs' => ['autocomplete' => 'current-password', 'minlength' => 6, 'maxlength' => 72]]) ?>
    <a class="link-right" href="#">Lupa Password?</a>
    <button class="btn btn-primary" type="submit"><span>Masuk Sebagai Mitra</span></button>
</form>
<a class="btn btn-secondary" style="margin-top:12px" href="<?= site_url('mitra/login/otp') ?>"><?= icon('key-round', 20) ?> Masuk dengan kode OTP</a>

<div class="notice"><?= icon('info', 18) ?> Khusus pemilik armada terdaftar. Pastikan akun kamu sudah diverifikasi oleh tim Drivana.</div>

<?= partial('components/demo', ['demo' => $demo ?? []]) ?>

<p class="foot">Belum terdaftar sebagai mitra? <a href="<?= site_url('mitra/register') ?>">Daftar Mitra</a></p>

<?= $this->endSection() ?>
