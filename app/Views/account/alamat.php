<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<?= partial('components/topbar', ['title' => 'Alamat tersimpan', 'back' => site_url($p . 'profil')]) ?>

<div class="page stack" style="padding-top:20px">
    <div class="stack" data-address-list>
        <?php foreach ($list as $a): ?>
            <div class="card" style="display:flex;align-items:center;gap:12px;padding:16px">
                <?= icon('map-pin', 20) ?>
                <div style="flex:1;min-width:0"><b><?= esc($a['label']) ?></b><p class="muted caption"><?= esc($a['address']) ?> · <?= esc($a['area']) ?></p></div>
                <button class="icon-btn" type="button" data-address-remove aria-label="Hapus alamat <?= esc($a['label'], 'attr') ?>"><?= icon('trash-2', 18) ?></button>
            </div>
        <?php endforeach ?>
    </div>

    <form class="panel" data-address-form novalidate>
        <h4>Tambah alamat</h4>
        <div class="field"><label for="al-label">Label</label><div class="input"><?= icon('tag', 20) ?><input id="al-label" placeholder="Rumah, Kantor…" maxlength="30"></div></div>
        <div class="field"><label for="al-address">Alamat lengkap</label><div class="input"><?= icon('house', 20) ?><input id="al-address" placeholder="Jl. Sudirman No. 12" maxlength="200"></div></div>
        <button class="btn btn-primary" type="submit"><span>Simpan alamat</span></button>
    </form>
</div>

<?= $this->endSection() ?>
