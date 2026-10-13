<?php
/**
 * Text-like field with inline validation message (server error + client JS share the same <p>).
 * Vars: $label, $name, $icon, $type='text', $placeholder, $required=true, $toggle=false, $id,
 *       $value (default), $hint, $attrs (extra html attributes: autocomplete, inputmode, pattern, minlength, data-error ...)
 */
$id    = $id ?? $name;
$type  = $type ?? 'text';
$err   = form_error($name);
$value = $type === 'password' ? '' : old_input($name, $value ?? '');
?>
<div class="field">
    <label for="<?= esc($id, 'attr') ?>"><?= esc($label) ?></label>
    <div class="input<?= $err ? ' has-error' : '' ?>">
        <?php if (! empty($icon)): ?><?= icon($icon, 20) ?><?php endif ?>
        <input id="<?= esc($id, 'attr') ?>" name="<?= esc($name, 'attr') ?>" type="<?= esc($type, 'attr') ?>"
               placeholder="<?= esc($placeholder ?? '', 'attr') ?>" value="<?= esc($value, 'attr') ?>"
               aria-describedby="<?= esc($id, 'attr') ?>-error"<?= $err ? ' aria-invalid="true"' : '' ?><?= ($required ?? true) ? ' required' : '' ?>
               <?php foreach (($attrs ?? []) as $k => $v): ?> <?= esc($k, 'attr') ?>="<?= esc($v, 'attr') ?>"<?php endforeach ?>>
        <?php if (! empty($toggle)): ?>
            <button type="button" data-toggle-password="#<?= esc($id, 'attr') ?>" aria-pressed="false" aria-label="Tampilkan password"><span class="eye eye-on"><?= icon('eye', 20) ?></span><span class="eye eye-off"><?= icon('eye-off', 20) ?></span></button>
        <?php endif ?>
    </div>
    <p class="field-error" id="<?= esc($id, 'attr') ?>-error" role="alert"><?= $err ? icon('circle-alert', 16) . ' ' . esc($err) : '' ?></p>
    <?php if (! empty($hint)): ?><p class="field-hint"><?= esc($hint) ?></p><?php endif ?>
</div>
