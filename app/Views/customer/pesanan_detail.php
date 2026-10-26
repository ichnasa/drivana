<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$car = $b['car']; $m = $b['mitra']; $rv = $b['review'];
$cancelable = in_array($b['status'], ['pending_payment', 'menunggu', 'dikonfirmasi'], true);
$ratingErr = form_error('rating'); $commentErr = form_error('comment');
?>

<?= partial('components/topbar', ['title' => $b['code'], 'back' => site_url('pesanan'), 'sub' => \App\Libraries\Drivana::statusLabel(\App\Libraries\Drivana::displayStatus($b))]) ?>

<div class="page has-cta stack" style="padding-top:20px">
    <?= partial('components/alerts') ?>

    <?php if ($b['status'] === 'pending_payment'): ?>
        <div class="banner banner-warn"><?= icon('clock', 20) ?><div><b>Belum dibayar</b><p>Bayar sebelum <?= date('H.i', strtotime($b['expires_at'])) ?> atau pesanan dibatalkan otomatis.</p></div></div>
    <?php endif ?>

    <div class="card" style="display:flex;gap:12px;padding:12px">
        <div class="photo" style="width:96px;height:68px;border-radius:8px;flex:none"><img src="<?= car_img($car['image']) ?>" alt=""></div>
        <div style="flex:1"><a href="<?= site_url('mobil/' . $car['id']) ?>"><b style="font-size:16px"><?= esc($car['name']) ?></b></a><p class="muted caption"><?= esc($m['business_name'] ?: $m['name']) ?> · <?= esc($car['plate']) ?></p></div>
    </div>

    <div class="panel"><h4>Status</h4><?= partial('components/timeline', ['b' => $b]) ?></div>

    <div class="panel">
        <h4>Detail sewa</h4>
        <div class="sum-row"><span>Tanggal</span><b><?= date_id($b['start_date'], false) ?> – <?= date_id($b['end_date']) ?></b></div>
        <div class="sum-row"><span>Durasi</span><b><?= $b['days'] ?> hari</b></div>
        <div class="sum-row"><span>Pengemudi</span><b><?= $b['with_driver'] ? 'Termasuk' : 'Lepas kunci' ?></b></div>
        <div class="sum-row"><span>Diantar ke</span><b style="text-align:right;max-width:60%"><?= esc($b['address']) ?></b></div>
        <?php if ($b['note']): ?><div class="sum-row"><span>Catatan</span><b style="text-align:right;max-width:60%"><?= esc($b['note']) ?></b></div><?php endif ?>
    </div>

    <div class="panel">
        <h4>Rincian biaya</h4>
        <div class="sum-row"><span>Sewa mobil (<?= $b['days'] ?> hari)</span><span><?= rupiah($b['rent_total']) ?></span></div>
        <?php if ($b['driver_total']): ?><div class="sum-row"><span>Pengemudi</span><span><?= rupiah($b['driver_total']) ?></span></div><?php endif ?>
        <div class="sum-row"><span>Biaya layanan</span><span><?= rupiah($b['service_fee']) ?></span></div>
        <?php if ($b['discount']): ?><div class="sum-row"><span>Promo <?= esc($b['promo_code']) ?></span><span>−<?= rupiah($b['discount']) ?></span></div><?php endif ?>
        <div class="sum-row total"><span>Total</span><b><?= rupiah($b['total']) ?></b></div>
        <?php if ($receipt): ?><a class="btn btn-secondary btn-sm" style="margin-top:12px" href="<?= site_url('bukti/' . $receipt['ref']) ?>"><?= icon('receipt', 18) ?> Lihat bukti pembayaran</a><?php endif ?>
        <?php if (in_array($b['status'], ['dibatalkan', 'ditolak'], true)): ?><p class="field-hint">Dana <?= rupiah($b['total']) ?> dikembalikan ke metode pembayaranmu.</p><?php endif ?>
    </div>

    <?php if ($b['status'] === 'selesai'): ?>
        <div class="panel" id="ulasan">
            <h4>Ulasan</h4>
            <?php if ($rv): ?>
                <div class="review"><?= stars((float) $rv['rating']) ?><?php if ($rv['comment']): ?><p>“<?= esc($rv['comment']) ?>”</p><?php endif ?></div>
            <?php else: ?>
                <form action="<?= site_url('pesanan/' . $b['code'] . '/ulasan') ?>" method="post" novalidate>
                    <?= csrf_field() ?>
                    <div class="field">
                        <fieldset class="stars-input" aria-label="Rating" data-label="Rating"><?php foreach ([5, 4, 3, 2, 1] as $n): ?><input type="radio" id="star<?= $n ?>" name="rating" value="<?= $n ?>" required <?= old_input('rating') === (string) $n ? 'checked' : '' ?>><label for="star<?= $n ?>" title="<?= $n ?> bintang"><?= icon('star', 28) ?></label><?php endforeach ?></fieldset>
                        <p class="field-error" role="alert"><?= $ratingErr ? icon('circle-alert', 16) . ' ' . esc($ratingErr) : '' ?></p>
                    </div>
                    <div class="field">
                        <label for="comment">Ceritakan pengalamanmu (opsional)</label>
                        <div class="input<?= $commentErr ? ' has-error' : '' ?>"><input id="comment" name="comment" maxlength="400" value="<?= esc(old_input('comment'), 'attr') ?>" placeholder="Mobil bersih, pengemudi ramah…" aria-describedby="comment-error"></div>
                        <p class="field-error" id="comment-error" role="alert"><?= $commentErr ? icon('circle-alert', 16) . ' ' . esc($commentErr) : '' ?></p>
                    </div>
                    <button class="btn btn-primary" type="submit"><span>Kirim ulasan</span></button>
                </form>
            <?php endif ?>
        </div>
    <?php endif ?>
</div>

<div class="sticky-cta" style="display:flex;gap:12px">
    <?php if ($b['status'] === 'pending_payment'): ?>
        <button class="btn btn-secondary" style="width:auto" type="button" data-modal-open="cancel-modal">Batalkan</button>
        <a class="btn btn-primary" href="<?= site_url('bayar/' . $b['code']) ?>">Bayar sekarang</a>
    <?php elseif ($cancelable): ?>
        <button class="btn btn-secondary" style="width:auto" type="button" data-modal-open="cancel-modal">Batalkan</button>
        <a class="btn btn-primary" href="<?= site_url('chat/mulai/' . $m['id']) . '?mobil=' . $car['id'] ?>"><?= icon('message-square', 18) ?> Chat mitra</a>
    <?php elseif ($b['status'] === 'aktif'): ?>
        <a class="btn btn-primary" href="<?= site_url('chat/mulai/' . $m['id']) . '?mobil=' . $car['id'] ?>"><?= icon('message-square', 18) ?> Chat mitra</a>
    <?php else: ?>
        <a class="btn btn-primary" href="<?= site_url('mobil/' . $car['id']) ?>">Pesan lagi</a>
    <?php endif ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?php if ($cancelable): ?>
<div class="modal" id="cancel-modal" role="dialog" aria-modal="true" aria-labelledby="cancel-title">
    <form class="modal-box" action="<?= site_url('pesanan/' . $b['code'] . '/batal') ?>" method="post" novalidate style="text-align:left">
        <?= csrf_field() ?>
        <span class="ico"><?= icon('triangle-alert', 24) ?></span>
        <h3 id="cancel-title">Batalkan pesanan ini?</h3>
        <p class="muted" style="margin:-8px 0 16px"><?= $b['status'] === 'pending_payment' ? 'Pesanan belum dibayar, jadi tidak ada biaya.' : 'Dana ' . rupiah($b['total']) . ' dikembalikan penuh.' ?></p>
        <div class="field"><label for="cancel-reason">Alasan (opsional)</label><div class="input"><input id="cancel-reason" name="reason" maxlength="200" placeholder="Rencana berubah"></div></div>
        <div class="modal-actions"><button class="btn btn-secondary" type="button" data-modal-close>Tidak jadi</button><button class="btn btn-danger" type="submit" style="background:var(--error);color:#fff"><span>Batalkan</span></button></div>
    </form>
</div>
<?php endif ?>
<?= $this->endSection() ?>
