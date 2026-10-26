<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<?= partial('components/topbar', ['title' => 'Pengaturan', 'right' => bell_btn('admin')]) ?>

<div class="page" style="padding-top:16px">
    <?= partial('components/alerts') ?>

    <div class="card" style="display:flex;align-items:center;gap:12px;padding:16px">
        <?= avatar($me['name'], 48) ?>
        <div style="flex:1;min-width:0"><b style="display:block"><?= esc($me['name']) ?></b><small class="muted"><?= esc($me['email']) ?></small></div>
        <span class="badge badge-green">Admin</span>
    </div>

    <div class="section-head"><h2>Kelola</h2></div>
    <div class="menu-group">
        <a class="row" href="<?= site_url('admin/mobil') ?>"><?= icon('car', 22) ?><span style="flex:1"><b style="display:block">Kelola Data Mobil</b><small class="muted"><?= $cars_pending ?> menunggu tinjauan</small></span><?= icon('chevron-right', 18) ?></a>
        <a class="row" href="<?= site_url('admin/transaksi') ?>"><?= icon('receipt', 22) ?><span style="flex:1"><b style="display:block">Transaksi Booking</b><small class="muted">Pantau booking dan pembayaran</small></span><?= icon('chevron-right', 18) ?></a>
        <a class="row" href="<?= site_url('admin/notifikasi') ?>"><?= icon('bell', 22) ?><span style="flex:1"><b style="display:block">Notifikasi</b></span><?= icon('chevron-right', 18) ?></a>
    </div>

    <form action="<?= site_url('logout') ?>" method="post" style="margin-top:32px"><?= csrf_field() ?><button class="btn btn-danger" type="submit"><?= icon('log-out', 20) ?> Keluar dari Akun</button></form>
</div>

<?= $this->endSection() ?>
