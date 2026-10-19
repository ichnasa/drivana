<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
use App\Libraries\Drivana;
$editing = $car !== null;
$v = fn (string $k, $d = '') => old_input($k, (string) ($car[$k] ?? $d));
$driverVal = old_input('driver', (string) ($car ? ($car['driver_only'] ? 2 : ($car['with_driver'] ? 1 : 0)) : 0));
$select = function (string $name, string $label, array $opts, string $icon, string $default) use ($v) {
    $cur = $v($name, $default);
    $err = form_error($name);
    ob_start(); ?>
    <div class="field"><label for="<?= $name ?>"><?= $label ?></label>
        <div class="input<?= $err ? ' has-error' : '' ?>"><?= icon($icon, 20) ?><select id="<?= $name ?>" name="<?= $name ?>" required aria-describedby="<?= $name ?>-error" data-label="<?= esc($label, 'attr') ?>"><?php foreach ($opts as $o): ?><option <?= (string) $cur === (string) $o ? 'selected' : '' ?>><?= esc((string) $o) ?></option><?php endforeach ?></select></div>
        <p class="field-error" id="<?= $name ?>-error" role="alert"><?= $err ? icon('circle-alert', 16) . ' ' . esc($err) : '' ?></p></div>
    <?php return ob_get_clean();
};
?>

<?= partial('components/topbar', ['title' => $title, 'back' => site_url('mitra/armada')]) ?>

<form class="page has-cta form" action="<?= site_url('mitra/armada/simpan') ?>" method="post" enctype="multipart/form-data" style="padding-top:20px" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $editing ? $car['id'] : '' ?>">
    <input type="hidden" name="driver" id="v-driver" value="<?= esc($driverVal, 'attr') ?>">
    <?= partial('components/alerts') ?>
    <?php if ($editing && $car['listing_status'] === 'rejected'): ?><div class="banner banner-error"><?= icon('circle-alert', 20) ?><div><b>Ditolak admin</b><p><?= esc($car['reject_reason']) ?> Perbaiki lalu simpan untuk mengajukan ulang.</p></div></div><?php endif ?>
    <?php if (! $editing): ?><div class="banner banner-info"><?= icon('info', 20) ?><div><b>Ditinjau admin dulu</b><p>Mobil baru tampil di pencarian setelah disetujui.</p></div></div><?php endif ?>

    <?= partial('components/field', ['label' => 'Nama Kendaraan', 'name' => 'name', 'placeholder' => 'Contoh: Toyota Fortuner', 'value' => $car['name'] ?? '', 'attrs' => ['minlength' => 3, 'maxlength' => 80]]) ?>
    <div class="date-grid">
        <?= partial('components/field', ['label' => 'Tahun', 'name' => 'year', 'placeholder' => '2022', 'value' => (string) ($car['year'] ?? ''), 'attrs' => ['inputmode' => 'numeric', 'pattern' => '(19[5-9][0-9]|20[0-9]{2})', 'data-error' => 'Tahun tidak valid. Contoh: 2022.']]) ?>
        <?= partial('components/field', ['label' => 'Plat Nomor', 'name' => 'plate', 'placeholder' => 'B 1234 ABC', 'value' => $car['plate'] ?? '', 'attrs' => ['maxlength' => 12, 'style' => 'text-transform:uppercase', 'pattern' => '[A-Za-z]{1,2}\\s?[0-9]{1,4}\\s?[A-Za-z]{0,3}', 'data-error' => 'Format plat tidak valid. Contoh: B 1234 ABC.']]) ?>
    </div>
    <?= $select('type', 'Tipe Mobil', Drivana::CAR_TYPES, 'car', 'SUV') ?>
    <div class="date-grid">
        <?= $select('seats', 'Kapasitas Penumpang', Drivana::SEAT_OPTIONS, 'users', '5') ?>
        <?= $select('transmission', 'Transmisi', Drivana::TRANSMISSION, 'settings-2', 'Automatic') ?>
    </div>
    <?= $select('fuel', 'BBM', Drivana::FUEL, 'fuel', 'Bensin') ?>
    <?= $select('area', 'Wilayah', Drivana::AREAS, 'map-pin', $me['area'] ?: 'Jakarta Selatan') ?>

    <div class="field">
        <label>Dengan Pengemudi?</label>
        <div class="choice-grid" data-choice-group data-choice-input="#v-driver" style="grid-template-columns:1fr 1fr 1fr">
            <?php foreach (['0' => ['Lepas kunci', 'Tanpa sopir'], '1' => ['Opsional', '+Rp 150rb/hari'], '2' => ['Wajib', 'Termasuk sopir']] as $k => [$t, $s]): ?>
                <button type="button" class="choice <?= $driverVal === (string) $k ? 'is-active' : '' ?>" data-value="<?= $k ?>"><b style="font-size:14px"><?= $t ?></b><small style="font-size:12px"><?= $s ?></small></button>
            <?php endforeach ?>
        </div>
        <p class="field-error" role="alert"><?= form_error('driver') ? icon('circle-alert', 16) . ' ' . esc(form_error('driver')) : '' ?></p>
    </div>

    <?= partial('components/field', ['label' => 'Harga Sewa', 'name' => 'price', 'placeholder' => '350000', 'value' => (string) ($car['price'] ?? ''), 'hint' => 'Angka saja tanpa titik. Minimal Rp 50.000.', 'attrs' => ['inputmode' => 'numeric', 'pattern' => '[0-9]{5,9}', 'data-error' => 'Isi angka saja tanpa titik. Contoh: 350000.', 'data-min' => 50000, 'data-min-error' => 'Harga sewa minimal Rp 50.000 per hari.']]) ?>
    <?= partial('components/field', ['label' => 'Deskripsi (opsional)', 'name' => 'description', 'icon' => 'text', 'required' => false, 'value' => $car['description'] ?? '', 'attrs' => ['maxlength' => 400]]) ?>

    <div class="field">
        <label>Gambar Kendaraan</label>
        <?php if ($editing): ?><div class="photo" style="height:140px;border-radius:12px;margin-bottom:12px"><img src="<?= car_img($car['image']) ?>" alt=""></div><?php endif ?>
        <label class="upload<?= form_error('image') ? ' has-error' : '' ?>" data-upload>
            <?= icon('image-plus', 24) ?><b data-upload-label><?= $editing ? 'Ganti foto (opsional)' : 'Pilih foto' ?></b><small>JPG atau PNG · maks. 5 MB</small>
            <input type="file" name="image" accept="image/*" data-max-mb="5" data-label="Foto kendaraan" <?= $editing ? '' : 'required' ?> hidden>
        </label>
        <p class="field-error" role="alert"><?= form_error('image') ? icon('circle-alert', 16) . ' ' . esc(form_error('image')) : '' ?></p>
    </div>

    <div class="sticky-cta"><button class="btn btn-primary" type="submit"><span><?= $editing ? 'Simpan Perubahan' : 'Tambah Kendaraan' ?></span></button></div>
</form>

<?= $this->endSection() ?>
