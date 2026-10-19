<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$name = $me['business_name'] ?: $me['name'];
$delta = $revenue_prev > 0 ? (int) round(($revenue - $revenue_prev) / $revenue_prev * 100) : null;
$premium = [
    ['circle-dollar-sign', 'Rekomendasi Harga', 'Harga sewa sesuai pasar', 'mitra/rekomendasi-harga'],
    ['chart-line', 'Analitik Rental', 'Booking, rating, pendapatan', 'mitra/analitik'],
    ['rocket', 'Boost listing', 'Tampil di urutan teratas', 'mitra/boost'],
];
?>

<div class="mitra-hero">
    <div class="greet">
        <?= avatar($name, 48) ?>
        <div style="flex:1"><small>Selamat datang,</small><b><?= esc($name) ?></b></div>
        <?= bell_btn() ?>
    </div>
    <div class="glass-card">
        <div><small>Booking Aktif Hari Ini</small><strong><?= $active_now ?></strong></div>
        <span class="glass-ico"><?= icon('calendar', 28) ?></span>
    </div>
</div>

<div class="page">
    <?= partial('components/verify_banner', ['me' => $me]) ?>

    <?php if ($to_confirm): ?>
        <a class="banner banner-warn" href="<?= site_url('mitra/reservasi?tab=konfirmasi') ?>"><?= icon('bell-ring', 20) ?><div><b><?= $to_confirm ?> pesanan perlu konfirmasi</b><p>Penyewa menunggu jawabanmu.</p></div><?= icon('chevron-right', 18) ?></a>
    <?php endif ?>

    <a class="card kpi" href="<?= site_url('mitra/pendapatan') ?>">
        <div><small>Pendapatan Bulan Ini</small><strong><?= rupiah($revenue) ?></strong>
            <?php if ($delta !== null): ?><span class="badge <?= $delta >= 0 ? 'badge-green' : 'badge-red' ?>"><?= ($delta >= 0 ? '+' : '') . $delta ?>% dari bulan lalu</span><?php endif ?></div>
        <span class="kpi-ico"><?= icon('banknote', 24) ?></span>
    </a>

    <?php if ($pending): ?>
        <div class="section-head"><h2>Perlu dikonfirmasi</h2><a href="<?= site_url('mitra/reservasi?tab=konfirmasi') ?>">Semua</a></div>
        <div class="stack">
            <?php foreach ($pending as $b): ?>
                <a class="rsv" href="<?= site_url('mitra/reservasi/' . $b['code']) ?>" style="display:block">
                    <div class="rsv-top"><?= avatar($b['customer_name'], 40) ?><div><b><?= esc($b['customer_name']) ?></b><small><?= esc($b['car_name']) ?> · <?= date_id($b['start_date'], false) ?> – <?= date_id($b['end_date'], false) ?></small></div><b><?= rupiah($b['total'] - $b['service_fee']) ?></b></div>
                </a>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <div class="section-head"><h2>Ringkasan Armada</h2><a href="<?= site_url('mitra/armada') ?>">Kelola</a></div>
    <div class="stat-row"><?php foreach ($fleet as $k => $n): ?><div><small style="text-transform:uppercase"><?= esc($k) ?></small><b><?= $n ?></b></div><?php endforeach ?></div>
    <?php if ($review_cars): ?><p class="muted caption" style="margin-top:12px"><?= $review_cars ?> mobil menunggu tinjauan admin.</p><?php endif ?>

    <div class="section-head"><h2>Fitur Berlangganan</h2>
        <?php if ($sub): ?><span class="badge badge-green">Aktif sampai <?= date_id($sub['ends_at'], false) ?></span><?php else: ?><a href="<?= site_url('mitra/langganan') ?>">Berlangganan</a><?php endif ?></div>
    <div class="tile-grid">
        <?php foreach ($premium as [$ico, $label, $hint, $url]): ?>
            <a class="card tile" href="<?= site_url($sub ? $url : 'mitra/langganan') ?>">
                <span class="tile-ico"><?= icon($ico, 24) ?></span>
                <b><?= esc($label) ?></b>
                <?php if (! $sub): ?><span class="badge"><?= icon('lock', 12) ?> Premium</span><?php endif ?>
            </a>
        <?php endforeach ?>
    </div>
</div>

<?= $this->endSection() ?>
