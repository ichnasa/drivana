<?php /** Large car card for the home screen. Var: $car (enriched). */ ?>
<a class="car-card" href="<?= site_url('mobil/' . $car['id']) ?>">
    <div class="photo"><img src="<?= car_img($car['image']) ?>" alt="<?= esc($car['name']) ?>" loading="lazy"></div>
    <div class="body">
        <div class="row-between">
            <h3><?= esc($car['name']) ?></h3>
            <?= $car['rating'] ? stars((float) $car['rating']) : '' ?>
        </div>
        <div class="tags">
            <?php if (! empty($car['is_boosted'])): ?><span class="badge badge-black">Sponsored</span><?php endif ?>
            <span class="badge"><?= $car['seats'] ?> kursi</span>
            <span class="badge"><?= esc($car['type']) ?></span>
            <?php if ($car['driver_only']): ?><span class="badge badge-blue">Dengan pengemudi</span><?php endif ?>
        </div>
        <div class="row-between">
            <div class="price"><span class="price-label"><?= esc($car['area']) ?></span><b><?= rupiah($car['price']) ?></b> <small>/hari</small></div>
            <span class="btn btn-primary btn-sm">Pesan</span>
        </div>
    </div>
</a>
