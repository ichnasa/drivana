<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php $max = max(1, max($trend)); ?>

<div class="greet">
    <?= avatar($me['name'], 44) ?>
    <div style="flex:1"><small>Selamat datang,</small><b><?= esc($me['name']) ?></b></div>
    <?= bell_btn('admin') ?>
</div>

<div class="page">
    <?= partial('components/alerts') ?>

    <div class="figure-row"><div class="figure"><small>Sewa berjalan sekarang</small><strong><?= $live ?></strong><span class="badge"><?= $bookings_month ?> booking bulan ini</span></div></div>
    <div class="figure-row"><div class="figure sm"><small>Pendapatan platform bulan ini</small><strong><?= rupiah($revenue) ?></strong><small>Biaya layanan <?= rupiah($fees) ?> · Premium dan boost <?= rupiah($sales) ?></small></div></div>

    <div class="section-head"><h2>Pengguna</h2><a href="<?= site_url('admin/pengguna') ?>">Kelola</a></div>
    <div class="stat-row">
        <div><small>Total</small><b><?= number_format($users['total'], 0, ',', '.') ?></b></div>
        <div><small>Menunggu</small><b><?= $users['pending'] ?></b></div>
        <div><small>Terverifikasi</small><b><?= $users['verified'] ?></b></div>
    </div>
    <p class="muted caption" style="margin-top:12px"><?= $users['rejected'] ?> ditolak · <?= $users['unverified'] ?> belum mengirim dokumen</p>

    <div class="section-head"><h2>Perlu perhatian</h2></div>
    <div>
        <a class="row" href="<?= site_url('admin/verifikasi') ?>"><?= icon('shield-check', 22) ?><span style="flex:1"><b style="display:block">Dokumen menunggu</b><small class="muted"><?= $users['pending'] ?> akun menunggu pemeriksaan</small></span><?= icon('chevron-right', 18) ?></a>
        <a class="row" href="<?= site_url('admin/mobil') ?>"><?= icon('car', 22) ?><span style="flex:1"><b style="display:block">Mobil baru</b><small class="muted"><?= $cars_pending ?> menunggu tinjauan</small></span><?= icon('chevron-right', 18) ?></a>
        <a class="row" href="<?= site_url('admin/keluhan') ?>"><?= icon('message-square-warning', 22) ?><span style="flex:1"><b style="display:block">Keluhan</b><small class="muted"><?= $complaints_open ?> belum dibalas</small></span><?= icon('chevron-right', 18) ?></a>
    </div>

    <?php if ($attention): ?>
        <div class="section-head"><h2>Antrean terlama</h2><a href="<?= site_url('admin/verifikasi') ?>">Periksa</a></div>
        <div class="u-list">
            <?php foreach ($attention as $u): ?>
                <article class="u-card"><div class="u-head"><?= avatar($u['name'], 40) ?><div><b><?= esc($u['name']) ?></b><small><?= esc($u['task']) ?> · <?= esc($u['meta']) ?></small></div><span class="badge <?= $u['urgent'] ? 'badge-yellow' : '' ?>"><?= $u['urgent'] ? 'Mendesak' : ucfirst($u['role']) ?></span></div></article>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <div class="section-head"><h2>Dokumen diperiksa</h2><span class="muted caption">7 hari terakhir</span></div>
    <div class="bars"><?php foreach ($trend as $day => $v): ?><div class="col"><small><?= $v ?: '' ?></small><div class="bar <?= $day === $peak && $v ? 'is-peak' : '' ?>" style="height:<?= $v ? max(6, round($v / $max * 80)) : 2 ?>%"></div><small><?= $day ?></small></div><?php endforeach ?></div>

    <div class="section-head"><h2>Aktivitas terbaru</h2><a href="<?= site_url('admin/log') ?>">Semua log</a></div>
    <div class="timeline"><?php foreach ($logs as $a): ?><div class="tl"><span class="dot <?= $a['tone'] === 'success' ? 'green' : ($a['tone'] === 'danger' ? 'red' : 'blue') ?>"></span><div><b><?= esc($a['text']) ?></b><?php if ($a['meta']): ?><small><?= esc($a['meta']) ?></small><?php endif ?><small><?= esc(\App\Libraries\Drivana::ago($a['created_at'])) ?></small></div></div><?php endforeach ?></div>

    <form action="<?= site_url('logout') ?>" method="post" style="margin-top:32px"><?= csrf_field() ?><button class="btn btn-danger" type="submit"><?= icon('log-out', 20) ?> Keluar dari akun</button></form>
</div>

<?= $this->endSection() ?>
