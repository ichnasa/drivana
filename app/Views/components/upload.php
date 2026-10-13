<?php
/** File tile with inline error. Vars: $name, $label, $sub, $icon='upload', $accept, $variant='tile'|'row', $capture */
$err = form_error($name);
$icon = $icon ?? 'upload';
$accept = $accept ?? 'image/*';
?>
<div class="field">
    <?php if (($variant ?? 'tile') === 'row'): ?>
        <label class="file-row<?= $err ? ' has-error' : '' ?>" data-upload style="cursor:pointer">
            <span class="icon-tile"><?= icon($icon, 20) ?></span>
            <div><b><?= esc($label) ?></b><small data-upload-label><?= esc($sub) ?></small></div>
            <?= icon('upload', 20) ?>
            <input type="file" name="<?= esc($name, 'attr') ?>" accept="<?= esc($accept, 'attr') ?>" data-max-mb="5" data-label="<?= esc($label, 'attr') ?>" required hidden>
        </label>
    <?php else: ?>
        <label><?= esc($label) ?></label>
        <label class="upload<?= $err ? ' has-error' : '' ?>" data-upload>
            <?= icon($icon, 24) ?>
            <b data-upload-label>Ketuk untuk upload</b>
            <small><?= esc($sub) ?></small>
            <input type="file" name="<?= esc($name, 'attr') ?>" accept="<?= esc($accept, 'attr') ?>" data-max-mb="5" data-label="<?= esc($label, 'attr') ?>" required hidden>
        </label>
    <?php endif ?>
    <p class="field-error" role="alert"><?= $err ? icon('circle-alert', 16) . ' ' . esc($err) : '' ?></p>
</div>
