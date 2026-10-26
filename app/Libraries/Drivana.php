<?php

namespace App\Libraries;

/** Business constants + display helpers shared by every module (single source of truth). */
class Drivana
{
    public const DRIVER_PRICE_PER_DAY = 150000;
    public const SERVICE_FEE          = 15000;
    public const MAX_DAYS             = 30;
    public const PAY_WINDOW_MINUTES   = 30;
    public const OTP_CODE             = '123456';
    public const OTP_TTL_SECONDS      = 300;

    public const AREAS = [
        'Jakarta Pusat', 'Jakarta Selatan', 'Tangerang Selatan', 'Bekasi Utara',
        'Depok Margonda', 'Bandung Kota', 'Banjarmasin Tengah', 'Banjarbaru',
    ];

    public const CAR_TYPES    = ['MPV', 'SUV', 'Minibus', 'Sedan', 'City Car', 'Off-road', 'Luxury'];
    public const SEAT_OPTIONS = [2, 4, 5, 7, 9, 12];
    public const TRANSMISSION = ['Manual', 'Automatic'];
    public const FUEL         = ['Bensin', 'Diesel', 'Listrik', 'Hybrid'];


    /** Saved-address list shown on the profile (UI only). */
    public const DUMMY_ADDRESSES = [
        ['label' => 'Rumah', 'address' => 'Jl. Sudirman No. 12, Jakarta', 'area' => 'Jakarta Selatan'],
        ['label' => 'Kantor', 'address' => 'Jl. Thamrin No. 45, Jakarta', 'area' => 'Jakarta Pusat'],
        ['label' => 'Rumah orang tua', 'address' => 'Jl. Margonda Raya No. 8, Depok', 'area' => 'Depok Margonda'],
    ];

    /** Subscription plans (Mitra premium). */
    public const PLANS = [
        'monthly' => ['name' => 'Bulanan', 'price' => 149000, 'days' => 30, 'note' => 'Fleksibel, perpanjang tiap bulan'],
        'yearly'  => ['name' => 'Tahunan', 'price' => 1490000, 'days' => 365, 'note' => 'Hemat 2 bulan'],
    ];

    public const PLAN_FEATURES = [
        ['icon' => 'circle-dollar-sign', 'title' => 'Rekomendasi harga', 'text' => 'Harga sewa mengikuti pasar dan permintaan.'],
        ['icon' => 'chart-line', 'title' => 'Analitik rental', 'text' => 'Booking, rating, dan pendapatan per periode.'],
        ['icon' => 'rocket', 'title' => 'Boost listing', 'text' => 'Tampil di urutan teratas hasil pencarian.'],
    ];

    /** Boost packages: days => price. */
    public const BOOSTS = [3 => 45000, 7 => 90000, 14 => 150000];

    public const PAYMENT_METHODS = [
        ['key' => 'bank',   'name' => 'Transfer Bank',      'hint' => 'BCA · BRI · Mandiri', 'icon' => 'landmark'],
        ['key' => 'wallet', 'name' => 'E-Wallet',           'hint' => 'GoPay · OVO · DANA',  'icon' => 'wallet'],
        ['key' => 'card',   'name' => 'Kartu Kredit/Debit', 'hint' => 'Visa · Mastercard',   'icon' => 'credit-card'],
    ];

    /** Documents each role must have approved before it is "verified". */
    public const REQUIRED_DOCS = [
        'customer' => ['ktp', 'sim', 'selfie'],
        'mitra'    => ['ktp', 'sim', 'selfie', 'business'],
    ];

    public const DOC_LABELS = ['ktp' => 'KTP', 'sim' => 'SIM', 'selfie' => 'Selfie', 'business' => 'Dokumen bisnis (SIUP/NIB)'];

    /** Booking status => [label, badge class]. */
    public const BOOKING_STATUS = [
        'pending_payment' => ['Menunggu pembayaran', 'badge-yellow'],
        'menunggu'        => ['Menunggu Konfirmasi', 'badge-yellow'],
        'dikonfirmasi'    => ['Dikonfirmasi', 'badge-blue'],
        'aktif'           => ['Sedang Digunakan', 'badge-blue'],
        'perlu_pengembalian' => ['Perlu Pengembalian', 'badge-yellow'],
        'selesai'         => ['Selesai', 'badge-green'],
        'dibatalkan'      => ['Dibatalkan', ''],
        'ditolak'         => ['Ditolak mitra', 'badge-red'],
        'kedaluwarsa'     => ['Kedaluwarsa', ''],
    ];

    /** Statuses that keep a car blocked for the booked dates. */
    public const BLOCKING = ['pending_payment', 'menunggu', 'dikonfirmasi', 'aktif'];

    /** Status shown to people: an active rental past its end date is "Perlu pengembalian". */
    public static function displayStatus(array $b): string
    {
        return ($b['status'] === 'aktif' && $b['end_date'] <= date('Y-m-d')) ? 'perlu_pengembalian' : $b['status'];
    }

    public static function statusLabel(string $s): string
    {
        return self::BOOKING_STATUS[$s][0] ?? $s;
    }

    public static function statusClass(string $s): string
    {
        return self::BOOKING_STATUS[$s][1] ?? '';
    }

    /** +62 / 62 / 0 prefixes all become 0xxxxxxxxxx. */
    public static function normalizePhone(string $value): string
    {
        $d = preg_replace('/[^0-9]/', '', $value);

        return str_starts_with($d, '62') ? '0' . substr($d, 2) : $d;
    }

    /**
     * Account verification from its documents (keyed by type).
     * unverified = required docs missing · pending = all uploaded, awaiting admin · rejected = any rejected · verified = all approved.
     * @return array{0:string,1:?string} [status, note]
     */
    public static function verificationFrom(array $docsByType, string $role): array
    {
        $required = self::REQUIRED_DOCS[$role] ?? [];
        if (! $required) {
            return ['verified', null];
        }
        foreach ($docsByType as $d) {
            if ($d['status'] === 'rejected') {
                return ['rejected', self::DOC_LABELS[$d['type']] . ' ditolak: ' . $d['reject_reason']];
            }
        }
        if (array_diff($required, array_keys($docsByType))) {
            return ['unverified', null];
        }
        foreach ($required as $t) {
            if ($docsByType[$t]['status'] !== 'approved') {
                return ['pending', null];
            }
        }

        return ['verified', null];
    }

    public static function home(string $role): string
    {
        return ['customer' => 'beranda', 'mitra' => 'mitra/dashboard', 'admin' => 'admin/dashboard'][$role] ?? 'login';
    }


    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function code(string $prefix): string
    {
        return $prefix . '-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
    }

    /** Human "5 menit lalu" style time. */
    public static function ago(?string $datetime): string
    {
        if (! $datetime) {
            return '';
        }
        $d = time() - strtotime($datetime);

        return match (true) {
            $d < 60       => 'baru saja',
            $d < 3600     => floor($d / 60) . ' menit lalu',
            $d < 86400    => floor($d / 3600) . ' jam lalu',
            $d < 86400 * 7 => floor($d / 86400) . ' hari lalu',
            default       => date('j M Y', strtotime($datetime)),
        };
    }
}
