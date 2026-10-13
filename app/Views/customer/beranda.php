<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<div class="hello">
    <div style="display:flex;align-items:center;justify-content:space-between">
        <?= avatar($me['name'], 40) ?>
        <?= bell_btn() ?>
    </div>
    <h1>Mau ke mana hari ini?</h1>
    <form class="searchbar" action="<?= site_url('cari') ?>" method="get">
        <?= icon('search', 20) ?>
        <input type="search" name="q" placeholder="Cari mobil atau rental" aria-label="Cari mobil">
        <a class="filter-btn" href="<?= site_url('filter') ?>" aria-label="Filter"><?= icon('sliders-horizontal', 18) ?></a>
    </form>
</div>

<div class="page">
    <div style="margin-top:16px"><?= partial('components/verify_banner', ['me' => $me]) ?></div>

    <?php if ($promo): ?>
        <div class="promo" style="margin-top:16px">
            <div><small>Promo</small><h3>Diskon <?= $promo['percent'] ?>% hari ini</h3></div>
            <span class="code"><?= esc($promo['code']) ?></span>
        </div>
    <?php endif ?>

    <div class="chips" style="margin-top:16px">
        <a class="chip is-active" href="<?= site_url('cari') ?>">Semua</a>
        <?php foreach ($types as $t): ?><a class="chip" href="<?= site_url('cari') . '?type=' . urlencode($t) ?>"><?= esc($t) ?></a><?php endforeach ?>
    </div>

    <div class="section-head" style="margin-top:8px"><h2>Mobil populer</h2><a href="<?= site_url('cari') ?>">Lihat semua</a></div>
    <div class="stack">
        <?php foreach ($cars as $car): ?><?= partial('components/car_card', ['car' => $car]) ?><?php endforeach ?>
    </div>
</div>

<?= $this->endSection() ?>
