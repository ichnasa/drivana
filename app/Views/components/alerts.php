<?php
/** Form-level error (errors.form) and, for multi-step forms ($summary), a one-line notice. */
$msg = form_error('form');
$all = session()->getFlashdata('errors');
?>
<?php if ($msg): ?>
    <div class="alert" role="alert"><?= icon('circle-alert', 18) ?><span><?= esc($msg) ?></span></div>
<?php elseif (! empty($summary) && is_array($all) && $all): ?>
    <div class="alert" role="alert"><?= icon('circle-alert', 18) ?><span>Ada data yang belum lengkap atau tidak valid. Periksa bagian yang bertanda merah.</span></div>
<?php endif ?>
