<?php

namespace App\Libraries;

/** In-app notifications + admin activity log (stored in the demo Store). */
class Notifier
{
    public static function to(int $userId, string $title, string $body = '', ?string $url = null): void
    {
        Store::insert('notifications', ['user_id' => $userId, 'title' => $title, 'body' => $body ?: null, 'url' => $url, 'read_at' => null, 'created_at' => Drivana::now()]);
    }

    public static function admins(string $title, string $body = '', ?string $url = null): void
    {
        foreach (Store::where('users', fn ($u) => $u['role'] === 'admin') as $a) {
            self::to((int) $a['id'], $title, $body, $url);
        }
    }

    public static function unread(int $userId): int
    {
        return Store::count('notifications', fn ($n) => (int) $n['user_id'] === $userId && $n['read_at'] === null);
    }

    public static function log(?int $actor, string $tone, string $text, ?string $meta = null): void
    {
        Store::insert('activity_logs', ['actor_id' => $actor, 'tone' => $tone, 'text' => $text, 'meta' => $meta, 'created_at' => Drivana::now()]);
    }
}
