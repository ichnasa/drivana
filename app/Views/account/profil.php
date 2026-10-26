<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$mitra = $me['role'] === 'mitra';
$name = $mitra ? ($me['business_name'] ?: $me['name']) : $me['name'];
$menu = [
    [['id-card', 'Edit Informasi Profil', 'profil/edit'], ['folder', 'Dokumen Saya', 'profil/dokumen'], ['map-pin', 'Alamat Tersimpan', 'profil/alamat']],
    $mitra
        ? [['crown', 'Langganan Premium', 'langganan'], ['wallet', 'Riwayat pendapatan', 'pendapatan'], ['shield-check', 'Keamanan', 'profil/keamanan'], ['bell', 'Notifikasi', 'notifikasi']]
        : [['shield-check', 'Keamanan', 'profil/keamanan'], ['bell', 'Notifikasi', 'notifikasi']],
    [['phone', 'Hubungi Kami', '!kontak'], ['shield-check', 'Kebijakan Privasi', '!privasi']],
];
?>

<div class="profile-head">
    <div class="profile-bell" style="display:flex;width:100%;align-items:center;justify-content:space-between"><?= bell_btn() ?><span style="display:flex"><a class="icon-btn" href="<?= site_url($mitra ? 'mitra/pendapatan' : 'pesanan') ?>" aria-label="Riwayat"><?= icon('history', 22) ?></a><a class="icon-btn" href="<?= site_url($p . 'profil/edit') ?>" aria-label="Opsi lainnya"><?= icon('ellipsis-vertical', 22) ?></a></span></div>
    <div style="position:relative"><?= avatar($name, 88) ?><a href="<?= site_url($p . 'profil/edit') ?>" aria-label="Ubah foto profil" style="position:absolute;right:-4px;bottom:-4px;display:grid;place-items:center;width:32px;height:32px;border-radius:50%;background:#fff;color:#0a2a6e"><?= icon('pencil', 16) ?></a></div>
    <h2><?= esc($name) ?></h2>
    <p class="email"><?= esc($me['email']) ?> · <?= esc($me['phone']) ?></p>
    <?php if ($me['verification_status'] === 'verified'): ?><span class="profile-verified"><?= icon('badge-check', 18) ?> Akun terverifikasi</span><?php endif ?>
</div>

<div style="padding:0 20px"><?= partial('components/verify_banner', ['me' => $me]) ?></div>

<div class="stats3">
    <?php foreach ($stats as $label => $v): ?><div class="stat"><b><?= esc((string) $v) ?></b><small><?= esc($label) ?></small></div><?php endforeach ?>
</div>

<?php foreach ($menu as $group): ?>
    <div class="menu-group">
        <?php foreach ($group as [$ico, $label, $url]): ?>
            <a class="row" href="<?= site_url($url[0] === '!' ? substr($url, 1) : $p . $url) ?>"><?= icon($ico, 20) ?><span><?= esc($label) ?></span><?= icon('chevron-right', 18) ?></a>
        <?php endforeach ?>
    </div>
<?php endforeach ?>

<div class="page" style="margin-top:16px">
    <form action="<?= site_url('logout') ?>" method="post"><?= csrf_field() ?><button class="btn btn-danger" type="submit"><?= icon('log-out', 20) ?> Keluar dari Akun</button></form>
</div>

<?= $this->endSection() ?>
