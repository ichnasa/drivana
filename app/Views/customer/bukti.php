<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
use App\Libraries\Drivana;
$refunded = $p['status'] === 'refunded';
$what = match ($p['kind']) {
    'booking'      => 'Sewa mobil ' . ($car['name'] ?? ''),
    'subscription' => 'Langganan Premium ' . (Drivana::PLANS[$p['plan']]['name'] ?? ''),
    default        => 'Boost listing ' . ($car['name'] ?? '') . ' · ' . $p['days'] . ' hari',
};
?>

<?= partial('components/topbar', ['title' => 'Bukti pembayaran', 'back' => site_url($back), 'sub' => $p['ref']]) ?>

<div class="page stack" style="padding-top:20px">
    <div class="pay-total">
        <small><?= esc($what) ?></small>
        <strong><?= rupiah($p['amount']) ?></strong>
        <span class="badge <?= $refunded ? '' : 'badge-green' ?>"><?= $refunded ? 'Dikembalikan (refund)' : 'Pembayaran berhasil' ?></span>
    </div>

    <div class="receipt">
        <div class="sum-row"><span>No. referensi</span><b><?= esc($p['ref']) ?></b></div>
        <div class="sum-row"><span>Tanggal bayar</span><b><?= date_id($p['paid_at'], false) ?>, <?= date('H.i', strtotime($p['paid_at'])) ?></b></div>
        <div class="sum-row"><span>Metode</span><b><?= esc($method) ?></b></div>
        <div class="sum-row"><span>Dibayar oleh</span><b><?= esc($payer['business_name'] ?: $payer['name']) ?></b></div>
        <?php if ($b): ?><div class="sum-row"><span>Kode booking</span><b><?= esc($b['code']) ?></b></div><?php endif ?>
        <?php if ($refunded): ?><div class="sum-row"><span>Tanggal refund</span><b><?= date_id($p['refunded_at'], false) ?>, <?= date('H.i', strtotime($p['refunded_at'])) ?></b></div><?php endif ?>
    </div>

    <?php if ($b): ?>
        <div class="panel">
            <h4>Rincian biaya</h4>
            <div class="sum-row"><span>Sewa mobil (<?= $b['days'] ?> hari)</span><span><?= rupiah($b['rent_total']) ?></span></div>
            <?php if ($b['driver_total']): ?><div class="sum-row"><span>Pengemudi</span><span><?= rupiah($b['driver_total']) ?></span></div><?php endif ?>
            <div class="sum-row"><span>Biaya layanan</span><span><?= rupiah($b['service_fee']) ?></span></div>
            <?php if ($b['discount']): ?><div class="sum-row"><span>Promo <?= esc($b['promo_code']) ?></span><span>−<?= rupiah($b['discount']) ?></span></div><?php endif ?>
            <div class="sum-row total"><span>Total</span><b><?= rupiah($p['amount']) ?></b></div>
        </div>
        <div class="panel">
            <h4>Detail sewa</h4>
            <div class="sum-row"><span>Mobil</span><b><?= esc($car['name']) ?></b></div>
            <div class="sum-row"><span>Tanggal</span><b><?= date_id($b['start_date'], false) ?> – <?= date_id($b['end_date']) ?></b></div>
        </div>
    <?php else: ?>
        <div class="panel">
            <h4>Rincian</h4>
            <div class="sum-row"><span><?= esc($what) ?></span><span><?= rupiah($p['amount']) ?></span></div>
            <div class="sum-row total"><span>Total</span><b><?= rupiah($p['amount']) ?></b></div>
        </div>
    <?php endif ?>
</div>

<?= $this->endSection() ?>
