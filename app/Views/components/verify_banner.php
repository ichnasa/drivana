<?php
/** Account verification status. Var: $me. Hidden once verified. */
$st = $me['verification_status'] ?? 'verified';
$mitra = ($me['role'] ?? '') === 'mitra';
$docs = site_url(($mitra ? 'mitra/' : '') . 'profil/dokumen');
?>
<?php if ($st === 'unverified'): ?>
    <div class="banner banner-warn"><?= icon('shield-alert', 20) ?><div><b>Verifikasi dulu</b><p><?= $mitra ? 'Lengkapi dokumen supaya armadamu bisa tampil.' : 'Kamu perlu verifikasi KTP, SIM, dan selfie sebelum memesan.' ?></p></div><a class="btn btn-primary btn-sm" href="<?= site_url($mitra ? 'mitra/verifikasi' : 'verifikasi') ?>">Mulai</a></div>
<?php elseif ($st === 'pending'): ?>
    <div class="banner banner-info"><?= icon('clock', 20) ?><div><b>Dokumen sedang diperiksa</b><p>Admin akan mengabari kamu lewat notifikasi.</p></div></div>
<?php elseif ($st === 'rejected'): ?>
    <div class="banner banner-error"><?= icon('circle-alert', 20) ?><div><b>Dokumen ditolak</b><p><?= esc($me['verification_note'] ?? '') ?></p></div><a class="btn btn-secondary btn-sm" href="<?= $docs ?>">Perbaiki</a></div>
<?php endif ?>
