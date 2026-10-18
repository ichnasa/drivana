<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$pay = ['paid' => ['Dibayar', 'badge-green'], 'pending' => ['Menunggu', 'badge-yellow'], 'cancelled' => ['Dibatalkan', ''], 'failed' => ['Gagal', 'badge-red'], 'refunded' => ['Refund', '']];
$method = $payment ? (array_values(array_filter(\App\Libraries\Drivana::PAYMENT_METHODS, fn ($m) => $m['key'] === $payment['method']))[0]['name'] ?? $payment['method']) : null;
?>

<?= partial('components/topbar', ['title' => $b['code'], 'back' => site_url('admin/transaksi'), 'sub' => \App\Libraries\Drivana::statusLabel($b['status'])]) ?>

<div class="page stack" style="padding-top:20px">
    <div class="card" style="display:flex;gap:12px;padding:12px">
        <div class="photo" style="width:96px;height:68px;border-radius:8px;flex:none"><img src="<?= car_img($car['image']) ?>" alt=""></div>
        <div style="flex:1"><b style="font-size:16px"><?= esc($car['name']) ?></b><p class="muted caption"><?= esc($car['plate']) ?> · <?= esc($b['area']) ?></p></div>
    </div>

    <div class="panel">
        <h4>Pihak terkait</h4>
        <div class="sum-row"><span>Penyewa</span><b style="text-align:right"><?= esc($customer['name']) ?><br><small class="muted"><?= esc($customer['phone']) ?></small></b></div>
        <div class="sum-row"><span>Mitra</span><b style="text-align:right"><?= esc($mitra['business_name'] ?: $mitra['name']) ?><br><small class="muted"><?= esc($mitra['phone']) ?></small></b></div>
    </div>

    <div class="panel">
        <h4>Pesanan</h4>
        <div class="sum-row"><span>Dibuat</span><b><?= date_id($b['created_at'], false) ?>, <?= date('H.i', strtotime($b['created_at'])) ?></b></div>
        <div class="sum-row"><span>Tanggal sewa</span><b><?= date_id($b['start_date'], false) ?> – <?= date_id($b['end_date']) ?></b></div>
        <div class="sum-row"><span>Durasi</span><b><?= $b['days'] ?> hari</b></div>
        <div class="sum-row"><span>Pengemudi</span><b><?= $b['with_driver'] ? 'Termasuk' : 'Lepas kunci' ?></b></div>
        <div class="sum-row"><span>Diantar ke</span><b style="text-align:right;max-width:60%"><?= esc($b['address']) ?></b></div>
        <?php if (! empty($b['cancel_reason'])): ?><div class="sum-row"><span>Alasan batal/tolak</span><b style="text-align:right;max-width:60%"><?= esc($b['cancel_reason']) ?></b></div><?php endif ?>
    </div>

    <div class="panel"><h4>Status</h4><?= partial('components/timeline', ['b' => $b]) ?></div>

    <div class="panel">
        <h4>Pembayaran</h4>
        <?php if ($payment): ?>
            <div class="sum-row"><span>No. referensi</span><b><?= esc($payment['ref']) ?></b></div>
            <div class="sum-row"><span>Metode</span><b><?= esc($method) ?></b></div>
            <div class="sum-row"><span>Status</span><span class="badge <?= $pay[$payment['status']][1] ?>"><?= $pay[$payment['status']][0] ?></span></div>
            <?php if ($payment['paid_at']): ?><div class="sum-row"><span>Dibayar</span><b><?= date_id($payment['paid_at'], false) ?>, <?= date('H.i', strtotime($payment['paid_at'])) ?></b></div><?php endif ?>
            <?php if ($payment['refunded_at']): ?><div class="sum-row"><span>Refund</span><b><?= date_id($payment['refunded_at'], false) ?>, <?= date('H.i', strtotime($payment['refunded_at'])) ?></b></div><?php endif ?>
        <?php else: ?>
            <p class="muted">Belum ada pembayaran untuk pesanan ini.</p>
        <?php endif ?>
        <div class="sum-row"><span>Sewa mobil (<?= $b['days'] ?> hari)</span><span><?= rupiah($b['rent_total']) ?></span></div>
        <?php if ($b['driver_total']): ?><div class="sum-row"><span>Pengemudi</span><span><?= rupiah($b['driver_total']) ?></span></div><?php endif ?>
        <div class="sum-row"><span>Biaya layanan</span><span><?= rupiah($b['service_fee']) ?></span></div>
        <?php if ($b['discount']): ?><div class="sum-row"><span>Promo <?= esc($b['promo_code']) ?></span><span>−<?= rupiah($b['discount']) ?></span></div><?php endif ?>
        <div class="sum-row total"><span>Total</span><b><?= rupiah($b['total']) ?></b></div>
        <?php if ($payment && in_array($payment['status'], ['paid', 'refunded'], true)): ?><a class="btn btn-secondary btn-sm" style="margin-top:12px" href="<?= site_url('bukti/' . $payment['ref']) ?>"><?= icon('receipt', 18) ?> Lihat bukti pembayaran</a><?php endif ?>
    </div>
</div>

<?= $this->endSection() ?>
