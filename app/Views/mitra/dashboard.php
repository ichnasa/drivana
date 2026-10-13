<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$name = $me['business_name'] ?: $me['name'];
$delta = $revenue_prev > 0 ? (int) round(($revenue - $revenue_prev) / $revenue_prev * 100) : null;
$premium = [
    ['map', 'Analisis wilayah', 'Di mana permintaan sedang tinggi', 'mitra/analisis-wilayah'],
    ['circle-dollar-sign', 'Rekomendasi harga', 'Harga sewa sesuai pasar', 'mitra/rekomendasi-harga'],
    ['chart-line', 'Analitik rental', 'Booking, rating, pendapatan', 'mitra/analitik'],
    ['rocket', 'Boost listing', 'Tampil di urutan teratas', 'mitra/boost'],
];
?>

<div class="greet">
    <?= avatar($name, 44) ?>
    <div style="flex:1"><small>Selamat datang,</small><b><?= esc($name) ?></b></div>
    <?= bell_btn() ?>
</div>

<div class="page">
    <?= partial('components/verify_banner', ['me' => $me]) ?>

    <?php if ($to_confirm): ?>
        <a class="banner banner-warn" href="<?= site_url('mitra/reservasi?tab=konfirmasi') ?>"><?= icon('bell-ring', 20) ?><div><b><?= $to_confirm ?> pesanan perlu konfirmasi</b><p>Penyewa menunggu jawabanmu.</p></div><?= icon('chevron-right', 18) ?></a>
    <?php endif ?>

    <div class="figure-row">
        <div class="figure"><small>Booking aktif sekarang</small><strong><?= $active_now ?></strong></div>
    </div>
    <div class="figure-row">
        <div class="figure sm"><small>Pendapatan bulan ini (bersih)</small><strong><?= rupiah($revenue) ?></strong>
            <?php if ($delta !== null): ?><span class="badge <?= $delta >= 0 ? 'badge-green' : 'badge-red' ?>"><?= ($delta >= 0 ? '+' : '') . $delta ?>% dari bulan lalu</span><?php endif ?></div>
        <a class="link" href="<?= site_url('mitra/pendapatan') ?>">Riwayat</a>
    </div>

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

    <div class="section-head"><h2>Armada</h2><a href="<?= site_url('mitra/armada') ?>">Kelola</a></div>
    <div class="stat-row"><?php foreach ($fleet as $k => $n): ?><div><small><?= esc($k) ?></small><b><?= $n ?></b></div><?php endforeach ?></div>
    <?php if ($review_cars): ?><p class="muted caption" style="margin-top:12px"><?= $review_cars ?> mobil menunggu tinjauan admin.</p><?php endif ?>

    <div class="section-head"><h2>Premium</h2>
        <?php if ($sub): ?><span class="badge badge-green">Aktif sampai <?= date_id($sub['ends_at'], false) ?></span><?php else: ?><a href="<?= site_url('mitra/langganan') ?>">Berlangganan</a><?php endif ?></div>
    <div>
        <?php foreach ($premium as [$ico, $label, $hint, $url]): ?>
            <a class="row" href="<?= site_url($sub ? $url : 'mitra/langganan') ?>">
                <?= icon($ico, 22) ?>
                <span style="flex:1"><b style="display:block"><?= esc($label) ?></b><small class="muted"><?= esc($hint) ?></small></span>
                <?= $sub ? icon('chevron-right', 18) : '<span class="badge">' . icon('lock', 12) . ' Premium</span>' ?>
            </a>
        <?php endforeach ?>
    </div>
</div>

<?= $this->endSection() ?>
