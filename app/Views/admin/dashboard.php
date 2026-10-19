<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php $max = max(1, max($trend)); ?>

<div class="mitra-hero">
    <div class="greet">
        <?= avatar($me['name'], 48) ?>
        <div style="flex:1"><small>Selamat datang,</small><b><?= esc($me['name']) ?></b></div>
        <?= bell_btn('admin') ?>
    </div>
    <div class="glass-card">
        <div><small>Booking Aktif Hari Ini</small><strong><?= $live ?></strong><span class="badge"><?= $bookings_month ?> booking bulan ini</span></div>
        <span class="glass-ico"><?= icon('calendar', 28) ?></span>
    </div>
</div>

<div class="page">
    <?= partial('components/alerts') ?>

    <a class="card kpi" href="<?= site_url('admin/transaksi') ?>">
        <div><small>Pendapatan Bulan Ini</small><strong><?= rupiah($revenue) ?></strong><small style="display:block;margin-top:4px">Biaya layanan <?= rupiah($fees) ?> · Premium dan boost <?= rupiah($sales) ?></small></div>
        <span class="kpi-ico"><?= icon('banknote', 24) ?></span>
    </a>

    <div class="section-head"><h2>Ringkasan Pengguna</h2><a href="<?= site_url('admin/pengguna') ?>">Kelola</a></div>
    <div class="stat-row">
        <div><small>Total</small><b><?= number_format($users['total'], 0, ',', '.') ?></b></div>
        <div><small>Menunggu</small><b><?= $users['pending'] ?></b></div>
        <div><small>Terverifikasi</small><b><?= $users['verified'] ?></b></div>
    </div>
    <div class="card" style="display:flex;align-items:center;justify-content:space-between;margin-top:12px;padding:14px 16px"><div><small class="muted">Pengajuan Ditolak</small><br><b style="font-size:22px"><?= $users['rejected'] ?></b></div><span class="badge badge-red"><?= $users['unverified'] ?> belum mengirim dokumen</span></div>

    <div class="section-head"><h2>Tren Verifikasi</h2><span class="muted caption">7 hari terakhir</span></div>
    <div class="bars"><?php foreach ($trend as $day => $v): ?><div class="col"><small><?= $v ?: '' ?></small><div class="bar <?= $day === $peak && $v ? 'is-peak' : '' ?>" style="height:<?= $v ? max(6, round($v / $max * 80)) : 2 ?>%"></div><small><?= $day ?></small></div><?php endforeach ?></div>

    <div class="section-head"><h2>Memerlukan Perhatian</h2><a href="<?= site_url('admin/verifikasi') ?>">Periksa</a></div>
    <div class="u-list">
        <?php foreach ($attention as $u): ?>
            <article class="u-card">
                <div class="u-head"><?= avatar($u['name'], 40) ?><div><b><?= esc($u['name']) ?></b><small><?= esc($u['task']) ?> · <?= esc($u['meta']) ?></small></div><span class="badge <?= $u['urgent'] ? 'badge-yellow' : '' ?>"><?= $u['urgent'] ? 'Mendesak' : ucfirst($u['role']) ?></span></div>
                <div style="display:flex;gap:8px;margin-top:12px"><a class="btn btn-secondary" style="height:44px;font-size:14px" href="<?= site_url('admin/verifikasi?status=menunggu') ?>">Lihat Detail</a><a class="btn btn-primary" style="height:44px;font-size:14px" href="<?= site_url('admin/verifikasi?status=menunggu') ?>">Periksa</a></div>
            </article>
        <?php endforeach ?>
        <a class="row" href="<?= site_url('admin/mobil') ?>"><?= icon('car', 22) ?><span style="flex:1"><b style="display:block">Mobil baru</b><small class="muted"><?= $cars_pending ?> menunggu tinjauan</small></span><?= icon('chevron-right', 18) ?></a>
        <a class="row" href="<?= site_url('admin/keluhan') ?>"><?= icon('message-square-warning', 22) ?><span style="flex:1"><b style="display:block">Keluhan</b><small class="muted"><?= $complaints_open ?> belum dibalas</small></span><?= icon('chevron-right', 18) ?></a>
    </div>

    <div class="section-head"><h2>Aktivitas Terbaru</h2><a href="<?= site_url('admin/aktivitas') ?>">Semua Log</a></div>
    <div class="timeline"><?php foreach ($logs as $a): ?><div class="tl"><span class="dot <?= $a['tone'] === 'success' ? 'green' : ($a['tone'] === 'danger' ? 'red' : 'blue') ?>"></span><div><b><?= esc($a['text']) ?></b><?php if ($a['meta']): ?><small><?= esc($a['meta']) ?></small><?php endif ?><small><?= esc(\App\Libraries\Drivana::ago($a['created_at'])) ?></small></div></div><?php endforeach ?></div>
</div>

<?= $this->endSection() ?>
