<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$statuses = $tab === 'booking' ? ['' => 'Semua', 'menunggu' => 'Menunggu', 'dikonfirmasi' => 'Dikonfirmasi', 'aktif' => 'Aktif', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan', 'ditolak' => 'Ditolak'] : ['' => 'Semua', 'paid' => 'Dibayar', 'pending' => 'Menunggu', 'failed' => 'Gagal'];
$link = fn (array $o) => site_url('admin/transaksi?' . http_build_query(array_filter(array_merge(['tab' => $tab, 'status' => $status, 'q' => $q], $o), fn ($v) => $v !== '')));
$pay = ['paid' => ['Dibayar', 'badge-green'], 'pending' => ['Menunggu', 'badge-yellow'], 'failed' => ['Gagal', 'badge-red'], 'refunded' => ['Refund', '']];
?>

<?= partial('components/topbar', ['title' => 'Transaksi', 'sub' => $total . ' data', 'right' => bell_btn('admin')]) ?>

<div class="page" style="padding-top:12px">
    <div class="chips"><a class="chip <?= $tab === 'booking' ? 'is-active' : '' ?>" href="<?= site_url('admin/transaksi') ?>">Booking</a><a class="chip <?= $tab === 'langganan' ? 'is-active' : '' ?>" href="<?= site_url('admin/transaksi?tab=langganan') ?>">Langganan dan boost</a></div>
    <form class="searchbar" action="<?= site_url('admin/transaksi') ?>" method="get"><?= icon('search', 20) ?><input type="search" name="q" value="<?= esc($q, 'attr') ?>" placeholder="Cari kode, nama, atau mobil" aria-label="Cari"><input type="hidden" name="tab" value="<?= esc($tab, 'attr') ?>"><input type="hidden" name="status" value="<?= esc($status, 'attr') ?>"></form>
    <div class="chips" style="margin-top:12px"><?php foreach ($statuses as $k => $l): ?><a class="chip <?= $status === (string) $k ? 'is-active' : '' ?>" href="<?= $link(['status' => $k]) ?>"><?= $l ?></a><?php endforeach ?></div>

    <?php if (! $rows): ?><div class="empty"><?= icon('receipt', 28) ?><p>Tidak ada transaksi.</p></div><?php endif ?>
    <div>
        <?php foreach ($rows as $r): ?>
            <?php if ($tab === 'booking'): ?>
                <div class="row" style="align-items:flex-start"><span style="flex:1;min-width:0"><b style="display:block"><?= esc($r['car']) ?></b><small class="muted"><?= esc($r['code']) ?> · <?= esc($r['customer']) ?> → <?= esc($r['mitra']) ?></small><small class="muted" style="display:block"><?= date_id($r['start_date'], false) ?> – <?= date_id($r['end_date'], false) ?> · <?= esc($r['area']) ?></small></span>
                    <span style="text-align:right"><b><?= rupiah($r['total']) ?></b><br><?= badge_for($r['status']) ?></span></div>
            <?php else: ?>
                <div class="row" style="align-items:flex-start"><span style="flex:1;min-width:0"><b style="display:block"><?= $r['kind'] === 'boost' ? 'Boost ' . esc((string) $r['car']) . ' · ' . $r['days'] . ' hari' : 'Premium ' . esc(\App\Libraries\Drivana::PLANS[$r['plan']]['name'] ?? '') ?></b><small class="muted"><?= esc($r['ref']) ?> · <?= esc($r['mitra']) ?></small><small class="muted" style="display:block"><?= esc(\App\Libraries\Drivana::ago($r['created_at'])) ?> · <?= esc($r['method']) ?></small></span>
                    <span style="text-align:right"><b><?= rupiah($r['amount']) ?></b><br><span class="badge <?= $pay[$r['status']][1] ?>"><?= $pay[$r['status']][0] ?></span></span></div>
            <?php endif ?>
        <?php endforeach ?>
    </div>
</div>

<?= $this->endSection() ?>
