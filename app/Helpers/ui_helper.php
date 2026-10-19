<?php

if (! function_exists('icon')) {
    /**
     * Inline a Lucide icon (app/Views/icons/{name}.svg).
     */
    function icon(string $name, int $size = 20, string $class = '', float $stroke = 2): string
    {
        static $cache = [];

        $name = preg_replace('/[^a-z0-9-]/', '', strtolower($name));
        $file = APPPATH . 'Views/icons/' . $name . '.svg';

        if (! isset($cache[$name])) {
            $cache[$name] = is_file($file) ? file_get_contents($file) : '';
        }

        if ($cache[$name] === '') {
            log_message('warning', 'Missing Lucide icon: ' . $name);

            return '';
        }

        $svg = preg_replace('/<!--.*?-->/s', '', $cache[$name]);

        // Only touch the root <svg> tag, never inner shapes (e.g. <rect width=...>).
        $svg = preg_replace_callback('/<svg\b[^>]*>/', static function (array $m) use ($size, $class, $stroke): string {
            $tag = preg_replace('/\s(width|height|class)="[^"]*"/', '', $m[0]);
            $tag = preg_replace('/stroke-width="[^"]*"/', 'stroke-width="' . $stroke . '"', $tag);

            return preg_replace(
                '/^<svg/',
                '<svg width="' . $size . '" height="' . $size . '" class="icon ' . esc($class, 'attr') . '" aria-hidden="true"',
                $tag
            );
        }, $svg, 1);

        return $svg;
    }
}

if (! function_exists('rupiah')) {
    function rupiah(int|float $n, bool $short = false): string
    {
        if ($short && $n >= 1000) {
            return 'Rp ' . rtrim(rtrim(number_format($n / 1000, 1, ',', '.'), '0'), ',') . 'rb';
        }

        return 'Rp ' . number_format($n, 0, ',', '.');
    }
}

if (! function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));

        return strtoupper(mb_substr($parts[0], 0, 1) . (isset($parts[1]) ? mb_substr($parts[1], 0, 1) : ''));
    }
}


if (! function_exists('avatar')) {
    /** Initials avatar (dummy photos are not part of the design assets). */
    function avatar(string $name, int $size = 44, string $class = ''): string
    {
        return '<span class="avatar ' . esc($class, 'attr') . '" style="--s:' . $size . 'px">' . esc(initials($name)) . '</span>';
    }
}

if (! function_exists('nav_items')) {
    /** Bottom navigation per role: key => [label, icon, url]. */
    function nav_items(string $role): array
    {
        return match ($role) {
            'mitra' => [
                'dashboard' => ['Dashboard', 'layout-dashboard', 'mitra/dashboard'],
                'armada'    => ['Armada', 'car', 'mitra/armada'],
                'reservasi' => ['Reservasi', 'calendar-days', 'mitra/reservasi'],
                'pesan'     => ['Pesan', 'message-circle', 'mitra/pesan'],
                'profil'    => ['Profil', 'circle-user-round', 'mitra/profil'],
            ],
            'admin' => [
                'dashboard'  => ['Dashboard', 'layout-dashboard', 'admin/dashboard'],
                'verifikasi' => ['Verifikasi', 'shield-check', 'admin/verifikasi'],
                'pengguna'   => ['Pengguna', 'users', 'admin/pengguna'],
                'aktivitas'  => ['Aktivitas', 'history', 'admin/aktivitas'],
                'pengaturan' => ['Pengaturan', 'settings', 'admin/pengaturan'],
            ],
            default => [
                'beranda'  => ['Beranda', 'house', 'beranda'],
                'cari'     => ['Cari', 'search', 'cari'],
                'pesanan'  => ['Pesanan', 'calendar-days', 'pesanan'],
                'chat'     => ['Chat', 'message-square', 'chat'],
                'profil'   => ['Profil', 'user-round', 'profil'],
            ],
        };
    }
}

if (! function_exists('role_url')) {
    /** URL for a shared feature under the signed-in role's prefix (customer has none). */
    function role_url(string $path, ?string $role = null): string
    {
        $role ??= current_user()['role'] ?? 'customer';

        return site_url(($role === 'customer' ? '' : $role . '/') . ltrim($path, '/'));
    }
}

if (! function_exists('bell_btn')) {
    /** Notification bell with an unread dot. */
    function bell_btn(?string $role = null): string
    {
        static $unread = null;
        $u = current_user();
        if ($u && $unread === null) {
            $unread = \App\Libraries\Notifier::unread((int) $u['id']);
        }

        return '<a class="icon-btn" href="' . role_url('notifikasi', $role) . '" aria-label="Notifikasi' . ($unread ? ' (' . $unread . ' baru)' : '') . '">' . icon('bell', 20) . ($unread ? '<span class="dot"></span>' : '') . '</a>';
    }
}

if (! function_exists('car_img')) {
    /** Public URL of a car photo (seed photos live in assets/, uploads in uploads/). */
    function car_img(string $path): string
    {
        return base_url(str_contains($path, '/') ? $path : 'assets/img/cars/' . $path);
    }
}

if (! function_exists('badge_for')) {
    function badge_for(string $status): string
    {
        return '<span class="badge ' . \App\Libraries\Drivana::statusClass($status) . '">' . esc(\App\Libraries\Drivana::statusLabel($status)) . '</span>';
    }
}

if (! function_exists('date_id')) {
    function date_id(?string $d, bool $withYear = true): string
    {
        if (! $d) {
            return '';
        }
        $m = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $t = strtotime($d);

        return date('j', $t) . ' ' . $m[(int) date('n', $t) - 1] . ($withYear ? ' ' . date('Y', $t) : '');
    }
}

if (! function_exists('stars')) {
    function stars(float $rating): string
    {
        return '<span class="rating">' . icon('star', 14, 'fill') . ' ' . number_format($rating, 1) . '</span>';
    }
}

if (! function_exists('partial')) {
    /** Render a component view with its own isolated data (nothing leaks between calls). */
    function partial(string $name, array $data = []): string
    {
        return view($name, $data, ['saveData' => false]);
    }
}


if (! function_exists('form_error')) {
    /** Server-side validation message for a field (flashed by the controller), or null. */
    function form_error(string $name): ?string
    {
        $errors = session()->getFlashdata('errors');

        return is_array($errors) ? ($errors[$name] ?? null) : null;
    }
}

if (! function_exists('old_input')) {
    /** Previously submitted value (passwords are never flashed). */
    function old_input(string $name, string $default = ''): string
    {
        $old = session()->getFlashdata('old');

        return is_array($old) ? (string) ($old[$name] ?? $default) : $default;
    }
}

if (! function_exists('current_user')) {
    function current_user(): ?array
    {
        return session('user');
    }
}
