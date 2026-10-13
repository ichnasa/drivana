<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
use App\Libraries\Drivana;
$now = date('Y-m-d H:i:s');
$carErr = form_error('car'); $daysErr = form_error('days'); $methodErr = form_error('method');
$selCar = old_input('car', $preselect ? (string) $preselect : ''); $selDays = old_input('days', '7'); $selM = old_input('method', 'bank');
?>

<?= partial('components/topbar', ['title' => 'Boost listing', 'back' => site_url('mitra/armada')]) ?>

<form class="page has-cta stack" action="<?= site_url('mitra/boost/beli') ?>" method="post" style="padding-top:16px" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="car" id="bo-car" value="<?= esc($selCar, 'attr') ?>">
    <input type="hidden" name="days" id="bo-days" value="<?= esc($selDays, 'attr') ?>">
    <input type="hidden" name="method" id="bo-method" value="<?= esc($selM, 'attr') ?>">

    <?= partial('components/alerts') ?>
    <p class="lead">Mobil yang di-boost tampil paling atas di hasil pencarian dengan label Sponsored.</p>

    <div class="panel">
        <h4>Pilih mobil</h4>
        <?php if (! $cars): ?><p class="muted">Belum ada mobil yang disetujui admin.</p><?php endif ?>
        <div style="display:grid;gap:12px" data-choice-group data-choice-input="#bo-car">
            <?php foreach ($cars as $c): $on = $c['boosted_until'] && $c['boosted_until'] > $now; ?>
                <button type="button" class="method <?= $selCar === (string) $c['id'] ? 'is-active' : '' ?>" data-value="<?= $c['id'] ?>">
                    <span class="photo" style="width:64px;height:46px;border-radius:6px;flex:none"><img src="<?= car_img($c['image']) ?>" alt=""></span>
                    <span class="meta"><b><?= esc($c['name']) ?></b><small><?= $on ? 'Boost aktif sampai ' . date_id($c['boosted_until'], false) : rupiah($c['price']) . '/hari' ?></small></span>
                    <span class="radio"></span>
                </button>
            <?php endforeach ?>
        </div>
        <p class="field-error" role="alert"><?= $carErr ? icon('circle-alert', 16) . ' ' . esc($carErr) : '' ?></p>
    </div>

    <div class="panel">
        <h4>Durasi</h4>
        <div class="choice-grid" data-choice-group data-choice-input="#bo-days" style="grid-template-columns:repeat(3,1fr)">
            <?php foreach (Drivana::BOOSTS as $d => $price): ?><button type="button" class="choice <?= $selDays === (string) $d ? 'is-active' : '' ?>" data-value="<?= $d ?>"><b><?= $d ?> hari</b><small><?= rupiah($price, true) ?></small></button><?php endforeach ?>
        </div>
        <p class="field-error" role="alert"><?= $daysErr ? icon('circle-alert', 16) . ' ' . esc($daysErr) : '' ?></p>
    </div>

    <div class="panel">
        <h4>Metode pembayaran</h4>
        <?= partial('components/pay_methods', ['methods' => Drivana::PAYMENT_METHODS, 'selected' => $selM, 'inputId' => 'bo-method']) ?>
        <p class="field-error" role="alert"><?= $methodErr ? icon('circle-alert', 16) . ' ' . esc($methodErr) : '' ?></p>
    </div>

    <div class="sticky-cta"><button class="btn btn-primary" type="submit"><span>Lanjut bayar</span></button></div>
</form>

<?= $this->endSection() ?>
