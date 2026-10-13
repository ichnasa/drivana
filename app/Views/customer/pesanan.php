<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php $tabs = ['semua' => 'Semua', 'aktif' => 'Aktif', 'menunggu' => 'Menunggu', 'selesai' => 'Riwayat']; ?>

<?= partial('components/topbar', ['title' => 'Pesanan saya', 'right' => bell_btn()]) ?>

<div class="page" style="padding-top:12px">
    <div class="chips">
        <?php foreach ($tabs as $k => $label): ?><a class="chip <?= $tab === $k ? 'is-active' : '' ?>" href="<?= site_url('pesanan') . ($k === 'semua' ? '' : '?tab=' . $k) ?>"><?= $label ?></a><?php endforeach ?>
    </div>

    <?php if (! $orders): ?>
        <div class="empty"><?= icon('calendar-x', 28) ?><p>Belum ada pesanan di sini.</p><a class="btn btn-primary btn-sm" href="<?= site_url('cari') ?>">Cari mobil</a></div>
    <?php endif ?>

    <div class="stack">
        <?php foreach ($orders as $o): ?>
            <a class="order" href="<?= site_url('pesanan/' . $o['code']) ?>" style="display:block">
                <div class="order-top"><b><?= esc($o['code']) ?></b><?= badge_for($o['status']) ?></div>
                <div class="order-main">
                    <div class="photo"><img src="<?= car_img($o['car_image']) ?>" alt="" loading="lazy"></div>
                    <div><h3><?= esc($o['car_name']) ?></h3><p><?= esc($o['business_name']) ?></p></div>
                </div>
                <div class="kv">
                    <div><?= icon('calendar', 14) ?> <?= date_id($o['start_date'], false) ?> – <?= date_id($o['end_date']) ?> (<?= $o['days'] ?> hari)</div>
                    <div><?= icon('map-pin', 14) ?> <?= esc($o['area']) ?></div>
                    <div><?= icon('banknote', 14) ?> <b style="color:var(--ink)"><?= rupiah($o['total']) ?></b></div>
                </div>
                <?php if ($o['status'] === 'pending_payment'): ?><span class="btn btn-primary btn-sm" style="width:100%">Bayar sekarang</span>
                <?php elseif ($o['status'] === 'selesai'): ?><span class="muted caption">Ketuk untuk memberi ulasan atau pesan lagi</span><?php endif ?>
            </a>
        <?php endforeach ?>
    </div>
</div>

<?= $this->endSection() ?>
