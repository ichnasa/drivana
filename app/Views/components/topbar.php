<?php
/** Vars: $title, $back (url|null), $right (html|null), $sub (string|null) */
?>
<header class="topbar">
    <?php if (! empty($back)): ?>
        <a class="icon-btn" href="<?= esc($back, 'attr') ?>" aria-label="Kembali"><?= icon('arrow-left', 22) ?></a>
    <?php endif ?>
    <div class="topbar-title">
        <h1><?= esc($title) ?></h1>
        <?php if (! empty($sub) && is_string($sub)): ?><p><?= esc($sub) ?></p><?php endif ?>
    </div>
    <?= $right ?? '' ?>
</header>
