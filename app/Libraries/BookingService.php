<?php

namespace App\Libraries;

/** Booking lifecycle: pending_payment → menunggu → dikonfirmasi → aktif → selesai (or dibatalkan/ditolak/kedaluwarsa). */
class BookingService
{
    public function __construct()
    {
        helper('ui');
    }

    /** Price breakdown. Driver-only cars always include the driver. */
    public static function quote(array $car, int $days, bool $driver, int $discountPercent = 0): array
    {
        $withDriver  = $driver || (int) ($car['driver_only'] ?? 0) === 1;
        $rent        = (int) $car['price'] * $days;
        $driverTotal = $withDriver ? Drivana::DRIVER_PRICE_PER_DAY * $days : 0;
        $fee         = Drivana::SERVICE_FEE;
        $subtotal    = $rent + $driverTotal + $fee;
        $discount    = (int) round($subtotal * $discountPercent / 100);

        return ['days' => $days, 'with_driver' => $withDriver ? 1 : 0, 'rent' => $rent, 'driver' => $driverTotal, 'fee' => $fee, 'discount' => $discount, 'total' => $subtotal - $discount];
    }

    public function find(int $id): ?array
    {
        return Store::get('bookings', $id);
    }

    public function findByCode(string $code): ?array
    {
        return Store::first('bookings', fn ($b) => $b['code'] === $code);
    }

    /** Release unpaid bookings whose payment window has passed. */
    public function expireStale(): void
    {
        $now = Drivana::now();
        foreach (Store::where('bookings', fn ($b) => $b['status'] === 'pending_payment' && $b['expires_at'] && $b['expires_at'] < $now) as $b) {
            Store::update('bookings', (int) $b['id'], ['status' => 'kedaluwarsa', 'updated_at' => $now]);
        }
    }

    /** Is the car free for [start, end)? A car can be returned and rented again on the same day. */
    public function isAvailable(int $carId, string $start, string $end, ?int $excludeId = null): bool
    {
        $this->expireStale();

        return Store::count('bookings', fn ($b) => (int) $b['car_id'] === $carId && in_array($b['status'], Drivana::BLOCKING, true)
            && $b['start_date'] < $end && $b['end_date'] > $start && (int) $b['id'] !== (int) $excludeId) === 0;
    }

    /** @return array{ok:bool, booking?:array, errors?:array<string,string>} */
    public function create(array $customer, array $car, array $in): array
    {
        $start = $in['start'];
        $end   = $in['end'];
        $days  = (int) round((strtotime($end) - strtotime($start)) / 86400);
        $err   = [];

        if ($customer['verification_status'] !== 'verified') {
            $err['form'] = 'Akun kamu belum terverifikasi. Verifikasi KTP, SIM, dan selfie dulu.';
        }
        if ($start < date('Y-m-d')) {
            $err['start'] = 'Tanggal mulai tidak boleh sebelum hari ini.';
        }
        if ($days < 1) {
            $err['end'] = 'Tanggal selesai harus setelah tanggal mulai.';
        } elseif ($days > Drivana::MAX_DAYS) {
            $err['end'] = 'Maksimal sewa ' . Drivana::MAX_DAYS . ' hari. Hubungi mitra untuk sewa lebih lama.';
        }
        if (($car['listing_status'] ?? '') !== 'approved' || $car['ops_status'] !== 'tersedia' || $car['deleted_at']) {
            $err['form'] = 'Mobil ini sedang tidak tersedia.';
        }
        if ((int) $car['mitra_id'] === (int) $customer['id']) {
            $err['form'] = 'Kamu tidak bisa menyewa mobil milikmu sendiri.';
        }
        if (! in_array($in['area'], Drivana::AREAS, true)) {
            $err['area'] = 'Pilih wilayah pengantaran.';
        }
        if (! $err && ! $this->isAvailable((int) $car['id'], $start, $end)) {
            $err['form'] = 'Maaf, mobil ini sudah dipesan di tanggal tersebut. Coba tanggal lain.';
        }
        if ($err) {
            return ['ok' => false, 'errors' => $err];
        }

        $q = self::quote($car, $days, (bool) $in['with_driver']);
        do {
            $code = Drivana::code('DRV');
        } while ($this->findByCode($code));
        $now = Drivana::now();
        $id = Store::insert('bookings', [
            'code' => $code, 'customer_id' => (int) $customer['id'], 'car_id' => (int) $car['id'], 'mitra_id' => (int) $car['mitra_id'],
            'start_date' => $start, 'end_date' => $end, 'days' => $days, 'with_driver' => $q['with_driver'],
            'area' => $in['area'], 'address' => $in['address'], 'note' => $in['note'] ?: null,
            'rent_total' => $q['rent'], 'driver_total' => $q['driver'], 'service_fee' => $q['fee'], 'discount' => 0, 'total' => $q['total'], 'promo_code' => null,
            'status' => 'pending_payment', 'cancel_reason' => null, 'confirmed_at' => null, 'started_at' => null, 'finished_at' => null,
            'expires_at' => date('Y-m-d H:i:s', time() + Drivana::PAY_WINDOW_MINUTES * 60), 'created_at' => $now, 'updated_at' => $now,
        ]);

        return ['ok' => true, 'booking' => $this->find($id)];
    }

    /** Apply (or clear) a promo to an unpaid booking. */
    public function applyPromo(array $booking, ?array $promo): array
    {
        $subtotal = $booking['rent_total'] + $booking['driver_total'] + $booking['service_fee'];
        $discount = $promo ? (int) round($subtotal * $promo['percent'] / 100) : 0;
        Store::update('bookings', (int) $booking['id'], ['discount' => $discount, 'total' => $subtotal - $discount, 'promo_code' => $promo['code'] ?? null, 'updated_at' => Drivana::now()]);

        return $this->find((int) $booking['id']);
    }

    private function setStatus(int $id, string $status, array $extra = []): void
    {
        Store::update('bookings', $id, ['status' => $status, 'updated_at' => Drivana::now()] + $extra);
    }

    /** Payment confirmed by the gateway. */
    public function markPaid(int $id): void
    {
        $b = $this->find($id);
        if (! $b || $b['status'] !== 'pending_payment') {
            return;
        }
        $this->setStatus($id, 'menunggu', ['expires_at' => null]);
        Notifier::to((int) $b['mitra_id'], 'Pesanan baru ' . $b['code'], 'Ada pesanan masuk untuk ' . $this->carName($b) . '. Konfirmasi sekarang.', 'mitra/reservasi/' . $b['code']);
        Notifier::to((int) $b['customer_id'], 'Pembayaran berhasil', 'Pesanan ' . $b['code'] . ' menunggu konfirmasi mitra.', 'pesanan/' . $b['code']);
        Notifier::log((int) $b['customer_id'], 'info', 'Pembayaran booking ' . $b['code'] . ' diterima', rupiah($b['total']));
    }

    public function confirm(int $id): bool
    {
        $b = $this->find($id);
        if (! $b || $b['status'] !== 'menunggu') {
            return false;
        }
        $this->setStatus($id, 'dikonfirmasi', ['confirmed_at' => Drivana::now()]);
        Notifier::to((int) $b['customer_id'], 'Pesanan dikonfirmasi', $this->carName($b) . ' siap untuk kamu pada ' . date_id($b['start_date'], false) . '.', 'pesanan/' . $b['code']);

        return true;
    }

    public function reject(int $id, string $reason): bool
    {
        $b = $this->find($id);
        if (! $b || ! in_array($b['status'], ['menunggu', 'dikonfirmasi'], true)) {
            return false;
        }
        $this->setStatus($id, 'ditolak', ['cancel_reason' => $reason]);
        (new PaymentService())->refundForBooking($id);
        Notifier::to((int) $b['customer_id'], 'Pesanan ditolak mitra', 'Dana ' . rupiah($b['total']) . ' dikembalikan. Alasan: ' . $reason, 'pesanan/' . $b['code']);

        return true;
    }

    public function cancel(int $id, string $reason = 'Dibatalkan oleh penyewa'): bool
    {
        $b = $this->find($id);
        if (! $b || ! in_array($b['status'], ['pending_payment', 'menunggu', 'dikonfirmasi'], true)) {
            return false;
        }
        $this->setStatus($id, 'dibatalkan', ['cancel_reason' => $reason]);
        if ($b['status'] !== 'pending_payment') {
            (new PaymentService())->refundForBooking($id);
            Notifier::to((int) $b['mitra_id'], 'Pesanan dibatalkan', $b['code'] . ' dibatalkan oleh penyewa.', 'mitra/reservasi/' . $b['code']);
        }

        return true;
    }

    public function start(int $id): bool
    {
        $b = $this->find($id);
        if (! $b || $b['status'] !== 'dikonfirmasi') {
            return false;
        }
        $this->setStatus($id, 'aktif', ['started_at' => Drivana::now()]);
        Notifier::to((int) $b['customer_id'], 'Sewa dimulai', 'Selamat jalan! ' . $this->carName($b) . ' sudah bersamamu.', 'pesanan/' . $b['code']);

        return true;
    }

    public function finish(int $id): bool
    {
        $b = $this->find($id);
        if (! $b || $b['status'] !== 'aktif') {
            return false;
        }
        $this->setStatus($id, 'selesai', ['finished_at' => Drivana::now()]);
        Notifier::to((int) $b['customer_id'], 'Sewa selesai', 'Bagaimana pengalamanmu? Beri ulasan untuk ' . $this->carName($b) . '.', 'pesanan/' . $b['code']);

        return true;
    }

    private function carName(array $b): string
    {
        return Store::get('cars', (int) $b['car_id'])['name'] ?? 'mobil';
    }
}
