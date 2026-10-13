<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<?= partial('components/topbar', ['title' => 'Aktivitas', 'back' => site_url('admin/dashboard')]) ?>

<div class="page" style="padding-top:16px">
    <div class="timeline">
        <?php foreach ($rows as $a): ?><div class="tl"><span class="dot <?= $a['tone'] === 'success' ? 'green' : ($a['tone'] === 'danger' ? 'red' : 'blue') ?>"></span><div><b><?= esc($a['text']) ?></b><?php if ($a['meta']): ?><small><?= esc($a['meta']) ?></small><?php endif ?><small><?= esc(\App\Libraries\Drivana::ago($a['created_at'])) ?></small></div></div><?php endforeach ?>
    </div>
</div>

<?= $this->endSection() ?>
