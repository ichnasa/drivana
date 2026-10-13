<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$car = $b['car']; $cu = $b['customer']; $rv = $b['review'];
$net = $b['total'] - $b['service_fee'];
?>

<?= partial('components/topbar', ['title' => $b['code'], 'back' => site_url('mitra/reservasi'), 'sub' => \App\Libraries\Drivana::statusLabel($b['status'])]) ?>

<div class="page has-cta stack" style="padding-top:20px">
    <?= partial('components/alerts') ?>

    <div class="card" style="display:flex;align-items:center;gap:12px;padding:16px">
        <?= avatar($cu['name'], 48) ?>
        <div style="flex:1"><b style="font-size:16px"><?= esc($cu['name']) ?></b><p class="muted caption"><?= esc($cu['phone']) ?></p></div>
        <a class="icon-btn" href="<?= $b['conversation'] ? site_url('mitra/pesan/' . $b['conversation']['id']) : site_url('mitra/pesan/mulai/' . $cu['id'] . '?mobil=' . $car['id']) ?>" aria-label="Chat penyewa"><?= icon('message-square', 20) ?></a>
    </div>

    <div class="panel"><h4>Status</h4><?= partial('components/timeline', ['b' => $b]) ?></div>

    <div class="panel">
        <h4>Pesanan</h4>
        <div class="sum-row"><span>Mobil</span><b><?= esc($car['name']) ?></b></div>
        <div class="sum-row"><span>Tanggal</span><b><?= date_id($b['start_date'], false) ?> – <?= date_id($b['end_date']) ?></b></div>
        <div class="sum-row"><span>Pengemudi</span><b><?= $b['with_driver'] ? 'Termasuk' : 'Lepas kunci' ?></b></div>
        <div class="sum-row"><span>Diantar ke</span><b style="text-align:right;max-width:60%"><?= esc($b['address']) ?></b></div>
        <?php if ($b['note']): ?><div class="sum-row"><span>Catatan</span><b style="text-align:right;max-width:60%"><?= esc($b['note']) ?></b></div><?php endif ?>
    </div>

    <div class="panel">
        <h4>Pendapatan</h4>
        <div class="sum-row"><span>Total dibayar penyewa</span><span><?= rupiah($b['total']) ?></span></div>
        <div class="sum-row"><span>Biaya layanan Drivana</span><span>−<?= rupiah($b['service_fee']) ?></span></div>
        <div class="sum-row total"><span>Untukmu</span><b><?= rupiah($net) ?></b></div>
    </div>

    <?php if ($rv): ?><div class="panel"><h4>Ulasan penyewa</h4><div class="review"><?= stars((float) $rv['rating']) ?><?php if ($rv['comment']): ?><p>“<?= esc($rv['comment']) ?>”</p><?php endif ?></div></div><?php endif ?>
</div>

<?php if (in_array($b['status'], ['menunggu', 'dikonfirmasi', 'aktif'], true)): ?>
<div class="sticky-cta" style="display:flex;gap:12px">
    <?php if ($b['status'] === 'menunggu'): ?>
        <button class="btn btn-danger" style="width:auto" type="button" data-modal-open="reject-modal">Tolak</button>
        <form action="<?= site_url('mitra/reservasi/' . $b['code'] . '/konfirmasi') ?>" method="post" style="flex:1"><?= csrf_field() ?><button class="btn btn-primary" type="submit"><span>Konfirmasi pesanan</span></button></form>
    <?php elseif ($b['status'] === 'dikonfirmasi'): ?>
        <button class="btn btn-danger" style="width:auto" type="button" data-modal-open="reject-modal">Batalkan</button>
        <form action="<?= site_url('mitra/reservasi/' . $b['code'] . '/mulai') ?>" method="post" style="flex:1"><?= csrf_field() ?><button class="btn btn-primary" type="submit"><span>Mulai sewa</span></button></form>
    <?php else: ?>
        <form action="<?= site_url('mitra/reservasi/' . $b['code'] . '/selesai') ?>" method="post" style="flex:1"><?= csrf_field() ?><button class="btn btn-primary" type="submit"><span>Selesaikan sewa</span></button></form>
    <?php endif ?>
</div>
<?php endif ?>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<div class="modal" id="reject-modal" role="dialog" aria-modal="true" aria-labelledby="reject-title">
    <form class="modal-box" action="<?= site_url('mitra/reservasi/' . $b['code'] . '/tolak') ?>" method="post" novalidate style="text-align:left">
        <?= csrf_field() ?>
        <span class="ico"><?= icon('triangle-alert', 24) ?></span>
        <h3 id="reject-title"><?= $b['status'] === 'menunggu' ? 'Tolak pesanan?' : 'Batalkan pesanan?' ?></h3>
        <p class="muted" style="margin:-8px 0 16px">Dana <?= rupiah($b['total']) ?> otomatis dikembalikan ke penyewa.</p>
        <div class="field"><label for="reject-reason">Alasan</label><div class="input<?= form_error('reason') ? ' has-error' : '' ?>"><input id="reject-reason" name="reason" required minlength="5" maxlength="200" placeholder="Mobil sedang tidak tersedia" data-label="Alasan"></div><p class="field-error" role="alert"><?= form_error('reason') ? icon('circle-alert', 16) . ' ' . esc(form_error('reason')) : '' ?></p></div>
        <div class="modal-actions"><button class="btn btn-secondary" type="button" data-modal-close>Kembali</button><button class="btn btn-danger" type="submit" style="background:var(--error);color:#fff"><span>Kirim</span></button></div>
    </form>
</div>
<?= $this->endSection() ?>
