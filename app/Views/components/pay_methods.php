<?php /** Payment method radio list. Vars: $methods, $name='method', $selected, $inputId */ ?>
<div style="display:grid;gap:12px" data-choice-group data-choice-input="#<?= esc($inputId, 'attr') ?>">
    <?php foreach ($methods as $m): ?>
        <button type="button" class="method <?= $selected === $m['key'] ? 'is-active' : '' ?>" data-value="<?= $m['key'] ?>">
            <?= icon($m['icon'], 24) ?>
            <span class="meta"><b><?= esc($m['name']) ?></b><small><?= esc($m['hint']) ?></small></span>
            <span class="radio"></span>
        </button>
    <?php endforeach ?>
</div>
