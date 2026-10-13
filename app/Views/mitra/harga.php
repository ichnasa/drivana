<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<?= partial('components/topbar', ['title' => 'Rekomendasi harga', 'back' => site_url('mitra/dashboard')]) ?>

<div class="page stack" style="padding-top:16px">
    <?= partial('components/alerts') ?>
    <?php if (! $car): ?>
        <div class="empty"><?= icon('car', 28) ?><p>Belum ada mobil yang disetujui admin.</p></div>
    <?php else: ?>
        <form action="<?= site_url('mitra/rekomendasi-harga') ?>" method="get">
            <div class="field" style="margin:0"><label for="car-pick">Kendaraan</label>
                <div class="input"><?= icon('car', 20) ?><select id="car-pick" name="mobil" onchange="this.form.submit()"><?php foreach ($cars as $c): ?><option value="<?= $c['id'] ?>" <?= $c['id'] === $car['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?> · <?= esc($c['plate']) ?></option><?php endforeach ?></select></div></div>
        </form>

        <div class="price-hero">
            <small>Saran harga pasar per hari (<?= esc($car['type']) ?>)</small>
            <strong><?= rupiah($rec['low']) ?> – <?= rupiah($rec['high']) ?></strong>
            <small>Harga kamu sekarang <b style="color:var(--ink)"><?= rupiah($car['price']) ?></b> · median <?= $rec['peers'] ?> mobil sejenis <?= rupiah($rec['market']) ?></small>
            <?php if ($rec['potential'] > 3): ?><div class="callout" style="color:var(--success-ink)"><?= icon('trending-up', 18) ?> Potensi pendapatan naik <b><?= $rec['potential'] ?>%</b></div>
            <?php elseif ($rec['potential'] < -3): ?><div class="callout"><?= icon('trending-down', 18) ?> Harga kamu di atas pasar. Turunkan untuk lebih laku.</div>
            <?php else: ?><div class="callout" style="color:var(--success-ink)"><?= icon('circle-check', 18) ?> Harga kamu sudah kompetitif.</div><?php endif ?>
        </div>

        <div class="tip"><?= icon('map-pin', 20) ?><div><b>Permintaan 7 hari terakhir</b><p><?= $rec['recent'] ?> booking untuk tipe <?= esc($car['type']) ?><?= $rec['growth'] ? ', ' . ($rec['growth'] > 0 ? 'naik ' : 'turun ') . abs($rec['growth']) . '% dibanding pekan sebelumnya' : '' ?>. <?= $rec['growth'] > 0 ? 'Menaikkan harga sedikit aman sekarang.' : 'Jaga harga tetap kompetitif.' ?></p></div></div>

        <div class="panel">
            <h4>Booking per hari (28 hari)</h4>
            <?php $max = max(1, max($rec['chart'])); $peak = array_keys($rec['chart'], max($rec['chart']))[0]; ?>
            <div class="bars"><?php foreach ($rec['chart'] as $day => $val): ?><div class="col"><small><?= $val ?></small><div class="bar <?= $day === $peak ? 'is-peak' : '' ?>" style="height:<?= round($val / $max * 80) ?>%"></div><small><?= $day ?></small></div><?php endforeach ?></div>
        </div>

        <form action="<?= site_url('mitra/rekomendasi-harga/terapkan') ?>" method="post" class="sticky-cta">
            <?= csrf_field() ?><input type="hidden" name="car" value="<?= $car['id'] ?>"><input type="hidden" name="price" value="<?= $rec['mid'] ?>">
            <button class="btn btn-primary" type="submit"><span>Terapkan <?= rupiah($rec['mid']) ?> / hari</span></button>
        </form>
    <?php endif ?>
</div>

<?= $this->endSection() ?>
