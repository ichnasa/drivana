<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
use App\Libraries\Drivana;
$e = fn (string $k) => form_error($k);
$msg = fn (?string $m) => $m ? icon('circle-alert', 16) . ' ' . esc($m) : '';
$driverOnly = (int) $car['driver_only'] === 1;
$driverOn = $driverOnly || (old_input('driver', $car['with_driver'] ? '0' : '0') === '1' && $car['with_driver']);
$verified = ($me['verification_status'] ?? '') === 'verified';
?>

<?= partial('components/topbar', ['title' => 'Form booking', 'back' => site_url('mobil/' . $car['id']), 'sub' => 'Booking → Bayar → Selesai']) ?>

<form class="page has-cta stack" action="<?= site_url('booking/' . $car['id']) ?>" method="post" style="padding-top:20px" novalidate
      data-booking data-price="<?= $car['price'] ?>" data-driver="<?= Drivana::DRIVER_PRICE_PER_DAY ?>" data-fee="<?= Drivana::SERVICE_FEE ?>" data-driver-only="<?= $driverOnly ? 1 : 0 ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="driver" id="b-driver" value="<?= $driverOn ? 1 : 0 ?>">

    <?= partial('components/alerts') ?>
    <?php if (! $verified): ?><?= partial('components/verify_banner', ['me' => $me]) ?><?php endif ?>

    <div class="stepper"><span class="s is-current">1</span><span class="bar"></span><span class="s">2</span><span class="bar"></span><span class="s">3</span></div>

    <div class="card" style="display:flex;align-items:center;gap:14px;padding:12px">
        <div class="photo" style="width:84px;height:60px;border-radius:8px;flex:none"><img src="<?= car_img($car['image']) ?>" alt=""></div>
        <div style="flex:1"><b style="font-size:16px"><?= esc($car['name']) ?></b><p class="muted caption"><?= esc($car['type']) ?> · <?= $car['seats'] ?> kursi</p></div>
        <div class="price" style="text-align:right"><b><?= rupiah($car['price'], true) ?></b><br><small>/hari</small></div>
    </div>

    <div class="panel">
        <h4>Tanggal sewa</h4>
        <div class="date-grid">
            <div class="field" style="margin:0">
                <label for="b-start">Mulai</label>
                <div class="input<?= $e('start') ? ' has-error' : '' ?>"><?= icon('calendar', 18) ?><input id="b-start" type="date" name="start" value="<?= esc(old_input('start', $start), 'attr') ?>" min="<?= date('Y-m-d') ?>" required aria-describedby="b-start-error" data-error="Tanggal mulai tidak boleh sebelum hari ini."></div>
                <p class="field-error" id="b-start-error" role="alert"><?= $msg($e('start')) ?></p>
            </div>
            <div class="field" style="margin:0">
                <label for="b-end">Selesai</label>
                <div class="input<?= $e('end') ? ' has-error' : '' ?>"><?= icon('calendar', 18) ?><input id="b-end" type="date" name="end" value="<?= esc(old_input('end', $end), 'attr') ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required aria-describedby="b-end-error" data-error="Tanggal selesai harus setelah tanggal mulai."></div>
                <p class="field-error" id="b-end-error" role="alert"><?= $msg($e('end')) ?></p>
            </div>
        </div>
        <div class="callout"><?= icon('clock', 20) ?> <span data-out="days">Durasi: 2 hari</span></div>
    </div>

    <div class="panel">
        <h4>Pengemudi</h4>
        <?php if ($driverOnly): ?>
            <div class="callout" style="margin:0"><?= icon('user-round-check', 20) ?> Termasuk pengemudi (wajib untuk mobil ini).</div>
        <?php elseif (! $car['with_driver']): ?>
            <div class="callout" style="margin:0"><?= icon('user-round-x', 20) ?> Lepas kunci. SIM A wajib.</div>
        <?php else: ?>
            <div class="choice-grid" data-choice-group data-choice-input="#b-driver">
                <button type="button" class="choice <?= $driverOn ? 'is-active' : '' ?>" data-value="1"><?= icon('user-round', 24) ?><b>Dengan pengemudi</b><small>+<?= rupiah(Drivana::DRIVER_PRICE_PER_DAY, true) ?>/hari</small></button>
                <button type="button" class="choice <?= $driverOn ? '' : 'is-active' ?>" data-value="0"><?= icon('user-round-x', 24) ?><b>Lepas kunci</b><small>SIM wajib</small></button>
            </div>
        <?php endif ?>
        <p class="field-error" role="alert"><?= $msg($e('driver')) ?></p>
    </div>

    <div class="panel">
        <h4>Pengantaran</h4>
        <div class="field">
            <label for="b-area">Wilayah</label>
            <div class="input<?= $e('area') ? ' has-error' : '' ?>"><?= icon('map-pin', 18) ?>
                <select id="b-area" name="area" required aria-describedby="b-area-error" data-label="Wilayah"><option value="">Pilih wilayah</option>
                    <?php $selArea = old_input('area', $me['area'] ?: $car['area']); foreach (Drivana::AREAS as $a): ?><option <?= $selArea === $a ? 'selected' : '' ?>><?= esc($a) ?></option><?php endforeach ?>
                </select></div>
            <p class="field-error" id="b-area-error" role="alert"><?= $msg($e('area')) ?></p>
        </div>
        <div class="field">
            <label for="b-address">Alamat lengkap</label>
            <div class="input<?= $e('address') ? ' has-error' : '' ?>"><?= icon('house', 18) ?><input id="b-address" name="address" value="<?= esc(old_input('address', $me['address'] ?? ''), 'attr') ?>" placeholder="Jl. Sudirman No. 12" required minlength="5" maxlength="200" aria-describedby="b-address-error"></div>
            <p class="field-error" id="b-address-error" role="alert"><?= $msg($e('address')) ?></p>
        </div>
        <div class="field" style="margin:0">
            <label for="b-note">Catatan untuk mitra (opsional)</label>
            <div class="input<?= $e('note') ? ' has-error' : '' ?>"><?= icon('message-square-text', 18) ?><input id="b-note" name="note" value="<?= esc(old_input('note'), 'attr') ?>" placeholder="Jam jemput, titik temu…" maxlength="200" aria-describedby="b-note-error"></div>
            <p class="field-error" id="b-note-error" role="alert"><?= $msg($e('note')) ?></p>
        </div>
    </div>

    <div class="panel">
        <h4>Ringkasan biaya</h4>
        <div class="sum-row"><span data-out="rent-label">Sewa mobil</span><span data-out="rent"></span></div>
        <div class="sum-row" data-row="driver"><span data-out="driver-label">Pengemudi</span><span data-out="driver"></span></div>
        <div class="sum-row"><span>Biaya layanan</span><span data-out="fee"></span></div>
        <div class="sum-row total"><span>Total</span><b data-out="total"></b></div>
        <p class="field-hint">Pembayaran di langkah berikutnya. Pembatalan gratis sebelum sewa dimulai.</p>
    </div>

    <div class="sticky-cta"><button class="btn btn-primary" type="submit" <?= $verified ? '' : 'disabled' ?>><span><?= $verified ? 'Lanjut bayar' : 'Verifikasi dulu untuk memesan' ?></span></button></div>
</form>

<?= $this->endSection() ?>
