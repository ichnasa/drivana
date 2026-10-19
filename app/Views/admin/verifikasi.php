<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
use App\Libraries\Drivana;
$tabs = ['semua' => 'Semua', 'menunggu' => 'Menunggu Verifikasi', 'disetujui' => 'Terverifikasi', 'ditolak' => 'Ditolak'];
$sorts = ['terbaru' => 'Terbaru', 'terlama' => 'Terlama', 'nama' => 'Nama'];
$to = fn (array $o) => site_url('admin/verifikasi?' . http_build_query(array_filter(array_merge(['status' => $status, 'q' => $q, 'urut' => $sort === 'terbaru' ? '' : $sort], $o), fn ($v) => $v !== '')));
$tone = ['pending' => ['Menunggu', 'badge-yellow'], 'approved' => ['Disetujui', 'badge-green'], 'rejected' => ['Ditolak', 'badge-red']];
$docs = array_slice($docs, 0, 50);
$reasonErr = form_error('reason'); $reasonDoc = form_error('doc');
?>

<?= partial('components/topbar', ['title' => 'Verifikasi dokumen', 'sub' => $counts['menunggu'] . ' dokumen menunggu', 'right' => bell_btn('admin')]) ?>

<div class="page" style="padding-top:16px">
    <?= partial('components/alerts') ?>
    <form class="searchbar" action="<?= site_url('admin/verifikasi') ?>" method="get">
        <?= icon('search', 20) ?><input type="search" name="q" value="<?= esc($q, 'attr') ?>" placeholder="Cari nama, email, atau jenis dokumen" aria-label="Cari">
        <input type="hidden" name="status" value="<?= esc($status, 'attr') ?>"><input type="hidden" name="urut" value="<?= $sort === 'terbaru' ? '' : esc($sort, 'attr') ?>">
    </form>
    <div class="chips" style="margin-top:12px"><?php foreach ($tabs as $k => $l): ?><a class="chip <?= $status === $k ? 'is-active' : '' ?>" href="<?= $to(['status' => $k]) ?>"><?= $l ?> (<?= $counts[$k] ?>)</a><?php endforeach ?></div>
    <div class="chips" style="margin-top:8px"><span class="chip" style="pointer-events:none"><?= icon('arrow-up-down', 14) ?> Urutkan</span><?php foreach ($sorts as $k => $l): ?><a class="chip <?= $sort === $k ? 'is-active' : '' ?>" href="<?= $to(['urut' => $k === 'terbaru' ? '' : $k]) ?>"><?= $l ?></a><?php endforeach ?></div>

    <?php if (! $docs): ?><div class="empty"><?= icon('file-check-2', 28) ?><p>Tidak ada dokumen di sini.</p></div><?php endif ?>
    <div class="u-list">
        <?php foreach ($docs as $d): $u = $d['user']; ?>
            <article class="u-card">
                <div class="u-head"><?= avatar($u['name'], 42) ?><div><b><?= esc($u['name']) ?></b><small><?= $u['role'] === 'mitra' ? 'Mitra' : 'Penyewa' ?> · <?= esc(Drivana::DOC_LABELS[$d['type']]) ?></small></div><span class="badge <?= $tone[$d['status']][1] ?>"><?= $tone[$d['status']][0] ?></span></div>
                <div class="u-body"><span><?= icon('clock', 14) ?> Diunggah <?= esc(Drivana::ago($d['created_at'])) ?></span><?php if ($d['status'] === 'rejected'): ?><span><?= icon('circle-alert', 14) ?> <?= esc($d['reject_reason']) ?></span><?php endif ?></div>
                <button class="btn <?= $d['status'] === 'pending' ? 'btn-primary' : 'btn-secondary' ?>" style="height:44px;font-size:14px" type="button" data-modal-open="doc-<?= $d['id'] ?>"><?= icon('eye', 16) ?> <?= $d['status'] === 'pending' ? 'Periksa' : 'Lihat detail' ?></button>
            </article>
        <?php endforeach ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?php foreach ($docs as $d): $u = $d['user']; $isImg = str_starts_with((string) $d['mime'], 'image/');
    $siblings = array_filter(\App\Libraries\Store::all('documents'), fn ($x) => $x['user_id'] === $d['user_id']); ?>
    <div class="modal is-sheet" id="doc-<?= $d['id'] ?>" role="dialog" aria-modal="true" aria-label="Detail dokumen">
        <div class="modal-box">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
                <div style="flex:1"><b style="font-size:18px">Detail dokumen</b><br><small class="muted"><?= esc(Drivana::DOC_LABELS[$d['type']]) ?> · <?= esc($u['name']) ?></small></div>
                <button class="icon-btn" type="button" data-modal-close aria-label="Tutup"><?= icon('x', 20) ?></button>
            </div>
            <?php if ($isImg): ?>
                <div data-doc-viewer>
                    <div class="doc-tools">
                        <button class="icon-btn" type="button" data-doc-act="in" aria-label="Zoom"><?= icon('zoom-in', 20) ?></button>
                        <button class="icon-btn" type="button" data-doc-act="out" aria-label="Perkecil"><?= icon('zoom-out', 20) ?></button>
                        <button class="icon-btn" type="button" data-doc-act="rotate" aria-label="Putar"><?= icon('rotate-cw', 20) ?></button>
                        <button class="icon-btn" type="button" data-doc-act="full" aria-label="Tampilan penuh"><?= icon('maximize', 20) ?></button>
                        <a class="icon-btn" href="<?= site_url('berkas/' . $d['id']) ?>" download aria-label="Unduh"><?= icon('download', 20) ?></a>
                    </div>
                    <div class="doc-stage"><img class="doc-full" data-doc-img src="<?= site_url('berkas/' . $d['id']) ?>" alt="<?= esc(Drivana::DOC_LABELS[$d['type']], 'attr') ?>" loading="lazy"></div>
                </div>
            <?php else: ?><a class="btn btn-secondary" href="<?= site_url('berkas/' . $d['id']) ?>" target="_blank" rel="noopener"><?= icon('file-text', 18) ?> Buka berkas PDF</a><?php endif ?>

            <div class="info-grid" style="margin-top:16px">
                <div><small>Nama</small><b><?= esc($u['name']) ?></b></div><div><small>Peran</small><b><?= $u['role'] === 'mitra' ? 'Mitra' : 'Penyewa' ?></b></div>
                <div><small>Email</small><b style="word-break:break-all"><?= esc($u['email']) ?></b></div><div><small>No. HP</small><b><?= esc($u['phone']) ?></b></div>
            </div>
            <p class="muted caption" style="margin-bottom:6px">Dokumen akun ini</p>
            <div class="pills" style="margin-bottom:16px"><?php foreach ($siblings as $s): ?><span class="badge <?= $tone[$s['status']][1] ?>"><?= esc(Drivana::DOC_LABELS[$s['type']]) ?>: <?= $tone[$s['status']][0] ?></span><?php endforeach ?></div>

            <?php if ($d['status'] === 'pending'): ?>
                <form action="<?= site_url('admin/verifikasi/' . $d['id'] . '/tolak') ?>" method="post" novalidate>
                    <?= csrf_field() ?>
                    <div class="field"><label for="r-<?= $d['id'] ?>">Alasan jika ditolak</label>
                        <div class="input<?= $reasonErr && $reasonDoc === (string) $d['id'] ? ' has-error' : '' ?>"><input id="r-<?= $d['id'] ?>" name="reason" minlength="5" maxlength="200" placeholder="Foto buram atau terpotong" data-label="Alasan" aria-describedby="r-<?= $d['id'] ?>-error"></div>
                        <p class="field-error" id="r-<?= $d['id'] ?>-error" role="alert"><?= $reasonErr && $reasonDoc === (string) $d['id'] ? icon('circle-alert', 16) . ' ' . esc($reasonErr) : '' ?></p></div>
                    <button class="btn btn-danger" type="submit" style="height:48px;font-size:14px"><?= icon('x', 16) ?> Tolak dokumen</button>
                </form>
                <form action="<?= site_url('admin/verifikasi/' . $d['id'] . '/setuju') ?>" method="post" style="margin-top:12px"><?= csrf_field() ?><button class="btn btn-primary" type="submit" style="height:48px;font-size:14px"><?= icon('check', 16) ?> Setujui dokumen</button></form>
            <?php else: ?>
                <p class="muted caption">Diputuskan <?= esc(Drivana::ago($d['reviewed_at'])) ?><?= $d['status'] === 'rejected' ? '. Alasan: ' . esc($d['reject_reason']) : '' ?>.</p>
                <button class="btn btn-secondary" style="margin-top:12px;height:48px;font-size:14px" type="button" data-modal-close>Tutup</button>
            <?php endif ?>
            <?php if ($d['status'] === 'pending'): ?><button class="btn btn-secondary" style="margin-top:12px;height:48px;font-size:14px" type="button" data-modal-close>Nanti saja</button><?php endif ?>
        </div>
    </div>
<?php endforeach ?>
<?= $this->endSection() ?>
