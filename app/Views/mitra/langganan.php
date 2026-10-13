<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
use App\Libraries\Drivana;
$planErr = form_error('plan'); $methodErr = form_error('method');
$sel = old_input('plan', 'yearly'); $selM = old_input('method', 'bank');
$days = $subscription ? max(0, (int) ceil((strtotime($subscription['ends_at']) - time()) / 86400)) : 0;
?>

<?= partial('components/topbar', ['title' => 'Langganan Premium', 'back' => site_url('mitra/dashboard')]) ?>

<div class="page has-cta stack" style="padding-top:16px">
    <?= partial('components/alerts') ?>

    <?php if ($subscription): ?>
        <div class="banner banner-ok"><?= icon('circle-check', 20) ?><div><b>Premium aktif</b><p>Paket <?= esc(Drivana::PLANS[$subscription['plan']]['name']) ?> sampai <?= date_id($subscription['ends_at']) ?> (<?= $days ?> hari lagi). Beli lagi untuk memperpanjang.</p></div></div>
    <?php else: ?>
        <div><h2 style="font-size:24px;line-height:30px">Tampil lebih tinggi, harga lebih tepat.</h2><p class="lead" style="margin-top:8px">Buka fitur analitik dan boost untuk armadamu.</p></div>
    <?php endif ?>

    <div class="panel" style="border:0;padding-top:4px">
        <?php foreach (Drivana::PLAN_FEATURES as $f): ?>
            <div class="row" style="padding:12px 0"><?= icon($f['icon'], 22) ?><span style="flex:1"><b style="display:block"><?= esc($f['title']) ?></b><small class="muted"><?= esc($f['text']) ?></small></span><?= icon('check', 18, 'check-ok') ?></div>
        <?php endforeach ?>
    </div>

    <form action="<?= site_url('mitra/langganan/beli') ?>" method="post" id="sub-form" novalidate class="stack">
        <?= csrf_field() ?>
        <input type="hidden" name="plan" id="s-plan" value="<?= esc($sel, 'attr') ?>">
        <input type="hidden" name="method" id="s-method" value="<?= esc($selM, 'attr') ?>">

        <div class="panel">
            <h4>Pilih paket</h4>
            <div class="stack" data-choice-group data-choice-input="#s-plan" style="display:grid;gap:12px">
                <?php foreach (Drivana::PLANS as $k => $pl): ?>
                    <button type="button" class="method <?= $sel === $k ? 'is-active' : '' ?>" data-value="<?= $k ?>">
                        <span class="meta"><b><?= esc($pl['name']) ?></b><small><?= esc($pl['note']) ?></small></span>
                        <span style="text-align:right"><b style="font-size:18px"><?= rupiah($pl['price'], true) ?></b><br><small class="muted"><?= $k === 'yearly' ? '/tahun' : '/bulan' ?></small></span>
                        <span class="radio"></span>
                    </button>
                <?php endforeach ?>
            </div>
            <p class="field-error" role="alert"><?= $planErr ? icon('circle-alert', 16) . ' ' . esc($planErr) : '' ?></p>
        </div>

        <div class="panel">
            <h4>Metode pembayaran</h4>
            <?= partial('components/pay_methods', ['methods' => Drivana::PAYMENT_METHODS, 'selected' => $selM, 'inputId' => 's-method']) ?>
            <p class="field-error" role="alert"><?= $methodErr ? icon('circle-alert', 16) . ' ' . esc($methodErr) : '' ?></p>
        </div>
    </form>

    <?php if ($history): ?>
        <div class="panel"><h4>Riwayat langganan</h4>
            <?php foreach ($history as $h): ?><div class="sum-row"><span><?= esc(Drivana::PLANS[$h['plan']]['name']) ?> · <?= date_id($h['starts_at'], false) ?> – <?= date_id($h['ends_at'], false) ?></span><span><?= rupiah($h['price']) ?></span></div><?php endforeach ?>
        </div>
    <?php endif ?>
</div>

<div class="sticky-cta"><button class="btn btn-primary" type="submit" form="sub-form"><span><?= $subscription ? 'Perpanjang Premium' : 'Berlangganan' ?></span></button></div>

<?= $this->endSection() ?>
