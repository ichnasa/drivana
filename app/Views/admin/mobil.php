<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$tabs = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];
$reasonErr = form_error('reason'); $errCar = form_error('car');
?>

<?= partial('components/topbar', ['title' => 'Tinjau mobil', 'sub' => $counts['pending'] . ' menunggu tinjauan', 'right' => bell_btn('admin')]) ?>

<div class="page" style="padding-top:12px">
    <?= partial('components/alerts') ?>
    <div class="chips"><?php foreach ($tabs as $k => $l): ?><a class="chip <?= $status === $k ? 'is-active' : '' ?>" href="<?= site_url('admin/mobil?status=' . $k) ?>"><?= $l ?> (<?= $counts[$k] ?>)</a><?php endforeach ?></div>
    <?php if (! $cars): ?><div class="empty"><?= icon('car', 28) ?><p>Tidak ada mobil di sini.</p></div><?php endif ?>
    <div class="stack">
        <?php foreach ($cars as $c): ?>
            <article class="fleet-card">
                <div class="photo"><img src="<?= car_img($c['image']) ?>" alt="<?= esc($c['name']) ?>" loading="lazy"></div>
                <div class="body">
                    <h3><?= esc($c['name']) ?> <?= $c['year'] ?></h3>
                    <p class="plate"><?= esc($c['plate']) ?> · <?= esc($c['mitra']['business_name']) ?></p>
                    <div class="fleet-meta"><span><?= esc($c['type']) ?></span><span><?= $c['seats'] ?> kursi</span><span><?= esc($c['area']) ?></span><span class="price"><b><?= rupiah($c['price'], true) ?></b></span></div>
                    <?php if ($c['listing_status'] === 'rejected'): ?><div class="ind-note" style="margin-top:12px"><?= icon('circle-alert', 14) ?> <?= esc($c['reject_reason']) ?></div><?php endif ?>
                    <?php if ($c['listing_status'] === 'pending'): ?>
                        <div class="actions" style="margin-top:14px"><button class="btn btn-danger" type="button" data-modal-open="car-<?= $c['id'] ?>">Tolak</button>
                            <form action="<?= site_url('admin/mobil/' . $c['id'] . '/setujui') ?>" method="post" style="flex:1"><?= csrf_field() ?><button class="btn btn-primary" type="submit" style="height:44px;font-size:14px"><span>Setujui</span></button></form></div>
                    <?php endif ?>
                </div>
            </article>
        <?php endforeach ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?php foreach ($cars as $c): if ($c['listing_status'] !== 'pending') continue; $mine = $errCar === (string) $c['id']; ?>
    <div class="modal" id="car-<?= $c['id'] ?>" role="dialog" aria-modal="true" aria-label="Tolak mobil">
        <form class="modal-box" action="<?= site_url('admin/mobil/' . $c['id'] . '/tolak') ?>" method="post" novalidate style="text-align:left">
            <?= csrf_field() ?>
            <span class="ico"><?= icon('triangle-alert', 24) ?></span>
            <h3>Tolak <?= esc($c['name']) ?>?</h3>
            <div class="field"><label for="cr-<?= $c['id'] ?>">Alasan</label><div class="input<?= $mine && $reasonErr ? ' has-error' : '' ?>"><input id="cr-<?= $c['id'] ?>" name="reason" required minlength="5" maxlength="200" placeholder="Foto kurang jelas" data-label="Alasan"></div><p class="field-error" role="alert"><?= $mine && $reasonErr ? icon('circle-alert', 16) . ' ' . esc($reasonErr) : '' ?></p></div>
            <div class="modal-actions"><button class="btn btn-secondary" type="button" data-modal-close>Batal</button><button class="btn btn-danger" type="submit" style="background:var(--error);color:#fff"><span>Tolak</span></button></div>
        </form>
    </div>
<?php endforeach ?>
<?= $this->endSection() ?>
