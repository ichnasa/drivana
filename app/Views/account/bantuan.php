<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$faq = [
    ['Bagaimana cara memesan mobil?', 'Cari mobil, pilih tanggal, isi alamat pengantaran, lalu bayar. Mitra akan mengonfirmasi pesananmu.'],
    ['Kenapa saya harus verifikasi identitas?', 'Demi keamanan penyewa dan pemilik mobil. Admin Drivana memeriksa KTP, SIM, dan selfie sebelum kamu bisa memesan.'],
    ['Apakah pesanan bisa dibatalkan?', 'Bisa, gratis sebelum sewa dimulai. Dana dikembalikan penuh ke metode pembayaranmu.'],
    ['Bagaimana pembayaran bekerja?', 'Pembayaran diproses lewat payment gateway. Setelah berhasil, pesananmu menunggu konfirmasi mitra.'],
    ['Apa itu Premium untuk mitra?', 'Langganan yang membuka rekomendasi harga, analitik rental, dan boost listing.'],
];
$e = fn (string $k) => form_error($k);
?>

<?= partial('components/topbar', ['title' => 'Bantuan', 'back' => site_url($p . 'profil')]) ?>

<div class="page stack" style="padding-top:16px">
    <?= partial('components/alerts') ?>

    <div>
        <div class="section-head" style="margin-top:0"><h2>Pertanyaan umum</h2></div>
        <?php foreach ($faq as [$q, $a]): ?>
            <details class="faq"><summary><?= esc($q) ?><?= icon('chevron-down', 18) ?></summary><p><?= esc($a) ?></p></details>
        <?php endforeach ?>
    </div>

    <div class="panel">
        <h4>Hubungi kami</h4>
        <p class="muted">Email <b style="color:var(--ink)">bantuan@drivana.test</b> · WhatsApp 0812-0000-0000 · Senin–Minggu, 08.00–21.00 WIB.</p>
        <p class="muted caption" style="margin-top:8px">Kami menjaga data pribadimu sesuai UU Perlindungan Data Pribadi. Dokumen identitas hanya dilihat admin untuk verifikasi.</p>
    </div>

    <div class="panel">
        <h4>Kirim keluhan</h4>
        <form action="<?= site_url($p . 'bantuan/keluhan') ?>" method="post" novalidate>
            <?= csrf_field() ?>
            <?= partial('components/field', ['label' => 'Subjek', 'name' => 'subject', 'icon' => 'tag', 'placeholder' => 'Ringkas masalahmu', 'attrs' => ['minlength' => 5, 'maxlength' => 120]]) ?>
            <div class="field">
                <label for="booking">Kode booking (opsional)</label>
                <div class="input<?= $e('booking') ? ' has-error' : '' ?>"><?= icon('receipt', 20) ?><select id="booking" name="booking" aria-describedby="booking-error"><option value="">Tidak terkait booking</option><?php foreach ($orders as $o): ?><option <?= old_input('booking') === $o['code'] ? 'selected' : '' ?>><?= esc($o['code']) ?></option><?php endforeach ?></select></div>
                <p class="field-error" id="booking-error" role="alert"><?= $e('booking') ? icon('circle-alert', 16) . ' ' . esc($e('booking')) : '' ?></p>
            </div>
            <div class="field">
                <label for="message">Ceritakan masalahmu</label>
                <div class="input textarea<?= $e('message') ? ' has-error' : '' ?>"><textarea id="message" name="message" rows="4" minlength="10" maxlength="1000" required placeholder="Apa yang terjadi?" aria-describedby="message-error"><?= esc(old_input('message')) ?></textarea></div>
                <p class="field-error" id="message-error" role="alert"><?= $e('message') ? icon('circle-alert', 16) . ' ' . esc($e('message')) : '' ?></p>
            </div>
            <button class="btn btn-primary" type="submit"><span>Kirim keluhan</span></button>
        </form>
    </div>

    <?php if ($mine): ?>
        <div>
            <div class="section-head" style="margin-top:0"><h2>Keluhan saya</h2></div>
            <div class="stack">
                <?php foreach ($mine as $c): ?>
                    <div class="card" style="padding:16px">
                        <div style="display:flex;justify-content:space-between;gap:8px"><b><?= esc($c['subject']) ?></b><span class="badge <?= $c['status'] === 'resolved' ? 'badge-green' : 'badge-yellow' ?>"><?= $c['status'] === 'resolved' ? 'Dibalas' : 'Menunggu balasan' ?></span></div>
                        <p class="muted" style="margin-top:6px"><?= esc($c['message']) ?></p>
                        <?php if ($c['admin_reply']): ?><div class="banner banner-info" style="margin-top:12px"><?= icon('message-square', 18) ?><div><b>Balasan admin</b><p><?= esc($c['admin_reply']) ?></p></div></div><?php endif ?>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    <?php endif ?>
</div>

<?= $this->endSection() ?>
