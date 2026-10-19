<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<div class="success">
    <span class="check"><?= icon('check', 28) ?></span>
    <h1>Pembayaran berhasil</h1>
    <p>Pesananmu sudah kami teruskan ke mitra. Kamu akan dapat kabar begitu mitra mengonfirmasi.</p>

    <div class="receipt">
        <div class="sum-row"><span>Kode booking</span><b><?= esc($b['code']) ?></b></div>
        <div class="sum-row"><span>Mobil</span><b><?= esc($b['car']['name']) ?></b></div>
        <div class="sum-row"><span>Tanggal sewa</span><b><?= date_id($b['start_date'], false) ?> – <?= date_id($b['end_date']) ?></b></div>
        <div class="sum-row total"><span>Total pembayaran</span><b><?= rupiah($p['amount']) ?></b></div>
    </div>

    <div class="stack" style="margin-top:24px">
        <a class="btn btn-primary" href="<?= site_url('pesanan/' . $b['code']) ?>">Detail pesanan</a>
        <a class="btn btn-secondary" href="<?= site_url('bukti/' . $p['ref']) ?>"><?= icon('receipt', 18) ?> Lihat bukti pembayaran</a>
        <a class="btn btn-secondary" href="<?= site_url('beranda') ?>">Kembali ke beranda</a>
    </div>
</div>

<?= $this->endSection() ?>
