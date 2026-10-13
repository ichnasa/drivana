<?php
/** Compact car result. Vars: $car (enriched), $qs (optional query string kept for the detail page). */
$qs = $qs ?? '';
?>
<a class="car-row" href="<?= site_url('mobil/' . $car['id']) . $qs ?>">
    <div class="photo"><img src="<?= car_img($car['image']) ?>" alt="<?= esc($car['name']) ?>" loading="lazy"></div>
    <div class="info">
        <div>
            <?php if (! empty($car['is_boosted'])): ?><span class="badge badge-black" style="margin-bottom:4px">Sponsored</span><?php endif ?>
            <h3><?= esc($car['name']) ?></h3>
            <p class="sub"><?= $car['seats'] ?> kursi · <?= esc($car['type']) ?> · <?= esc($car['transmission']) ?></p>
            <?= $car['rating'] ? stars((float) $car['rating']) . ' <span class="muted caption">(' . $car['reviews_count'] . ')</span>' : '<span class="muted caption">Belum ada ulasan</span>' ?>
        </div>
        <div class="price"><b><?= rupiah($car['price']) ?></b> <small>/ 24 jam</small></div>
    </div>
</a>
