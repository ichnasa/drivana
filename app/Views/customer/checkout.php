<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$methodErr = form_error('method'); $promoErr = form_error('promo');
$sub = $b['rent_total'] + $b['driver_total'] + $b['service_fee'];
$sel = old_input('method', 'bank');
?>

<?= partial('components/topbar', ['title' => 'Pembayaran', 'back' => site_url('booking/' . $b['car_id']), 'sub' => 'Booking → Bayar → Selesai']) ?>

<form class="page has-cta stack" action="<?= site_url('bayar/' . $b['code']) ?>" method="post" style="padding-top:20px" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="method" id="p-method" value="<?= esc($sel, 'attr') ?>">

    <?= partial('components/alerts') ?>
    <div class="stepper"><span class="s is-done"><?= icon('check', 14, '', 3) ?></span><span class="bar is-done"></span><span class="s is-current">2</span><span class="bar"></span><span class="s">3</span></div>

    <div class="pay-total">
        <small>Total yang harus dibayar</small>
        <strong data-pay-total data-base="<?= $sub ?>"><?= rupiah($b['total']) ?></strong>
        <span class="badge"><?= esc($b['car']['name']) ?> · <?= $b['days'] ?> hari<?= $b['with_driver'] ? ' · dengan pengemudi' : '' ?></span>
        <p class="field-hint" style="margin-top:8px">Selesaikan sebelum <b data-countdown="<?= date('c', strtotime($b['expires_at'])) ?>"><?= date('H.i', strtotime($b['expires_at'])) ?></b>, atau pesanan dibatalkan otomatis.</p>
    </div>

    <div class="card-soft" style="padding:16px;font-size:14px">
        <div class="sum-row"><span><?= date_id($b['start_date'], false) ?> – <?= date_id($b['end_date']) ?></span><b><?= $b['days'] ?> hari</b></div>
        <div class="sum-row"><span>Diantar ke</span><b style="text-align:right;max-width:60%"><?= esc($b['area']) ?></b></div>
    </div>

    <div class="panel">
        <h4>Metode Pembayaran</h4>
        <?= partial('components/pay_methods', ['methods' => $methods, 'selected' => $sel, 'inputId' => 'p-method']) ?>
        <p class="field-error" role="alert"><?= $methodErr ? icon('circle-alert', 16) . ' ' . esc($methodErr) : '' ?></p>
    </div>

    <div class="panel">
        <h4>Kode Promo</h4>
        <div style="display:flex;gap:12px;align-items:flex-start">
            <div class="field" style="flex:1;min-width:0;margin:0">
                <div class="input<?= $promoErr ? ' has-error' : '' ?>"><?= icon('ticket', 20) ?><input name="promo" data-promo-input value="<?= esc(old_input('promo', $b['promo_code'] ?? ''), 'attr') ?>" placeholder="Masukkan kode promo" autocomplete="off" maxlength="20" aria-label="Kode promo" style="text-transform:uppercase"></div>
                <p class="field-error" role="alert"><?= $promoErr ? icon('circle-alert', 16) . ' ' . esc($promoErr) : '' ?></p>
            </div>
            <?php if ($offer): ?><button type="button" class="btn btn-secondary btn-sm" style="height:52px" data-promo-apply data-code="<?= esc($offer['code'], 'attr') ?>" data-percent="<?= $offer['percent'] ?>">Pakai</button><?php endif ?>
        </div>
        <span class="hide" data-promo-msg style="margin-top:12px"></span>
        <?php if ($offer): ?><p class="field-hint">Coba <b><?= esc($offer['code']) ?></b> untuk diskon <?= $offer['percent'] ?>%.</p><?php endif ?>
    </div>

    <div class="sticky-cta"><button class="btn btn-primary btn-cta" type="submit"><span>Bayar Sekarang</span><small data-cta-total><?= rupiah($b['total']) ?></small></button></div>
</form>

<?= $this->endSection() ?>
