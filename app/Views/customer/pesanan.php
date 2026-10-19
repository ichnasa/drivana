<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php $tabs = ['semua' => 'Semua', 'aktif' => 'Aktif', 'menunggu' => 'Menunggu Konfirmasi', 'selesai' => 'Riwayat']; ?>

<?= partial('components/topbar', ['title' => 'Pesanan saya', 'right' => bell_btn()]) ?>

<div class="page" style="padding-top:12px">
    <div class="chips">
        <?php foreach ($tabs as $k => $label): ?><a class="chip <?= $tab === $k ? 'is-active' : '' ?>" href="<?= site_url('pesanan') . ($k === 'semua' ? '' : '?tab=' . $k) ?>"><?= $label ?></a><?php endforeach ?>
    </div>

    <?php if (! $orders): ?>
        <div class="empty"><?= icon('calendar-x', 28) ?><p>Belum ada pesanan di sini.</p><a class="btn btn-primary btn-sm" href="<?= site_url('cari') ?>">Cari mobil</a></div>
    <?php endif ?>

    <div class="stack">
        <?php foreach ($orders as $o): $detail = site_url('pesanan/' . $o['code']); $chat = site_url('chat/mulai/' . $o['mitra_id']) . '?mobil=' . $o['car_id']; ?>
            <article class="order">
                <a href="<?= $detail ?>" style="display:block">
                    <div class="order-top"><b><?= esc($o['code']) ?></b><?= badge_for(\App\Libraries\Drivana::displayStatus($o)) ?></div>
                    <div class="order-main">
                        <div class="photo"><img src="<?= car_img($o['car_image']) ?>" alt="" loading="lazy"></div>
                        <div><h3><?= esc($o['car_name']) ?></h3><p><?= esc($o['business_name']) ?></p></div>
                    </div>
                    <div class="kv">
                        <div><?= icon('calendar', 14) ?> <?= date_id($o['start_date'], false) ?> – <?= date_id($o['end_date']) ?> (<?= $o['days'] ?> hari)</div>
                        <div><?= icon('map-pin', 14) ?> <?= esc($o['area']) ?></div>
                        <div><?= icon('banknote', 14) ?> <b style="color:var(--ink)"><?= rupiah($o['total']) ?></b></div>
                    </div>
                </a>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px">
                    <?php if ($o['status'] === 'pending_payment'): ?>
                        <a class="btn btn-primary btn-sm" style="flex:1" href="<?= site_url('bayar/' . $o['code']) ?>">Bayar sekarang</a>
                    <?php elseif ($o['status'] === 'selesai'): ?>
                        <a class="btn btn-primary btn-sm" style="flex:1" href="<?= site_url('mobil/' . $o['car_id']) ?>">Pesan lagi</a>
                        <?php if (! $o['reviewed']): ?><a class="btn btn-secondary btn-sm" style="flex:1" href="<?= $detail ?>#ulasan"><?= icon('star', 16) ?> Beri ulasan</a><?php endif ?>
                    <?php elseif (in_array($o['status'], ['menunggu', 'dikonfirmasi', 'aktif'], true)): ?>
                        <a class="btn btn-secondary btn-sm" style="flex:1" href="<?= $chat ?>"><?= icon('message-square', 16) ?> Chat mitra</a>
                    <?php endif ?>
                    <a class="btn btn-secondary btn-sm" style="flex:1" href="<?= $detail ?>">Lihat detail</a>
                    <?php if ($o['status'] === 'menunggu'): ?>
                        <button class="btn btn-secondary btn-sm" style="flex:1" type="button" data-modal-open="cancel-modal" data-fill-action="<?= site_url('pesanan/' . $o['code'] . '/batal') ?>" data-fill-name="<?= esc($o['car_name'], 'attr') ?>">Batalkan</button>
                    <?php endif ?>
                </div>
            </article>
        <?php endforeach ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<div class="modal" id="cancel-modal" role="dialog" aria-modal="true" aria-labelledby="cancel-title">
    <form class="modal-box" method="post" data-form-target novalidate style="text-align:left">
        <?= csrf_field() ?>
        <span class="ico"><?= icon('triangle-alert', 24) ?></span>
        <h3 id="cancel-title">Batalkan pesanan ini?</h3>
        <p class="muted" style="margin:-8px 0 16px">Pesanan <b data-slot="name"></b> dibatalkan dan dana dikembalikan penuh.</p>
        <div class="field"><label for="cancel-reason">Alasan (opsional)</label><div class="input"><input id="cancel-reason" name="reason" maxlength="200" placeholder="Rencana berubah"></div></div>
        <div class="modal-actions"><button class="btn btn-secondary" type="button" data-modal-close>Tidak jadi</button><button class="btn btn-danger" type="submit" style="background:var(--error);color:#fff"><span>Batalkan</span></button></div>
    </form>
</div>
<?= $this->endSection() ?>
