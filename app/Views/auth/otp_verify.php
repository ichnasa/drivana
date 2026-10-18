<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>
<?php $tail = substr((string) $phone, -4); ?>

<div class="logo-box"><img src="<?= base_url('assets/img/logo.png') ?>" alt="Drivana"></div>

<h1 class="title">Masukkan kode OTP</h1>
<p class="lead">Kode dikirim ke nomor HP berakhiran <b><?= esc($tail) ?></b>. Berlaku 5 menit.</p>

<?= partial('components/alerts') ?>

<div class="notice"><?= icon('smartphone', 18) ?> Kode OTP kamu: <b style="letter-spacing:.2em"><?= esc($otp) ?></b></div>

<form action="<?= site_url($base . '/otp/kode') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <?= partial('components/field', ['label' => 'Kode OTP', 'name' => 'code', 'icon' => 'key-round', 'placeholder' => '6 angka', 'attrs' => [
        'autocomplete' => 'one-time-code', 'inputmode' => 'numeric', 'maxlength' => 6, 'minlength' => 6, 'pattern' => '[0-9]{6}', 'data-error' => 'Kode OTP terdiri dari 6 angka.',
    ]]) ?>
    <button class="btn btn-primary" type="submit"><span>Verifikasi &amp; masuk</span></button>
</form>

<p class="foot">Salah nomor? <a href="<?= site_url($base . '/otp') ?>">Ganti akun</a></p>

<?= $this->endSection() ?>
