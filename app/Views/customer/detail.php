<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$inService = $car['ops_status'] !== 'tersedia';
$qs = ($start && $end) ? '?start=' . urlencode($start) . '&end=' . urlencode($end) : '';
?>

<?= partial('components/topbar', ['title' => 'Detail mobil', 'back' => site_url('cari')]) ?>

<div class="hero-photo">
    <img src="<?= car_img($car['image']) ?>" alt="<?= esc($car['name']) ?>">
    <?php if ($inService): ?><span class="badge badge-yellow">Sedang servis</span>
    <?php elseif ($car['is_boosted']): ?><span class="badge badge-black">Sponsored</span><?php endif ?>
</div>

<div class="page has-cta stack" style="padding-top:16px">
    <?= partial('components/alerts') ?>

    <div class="card detail-card stack">
    <div class="detail-head">
        <div>
            <h2><?= esc($car['name']) ?></h2>
            <p class="muted" style="margin-top:4px"><?= $car['rating'] ? stars((float) $car['rating']) . ' (' . $car['reviews_count'] . ' ulasan)' : 'Belum ada ulasan' ?></p>
        </div>
        <div class="price" style="text-align:right"><b style="font-size:22px"><?= rupiah($car['price'], true) ?></b><br><small>/ hari</small></div>
    </div>

    <div class="pills">
        <span class="badge"><?= $car['seats'] ?> kursi</span><span class="badge"><?= esc($car['type']) ?></span><span class="badge"><?= $car['year'] ?></span>
        <span class="badge <?= $car['driver_only'] ? 'badge-blue' : '' ?>"><?= $car['driver_only'] ? 'Termasuk pengemudi' : ($car['with_driver'] ? 'Pengemudi opsional' : 'Lepas kunci') ?></span>
    </div>

    <div class="facts">
        <div><small>Transmisi</small><b><?= esc($car['transmission']) ?></b></div>
        <div><small>BBM</small><b><?= esc($car['fuel']) ?></b></div>
        <div><small>Wilayah</small><b><?= esc($car['area']) ?></b></div>
    </div>
    </div>

    <?php if ($car['description']): ?><p class="lead" style="color:var(--ink-2)"><?= esc($car['description']) ?></p><?php endif ?>

    <div class="card" style="display:flex;align-items:center;gap:14px;padding:16px">
        <?= avatar($car['business_name'] ?: $car['mitra_name'], 48) ?>
        <div style="flex:1"><b style="font-size:16px"><?= esc($car['business_name'] ?: $car['mitra_name']) ?></b>
            <p class="muted caption"><?= $car['mitra_rating'] ? icon('star', 12, 'fill') . ' ' . number_format($car['mitra_rating'], 1) . ' · ' : '' ?><?= $car['fleet_count'] ?> armada</p></div>
        <a class="icon-btn" href="<?= site_url('chat/mulai/' . $car['owner_id']) . '?mobil=' . $car['id'] ?>" aria-label="Chat mitra"><?= icon('message-square', 20) ?></a>
    </div>

    <div class="panel">
        <h4>Cek ketersediaan</h4>
        <form action="<?= site_url('mobil/' . $car['id']) ?>" method="get" novalidate>
            <div class="date-grid">
                <div class="field" style="margin:0"><label for="d-start">Mulai</label><div class="input"><?= icon('calendar', 18) ?><input id="d-start" type="date" name="start" value="<?= esc($start, 'attr') ?>" min="<?= date('Y-m-d') ?>" required data-error="Tanggal mulai tidak boleh sebelum hari ini." aria-describedby="d-start-error"></div><p class="field-error" id="d-start-error" role="alert"></p></div>
                <div class="field" style="margin:0"><label for="d-end">Selesai</label><div class="input"><?= icon('calendar', 18) ?><input id="d-end" type="date" name="end" value="<?= esc($end, 'attr') ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required data-error="Tanggal selesai harus setelah tanggal mulai." aria-describedby="d-end-error"></div><p class="field-error" id="d-end-error" role="alert"></p></div>
            </div>
            <button class="btn btn-secondary btn-sm" style="margin-top:12px;width:100%" type="submit"><span>Cek tanggal</span></button>
        </form>
        <?php if ($check === true): ?><div class="alert alert-success" style="margin:12px 0 0"><?= icon('circle-check', 18) ?><span>Tersedia <?= date_id($start, false) ?> – <?= date_id($end, false) ?>.</span></div>
        <?php elseif ($check === false): ?><div class="alert" style="margin:12px 0 0"><?= icon('circle-x', 18) ?><span>Tidak tersedia di tanggal itu. Coba tanggal lain.</span></div><?php endif ?>
        <?php if ($busy): ?>
            <p class="muted caption" style="margin-top:12px">Sudah dipesan:</p>
            <div class="pills" style="margin-top:6px"><?php foreach ($busy as $b): ?><span class="badge"><?= date_id($b['start_date'], false) ?> – <?= date_id($b['end_date'], false) ?></span><?php endforeach ?></div>
        <?php endif ?>
    </div>

    <div>
        <div class="section-head" style="margin-top:8px"><h2>Ulasan</h2></div>
        <?php if (! $reviews): ?><p class="muted">Belum ada ulasan untuk mobil ini.</p><?php endif ?>
        <?php foreach ($reviews as $r): ?>
            <div class="review" style="padding:12px 0;border-bottom:1px solid var(--line)">
                <div style="display:flex;align-items:center;gap:10px"><?= avatar($r['author'], 32) ?><b><?= esc($r['author']) ?></b><span style="margin-left:auto"><?= stars((float) $r['rating']) ?></span></div>
                <?php if ($r['comment']): ?><p>“<?= esc($r['comment']) ?>”</p><?php endif ?>
            </div>
        <?php endforeach ?>
    </div>

</div>

<div class="sticky-cta" style="display:flex;gap:12px">
    <a class="btn btn-secondary" href="<?= site_url('chat/mulai/' . $car['owner_id']) . '?mobil=' . $car['id'] ?>" style="width:56px;padding:0" aria-label="Chat"><?= icon('message-square', 22) ?></a>
    <?php if ($inService): ?><button class="btn" disabled>Sedang servis</button>
    <?php else: ?><a class="btn btn-primary" href="<?= site_url('booking/' . $car['id']) . $qs ?>">Booking sekarang</a><?php endif ?>
</div>

<?= $this->endSection() ?>
