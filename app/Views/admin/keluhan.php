<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php $replyErr = form_error('reply'); $replyId = form_error('complaint'); ?>

<?= partial('components/topbar', ['title' => 'Keluhan', 'back' => site_url('admin/dashboard')]) ?>

<div class="page stack" style="padding-top:16px">
    <?= partial('components/alerts') ?>
    <?php if (! $rows): ?><div class="empty"><?= icon('message-square', 28) ?><p>Belum ada keluhan.</p></div><?php endif ?>
    <?php foreach ($rows as $c): $open = $c['status'] === 'open'; ?>
        <article class="card" style="padding:16px">
            <div style="display:flex;justify-content:space-between;gap:8px"><b><?= esc($c['subject']) ?></b><span class="badge <?= $open ? 'badge-yellow' : 'badge-green' ?>"><?= $open ? 'Baru' : 'Dibalas' ?></span></div>
            <p class="muted caption" style="margin-top:4px"><?= esc($c['user']['name']) ?> · <?= $c['user']['role'] === 'mitra' ? 'Mitra' : 'Penyewa' ?><?= $c['code'] ? ' · ' . esc($c['code']) : '' ?> · <?= esc(\App\Libraries\Drivana::ago($c['created_at'])) ?></p>
            <p style="margin-top:10px"><?= esc($c['message']) ?></p>
            <?php if (! $open): ?><div class="banner banner-info" style="margin-top:12px"><?= icon('message-square', 18) ?><div><b>Balasan admin</b><p><?= esc($c['admin_reply']) ?></p></div></div>
            <?php else: ?>
                <form action="<?= site_url('admin/keluhan/' . $c['id'] . '/balas') ?>" method="post" novalidate style="margin-top:12px">
                    <?= csrf_field() ?>
                    <div class="field"><label for="rp-<?= $c['id'] ?>">Balasan</label>
                        <div class="input<?= $replyErr && $replyId === (string) $c['id'] ? ' has-error' : '' ?>"><input id="rp-<?= $c['id'] ?>" name="reply" required minlength="5" maxlength="1000" placeholder="Tulis balasan…" data-label="Balasan"></div>
                        <p class="field-error" role="alert"><?= $replyErr && $replyId === (string) $c['id'] ? icon('circle-alert', 16) . ' ' . esc($replyErr) : '' ?></p></div>
                    <button class="btn btn-primary btn-sm" style="width:100%" type="submit"><span>Kirim balasan</span></button>
                </form>
            <?php endif ?>
        </article>
    <?php endforeach ?>
</div>

<?= $this->endSection() ?>
