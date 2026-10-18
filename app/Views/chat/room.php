<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php $last = $msgs ? end($msgs)['id'] : 0; ?>

<header class="topbar">
    <a class="icon-btn" href="<?= site_url($base) ?>" aria-label="Kembali"><?= icon('arrow-left', 22) ?></a>
    <div class="contact"><?= avatar($c['other_name'], 40) ?><div><b><?= esc($c['other_name']) ?></b><small><?= esc($c['sub_label']) ?></small></div></div>
</header>

<?php if ($c['car_name']): ?>
    <div style="background:var(--surface);padding-bottom:1px">
        <a class="vehicle-strip" href="<?= $role === 'mitra' ? '#' : site_url('mobil/' . $c['car_id']) ?>"><img src="<?= car_img($c['car_image']) ?>" alt=""><div><b><?= esc($c['car_name']) ?></b><small><?= esc($c['car_plate']) ?></small></div></a>
    </div>
<?php endif ?>

<div class="chat-body" data-chat-body data-poll="<?= site_url($base . '/' . $c['id'] . '/pesan') ?>" data-last-id="<?= $last ?>" style="min-height:calc(100vh - 150px)">
    <?php if (! $msgs): ?><p class="muted caption" style="align-self:center;margin-top:24px">Belum ada pesan. Sapa dulu 👋</p><?php endif ?>
    <?php foreach ($msgs as $m): ?>
        <div class="bubble <?= (int) $m['sender_id'] === $c['me'] ? 'me' : 'them' ?>" data-id="<?= $m['id'] ?>"><?= esc($m['body']) ?><small><?= date('H.i', strtotime($m['created_at'])) ?></small></div>
    <?php endforeach ?>
</div>

<form class="chat-input" action="<?= site_url($base . '/' . $c['id'] . '/kirim') ?>" method="post" data-chat-form>
    <?= csrf_field() ?>
    <div class="input"><input name="body" placeholder="Ketik pesan…" autocomplete="off" maxlength="1000" required aria-label="Pesan"></div>
    <button class="send" type="submit" aria-label="Kirim"><?= icon('send', 20) ?></button>
</form>

<?= $this->endSection() ?>
