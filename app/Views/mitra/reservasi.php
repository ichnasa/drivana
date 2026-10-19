<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php $tabs = ['semua' => 'Semua', 'aktif' => 'Aktif', 'konfirmasi' => 'Menunggu', 'selesai' => 'Riwayat']; ?>

<?= partial('components/topbar', ['title' => 'Pesanan masuk', 'right' => bell_btn()]) ?>

<div class="page" style="padding-top:12px">
    <div class="chips"><?php foreach ($tabs as $k => $l): ?><a class="chip <?= $tab === $k ? 'is-active' : '' ?>" href="<?= site_url('mitra/reservasi') . ($k === 'semua' ? '' : '?tab=' . $k) ?>"><?= $l ?></a><?php endforeach ?></div>
    <?php if (! $items): ?><div class="empty"><?= icon('calendar-x', 28) ?><p>Belum ada pesanan di sini.</p></div><?php endif ?>
    <div class="stack">
        <?php foreach ($items as $r): $detail = site_url('mitra/reservasi/' . $r['code']); ?>
            <article class="rsv">
                <a href="<?= $detail ?>" style="display:block">
                    <div class="rsv-top"><?= avatar($r['customer_name'], 44) ?><div><b><?= esc($r['customer_name']) ?></b><small><?= esc($r['code']) ?></small></div><?= badge_for(\App\Libraries\Drivana::displayStatus($r)) ?></div>
                    <div class="kv">
                        <div><?= icon('car', 14) ?> <?= esc($r['car_name']) ?></div>
                        <div><?= icon('calendar', 14) ?> <?= date_id($r['start_date'], false) ?> – <?= date_id($r['end_date']) ?> · <?= $r['days'] ?> hari</div>
                        <div><?= icon('map-pin', 14) ?> <?= esc($r['area']) ?></div>
                        <div><?= icon('wallet', 14) ?> <b style="color:var(--ink)"><?= rupiah($r['total'] - $r['service_fee']) ?></b> <span class="muted">bersih untukmu</span></div>
                    </div>
                </a>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px">
                    <?php if ($r['status'] === 'menunggu'): ?>
                        <button class="btn btn-secondary btn-sm" style="flex:1" type="button" data-modal-open="reject-modal" data-fill-action="<?= site_url('mitra/reservasi/' . $r['code'] . '/tolak') ?>" data-fill-name="<?= esc($r['code'], 'attr') ?>">Tolak</button>
                    <?php endif ?>
                    <a class="btn btn-secondary btn-sm" style="flex:1" href="<?= site_url('mitra/pesan/mulai/' . $r['customer_id']) . '?mobil=' . $r['car_id'] ?>"><?= icon('message-square', 16) ?> Chat</a>
                    <a class="btn <?= $r['status'] === 'menunggu' ? 'btn-primary' : 'btn-secondary' ?> btn-sm" style="flex:1" href="<?= $detail ?>">Lihat detail</a>
                </div>
            </article>
        <?php endforeach ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<div class="modal" id="reject-modal" role="dialog" aria-modal="true" aria-labelledby="reject-title">
    <form class="modal-box" method="post" data-form-target novalidate style="text-align:left">
        <?= csrf_field() ?>
        <span class="ico"><?= icon('triangle-alert', 24) ?></span>
        <h3 id="reject-title">Tolak pesanan?</h3>
        <p class="muted" style="margin:-8px 0 16px">Pesanan <b data-slot="name"></b> ditolak dan dana otomatis dikembalikan ke penyewa.</p>
        <div class="field"><label for="reject-reason">Alasan</label><div class="input"><input id="reject-reason" name="reason" required minlength="5" maxlength="200" placeholder="Mobil sedang tidak tersedia" data-label="Alasan"></div><p class="field-error" role="alert"></p></div>
        <div class="modal-actions"><button class="btn btn-secondary" type="button" data-modal-close>Kembali</button><button class="btn btn-danger" type="submit" style="background:var(--error);color:#fff"><span>Tolak</span></button></div>
    </form>
</div>
<?= $this->endSection() ?>
