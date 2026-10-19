<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$mitra = $me['role'] === 'mitra';
$name = $mitra ? ($me['business_name'] ?: $me['name']) : $me['name'];
$menu = [
    [['id-card', 'Edit profil', 'profil/edit'], ['folder', 'Dokumen saya', 'profil/dokumen'], ['map-pin', 'Alamat tersimpan', 'profil/alamat']],
    $mitra
        ? [['crown', 'Langganan Premium', 'langganan'], ['wallet', 'Riwayat pendapatan', 'pendapatan'], ['shield-check', 'Keamanan', 'profil/keamanan'], ['bell', 'Notifikasi', 'notifikasi']]
        : [['shield-check', 'Keamanan', 'profil/keamanan'], ['bell', 'Notifikasi', 'notifikasi']],
    [['life-buoy', 'Bantuan dan keluhan', 'bantuan'], ['phone', 'Hubungi kami', '!kontak'], ['shield-check', 'Kebijakan privasi', '!privasi']],
];
?>

<div class="profile-head">
    <div class="profile-bell"><?= bell_btn() ?></div>
    <?= avatar($name, 88) ?>
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
    <form action="<?= site_url('logout') ?>" method="post"><?= csrf_field() ?><button class="btn btn-danger" type="submit"><?= icon('log-out', 20) ?> Keluar dari akun</button></form>
</div>

<?= $this->endSection() ?>
