<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$what = match ($p['kind']) { 'booking' => 'Booking ' . ($b['code'] ?? ''), 'subscription' => 'Langganan Premium', default => 'Boost listing' };
$va = '8808 ' . substr(preg_replace('/\D/', '', $p['ref']) . '0000000000', 0, 10);
?>

<?= partial('components/topbar', ['title' => 'Payment Gateway', 'sub' => 'Selesaikan pembayaran Anda']) ?>

<div class="page stack" style="padding-top:20px">
    <?= partial('components/alerts') ?>

    <div class="pay-total">
        <small><?= esc($what) ?></small>
        <strong><?= rupiah($p['amount']) ?></strong>
        <span class="badge"><?= esc($method['name']) ?></span>
    </div>

    <div class="card-soft" style="padding:16px">
        <?php if ($method['key'] === 'bank'): ?>
            <p class="caption muted">Transfer ke virtual account</p>
            <p style="font-size:22px;font-weight:700;letter-spacing:.04em;margin:4px 0"><?= esc($va) ?></p>
            <p class="caption muted">BCA · BRI · Mandiri · atas nama Drivana</p>
        <?php elseif ($method['key'] === 'wallet'): ?>
            <p class="caption muted">Scan QR dengan GoPay, OVO, atau DANA</p>
            <div class="qr" aria-hidden="true"><?php for ($i = 0; $i < 49; $i++): ?><i class="<?= crc32($p['ref'] . $i) % 3 === 0 ? '' : 'on' ?>"></i><?php endfor ?></div>
        <?php else: ?>
            <p class="caption muted">Kartu terhubung</p>
            <p style="font-size:18px;font-weight:600;margin:4px 0">•••• •••• •••• 4242</p>
            <p class="caption muted">Visa · akan ditagih <?= rupiah($p['amount']) ?></p>
        <?php endif ?>
    </div>

    <dl class="kv-list">
        <div><dt>Referensi</dt><dd><?= esc($p['ref']) ?></dd></div>
        <div><dt>Status</dt><dd><span class="badge badge-yellow">Menunggu pembayaran</span></dd></div>
    </dl>

    <div class="notice"><?= icon('info', 18) ?> Selesaikan pembayaran sesuai metode di atas, lalu konfirmasi agar pesananmu diproses.</div>

    <form action="<?= site_url('gateway/' . $p['ref'] . '/berhasil') ?>" method="post"><?= csrf_field() ?><button class="btn btn-primary" type="submit"><span>Saya sudah membayar</span></button></form>
    <form action="<?= site_url('gateway/' . $p['ref'] . '/batal') ?>" method="post"><?= csrf_field() ?><button class="btn btn-danger" type="submit"><span>Batalkan pembayaran</span></button></form>
</div>

<?= $this->endSection() ?>
