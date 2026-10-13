<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$now = date('Y-m-d H:i:s');
$state = function (array $c) use ($now): string {
    if ($c['listing_status'] === 'pending') return 'review';
    if ($c['listing_status'] === 'rejected') return 'ditolak';
    if ($c['ops_status'] === 'servis') return 'servis';
    return $c['renting_now'] ? 'disewa' : 'tersedia';
};
$tabs = ['all' => 'Semua', 'tersedia' => 'Tersedia', 'disewa' => 'Disewa', 'servis' => 'Servis', 'review' => 'Ditinjau'];
$label = ['review' => ['Menunggu review', 'badge-yellow'], 'ditolak' => ['Ditolak', 'badge-red'], 'servis' => ['Servis', ''], 'disewa' => ['Disewa', 'badge-blue'], 'tersedia' => ['Tersedia', 'badge-green']];
?>

<?= partial('components/topbar', ['title' => 'Kelola armada', 'right' => bell_btn()]) ?>

<div class="page" style="padding-top:12px">
    <?= partial('components/alerts') ?>
    <div class="chips" data-filter-group="#fleet">
        <?php foreach ($tabs as $k => $l): ?><button class="chip <?= $k === 'all' ? 'is-active' : '' ?>" type="button" data-filter="<?= $k ?>"><?= $l ?></button><?php endforeach ?>
    </div>

    <?php if (! $cars): ?><div class="empty"><?= icon('car', 28) ?><p>Belum ada mobil. Tambahkan armada pertamamu.</p></div><?php endif ?>

    <div id="fleet" class="stack">
        <?php foreach ($cars as $c): $st = $state($c); $boost = $c['boosted_until'] && $c['boosted_until'] > $now; ?>
            <article class="fleet-card is-<?= $st ?>" data-status="<?= $st ?>">
                <div class="photo">
                    <img src="<?= car_img($c['image']) ?>" alt="<?= esc($c['name']) ?>" loading="lazy">
                    <span class="badge <?= $label[$st][1] ?>"><?= $label[$st][0] ?></span>
                    <?php if ($boost): ?><span class="badge badge-black" style="left:12px;right:auto">Boost sampai <?= date_id($c['boosted_until'], false) ?></span><?php endif ?>
                </div>
                <div class="body">
                    <h3><?= esc($c['name']) ?> <?= $c['year'] ?></h3>
                    <p class="plate"><?= esc($c['plate']) ?> · <?= esc($c['area']) ?></p>
                    <div class="fleet-meta"><span><?= icon('settings-2', 16) ?> <?= esc($c['transmission']) ?></span><span><?= icon('users', 16) ?> <?= $c['seats'] ?> kursi</span><span class="price"><b><?= rupiah($c['price'], true) ?></b> <small>/hari</small></span></div>
                    <?php if ($st === 'ditolak'): ?><div class="ind-note" style="margin-top:12px"><?= icon('circle-alert', 14) ?> <?= esc($c['reject_reason']) ?> Edit lalu ajukan ulang.</div><?php endif ?>
                    <?php if ($st === 'servis'): ?><div class="notice"><?= icon('wrench', 16) ?> Estimasi selesai: <?= date_id($c['service_until']) ?></div><?php endif ?>
                    <div class="actions" style="margin-top:14px">
                        <a class="btn btn-secondary" href="<?= site_url('mitra/armada/' . $c['id'] . '/edit') ?>">Edit</a>
                        <?php if ($c['listing_status'] === 'approved'): ?>
                            <?php if ($c['ops_status'] === 'servis'): ?>
                                <form action="<?= site_url('mitra/armada/' . $c['id'] . '/status') ?>" method="post" style="flex:1"><?= csrf_field() ?><input type="hidden" name="status" value="tersedia"><button class="btn btn-secondary" type="submit" style="width:100%">Tersedia lagi</button></form>
                            <?php else: ?>
                                <button class="btn btn-secondary" type="button" data-modal-open="service-modal" data-fill-action="<?= site_url('mitra/armada/' . $c['id'] . '/status') ?>" data-fill-name="<?= esc($c['name'], 'attr') ?>">Servis</button>
                            <?php endif ?>
                            <a class="btn btn-soft" href="<?= site_url('mitra/boost?mobil=' . $c['id']) ?>"><?= icon('rocket', 16) ?></a>
                        <?php endif ?>
                        <button class="btn btn-danger" type="button" style="width:auto" data-modal-open="delete-modal" data-fill-action="<?= site_url('mitra/armada/' . $c['id'] . '/hapus') ?>" data-fill-name="<?= esc($c['name'], 'attr') ?>" aria-label="Hapus"><?= icon('trash-2', 16) ?></button>
                    </div>
                </div>
            </article>
        <?php endforeach ?>
    </div>
</div>

<?php if ($me['verification_status'] === 'verified'): ?><a class="fab" href="<?= site_url('mitra/armada/tambah') ?>" aria-label="Tambah kendaraan"><?= icon('plus', 28) ?></a><?php endif ?>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<div class="modal" id="service-modal" role="dialog" aria-modal="true" aria-labelledby="service-title">
    <form class="modal-box" method="post" data-form-target novalidate style="text-align:left">
        <?= csrf_field() ?><input type="hidden" name="status" value="servis">
        <h3 id="service-title">Tandai servis</h3>
        <p class="muted" style="margin:-8px 0 16px"><b data-slot="name"></b> tidak tampil di pencarian selama servis.</p>
        <div class="field"><label for="service-until">Estimasi selesai</label><div class="input"><?= icon('calendar', 18) ?><input id="service-until" type="date" name="until" min="<?= date('Y-m-d') ?>" required data-error="Pilih tanggal hari ini atau setelahnya."></div><p class="field-error" role="alert"></p></div>
        <div class="modal-actions"><button class="btn btn-secondary" type="button" data-modal-close>Batal</button><button class="btn btn-primary" type="submit"><span>Simpan</span></button></div>
    </form>
</div>
<div class="modal" id="delete-modal" role="dialog" aria-modal="true" aria-labelledby="delete-title">
    <form class="modal-box" method="post" data-form-target style="text-align:left">
        <?= csrf_field() ?>
        <span class="ico"><?= icon('trash-2', 24) ?></span>
        <h3 id="delete-title">Hapus dari armada?</h3>
        <p class="muted" style="margin:-8px 0 16px"><b data-slot="name"></b> tidak akan tampil lagi. Tidak bisa dibatalkan.</p>
        <div class="modal-actions"><button class="btn btn-secondary" type="button" data-modal-close>Batal</button><button class="btn btn-danger" type="submit" style="background:var(--error);color:#fff"><span>Hapus</span></button></div>
    </form>
</div>
<?= $this->endSection() ?>
