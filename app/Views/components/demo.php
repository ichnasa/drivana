<?php /** Demo credentials (development only). Vars: $demo = ['label' => [email, password], ...] */ ?>
<?php if (! empty($demo) && (ENVIRONMENT !== 'production' || getenv('DEMO_MODE'))): ?>
    <div class="demo">
        <b>Akun demo</b>
        <?php foreach ($demo as $label => [$email, $pass]): ?>
            <div>
                <span><?= esc(ucfirst($label)) ?><br><code><?= esc($email) ?> · <?= esc($pass) ?></code></span>
                <button type="button" data-fill='<?= esc(json_encode(['identity' => $email, 'email' => $email, 'password' => $pass]), 'attr') ?>'>Isi otomatis</button>
            </div>
        <?php endforeach ?>
        <form action="<?= site_url('demo/reset') ?>" method="post" style="margin-top:8px"><?= csrf_field() ?><button type="submit">Reset data demo</button></form>
    </div>
<?php endif ?>
