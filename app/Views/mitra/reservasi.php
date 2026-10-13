<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php $tabs = ['semua' => 'Semua', 'konfirmasi' => 'Perlu konfirmasi', 'aktif' => 'Aktif', 'selesai' => 'Riwayat']; ?>

<?= partial('components/topbar', ['title' => 'Pesanan masuk', 'right' => bell_btn()]) ?>

<div class="page" style="padding-top:12px">
    <div class="chips"><?php foreach ($tabs as $k => $l): ?><a class="chip <?= $tab === $k ? 'is-active' : '' ?>" href="<?= site_url('mitra/reservasi') . ($k === 'semua' ? '' : '?tab=' . $k) ?>"><?= $l ?></a><?php endforeach ?></div>
    <?php if (! $items): ?><div class="empty"><?= icon('calendar-x', 28) ?><p>Belum ada pesanan di sini.</p></div><?php endif ?>
    <div class="stack">
        <?php foreach ($items as $r): ?>
            <a class="rsv" href="<?= site_url('mitra/reservasi/' . $r['code']) ?>" style="display:block">
                <div class="rsv-top"><?= avatar($r['customer_name'], 44) ?><div><b><?= esc($r['customer_name']) ?></b><small><?= esc($r['code']) ?></small></div><?= badge_for($r['status']) ?></div>
                <div class="kv">
                    <div><?= icon('car', 14) ?> <?= esc($r['car_name']) ?></div>
                    <div><?= icon('calendar', 14) ?> <?= date_id($r['start_date'], false) ?> – <?= date_id($r['end_date']) ?> · <?= $r['days'] ?> hari</div>
                    <div><?= icon('map-pin', 14) ?> <?= esc($r['area']) ?></div>
                    <div><?= icon('wallet', 14) ?> <b style="color:var(--ink)"><?= rupiah($r['total'] - $r['service_fee']) ?></b> <span class="muted">bersih untukmu</span></div>
                </div>
                <?php if ($r['status'] === 'menunggu'): ?><span class="btn btn-primary btn-sm" style="width:100%">Tinjau pesanan</span><?php endif ?>
            </a>
        <?php endforeach ?>
    </div>
</div>

<?= $this->endSection() ?>
