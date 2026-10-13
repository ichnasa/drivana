<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<?= partial('components/topbar', ['title' => 'Notifikasi', 'back' => site_url($role === 'admin' ? 'admin/dashboard' : ($role === 'mitra' ? 'mitra/dashboard' : 'beranda'))]) ?>

<div class="page" style="padding-top:8px">
    <?php if (! $list): ?><div class="empty"><?= icon('bell-off', 28) ?><p>Belum ada notifikasi.</p></div><?php endif ?>
    <?php foreach ($list as $n): ?>
        <a class="row notif <?= $n['read_at'] === null ? 'is-new' : '' ?>" href="<?= $n['url'] ? site_url($n['url']) : '#' ?>">
            <span class="nd" aria-hidden="true"></span>
            <span style="flex:1"><b style="display:block"><?= esc($n['title']) ?></b><?php if ($n['body']): ?><small class="muted"><?= esc($n['body']) ?></small><?php endif ?><small class="muted" style="display:block;margin-top:2px"><?= esc(\App\Libraries\Drivana::ago($n['created_at'])) ?></small></span>
            <?= icon('chevron-right', 18) ?>
        </a>
    <?php endforeach ?>
</div>

<?= $this->endSection() ?>
