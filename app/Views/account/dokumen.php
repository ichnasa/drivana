<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
use App\Libraries\Drivana;
$tone = ['pending' => ['Menunggu pemeriksaan', 'badge-yellow'], 'approved' => ['Disetujui', 'badge-green'], 'rejected' => ['Ditolak', 'badge-red']];
?>

<?= partial('components/topbar', ['title' => 'Dokumen saya', 'back' => site_url($p . 'profil')]) ?>

<div class="page stack" style="padding-top:16px">
    <?= partial('components/alerts') ?>
    <?= partial('components/verify_banner', ['me' => $me]) ?>

    <?php foreach ($required as $type):
        $d = $docs[$type] ?? null; $err = form_error('doc_' . $type);
        $accept = $type === 'business' ? 'image/*,application/pdf' : 'image/*'; $types = $type === 'business' ? 'jpeg,png,pdf' : 'jpeg,png'; ?>
        <div class="card" style="padding:16px">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px"><b><?= esc(Drivana::DOC_LABELS[$type]) ?></b>
                <?= $d ? '<span class="badge ' . $tone[$d['status']][1] . '">' . $tone[$d['status']][0] . '</span>' : '<span class="badge">Belum diunggah</span>' ?></div>
            <?php if ($d): ?>
                <p class="muted caption" style="margin-top:6px">Diunggah <?= esc(Drivana::ago($d['updated_at'])) ?> · <?= esc($d['original_name']) ?></p>
                <?php if ($d['status'] === 'rejected'): ?><div class="ind-note" style="margin-top:10px"><?= icon('circle-alert', 14) ?> <?= esc($d['reject_reason']) ?></div><?php endif ?>
                <?php if (str_starts_with((string) $d['mime'], 'image/')): ?><a href="<?= site_url('berkas/' . $d['id']) ?>" target="_blank" rel="noopener"><img class="doc-thumb" src="<?= site_url('berkas/' . $d['id']) ?>" alt="Pratinjau <?= esc(Drivana::DOC_LABELS[$type], 'attr') ?>" loading="lazy"></a>
                <?php else: ?><a class="link" href="<?= site_url('berkas/' . $d['id']) ?>" target="_blank" rel="noopener">Buka berkas</a><?php endif ?>
            <?php endif ?>
            <?php if (! $d || $d['status'] !== 'approved'): ?>
                <form action="<?= site_url($p . 'profil/dokumen/' . $type) ?>" method="post" enctype="multipart/form-data" novalidate style="margin-top:12px">
                    <?= csrf_field() ?>
                    <label class="upload<?= $err ? ' has-error' : '' ?>" data-upload><?= icon('upload', 22) ?><b data-upload-label><?= $d ? 'Ganti berkas' : 'Ketuk untuk upload' ?></b><small>JPG, PNG<?= $type === 'business' ? ', PDF' : '' ?> · maks. 5 MB</small>
                        <input type="file" name="file" accept="<?= $accept ?>" data-types="<?= $types ?>" data-max-mb="5" data-label="<?= esc(Drivana::DOC_LABELS[$type], 'attr') ?>" required hidden></label>
                    <p class="field-error" role="alert"><?= $err ? icon('circle-alert', 16) . ' ' . esc($err) : '' ?></p>
                    <button class="btn btn-primary btn-sm" style="width:100%;margin-top:12px" type="submit"><span>Kirim</span></button>
                </form>
            <?php endif ?>
        </div>
    <?php endforeach ?>
</div>

<?= $this->endSection() ?>
