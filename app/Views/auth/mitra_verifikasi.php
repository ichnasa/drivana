<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<form action="<?= site_url('mitra/verifikasi') ?>" method="post" enctype="multipart/form-data" data-steps novalidate>
    <?= csrf_field() ?>
    <div class="logo-box icon-box"><?= icon('shield-check', 32) ?></div>
    <?= partial('components/stepper') ?>
    <div style="margin-top:24px"><?= partial('components/alerts', ['summary' => true]) ?></div>

    <!-- 1 -->
    <section class="step-pane" data-step-pane>
        <h1 class="title">Verifikasi pemilik</h1>
        <p class="lead">KTP, SIM, dan selfie pemilik usaha.</p>
        <div style="margin-top:24px">
            <?= partial('components/upload', ['name' => 'ktp', 'label' => 'Foto KTP', 'sub' => 'Upload foto KTP kamu', 'icon' => 'id-card', 'variant' => 'row']) ?>
            <?= partial('components/upload', ['name' => 'sim', 'label' => 'Foto SIM', 'sub' => 'Upload foto SIM kamu', 'icon' => 'car', 'variant' => 'row']) ?>
            <?= partial('components/upload', ['name' => 'selfie', 'label' => 'Foto selfie', 'sub' => 'Upload foto selfie kamu', 'icon' => 'camera', 'variant' => 'row']) ?>
            <p class="field-hint">Dokumen harus jelas, tidak buram, dan sesuai identitas.</p>
        </div>
        <button class="btn btn-primary" type="button" data-next style="margin-top:24px">Lanjut</button>
    </section>

    <!-- 2 -->
    <section class="step-pane" data-step-pane>
        <h1 class="title">Data usaha</h1>
        <p class="lead">Dipakai untuk operasional dan pembayaran.</p>
        <div style="margin-top:24px">
            <?= partial('components/field', ['label' => 'Nama rental', 'name' => 'rental', 'icon' => 'store', 'placeholder' => 'Rental Jaya Abadi', 'attrs' => ['minlength' => 3, 'maxlength' => 80]]) ?>
            <?= partial('components/field', ['label' => 'Alamat rental', 'name' => 'address', 'icon' => 'map-pin', 'placeholder' => 'Jl. Sudirman No. 12, Jakarta', 'attrs' => ['minlength' => 10, 'maxlength' => 200, 'autocomplete' => 'street-address']]) ?>
            <?= partial('components/field', ['label' => 'Nomor WhatsApp', 'name' => 'whatsapp', 'icon' => 'message-circle', 'type' => 'tel', 'placeholder' => '081234567890', 'attrs' => ['inputmode' => 'tel', 'pattern' => '(\+62|62|0)8[0-9]{8,12}', 'data-error' => 'Nomor HP tidak valid. Contoh: 081234567890.']]) ?>
            <?= partial('components/field', ['label' => 'Nomor rekening', 'name' => 'account', 'icon' => 'landmark', 'placeholder' => '8–16 digit', 'hint' => 'Dipakai untuk pencairan pendapatan.', 'attrs' => ['inputmode' => 'numeric', 'pattern' => '[0-9]{8,16}', 'data-error' => 'Nomor rekening 8–16 digit, hanya angka.']]) ?>
        </div>
        <div class="actions" style="margin-top:24px">
            <button class="btn btn-secondary" type="button" data-prev>Kembali</button>
            <button class="btn btn-primary" type="button" data-next>Lanjut</button>
        </div>
    </section>

    <!-- 3 -->
    <section class="step-pane" data-step-pane>
        <h1 class="title">Cek sebelum kirim</h1>
        <p class="lead">Tim Drivana akan memeriksa data ini.</p>
        <div style="margin-top:24px">
            <?php foreach ([['Foto KTP', 'Siap dikirim', 'id-card'], ['Foto SIM', 'Siap dikirim', 'car'], ['Foto selfie', 'Siap dikirim', 'camera'], ['Data usaha', 'Terisi lengkap', 'store']] as [$t, $s, $ico]): ?>
                <div class="file-row"><span class="icon-tile"><?= icon($ico, 20) ?></span><div><b><?= $t ?></b><small><?= $s ?></small></div><?= icon('circle-check', 22, 'check-ok') ?></div>
            <?php endforeach ?>
        </div>
        <div class="actions" style="margin-top:24px">
            <button class="btn btn-secondary" type="button" data-prev>Kembali</button>
            <button class="btn btn-primary" type="submit"><span>Kirim verifikasi</span></button>
        </div>
    </section>
</form>

<?= $this->endSection() ?>
