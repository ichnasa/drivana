<?php

namespace App\Libraries;

/** Customer-facing car queries. Only approved cars of verified, active mitra that are not in service show up in search. */
class CarSearch
{
    /** Cars with rating, review count, boost flag and owner names attached. */
    private static function enriched(): array
    {
        $now = Drivana::now();
        $users = [];
        foreach (Store::all('users') as $u) {
            $users[(int) $u['id']] = $u;
        }
        $stats = [];
        foreach (Store::all('reviews') as $r) {
            $stats[(int) $r['car_id']]['sum'] = ($stats[(int) $r['car_id']]['sum'] ?? 0) + $r['rating'];
            $stats[(int) $r['car_id']]['n'] = ($stats[(int) $r['car_id']]['n'] ?? 0) + 1;
        }
        $out = [];
        foreach (Store::all('cars') as $c) {
            $u = $users[(int) $c['mitra_id']] ?? null;
            $st = $stats[(int) $c['id']] ?? null;
            $out[] = $c + [
                'business_name' => $u['business_name'] ?? '', 'mitra_name' => $u['name'] ?? '', 'mitra_status' => $u['status'] ?? '', 'mitra_verification' => $u['verification_status'] ?? '',
                'rating' => $st ? round($st['sum'] / $st['n'], 1) : null, 'reviews_count' => $st['n'] ?? 0,
                'is_boosted' => ($c['boosted_until'] && $c['boosted_until'] > $now) ? 1 : 0,
            ];
        }

        return $out;
    }

    private static function visible(array $c): bool
    {
        return $c['listing_status'] === 'approved' && ! $c['deleted_at'] && $c['ops_status'] === 'tersedia'
            && $c['mitra_status'] === 'active' && $c['mitra_verification'] === 'verified';
    }

    /**
     * @param array{q?:string,type?:string,seats?:int,min?:int,max?:int,driver?:string,area?:string,start?:string,end?:string,sort?:string} $f
     * @return array{items:array<int,array>,total:int,pages:int}
     */
    public static function search(array $f, int $page = 1, int $per = 8): array
    {
        $bookings = new BookingService();
        $bookings->expireStale();
        $q = mb_strtolower(trim((string) ($f['q'] ?? '')));

        $items = array_values(array_filter(self::enriched(), function ($c) use ($f, $q, $bookings) {
            if (! self::visible($c)) {
                return false;
            }
            if ($q !== '' && ! str_contains(mb_strtolower($c['name'] . ' ' . $c['type'] . ' ' . $c['business_name']), $q)) {
                return false;
            }
            if (! empty($f['type']) && $f['type'] !== 'Semua' && $c['type'] !== $f['type']) {
                return false;
            }
            if (! empty($f['seats']) && $c['seats'] < (int) $f['seats']) {
                return false;
            }
            if (! empty($f['min']) && $c['price'] < (int) $f['min']) {
                return false;
            }
            if (! empty($f['max']) && $c['price'] > (int) $f['max']) {
                return false;
            }
            if (($f['driver'] ?? '') === '1' && ! ($c['with_driver'] || $c['driver_only'])) {
                return false;
            }
            if (($f['driver'] ?? '') === '0' && $c['driver_only']) {
                return false;
            }
            if (! empty($f['area']) && $c['area'] !== $f['area']) {
                return false;
            }
            if (! empty($f['start']) && ! empty($f['end']) && ! $bookings->isAvailable((int) $c['id'], $f['start'], $f['end'])) {
                return false;
            }

            return true;
        }));

        $byRating = fn ($a, $b) => [$b['rating'] ?? 0, $b['reviews_count']] <=> [$a['rating'] ?? 0, $a['reviews_count']];
        usort($items, function ($a, $b) use ($f, $byRating) {
            if ($a['is_boosted'] !== $b['is_boosted']) {
                return $b['is_boosted'] <=> $a['is_boosted'];   // boosted listings always first
            }

            return match ($f['sort'] ?? 'rekomendasi') {
                'termurah' => $a['price'] <=> $b['price'],
                'termahal' => $b['price'] <=> $a['price'],
                default    => $byRating($a, $b) ?: $a['id'] <=> $b['id'],
            };
        });

        $total = count($items);
        $page = max(1, $page);

        return ['items' => array_slice($items, ($page - 1) * $per, $per), 'total' => $total, 'pages' => max(1, (int) ceil($total / $per))];
    }

    /** One car, visible to customers (even when in service, so the page can say so), or null. */
    public static function detail(int $id): ?array
    {
        foreach (self::enriched() as $c) {
            if ((int) $c['id'] === $id) {
                if ($c['listing_status'] !== 'approved' || $c['deleted_at'] || $c['mitra_status'] !== 'active') {
                    return null;
                }
                $own = array_filter(self::enriched(), fn ($x) => $x['mitra_id'] === $c['mitra_id'] && $x['listing_status'] === 'approved' && ! $x['deleted_at']);
                $rated = array_filter(array_column($own, 'rating'));
                $c['fleet_count'] = count($own);
                $c['mitra_rating'] = $rated ? round(array_sum($rated) / count($rated), 1) : null;
                $c['owner_id'] = (int) $c['mitra_id'];

                return $c;
            }
        }

        return null;
    }

    public static function reviews(int $carId, int $limit = 5): array
    {
        $rows = Store::where('reviews', fn ($r) => (int) $r['car_id'] === $carId);
        usort($rows, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return array_map(fn ($r) => $r + ['author' => Store::get('users', (int) $r['customer_id'])['name'] ?? 'Penyewa'], array_slice($rows, 0, $limit));
    }

    /** Date ranges already taken (for the detail page). */
    public static function busyRanges(int $carId): array
    {
        (new BookingService())->expireStale();
        $today = date('Y-m-d');
        $rows = Store::where('bookings', fn ($b) => (int) $b['car_id'] === $carId && in_array($b['status'], Drivana::BLOCKING, true) && $b['end_date'] >= $today);
        usort($rows, fn ($a, $b) => strcmp($a['start_date'], $b['start_date']));

        return array_map(fn ($b) => ['start_date' => $b['start_date'], 'end_date' => $b['end_date']], $rows);
    }
}
