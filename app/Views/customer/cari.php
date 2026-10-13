<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php
$keep = fn (array $over = []) => '?' . http_build_query(array_filter(array_merge($f, ['page' => null], $over), fn ($v) => $v !== null && $v !== ''));
$filterCount = count(array_diff_key($f, array_flip(['q', 'type', 'sort', 'page'])));
$sorts = ['rekomendasi' => 'Rekomendasi', 'termurah' => 'Termurah', 'termahal' => 'Termahal', 'rating' => 'Rating tertinggi'];
?>

<?= partial('components/topbar', ['title' => 'Cari mobil', 'right' => bell_btn()]) ?>

<div class="page" style="padding-top:16px">
    <form class="searchbar" action="<?= site_url('cari') ?>" method="get">
        <?= icon('search', 20) ?>
        <input type="search" name="q" value="<?= esc($f['q'] ?? '', 'attr') ?>" placeholder="Cari mobil atau rental" aria-label="Cari mobil">
        <?php foreach (array_diff_key($f, ['q' => 1, 'page' => 1]) as $k => $v): ?><input type="hidden" name="<?= esc($k, 'attr') ?>" value="<?= esc($v, 'attr') ?>"><?php endforeach ?>
    </form>

    <div class="chips" style="margin-top:12px">
        <a class="chip" href="<?= site_url('filter') ?>" aria-label="Filter"><?= icon('sliders-horizontal', 16) ?> Filter<?= $filterCount ? ' · ' . $filterCount : '' ?></a>
        <?php foreach ($types as $t): ?>
            <a class="chip <?= $t === $type ? 'is-active' : '' ?>" href="<?= site_url('cari') . $keep(['type' => $t === 'Semua' ? null : $t]) ?>"><?= esc($t) ?></a>
        <?php endforeach ?>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin:4px 0 12px">
        <p class="muted caption"><b style="color:var(--ink)"><?= $res['total'] ?></b> mobil<?php if (! empty($f['start'])): ?> tersedia <?= date_id($f['start'], false) ?> – <?= date_id($f['end'], false) ?><?php endif ?></p>
        <form action="<?= site_url('cari') ?>" method="get" class="sortform">
            <?php foreach (array_diff_key($f, ['sort' => 1, 'page' => 1]) as $k => $v): ?><input type="hidden" name="<?= esc($k, 'attr') ?>" value="<?= esc($v, 'attr') ?>"><?php endforeach ?>
            <label class="sort"><?= icon('arrow-up-down', 14) ?>
                <select name="sort" onchange="this.form.submit()" aria-label="Urutkan">
                    <?php foreach ($sorts as $k => $label): ?><option value="<?= $k ?>" <?= ($f['sort'] ?? 'rekomendasi') === $k ? 'selected' : '' ?>><?= $label ?></option><?php endforeach ?>
                </select>
            </label>
        </form>
    </div>

    <?php if (empty($res['items'])): ?>
        <div class="empty"><?= icon('car', 28) ?><p>Tidak ada mobil yang cocok. Coba ubah filter atau tanggal.</p><a class="btn btn-secondary btn-sm" href="<?= site_url('cari') ?>">Reset pencarian</a></div>
    <?php else: ?>
        <?php $qs = (! empty($f['start']) ? '?start=' . $f['start'] . '&end=' . $f['end'] : ''); ?>
        <div class="stack">
            <?php foreach ($res['items'] as $car): ?><?= partial('components/car_row', ['car' => $car, 'qs' => $qs]) ?><?php endforeach ?>
        </div>
        <?php if ($res['pages'] > 1): ?>
            <nav class="pager" aria-label="Halaman">
                <?php if ($page > 1): ?><a class="btn btn-secondary btn-sm" href="<?= site_url('cari') . $keep(['page' => $page - 1]) ?>"><?= icon('chevron-left', 16) ?> Sebelumnya</a><?php else: ?><span></span><?php endif ?>
                <span class="muted caption"><?= $page ?> / <?= $res['pages'] ?></span>
                <?php if ($page < $res['pages']): ?><a class="btn btn-secondary btn-sm" href="<?= site_url('cari') . $keep(['page' => $page + 1]) ?>">Berikutnya <?= icon('chevron-right', 16) ?></a><?php else: ?><span></span><?php endif ?>
            </nav>
        <?php endif ?>
    <?php endif ?>
</div>

<?= $this->endSection() ?>
