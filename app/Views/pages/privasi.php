<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<a class="icon-btn" href="javascript:history.back()" aria-label="Kembali" style="margin-left:-8px"><?= icon('arrow-left', 22) ?></a>
<h1 class="title" style="margin-top:16px">Kebijakan privasi</h1>
<p class="lead">Drivana menjaga data pribadimu sesuai UU Perlindungan Data Pribadi (UU PDP).</p>

<div class="stack" style="margin-top:24px">
    <div class="panel"><h4>Data yang kami kumpulkan</h4><p class="muted">Nama, email, nomor HP, alamat, serta dokumen identitas (KTP, SIM, selfie) dan dokumen usaha untuk mitra.</p></div>
    <div class="panel"><h4>Untuk apa data dipakai</h4><p class="muted">Verifikasi identitas, memproses pemesanan dan pembayaran, serta komunikasi antara penyewa dan mitra.</p></div>
    <div class="panel"><h4>Siapa yang dapat melihat</h4><p class="muted">Dokumen identitas hanya dilihat admin Drivana untuk verifikasi. Mitra hanya melihat data penyewa yang memesan mobilnya.</p></div>
    <div class="panel"><h4>Keamanan</h4><p class="muted">Data dikirim lewat koneksi terenkripsi dan password disimpan dalam bentuk hash.</p></div>
    <div class="panel"><h4>Hak kamu</h4><p class="muted">Kamu dapat memperbarui data di menu Profil atau meminta bantuan lewat menu Bantuan.</p></div>
</div>

<?= $this->endSection() ?>
