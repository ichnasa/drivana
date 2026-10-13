<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<a class="icon-btn" href="javascript:history.back()" aria-label="Kembali" style="margin-left:-8px"><?= icon('arrow-left', 22) ?></a>
<h1 class="title" style="margin-top:16px">Kredit foto</h1>
<p class="lead">Foto mobil di Drivana berasal dari fotografer di Unsplash, dipakai di bawah Unsplash License.</p>

<div class="stack" style="margin-top:24px">
    <?php foreach ($items as $i): ?>
        <a class="car-row" href="<?= esc($i['url'], 'attr') ?>" target="_blank" rel="noopener">
            <div class="photo" style="width:96px;height:68px"><img src="<?= car_img($i['image']) ?>" alt="" loading="lazy"></div>
            <div class="info"><div><h3 style="font-size:15px"><?= esc($i['car']) ?></h3><p class="sub">Foto oleh <?= esc($i['photographer'] ?: 'Unsplash') ?> di Unsplash</p></div></div>
        </a>
    <?php endforeach ?>
</div>

<?= $this->endSection() ?>
