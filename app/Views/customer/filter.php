<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
use App\Libraries\Drivana;
$types = array_values(array_diff(Drivana::CAR_TYPES, ['Luxury']));
$cur = fn (string $k, string $d = '') => old_input($k, $_GET[$k] ?? $d);
$err = fn (string $k) => form_error($k);
?>

<?= partial('components/topbar', ['title' => 'Filter Mobil', 'back' => site_url('cari')]) ?>

<form class="page has-cta stack" action="<?= site_url('cari') ?>" method="get" style="padding-top:20px" novalidate data-gte-pair="#f-min,#f-max">
    <?= partial('components/alerts') ?>
    <input type="hidden" name="type" id="f-type" value="<?= esc($cur('type'), 'attr') ?>">
    <input type="hidden" name="seats" id="f-seats" value="<?= esc($cur('seats'), 'attr') ?>">
    <input type="hidden" name="driver" id="f-driver" value="<?= esc($cur('driver'), 'attr') ?>">

    <div class="panel">
        <h4>Tipe Mobil</h4>
        <div class="pills" data-choice-group data-choice-input="#f-type" data-toggle>
            <?php foreach ($types as $t): ?><button type="button" class="chip <?= $cur('type') === $t ? 'is-active' : '' ?>" data-value="<?= esc($t, 'attr') ?>"><?= esc($t) ?></button><?php endforeach ?>
        </div>
    </div>

    <div class="panel">
        <h4>Kapasitas Penumpang</h4>
        <div class="pills" data-choice-group data-choice-input="#f-seats" data-toggle>
            <?php foreach ([2, 5, 7] as $s): ?><button type="button" class="chip <?= (string) $cur('seats') === (string) $s ? 'is-active' : '' ?>" data-value="<?= $s ?>"><?= $s ?></button><?php endforeach ?>
        </div>
    </div>

    <div class="panel">
        <h4>Rentang Harga</h4>
        <div class="date-grid">
            <div class="field" style="margin:0"><label for="f-min">Harga Min</label><div class="input<?= $err('min') ? ' has-error' : '' ?>"><input id="f-min" name="min" inputmode="numeric" pattern="[0-9]*" data-error="Isi angka saja." placeholder="Rp 0" value="<?= esc($cur('min'), 'attr') ?>" aria-describedby="f-min-error"></div><p class="field-error" id="f-min-error" role="alert"><?= $err('min') ? esc($err('min')) : '' ?></p></div>
            <div class="field" style="margin:0"><label for="f-max">Harga Max</label><div class="input<?= $err('max') ? ' has-error' : '' ?>"><input id="f-max" name="max" inputmode="numeric" pattern="[0-9]*" data-error="Isi angka saja." data-gte="#f-min" data-gte-error="Harga max harus lebih besar dari harga min." placeholder="Rp 0" value="<?= esc($cur('max'), 'attr') ?>" aria-describedby="f-max-error"></div><p class="field-error" id="f-max-error" role="alert"><?= $err('max') ? esc($err('max')) : '' ?></p></div>
        </div>
    </div>

    <div class="panel">
        <h4>Dengan Pengemudi?</h4>
        <div class="pills" style="display:grid;grid-template-columns:1fr 1fr" data-choice-group data-choice-input="#f-driver" data-toggle>
            <button type="button" class="chip <?= $cur('driver') === '1' ? 'is-active' : '' ?>" style="justify-content:center;height:44px" data-value="1">Ya</button>
            <button type="button" class="chip <?= $cur('driver') === '0' ? 'is-active' : '' ?>" style="justify-content:center;height:44px" data-value="0">Tidak</button>
        </div>
    </div>

    <div class="sticky-cta" style="display:flex;gap:12px"><a class="btn btn-secondary" style="width:auto" href="<?= site_url('filter') ?>">Reset</a><button class="btn btn-primary" type="submit"><span>Terapkan Filter</span></button></div>
</form>

<?= $this->endSection() ?>
