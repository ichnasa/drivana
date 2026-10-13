<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
use App\Libraries\Drivana;
$types = Drivana::CAR_TYPES;
$cur = fn (string $k, string $d = '') => old_input($k, $_GET[$k] ?? $d);
$err = fn (string $k) => form_error($k);
?>

<?= partial('components/topbar', ['title' => 'Filter mobil', 'back' => site_url('cari')]) ?>

<form class="page has-cta stack" action="<?= site_url('cari') ?>" method="get" style="padding-top:20px" novalidate data-gte-pair="#f-min,#f-max">
    <?= partial('components/alerts') ?>
    <input type="hidden" name="type" id="f-type" value="<?= esc($cur('type'), 'attr') ?>">
    <input type="hidden" name="seats" id="f-seats" value="<?= esc($cur('seats'), 'attr') ?>">
    <input type="hidden" name="driver" id="f-driver" value="<?= esc($cur('driver'), 'attr') ?>">

    <div class="panel">
        <h4>Tanggal sewa</h4>
        <div class="date-grid">
            <div class="field" style="margin:0"><label for="f-start">Mulai</label><div class="input<?= $err('start') ? ' has-error' : '' ?>"><?= icon('calendar', 18) ?><input id="f-start" type="date" name="start" value="<?= esc($cur('start'), 'attr') ?>" min="<?= date('Y-m-d') ?>" aria-describedby="f-start-error" data-error="Tanggal mulai tidak boleh sebelum hari ini."></div><p class="field-error" id="f-start-error" role="alert"><?= $err('start') ? esc($err('start')) : '' ?></p></div>
            <div class="field" style="margin:0"><label for="f-end">Selesai</label><div class="input<?= $err('end') ? ' has-error' : '' ?>"><?= icon('calendar', 18) ?><input id="f-end" type="date" name="end" value="<?= esc($cur('end'), 'attr') ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" aria-describedby="f-end-error" data-error="Tanggal selesai harus setelah tanggal mulai."></div><p class="field-error" id="f-end-error" role="alert"><?= $err('end') ? esc($err('end')) : '' ?></p></div>
        </div>
        <p class="field-hint">Isi keduanya untuk hanya melihat mobil yang tersedia di tanggal itu.</p>
    </div>

    <div class="panel">
        <h4>Tipe mobil</h4>
        <div class="pills" data-choice-group data-choice-input="#f-type" data-toggle>
            <?php foreach ($types as $t): ?><button type="button" class="chip <?= $cur('type') === $t ? 'is-active' : '' ?>" data-value="<?= esc($t, 'attr') ?>"><?= esc($t) ?></button><?php endforeach ?>
        </div>
    </div>

    <div class="panel">
        <h4>Minimal kursi</h4>
        <div class="pills" data-choice-group data-choice-input="#f-seats" data-toggle>
            <?php foreach (Drivana::SEAT_OPTIONS as $s): ?><button type="button" class="chip <?= (string) $cur('seats') === (string) $s ? 'is-active' : '' ?>" data-value="<?= $s ?>"><?= $s ?>+</button><?php endforeach ?>
        </div>
    </div>

    <div class="panel">
        <h4>Rentang harga / hari</h4>
        <div class="date-grid">
            <div class="field" style="margin:0"><label for="f-min">Harga min</label><div class="input<?= $err('min') ? ' has-error' : '' ?>"><input id="f-min" name="min" inputmode="numeric" pattern="[0-9]*" data-error="Isi angka saja." placeholder="Rp 0" value="<?= esc($cur('min'), 'attr') ?>" aria-describedby="f-min-error"></div><p class="field-error" id="f-min-error" role="alert"><?= $err('min') ? esc($err('min')) : '' ?></p></div>
            <div class="field" style="margin:0"><label for="f-max">Harga max</label><div class="input<?= $err('max') ? ' has-error' : '' ?>"><input id="f-max" name="max" inputmode="numeric" pattern="[0-9]*" data-error="Isi angka saja." data-gte="#f-min" data-gte-error="Harga max harus lebih besar dari harga min." placeholder="Rp 0" value="<?= esc($cur('max'), 'attr') ?>" aria-describedby="f-max-error"></div><p class="field-error" id="f-max-error" role="alert"><?= $err('max') ? esc($err('max')) : '' ?></p></div>
        </div>
    </div>

    <div class="panel">
        <h4>Pengemudi</h4>
        <div class="choice-grid" data-choice-group data-choice-input="#f-driver" data-toggle>
            <button type="button" class="choice <?= $cur('driver') === '1' ? 'is-active' : '' ?>" data-value="1"><b>Dengan pengemudi</b></button>
            <button type="button" class="choice <?= $cur('driver') === '0' ? 'is-active' : '' ?>" data-value="0"><b>Lepas kunci</b></button>
        </div>
    </div>

    <div class="panel">
        <h4>Wilayah dan urutan</h4>
        <div class="field"><label for="f-area">Wilayah</label><div class="input"><?= icon('map-pin', 18) ?><select id="f-area" name="area"><option value="">Semua wilayah</option><?php foreach (Drivana::AREAS as $a): ?><option <?= $cur('area') === $a ? 'selected' : '' ?>><?= esc($a) ?></option><?php endforeach ?></select></div></div>
        <div class="field" style="margin:0"><label for="f-sort">Urutkan</label><div class="input"><?= icon('arrow-up-down', 18) ?><select id="f-sort" name="sort"><?php foreach (['rekomendasi' => 'Rekomendasi', 'termurah' => 'Termurah', 'termahal' => 'Termahal', 'rating' => 'Rating tertinggi'] as $k => $l): ?><option value="<?= $k ?>" <?= $cur('sort', 'rekomendasi') === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach ?></select></div></div>
    </div>

    <div class="sticky-cta" style="display:flex;gap:12px"><a class="btn btn-secondary" style="width:auto" href="<?= site_url('filter') ?>">Reset</a><button class="btn btn-primary" type="submit"><span>Tampilkan mobil</span></button></div>
</form>

<?= $this->endSection() ?>
