<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="logo-box"><img src="<?= base_url('assets/img/logo.png') ?>" alt="Drivana"></div>

<h1 class="title">Masuk dengan OTP</h1>
<p class="lead">Kami kirim kode 6 angka ke nomor HP yang terdaftar.</p>

<?= partial('components/alerts') ?>

<form action="<?= site_url($base . '/otp') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <?= partial('components/field', ['label' => $label, 'name' => 'identity', 'icon' => 'mail', 'placeholder' => 'nama@email.com atau 0812…', 'attrs' => [
        'autocomplete' => 'username', 'maxlength' => 100,
        'pattern' => '([^\s@]+@[^\s@]+\.[^\s@]+)|((\+62|62|0)8[0-9]{8,12})', 'data-error' => 'Isi email atau nomor HP yang valid.',
    ]]) ?>
    <button class="btn btn-primary" type="submit"><span>Kirim kode OTP</span></button>
</form>

<a class="btn btn-secondary" style="margin-top:12px" href="<?= site_url($base) ?>"><?= icon('lock', 20) ?> Masuk dengan password</a>

<?= $this->endSection() ?>
