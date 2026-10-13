<?php
/**
 * Main app shell (mobile column) for Customer / Mitra / Admin screens.
 * Vars: $title, $role ('customer'|'mitra'|'admin'), $active (nav key), $nav (bool, default true)
 */
$role   = $role ?? 'customer';
$showNav = $nav ?? true;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Drivana') ?> · Drivana</title>
    <link rel="icon" href="<?= base_url('assets/img/favicon.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/drivana.css') ?>">
</head>
<body>
<?= partial('components/toast') ?>
<main class="app<?= $showNav ? ' has-nav' : '' ?>">
    <?= $this->renderSection('content') ?>

    <?php if ($showNav): ?>
        <nav class="bottom-nav" aria-label="Navigasi utama">
            <?php foreach (nav_items($role) as $key => [$label, $ico, $url]): ?>
                <a href="<?= $url === '#' ? '#' : site_url($url) ?>" class="<?= ($active ?? '') === $key ? 'is-active' : '' ?>">
                    <?= icon($ico, 24) ?>
                    <span><?= esc($label) ?></span>
                </a>
            <?php endforeach ?>
        </nav>
    <?php endif ?>
</main>
<?= $this->renderSection('modals') ?>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
