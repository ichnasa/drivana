<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$statuses = $tab === 'booking' ? ['' => 'Semua', 'menunggu' => 'Menunggu', 'dikonfirmasi' => 'Dikonfirmasi', 'aktif' => 'Aktif', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan', 'ditolak' => 'Ditolak'] : ['' => 'Semua', 'paid' => 'Dibayar', 'pending' => 'Menunggu', 'cancelled' => 'Dibatalkan', 'failed' => 'Gagal', 'refunded' => 'Refund'];
$link = fn (array $o) => site_url('admin/transaksi?' . http_build_query(array_filter(array_merge(['tab' => $tab, 'status' => $status, 'q' => $q, 'dari' => $from, 'sampai' => $to], $o), fn ($v) => $v !== '')));
$pay = ['paid' => ['Dibayar', 'badge-green'], 'pending' => ['Menunggu', 'badge-yellow'], 'cancelled' => ['Dibatalkan', ''], 'failed' => ['Gagal', 'badge-red'], 'refunded' => ['Refund', '']];
?>

<?= partial('components/topbar', ['title' => 'Transaksi', 'sub' => $total . ' data', 'right' => bell_btn('admin')]) ?>

<div class="page" style="padding-top:12px">
    <div class="chips"><a class="chip <?= $tab === 'booking' ? 'is-active' : '' ?>" href="<?= site_url('admin/transaksi' . ($from || $to ? '?' . http_build_query(['dari' => $from, 'sampai' => $to]) : '')) ?>">Booking</a><a class="chip <?= $tab === 'langganan' ? 'is-active' : '' ?>" href="<?= site_url('admin/transaksi?' . http_build_query(array_filter(['tab' => 'langganan', 'dari' => $from, 'sampai' => $to]))) ?>">Langganan dan boost</a></div>
    <form action="<?= site_url('admin/transaksi') ?>" method="get">
        <div class="searchbar"><?= icon('search', 20) ?><input type="search" name="q" value="<?= esc($q, 'attr') ?>" placeholder="Cari kode, nama, atau mobil" aria-label="Cari"></div>
        <input type="hidden" name="tab" value="<?= esc($tab, 'attr') ?>"><input type="hidden" name="status" value="<?= esc($status, 'attr') ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px">
            <div class="field" style="margin:0"><label for="t-dari">Dari tanggal</label><div class="input"><?= icon('calendar', 18) ?><input id="t-dari" type="date" name="dari" value="<?= esc($from, 'attr') ?>"></div></div>
            <div class="field" style="margin:0"><label for="t-sampai">Sampai tanggal</label><div class="input"><?= icon('calendar', 18) ?><input id="t-sampai" type="date" name="sampai" value="<?= esc($to, 'attr') ?>"></div></div>
        </div>
        <button class="btn btn-secondary btn-sm" type="submit" style="margin-top:12px"><span>Terapkan</span></button>
        <?php if ($q !== '' || $from || $to || $status !== ''): ?><a class="btn btn-secondary btn-sm" style="margin-top:12px" href="<?= site_url('admin/transaksi?tab=' . $tab) ?>">Reset</a><?php endif ?>
    </form>
    <div class="chips" style="margin-top:12px"><?php foreach ($statuses as $k => $l): ?><a class="chip <?= $status === (string) $k ? 'is-active' : '' ?>" href="<?= $link(['status' => $k]) ?>"><?= $l ?></a><?php endforeach ?></div>

    <?php if (! $rows): ?><div class="empty"><?= icon('receipt', 28) ?><p>Tidak ada transaksi.</p></div><?php endif ?>
    <div>
        <?php foreach ($rows as $r): ?>
            <?php if ($tab === 'booking'): ?>
                <a class="row" href="<?= site_url('admin/transaksi/' . $r['code']) ?>" style="align-items:flex-start"><span style="flex:1;min-width:0"><b style="display:block"><?= esc($r['car']) ?></b><small class="muted"><?= esc($r['code']) ?> · <?= esc($r['customer']) ?> → <?= esc($r['mitra']) ?></small><small class="muted" style="display:block"><?= date_id($r['start_date'], false) ?> – <?= date_id($r['end_date'], false) ?> · <?= esc($r['area']) ?></small></span>
                    <span style="text-align:right"><b><?= rupiah($r['total']) ?></b><br><?= badge_for(\App\Libraries\Drivana::displayStatus($r)) ?><?php if ($r['pay']): ?><br><span class="badge <?= $pay[$r['pay']][1] ?>"><?= $pay[$r['pay']][0] ?></span><?php endif ?></span></a>
            <?php else: ?>
                <a class="row" <?= in_array($r['status'], ['paid', 'refunded'], true) ? 'href="' . site_url('bukti/' . $r['ref']) . '"' : '' ?> style="align-items:flex-start"><span style="flex:1;min-width:0"><b style="display:block"><?= $r['kind'] === 'boost' ? 'Boost ' . esc((string) $r['car']) . ' · ' . $r['days'] . ' hari' : 'Premium ' . esc(\App\Libraries\Drivana::PLANS[$r['plan']]['name'] ?? '') ?></b><small class="muted"><?= esc($r['ref']) ?> · <?= esc($r['mitra']) ?></small><small class="muted" style="display:block"><?= esc(\App\Libraries\Drivana::ago($r['created_at'])) ?> · <?= esc($r['method']) ?></small></span>
                    <span style="text-align:right"><b><?= rupiah($r['amount']) ?></b><br><span class="badge <?= $pay[$r['status']][1] ?>"><?= $pay[$r['status']][0] ?></span></span></a>
            <?php endif ?>
        <?php endforeach ?>
    </div>
</div>

<?= $this->endSection() ?>
