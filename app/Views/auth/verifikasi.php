<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>
<?php $selfieErr = form_error('selfie'); $confirmErr = form_error('confirm'); ?>

<form action="<?= site_url('verifikasi') ?>" method="post" enctype="multipart/form-data" data-steps novalidate>
    <?= csrf_field() ?>

    <div class="logo-box icon-box"><?= icon('shield-check', 32) ?></div>
    <?= partial('components/stepper') ?>
    <div style="margin-top:24px"><?= partial('components/alerts', ['summary' => true]) ?></div>

    <!-- 1: KTP + SIM -->
    <section class="step-pane" data-step-pane>
        <h1 class="title">Verifikasi identitas</h1>
        <p class="lead">Foto KTP dan SIM kamu.</p>
        <div style="margin-top:24px">
            <?= partial('components/upload', ['name' => 'ktp', 'label' => 'Foto KTP', 'sub' => 'JPG atau PNG · maks. 5 MB']) ?>
            <?= partial('components/upload', ['name' => 'sim', 'label' => 'Foto SIM', 'sub' => 'JPG atau PNG · maks. 5 MB']) ?>
            <p class="field-hint">Seluruh sisi dokumen harus terlihat dan tidak buram.</p>
        </div>
        <button class="btn btn-primary" type="button" data-next style="margin-top:24px">Lanjut</button>
    </section>

    <!-- 2: Selfie -->
    <section class="step-pane" data-step-pane>
        <h1 class="title">Selfie</h1>
        <p class="lead">Wajah terlihat jelas, tanpa masker.</p>
        <div class="field" style="margin-top:24px">
            <label class="selfie<?= $selfieErr ? ' has-error' : '' ?>" data-upload style="cursor:pointer">
                <span class="oval"><?= icon('camera', 36) ?></span>
                <input type="file" name="selfie" accept="image/*" capture="user" data-max-mb="5" data-label="Foto selfie" required hidden>
                <b data-upload-label style="position:absolute;bottom:16px">Ambil selfie</b>
            </label>
            <p class="field-error" role="alert"><?= $selfieErr ? icon('circle-alert', 16) . ' ' . esc($selfieErr) : '' ?></p>
        </div>
        <ul class="tips" style="list-style:none;padding:0;margin:16px 0 0">
            <li><?= icon('check', 16) ?> Lepas masker</li>
            <li><?= icon('check', 16) ?> Hindari pencahayaan gelap</li>
            <li><?= icon('check', 16) ?> Jangan pakai filter</li>
        </ul>
        <div class="actions" style="margin-top:24px">
            <button class="btn btn-secondary" type="button" data-prev>Kembali</button>
            <button class="btn btn-primary" type="button" data-next>Lanjut</button>
        </div>
    </section>

    <!-- 3: Confirm -->
    <section class="step-pane" data-step-pane>
        <h1 class="title">Cek sebelum kirim</h1>
        <p class="lead">Admin akan memeriksa dokumen ini.</p>
        <div style="margin-top:24px">
            <?php foreach (['Foto KTP' => 'id-card', 'Foto SIM' => 'car', 'Foto selfie' => 'scan-face'] as $doc => $ico): ?>
                <div class="file-row"><span class="icon-tile"><?= icon($ico, 20) ?></span><div><b><?= $doc ?></b><small>Siap dikirim</small></div><?= icon('circle-check', 22, 'check-ok') ?></div>
            <?php endforeach ?>
        </div>
        <div class="field" style="margin-top:24px">
            <label class="checkline"><input type="checkbox" name="confirm" value="1" required> Data yang saya kirim benar</label>
            <p class="field-error" role="alert"><?= $confirmErr ? icon('circle-alert', 16) . ' ' . esc($confirmErr) : '' ?></p>
        </div>
        <div class="actions" style="margin-top:8px">
            <button class="btn btn-secondary" type="button" data-prev>Kembali</button>
            <button class="btn btn-primary" type="submit"><span>Kirim verifikasi</span></button>
        </div>
    </section>
</form>

<?= $this->endSection() ?>
