<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="logo-box"><img src="<?= base_url('assets/img/logo.png') ?>" alt="Drivana"></div>
<h1 class="title">Buat akun Baru</h1>
<p class="lead">Daftar sekarang, gratis!</p>

<?= partial('components/alerts') ?>

<form action="<?= site_url('register') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <?= partial('components/field', ['label' => 'Nama lengkap', 'name' => 'name', 'icon' => 'user', 'placeholder' => 'Sesuai KTP', 'attrs' => ['autocomplete' => 'name', 'minlength' => 3, 'maxlength' => 60]]) ?>
    <?= partial('components/field', ['label' => 'Email', 'name' => 'email', 'icon' => 'mail', 'type' => 'email', 'placeholder' => 'nama@email.com', 'attrs' => ['autocomplete' => 'email', 'maxlength' => 100, 'data-error' => 'Format email tidak valid. Contoh: nama@email.com.']]) ?>
    <?= partial('components/field', ['label' => 'No. HP', 'name' => 'phone', 'icon' => 'phone', 'type' => 'tel', 'placeholder' => '081234567890', 'attrs' => ['autocomplete' => 'tel', 'inputmode' => 'tel', 'pattern' => '(\+62|62|0)8[0-9]{8,12}', 'data-error' => 'Nomor HP tidak valid. Contoh: 081234567890.']]) ?>
    <?= partial('components/field', ['label' => 'Password', 'name' => 'password', 'icon' => 'lock', 'type' => 'password', 'placeholder' => 'Min. 8 karakter', 'toggle' => true, 'hint' => 'Minimal 8 karakter, kombinasi huruf dan angka.', 'attrs' => ['autocomplete' => 'new-password', 'minlength' => 8, 'maxlength' => 72, 'pattern' => '(?=.*[A-Za-z])(?=.*\d).+', 'data-error' => 'Password harus berisi huruf dan angka.']]) ?>

    <button class="btn btn-primary" type="submit" style="margin-top:8px"><span>Daftar Sekarang</span></button>
</form>

<p class="foot">Sudah punya akun? <a href="<?= site_url('login') ?>">Masuk</a></p>

<?= $this->endSection() ?>
