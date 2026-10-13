<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<?= partial('components/topbar', ['title' => 'Keamanan', 'back' => site_url($p . 'profil')]) ?>

<form class="page has-cta" action="<?= site_url($p . 'profil/keamanan') ?>" method="post" style="padding-top:20px" novalidate>
    <?= csrf_field() ?>
    <?= partial('components/alerts') ?>
    <?= partial('components/field', ['label' => 'Password saat ini', 'name' => 'current', 'icon' => 'lock', 'type' => 'password', 'toggle' => true, 'attrs' => ['autocomplete' => 'current-password']]) ?>
    <?= partial('components/field', ['label' => 'Password baru', 'name' => 'password', 'icon' => 'key-round', 'type' => 'password', 'toggle' => true, 'hint' => 'Minimal 8 karakter, kombinasi huruf dan angka.', 'attrs' => ['autocomplete' => 'new-password', 'minlength' => 8, 'maxlength' => 72, 'pattern' => '(?=.*[A-Za-z])(?=.*\d).+', 'data-error' => 'Password harus berisi huruf dan angka.']]) ?>
    <?= partial('components/field', ['label' => 'Ulangi password baru', 'name' => 'confirm', 'icon' => 'key-round', 'type' => 'password', 'attrs' => ['autocomplete' => 'new-password', 'data-match' => '#password', 'data-match-error' => 'Konfirmasi tidak sama dengan password baru.']]) ?>
    <div class="sticky-cta"><button class="btn btn-primary" type="submit"><span>Ganti password</span></button></div>
</form>

<?= $this->endSection() ?>
