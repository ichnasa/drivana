<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="logo-box"><img src="<?= base_url('assets/img/logo.png') ?>" alt="Drivana"></div>

<h1 class="title">Masuk</h1>
<p class="lead">Pakai email atau nomor HP yang terdaftar.</p>

<?= partial('components/alerts') ?>

<form action="<?= site_url('login') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <?= partial('components/field', ['label' => 'Email / No. HP', 'name' => 'identity', 'icon' => 'mail', 'placeholder' => 'nama@email.com atau 0812…', 'attrs' => [
        'autocomplete' => 'username', 'maxlength' => 100,
        'pattern' => '([^\s@]+@[^\s@]+\.[^\s@]+)|((\+62|62|0)8[0-9]{8,12})', 'data-error' => 'Isi email atau nomor HP yang valid.',
    ]]) ?>
    <?= partial('components/field', ['label' => 'Password', 'name' => 'password', 'icon' => 'lock', 'type' => 'password', 'placeholder' => 'Masukkan password', 'toggle' => true, 'attrs' => [
        'autocomplete' => 'current-password', 'minlength' => 6, 'maxlength' => 72,
    ]]) ?>

    <a class="link-right" href="#">Lupa password?</a>
    <button class="btn btn-primary" type="submit"><span>Masuk</span></button>
</form>

<div class="divider">atau</div>
<a class="btn btn-secondary" href="<?= site_url('mitra/login') ?>"><?= icon('store', 20) ?> Masuk sebagai mitra</a>

<?= partial('components/demo', ['demo' => $demo ?? []]) ?>

<p class="foot">Belum punya akun? <a href="<?= site_url('register') ?>">Daftar</a></p>

<?= $this->endSection() ?>
