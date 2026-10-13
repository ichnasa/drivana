<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php $trend = $d['prev_total'] > 0 ? (int) round(($d['total'] - $d['prev_total']) / $d['prev_total'] * 100) : null; ?>

<?= partial('components/topbar', ['title' => 'Analisis wilayah', 'back' => site_url('mitra/dashboard'), 'sub' => 'Permintaan sewa di seluruh Drivana']) ?>

<div class="page" style="padding-top:16px">
    <div class="chips"><?php foreach ($periods as $k => [$label]): ?><a class="chip <?= $period === $k ? 'is-active' : '' ?>" href="<?= site_url('mitra/analisis-wilayah?periode=' . $k) ?>"><?= $label ?></a><?php endforeach ?></div>

    <div class="figure-row" style="padding-top:8px">
        <div class="figure"><small>Total booking di semua wilayah</small><strong><?= $d['total'] ?></strong>
            <?php if ($trend !== null): ?><span class="badge <?= $trend >= 0 ? 'badge-green' : 'badge-red' ?>"><?= ($trend >= 0 ? '+' : '') . $trend ?>% dari periode sebelumnya</span><?php endif ?></div>
    </div>

    <?php if ($d['top']): ?>
        <div class="section-head"><h2>Wilayah teratas</h2></div>
        <div class="region-top">
            <?php foreach ($d['top'] as $t): ?>
                <div class="region"><span class="badge badge-black"><?= icon('trending-up', 12) ?> <?= $t['level'] ?></span><h4><?= esc($t['name']) ?></h4><strong><?= $t['percent'] ?>%</strong><small><?= $t['count'] ?> booking</small></div>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <div class="section-head"><h2>Semua wilayah</h2></div>
    <div class="card" style="padding:4px 16px">
        <?php foreach ($d['areas'] as $a): ?>
            <div class="level">
                <?= icon('map-pin', 20) ?>
                <div><b><?= esc($a['name']) ?></b><small><?= $a['count'] ?> booking · <?= $a['per_day'] ?>/hari<?= $a['trend'] ? ' · ' . ($a['trend'] > 0 ? '+' : '') . $a['trend'] . '%' : '' ?></small>
                    <div class="meter" aria-hidden="true"><i style="width:<?= max(3, $a['percent']) ?>%"></i></div></div>
                <span class="lvl <?= strtolower($a['level']) ?>"><?= strtoupper($a['level']) ?></span>
            </div>
        <?php endforeach ?>
    </div>
    <p class="field-hint" style="margin-top:12px">Dihitung dari booking aktif, dikonfirmasi, dan selesai. Tempatkan mobil di wilayah berpermintaan tinggi.</p>
</div>

<?= $this->endSection() ?>
