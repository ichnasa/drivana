<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

/** Identity documents: storing uploads and deriving the account's verification status. */
class VerificationService
{
    public const DIR = WRITEPATH . 'uploads/documents/';

    /** Save (or replace) one document and re-evaluate the account. */
    public static function store(int $userId, string $type, UploadedFile $file): void
    {
        $dir = self::DIR . $userId . '/';
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $name = $type . '-' . bin2hex(random_bytes(6)) . '.' . ($file->guessExtension() ?: $file->getClientExtension());
        $file->move($dir, $name);
        $now = Drivana::now();
        $row = ['path' => $userId . '/' . $name, 'original_name' => substr($file->getClientName(), 0, 120), 'mime' => $file->getClientMimeType(),
                'status' => 'pending', 'reject_reason' => null, 'reviewed_by' => null, 'reviewed_at' => null, 'updated_at' => $now];

        $existing = Store::first('documents', fn ($d) => (int) $d['user_id'] === $userId && $d['type'] === $type);
        if ($existing) {
            if (! str_starts_with($existing['path'], 'seed/')) {
                @unlink(self::DIR . $existing['path']);
            }
            Store::update('documents', (int) $existing['id'], $row);
        } else {
            Store::insert('documents', $row + ['user_id' => $userId, 'type' => $type, 'created_at' => $now]);
        }
        self::recompute($userId);
    }

    public static function recompute(int $userId): void
    {
        $user = Store::get('users', $userId);
        if (! $user || ! isset(Drivana::REQUIRED_DOCS[$user['role']])) {
            return;
        }
        $docs = [];
        foreach (Store::where('documents', fn ($d) => (int) $d['user_id'] === $userId) as $d) {
            $docs[$d['type']] = $d;
        }
        [$status, $note] = Drivana::verificationFrom($docs, $user['role']);

        if ($status !== $user['verification_status']) {
            Store::update('users', $userId, ['verification_status' => $status, 'verification_note' => $note, 'updated_at' => Drivana::now()]);
            if ($status === 'pending') {
                Notifier::admins('Dokumen baru perlu diperiksa', $user['name'] . ' mengirim dokumen verifikasi.', 'admin/verifikasi');
            }
            if ($status === 'verified') {
                Notifier::to($userId, 'Akun terverifikasi', $user['role'] === 'mitra' ? 'Kamu sekarang bisa memasang armada.' : 'Kamu sekarang bisa memesan mobil.', $user['role'] === 'mitra' ? 'mitra/armada' : 'cari');
            }
            if ($status === 'rejected') {
                Notifier::to($userId, 'Dokumen ditolak', (string) $note . ' Unggah ulang dokumen yang benar.', ($user['role'] === 'mitra' ? 'mitra' : 'profil') . '/dokumen');
            }
        } elseif ($note !== $user['verification_note']) {
            Store::update('users', $userId, ['verification_note' => $note]);
        }
    }

    /** Admin decision on one document. */
    public static function review(int $docId, int $adminId, bool $approve, ?string $reason = null): bool
    {
        $doc = Store::get('documents', $docId);
        if (! $doc) {
            return false;
        }
        Store::update('documents', $docId, [
            'status' => $approve ? 'approved' : 'rejected', 'reject_reason' => $approve ? null : $reason,
            'reviewed_by' => $adminId, 'reviewed_at' => Drivana::now(), 'updated_at' => Drivana::now(),
        ]);
        $user = Store::get('users', (int) $doc['user_id']);
        Notifier::log($adminId, $approve ? 'success' : 'danger', ($approve ? 'Admin menyetujui ' : 'Admin menolak ') . Drivana::DOC_LABELS[$doc['type']] . ' ' . $user['name'], $approve ? null : $reason);
        self::recompute((int) $doc['user_id']);

        return true;
    }
}
