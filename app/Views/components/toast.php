<?php
$ok  = session()->getFlashdata('success');
$bad = session()->getFlashdata('error');
?>
<?php if ($ok || $bad): ?>
    <div class="toast<?= $bad ? ' is-error' : '' ?>" role="status" aria-live="polite" data-toast>
        <?= icon($bad ? 'circle-alert' : 'circle-check', 18) ?><span><?= esc($bad ?: $ok) ?></span>
    </div>
<?php endif ?>
