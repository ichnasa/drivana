<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php use App\Libraries\Drivana; $mitra = $me['role'] === 'mitra'; $v = fn (string $k) => old_input($k, (string) ($me[$k] ?? '')); ?>

<?= partial('components/topbar', ['title' => 'Edit profil', 'back' => site_url($p . 'profil')]) ?>

<form class="page has-cta" action="<?= site_url($p . 'profil/edit') ?>" method="post" style="padding-top:20px" novalidate>
    <?= csrf_field() ?>
    <?= partial('components/alerts') ?>
    <?php if ($mitra): ?>
        <?= partial('components/field', ['label' => 'Nama bisnis', 'name' => 'business_name', 'icon' => 'store', 'value' => $me['business_name'], 'attrs' => ['minlength' => 3, 'maxlength' => 80]]) ?>
        <?= partial('components/field', ['label' => 'Alamat bisnis', 'name' => 'business_address', 'icon' => 'map-pin', 'value' => $me['business_address'], 'attrs' => ['minlength' => 10, 'maxlength' => 200]]) ?>
    <?php endif ?>
    <?= partial('components/field', ['label' => $mitra ? 'Nama pemilik' : 'Nama lengkap', 'name' => 'name', 'icon' => 'user', 'value' => $me['name'], 'attrs' => ['minlength' => 3, 'maxlength' => 60]]) ?>
    <?= partial('components/field', ['label' => 'No. HP', 'name' => 'phone', 'icon' => 'phone', 'type' => 'tel', 'value' => $me['phone'], 'attrs' => ['inputmode' => 'tel', 'pattern' => '(\+62|62|0)8[0-9]{8,12}', 'data-error' => 'Nomor HP tidak valid. Contoh: 081234567890.']]) ?>
    <?php if ($mitra): ?>
        <?= partial('components/field', ['label' => 'Nomor WhatsApp', 'name' => 'whatsapp', 'icon' => 'message-circle', 'type' => 'tel', 'value' => $me['whatsapp'], 'attrs' => ['inputmode' => 'tel', 'pattern' => '(\+62|62|0)8[0-9]{8,12}', 'data-error' => 'Nomor HP tidak valid. Contoh: 081234567890.']]) ?>
        <?= partial('components/field', ['label' => 'Nomor rekening', 'name' => 'bank_account', 'icon' => 'landmark', 'value' => $me['bank_account'], 'hint' => 'Untuk pencairan pendapatan.', 'attrs' => ['inputmode' => 'numeric', 'pattern' => '[0-9]{8,16}', 'data-error' => 'Nomor rekening 8–16 digit, hanya angka.']]) ?>
    <?php else: ?>
        <?= partial('components/field', ['label' => 'Alamat', 'name' => 'address', 'icon' => 'house', 'required' => false, 'value' => $me['address'] ?? '', 'attrs' => ['maxlength' => 200]]) ?>
    <?php endif ?>
    <div class="field"><label for="area">Wilayah</label><div class="input"><?= icon('map-pin', 20) ?><select id="area" name="area"><option value="">Pilih wilayah</option><?php foreach (Drivana::AREAS as $a): ?><option <?= $v('area') === $a ? 'selected' : '' ?>><?= esc($a) ?></option><?php endforeach ?></select></div></div>
    <?php if ($mitra): ?><input type="hidden" name="address" value="<?= esc($me['address'] ?? '', 'attr') ?>"><?php endif ?>
    <div class="sticky-cta"><button class="btn btn-primary" type="submit"><span>Simpan perubahan</span></button></div>
</form>

<?= $this->endSection() ?>
