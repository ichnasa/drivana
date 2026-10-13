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
<main class="app auth">
    <?= $this->renderSection('content') ?>
</main>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
