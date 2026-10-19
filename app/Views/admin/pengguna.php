<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$ver = ['verified' => ['Terverifikasi', 'badge-green'], 'pending' => ['Menunggu', 'badge-yellow'], 'rejected' => ['Ditolak', 'badge-red'], 'unverified' => ['Belum verifikasi', '']];
$roles = ['' => 'Semua', 'customer' => 'Penyewa', 'mitra' => 'Mitra'];
?>

<?= partial('components/topbar', ['title' => 'Pengguna', 'sub' => count($users) . ' akun', 'right' => bell_btn('admin')]) ?>

<div class="page" style="padding-top:12px">
    <?= partial('components/alerts') ?>
    <form class="searchbar" action="<?= site_url('admin/pengguna') ?>" method="get"><?= icon('search', 20) ?><input type="search" name="q" value="<?= esc($q, 'attr') ?>" placeholder="Cari nama, email, atau rental" aria-label="Cari"><input type="hidden" name="peran" value="<?= esc($peran, 'attr') ?>"></form>
    <div class="chips" style="margin-top:12px"><?php foreach ($roles as $k => $l): ?><a class="chip <?= $peran === (string) $k ? 'is-active' : '' ?>" href="<?= site_url('admin/pengguna?peran=' . $k . ($q ? '&q=' . urlencode($q) : '')) ?>"><?= $l ?></a><?php endforeach ?></div>

    <div class="u-list">
        <?php foreach ($users as $u): $isOn = $u['status'] === 'active'; ?>
            <article class="u-card">
                <div class="u-head"><?= avatar($u['business_name'] ?: $u['name'], 42) ?><div><b><?= esc($u['business_name'] ?: $u['name']) ?></b><small><?= esc($u['email']) ?></small></div><span class="badge <?= $ver[$u['verification_status']][1] ?>"><?= $ver[$u['verification_status']][0] ?></span></div>
                <div class="u-body"><span><?= icon($u['role'] === 'mitra' ? 'store' : 'user', 14) ?> <?= $u['role'] === 'mitra' ? 'Mitra' : 'Penyewa' ?> · bergabung <?= date_id($u['created_at']) ?></span><?php if (! $isOn): ?><span><?= icon('ban', 14) ?> Akun dinonaktifkan</span><?php endif ?></div>
                <button class="btn <?= $isOn ? 'btn-secondary' : 'btn-primary' ?>" style="height:44px;font-size:14px" type="button" data-modal-open="user-modal" data-fill-action="<?= site_url('admin/pengguna/' . $u['id'] . '/status') ?>" data-fill-name="<?= esc($u['business_name'] ?: $u['name'], 'attr') ?>" data-fill-verb="<?= $isOn ? 'Nonaktifkan' : 'Aktifkan kembali' ?>"><?= icon($isOn ? 'user-x' : 'user-check', 16) ?> <?= $isOn ? 'Nonaktifkan akun' : 'Aktifkan kembali' ?></button>
            </article>
        <?php endforeach ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<div class="modal" id="user-modal" role="dialog" aria-modal="true" aria-labelledby="user-title">
    <form class="modal-box" method="post" data-form-target style="text-align:left">
        <?= csrf_field() ?>
        <h3 id="user-title"><span data-slot="verb"></span>?</h3>
        <p class="muted" style="margin:-8px 0 16px"><b data-slot="name"></b>. Akun nonaktif tidak bisa masuk dan mobilnya disembunyikan dari pencarian.</p>
        <div class="modal-actions"><button class="btn btn-secondary" type="button" data-modal-close>Batal</button><button class="btn btn-primary" type="submit"><span>Ya, lanjut</span></button></div>
    </form>
</div>
<?= $this->endSection() ?>
