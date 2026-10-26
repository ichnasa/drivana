<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<?= partial('components/topbar', ['title' => 'Chat', 'right' => bell_btn()]) ?>

<div class="page" style="padding-top:16px">
    <?php if ($total): ?>
        <form class="searchbar" action="<?= site_url($base) ?>" method="get" style="margin-bottom:12px"><?= icon('search', 20) ?><input type="search" name="q" value="<?= esc($q, 'attr') ?>" placeholder="Cari percakapan" aria-label="Cari percakapan"></form>
    <?php endif ?>
    <?php if (! $list && $q !== ''): ?>
        <div class="empty"><?= icon('search', 28) ?><p>Tidak ada percakapan yang cocok dengan “<?= esc($q) ?>”.</p></div>
    <?php elseif (! $list): ?>
        <div class="empty"><?= icon('message-circle', 28) ?><p>Tidak ada percakapan. <?= $role === 'mitra' ? 'Belum ada percakapan. Pesan dari penyewa muncul di sini.' : 'Belum ada percakapan. Buka detail mobil lalu ketuk ikon chat untuk menghubungi mitra.' ?></p><?php if ($role !== 'mitra'): ?><a class="btn btn-primary btn-sm" href="<?= site_url('cari') ?>">Cari mobil</a><?php endif ?></div>
    <?php endif ?>
    <div class="stack">
        <?php foreach ($list as $c): ?>
            <a class="conv" href="<?= site_url($base . '/' . $c['id']) ?>">
                <?= avatar($c['other_name'], 48) ?>
                <div style="flex:1;min-width:0">
                    <div style="display:flex;justify-content:space-between;gap:8px"><b><?= esc($c['other_name']) ?></b><span class="muted caption"><?= esc(\App\Libraries\Drivana::ago($c['last_message_at'])) ?></span></div>
                    <small><?= $c['car_name'] ? esc($c['car_name']) . ' · ' : '' ?><?= esc($c['last_body']) ?></small>
                </div>
                <?php if ($c['unread']): ?><span class="badge badge-black" aria-label="<?= $c['unread'] ?> pesan baru"><?= $c['unread'] ?></span><?php endif ?>
            </a>
        <?php endforeach ?>
    </div>
</div>

<?= $this->endSection() ?>
