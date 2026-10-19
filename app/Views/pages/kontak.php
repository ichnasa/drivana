<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<a class="icon-btn" href="javascript:history.back()" aria-label="Kembali" style="margin-left:-8px"><?= icon('arrow-left', 22) ?></a>
<h1 class="title" style="margin-top:16px">Hubungi kami</h1>
<p class="lead">Tim Drivana siap membantu pesanan, pembayaran, dan akunmu.</p>

<div class="stack" style="margin-top:24px">
    <div class="card" style="display:flex;gap:12px;padding:16px"><?= icon('mail', 20) ?><div><b>Email</b><p class="muted">bantuan@drivana.test</p></div></div>
    <div class="card" style="display:flex;gap:12px;padding:16px"><?= icon('message-circle', 20) ?><div><b>WhatsApp</b><p class="muted">0812-0000-0000</p></div></div>
    <div class="card" style="display:flex;gap:12px;padding:16px"><?= icon('clock', 20) ?><div><b>Jam layanan</b><p class="muted">Senin–Minggu, 08.00–21.00 WIB</p></div></div>
    <p class="muted caption">Untuk keluhan terkait pesanan, kirim lewat menu Bantuan agar admin bisa membalas langsung ke akunmu.</p>
</div>

<?= $this->endSection() ?>
