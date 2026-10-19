<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<div class="call">
    <div class="call-top">
        <a class="icon-btn" href="<?= site_url($base . '/' . $c['id']) ?>" aria-label="Kecilkan"><?= icon('chevron-down', 24) ?></a>
        <div><b data-call-status>Memanggil…</b><small><?= icon('lock', 12) ?> Terenkripsi</small></div>
        <a class="icon-btn" href="<?= site_url($base . '/' . $c['id']) ?>" aria-label="Informasi"><?= icon('info', 22) ?></a>
    </div>

    <div class="call-who">
        <?= avatar($c['other_name'], 112) ?>
        <h1><?= esc($c['other_name']) ?></h1>
        <p class="muted" data-call-time>00:00</p>
    </div>

    <div class="call-grid">
        <button type="button" class="call-btn" data-call-toggle aria-pressed="false"><?= icon('mic-off', 24) ?><span>Bisukan</span></button>
        <button type="button" class="call-btn" data-call-toggle aria-pressed="false"><?= icon('grid-3x3', 24) ?><span>Keypad</span></button>
        <button type="button" class="call-btn" data-call-toggle aria-pressed="false"><?= icon('volume-2', 24) ?><span>Speaker</span></button>
        <button type="button" class="call-btn" data-call-toggle aria-pressed="false"><?= icon('plus', 24) ?><span>Tambah</span></button>
        <button type="button" class="call-btn" data-call-toggle aria-pressed="false"><?= icon('video', 24) ?><span>Video</span></button>
        <button type="button" class="call-btn" data-call-toggle aria-pressed="false"><?= icon('radio', 24) ?><span>Bluetooth</span></button>
    </div>

    <a class="call-end" href="<?= site_url($base . '/' . $c['id']) ?>" aria-label="Akhiri panggilan"><?= icon('phone-off', 28) ?></a>
</div>

<?= $this->endSection() ?>
