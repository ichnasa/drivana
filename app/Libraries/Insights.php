<?php

namespace App\Libraries;

/** Premium analytics for Mitra, computed from the demo data. */
class Insights
{
    public const PERIODS = ['minggu' => ['Minggu ini', 7], 'bulan' => ['Bulan ini', 30], 'kuartal' => ['3 bulan terakhir', 90]];

    private const COUNTED = ['menunggu', 'dikonfirmasi', 'aktif', 'selesai'];

    private static function counted(): array
    {
        return Store::where('bookings', fn ($b) => in_array($b['status'], self::COUNTED, true));
    }

    /** Suggested daily price for a car, based on same-type cars and recent demand. */
    public static function price(array $car): array
    {
        $prices = array_map(fn ($c) => (int) $c['price'], Store::where('cars', fn ($c) => $c['type'] === $car['type'] && (int) $c['id'] !== (int) $car['id'] && $c['listing_status'] === 'approved' && ! $c['deleted_at']));
        sort($prices);
        $n = count($prices);
        $market = $n ? ($n % 2 ? $prices[intdiv($n, 2)] : (int) (($prices[$n / 2 - 1] + $prices[$n / 2]) / 2)) : (int) $car['price'];

        $typeBookings = array_filter(self::counted(), fn ($b) => (Store::get('cars', (int) $b['car_id'])['type'] ?? '') === $car['type']);
        $since7 = date('Y-m-d', strtotime('-7 days'));
        $since14 = date('Y-m-d', strtotime('-14 days'));
        $recent = count(array_filter($typeBookings, fn ($b) => $b['start_date'] >= $since7));
        $before = count(array_filter($typeBookings, fn ($b) => $b['start_date'] >= $since14 && $b['start_date'] < $since7));
        $growth = $before > 0 ? ($recent - $before) / $before : ($recent > 0 ? .15 : 0);
        $growth = max(-.1, min(.25, $growth));

        $round = fn (float $v) => (int) (round($v / 5000) * 5000);
        $low = $round($market * (0.95 + $growth / 2));
        $high = $round($market * (1.10 + $growth));
        $mid = (int) (($low + $high) / 2);
        $potential = (int) $car['price'] > 0 ? (int) round(($mid - $car['price']) / $car['price'] * 100) : 0;

        $names = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $chart = array_fill_keys(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'], 0);
        $since28 = date('Y-m-d', strtotime('-28 days'));
        foreach ($typeBookings as $b) {
            if ($b['start_date'] >= $since28) {
                $chart[$names[(int) date('w', strtotime($b['start_date']))]]++;
            }
        }

        return ['market' => $market, 'low' => $low, 'high' => $high, 'mid' => $mid, 'potential' => $potential, 'growth' => (int) round($growth * 100), 'recent' => $recent, 'peers' => $n, 'chart' => $chart];
    }

    /** Own performance for one mitra in a period, vs the previous equal period. */
    public static function analytics(int $mitraId, string $period): array
    {
        $days = self::PERIODS[$period][1] ?? 30;
        $from = date('Y-m-d', strtotime("-{$days} days"));
        $prev = date('Y-m-d', strtotime('-' . ($days * 2) . ' days'));
        $mine = array_filter(self::counted(), fn ($b) => (int) $b['mitra_id'] === $mitraId);
        $cur = array_filter($mine, fn ($b) => $b['start_date'] >= $from);
        $old = array_filter($mine, fn ($b) => $b['start_date'] >= $prev && $b['start_date'] < $from);
        $net = fn (array $rows) => array_sum(array_map(fn ($b) => $b['status'] === 'selesai' ? $b['total'] - $b['service_fee'] : 0, $rows));
        $delta = fn ($a, $b) => $b > 0 ? (int) round(($a - $b) / $b * 100) : ($a > 0 ? 100 : 0);

        $myCars = array_column(Store::where('cars', fn ($c) => (int) $c['mitra_id'] === $mitraId), null, 'id');
        $ratings = array_map(fn ($r) => $r['rating'], Store::where('reviews', fn ($r) => isset($myCars[(int) $r['car_id']])));

        $byArea = [];
        $byCar = [];
        foreach ($cur as $b) {
            $byArea[$b['area']] = ($byArea[$b['area']] ?? 0) + 1;
            $byCar[(int) $b['car_id']] = ($byCar[(int) $b['car_id']] ?? 0) + 1;
        }
        arsort($byArea);
        arsort($byCar);
        $insight = 'Belum cukup data. Insight muncul setelah ada booking.';
        if ($byArea && $byCar) {
            $insight = $myCars[array_key_first($byCar)]['name'] . ' paling laku di periode ini, dengan permintaan terbanyak dari ' . array_key_first($byArea) . '. Pertimbangkan menaikkan harganya sedikit di akhir pekan.';
        }

        return [
            'period' => self::PERIODS[$period][0], 'bookings' => count($cur), 'bookings_delta' => $delta(count($cur), count($old)),
            'revenue' => $net($cur), 'revenue_delta' => $delta($net($cur), $net($old)),
            'rating' => $ratings ? round(array_sum($ratings) / count($ratings), 1) : null, 'reviews' => count($ratings), 'insight' => $insight,
        ];
    }

    /** Net earnings per month for a mitra (completed bookings; the platform keeps the service fee). */
    public static function earnings(int $mitraId, int $months = 6): array
    {
        $by = [];
        foreach (Store::where('bookings', fn ($b) => (int) $b['mitra_id'] === $mitraId && $b['status'] === 'selesai') as $b) {
            $ym = substr($b['finished_at'] ?: $b['end_date'], 0, 7);
            $by[$ym]['ym'] = $ym;
            $by[$ym]['n'] = ($by[$ym]['n'] ?? 0) + 1;
            $by[$ym]['net'] = ($by[$ym]['net'] ?? 0) + $b['total'] - $b['service_fee'];
        }
        krsort($by);

        return array_slice(array_values($by), 0, $months);
    }
}
