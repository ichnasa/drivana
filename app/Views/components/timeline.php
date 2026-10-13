<?php
/** Vertical booking status ("ride status") timeline. Var: $b (booking row). */
$status = $b['status'];
$flow = ['Dipesan', 'Dibayar', 'Dikonfirmasi mitra', 'Sewa berjalan', 'Selesai'];
$reached = ['pending_payment' => 0, 'menunggu' => 1, 'dikonfirmasi' => 2, 'aktif' => 3, 'selesai' => 4][$status] ?? 1;
$times = [$b['created_at'], null, $b['confirmed_at'], $b['started_at'], $b['finished_at']];
$hint = ['Menunggu pembayaran.', 'Menunggu konfirmasi mitra.', 'Siap diantar pada ' . date_id($b['start_date'], false) . '.', 'Mobil sedang kamu pakai.', 'Terima kasih sudah menyewa.'];
$ended = ['dibatalkan' => 'Dibatalkan', 'ditolak' => 'Ditolak mitra', 'kedaluwarsa' => 'Kedaluwarsa'][$status] ?? null;
?>
<ol class="vtl">
    <?php foreach ($flow as $i => $label):
        $state = $ended ? ($i <= 1 && $status !== 'kedaluwarsa' ? 'done' : ($i === 0 ? 'done' : 'todo')) : ($i < $reached ? 'done' : ($i === $reached ? 'current' : 'todo'));
        if ($ended && $i > 1) { continue; } ?>
        <li class="<?= $state ?>">
            <span class="dot"><?= $state === 'done' ? icon('check', 12, '', 3) : '' ?></span>
            <div><b><?= esc($label) ?></b><small><?= $state === 'current' ? esc($hint[$i]) : ($times[$i] ? esc(date_id($times[$i], false) . ', ' . date('H.i', strtotime($times[$i]))) : '') ?></small></div>
        </li>
    <?php endforeach ?>
    <?php if ($ended): ?>
        <li class="current end"><span class="dot"><?= icon('x', 12, '', 3) ?></span><div><b><?= $ended ?></b><small><?= esc($b['cancel_reason'] ?: '') ?></small></div></li>
    <?php endif ?>
</ol>
