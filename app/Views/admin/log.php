<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<?= partial('components/topbar', ['title' => 'Aktivitas', 'right' => bell_btn('admin')]) ?>

<div class="page" style="padding-top:16px">
    <div class="menu-group" style="margin-bottom:8px">
        <a class="row" href="<?= site_url('admin/transaksi') ?>"><?= icon('receipt', 22) ?><span style="flex:1"><b>Transaksi Booking</b></span><?= icon('chevron-right', 18) ?></a>
    </div>
    <div class="timeline">
        <?php foreach ($rows as $a): ?><div class="tl"><span class="dot <?= $a['tone'] === 'success' ? 'green' : ($a['tone'] === 'danger' ? 'red' : 'blue') ?>"></span><div><b><?= esc($a['text']) ?></b><?php if ($a['meta']): ?><small><?= esc($a['meta']) ?></small><?php endif ?><small><?= esc(\App\Libraries\Drivana::ago($a['created_at'])) ?></small></div></div><?php endforeach ?>
    </div>
</div>

<?= $this->endSection() ?>
