<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php $dl = fn (int $v) => '<span class="badge ' . ($v >= 0 ? 'badge-green' : 'badge-red') . '">' . ($v >= 0 ? '+' : '') . $v . '% dari periode sebelumnya</span>'; ?>

<?= partial('components/topbar', ['title' => 'Analitik rental', 'back' => site_url('mitra/dashboard'), 'sub' => $a['period']]) ?>

<div class="page" style="padding-top:12px">
    <div class="chips"><?php foreach ($periods as $k => [$label]): ?><a class="chip <?= $period === $k ? 'is-active' : '' ?>" href="<?= site_url('mitra/analitik?periode=' . $k) ?>"><?= $label ?></a><?php endforeach ?></div>

    <div class="figure-row" style="padding-top:8px"><div class="figure"><small>Total booking</small><strong><?= $a['bookings'] ?></strong><?= $dl($a['bookings_delta']) ?></div></div>
    <div class="figure-row"><div class="figure sm"><small>Pendapatan bersih</small><strong><?= rupiah($a['revenue']) ?></strong><?= $dl($a['revenue_delta']) ?></div></div>
    <div class="figure-row" style="padding-bottom:20px">
        <div class="stat-row" style="width:100%"><div><small>Rating rata-rata</small><b><?= $a['rating'] !== null ? number_format($a['rating'], 1) : '–' ?></b></div><div><small>Ulasan</small><b><?= $a['reviews'] ?></b></div></div>
    </div>

    <div class="section-head"><h2>Insight</h2></div>
    <div class="tip"><?= icon('lightbulb', 20) ?><div><b>Untuk periode ini</b><p><?= esc($a['insight']) ?></p></div></div>
</div>

<?= $this->endSection() ?>
