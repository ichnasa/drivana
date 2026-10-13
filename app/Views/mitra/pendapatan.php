<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php $total = array_sum(array_column($months, 'net')); $ym = fn (string $s) => date_id($s . '-01', true); ?>

<?= partial('components/topbar', ['title' => 'Riwayat pendapatan', 'back' => site_url('mitra/dashboard')]) ?>

<div class="page" style="padding-top:16px">
    <div class="figure-row" style="padding-top:8px"><div class="figure"><small>Total <?= count($months) ?> bulan terakhir (bersih)</small><strong style="font-size:30px"><?= rupiah($total) ?></strong><small>Setelah dikurangi biaya layanan Drivana</small></div></div>

    <div class="section-head"><h2>Per bulan</h2></div>
    <?php if (! $months): ?><p class="muted">Belum ada sewa yang selesai.</p><?php endif ?>
    <?php foreach ($months as $m): ?>
        <div class="row"><span style="flex:1"><b style="display:block"><?= esc(substr(date_id($m['ym'] . '-01', true), 2)) ?></b><small class="muted"><?= $m['n'] ?> sewa selesai</small></span><b><?= rupiah($m['net']) ?></b></div>
    <?php endforeach ?>

    <?php if ($recent): ?>
        <div class="section-head"><h2>Transaksi terakhir</h2></div>
        <?php foreach ($recent as $r): ?>
            <a class="row" href="<?= site_url('mitra/reservasi/' . $r['code']) ?>"><span style="flex:1"><b style="display:block"><?= esc($r['car_name']) ?></b><small class="muted"><?= esc($r['code']) ?> · <?= date_id($r['end_date'], false) ?></small></span><b><?= rupiah($r['total'] - $r['service_fee']) ?></b></a>
        <?php endforeach ?>
    <?php endif ?>
</div>

<?= $this->endSection() ?>
