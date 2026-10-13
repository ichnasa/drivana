<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>
<?php $docErr = form_error('document'); ?>

<a class="icon-btn" href="<?= site_url('mitra/login') ?>" aria-label="Kembali" style="margin-left:-8px"><?= icon('arrow-left', 22) ?></a>
<span class="badge badge-black" style="margin-top:20px;align-self:flex-start">Portal Mitra</span>
<h1 class="title" style="margin-top:12px">Daftar jadi mitra</h1>
<p class="lead">Pasang armadamu di Drivana.</p>

<?= partial('components/alerts') ?>

<form action="<?= site_url('mitra/register') ?>" method="post" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <?= partial('components/field', ['label' => 'Nama bisnis / usaha', 'name' => 'business', 'icon' => 'store', 'placeholder' => 'Nama rental kamu', 'attrs' => ['minlength' => 3, 'maxlength' => 80]]) ?>
    <?= partial('components/field', ['label' => 'Email bisnis', 'name' => 'email', 'icon' => 'mail', 'type' => 'email', 'placeholder' => 'email@bisnis.com', 'attrs' => ['autocomplete' => 'email', 'maxlength' => 100, 'data-error' => 'Format email tidak valid. Contoh: nama@email.com.']]) ?>
    <?= partial('components/field', ['label' => 'No. HP bisnis', 'name' => 'phone', 'icon' => 'phone', 'type' => 'tel', 'placeholder' => '081234567890', 'attrs' => ['inputmode' => 'tel', 'pattern' => '(\+62|62|0)8[0-9]{8,12}', 'data-error' => 'Nomor HP tidak valid. Contoh: 081234567890.']]) ?>
    <?= partial('components/field', ['label' => 'Password', 'name' => 'password', 'icon' => 'lock', 'type' => 'password', 'placeholder' => 'Min. 8 karakter', 'toggle' => true, 'hint' => 'Minimal 8 karakter, kombinasi huruf dan angka.', 'attrs' => ['autocomplete' => 'new-password', 'minlength' => 8, 'maxlength' => 72, 'pattern' => '(?=.*[A-Za-z])(?=.*\d).+', 'data-error' => 'Password harus berisi huruf dan angka.']]) ?>

    <div class="field">
        <label class="upload<?= $docErr ? ' has-error' : '' ?>" data-upload>
            <?= icon('file-up', 24) ?>
            <b data-upload-label>Upload dokumen bisnis</b>
            <small>SIUP / NIB / KTP pemilik · JPG, PNG, PDF · maks. 5 MB</small>
            <input type="file" name="document" accept="image/*,application/pdf" data-types="jpeg,png,pdf" data-max-mb="5" data-label="Dokumen bisnis" required hidden>
        </label>
        <p class="field-error" role="alert"><?= $docErr ? icon('circle-alert', 16) . ' ' . esc($docErr) : '' ?></p>
    </div>

    <button class="btn btn-primary" type="submit"><span>Daftar</span></button>
</form>

<p class="foot">Sudah punya akun mitra? <a href="<?= site_url('mitra/login') ?>">Masuk</a></p>

<?= $this->endSection() ?>
