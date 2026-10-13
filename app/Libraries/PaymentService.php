<?php

namespace App\Libraries;

/**
 * Payments + the simulated gateway. A payment is created "pending", the gateway page
 * calls succeed()/fail() (like a webhook), and success triggers the right effect per kind.
 */
class PaymentService
{
    public function __construct()
    {
        helper('ui');
    }

    public function find(string $ref): ?array
    {
        return Store::first('payments', fn ($p) => $p['ref'] === $ref);
    }

    public function create(int $userId, string $kind, int $amount, string $method, array $extra = []): array
    {
        do {
            $ref = Drivana::code('PAY');
        } while ($this->find($ref));
        $now = Drivana::now();
        Store::insert('payments', [
            'ref' => $ref, 'user_id' => $userId, 'kind' => $kind, 'booking_id' => $extra['booking_id'] ?? null, 'plan' => $extra['plan'] ?? null,
            'car_id' => $extra['car_id'] ?? null, 'days' => $extra['days'] ?? null, 'amount' => $amount, 'method' => $method, 'status' => 'pending',
            'paid_at' => null, 'refunded_at' => null, 'created_at' => $now, 'updated_at' => $now,
        ]);

        return $this->find($ref);
    }

    /** Gateway says: paid. Idempotent. */
    public function succeed(string $ref): bool
    {
        $p = $this->find($ref);
        if (! $p || $p['status'] !== 'pending') {
            return false;
        }
        $now = Drivana::now();
        Store::update('payments', (int) $p['id'], ['status' => 'paid', 'paid_at' => $now, 'updated_at' => $now]);
        $p = $this->find($ref);

        match ($p['kind']) {
            'booking'      => (new BookingService())->markPaid((int) $p['booking_id']),
            'subscription' => $this->activateSubscription($p),
            'boost'        => $this->activateBoost($p),
            default        => null,
        };

        return true;
    }

    public function fail(string $ref): bool
    {
        $p = $this->find($ref);
        if (! $p || $p['status'] !== 'pending') {
            return false;
        }
        Store::update('payments', (int) $p['id'], ['status' => 'failed', 'updated_at' => Drivana::now()]);
        Notifier::to((int) $p['user_id'], 'Pembayaran gagal', 'Pembayaran ' . $p['ref'] . ' tidak berhasil. Coba lagi.', null);

        return true;
    }

    public function refundForBooking(int $bookingId): void
    {
        $p = Store::first('payments', fn ($x) => (int) $x['booking_id'] === $bookingId && $x['status'] === 'paid');
        if ($p) {
            Store::update('payments', (int) $p['id'], ['status' => 'refunded', 'refunded_at' => Drivana::now(), 'updated_at' => Drivana::now()]);
            Notifier::log((int) $p['user_id'], 'danger', 'Refund ' . rupiah($p['amount']) . ' untuk ' . $p['ref'], null);
        }
    }

    /* ───────── subscription / boost effects ───────── */

    public function activeSubscription(int $mitraId): ?array
    {
        $now = Drivana::now();
        $subs = Store::where('subscriptions', fn ($s) => (int) $s['mitra_id'] === $mitraId && $s['ends_at'] > $now);
        usort($subs, fn ($a, $b) => strcmp($b['ends_at'], $a['ends_at']));

        return $subs[0] ?? null;
    }

    private function activateSubscription(array $p): void
    {
        $plan   = Drivana::PLANS[$p['plan']];
        $active = $this->activeSubscription((int) $p['user_id']);
        $starts = $active ? $active['ends_at'] : Drivana::now();
        $ends   = date('Y-m-d H:i:s', strtotime($starts . ' +' . $plan['days'] . ' days'));
        Store::insert('subscriptions', ['mitra_id' => (int) $p['user_id'], 'plan' => $p['plan'], 'price' => $p['amount'], 'starts_at' => $starts, 'ends_at' => $ends, 'payment_id' => (int) $p['id'], 'created_at' => Drivana::now()]);
        Notifier::to((int) $p['user_id'], 'Langganan Premium aktif', 'Paket ' . $plan['name'] . ' aktif sampai ' . date_id($ends) . '.', 'mitra/langganan');
        Notifier::log((int) $p['user_id'], 'success', 'Langganan Premium ' . $plan['name'] . ' dibeli', rupiah($p['amount']));
    }

    private function activateBoost(array $p): void
    {
        $car    = Store::get('cars', (int) $p['car_id']);
        $now    = Drivana::now();
        $base   = ($car && $car['boosted_until'] && $car['boosted_until'] > $now) ? $car['boosted_until'] : $now;
        $ends   = date('Y-m-d H:i:s', strtotime($base . ' +' . $p['days'] . ' days'));
        Store::insert('boosts', ['car_id' => (int) $p['car_id'], 'mitra_id' => (int) $p['user_id'], 'days' => (int) $p['days'], 'price' => $p['amount'], 'starts_at' => $base, 'ends_at' => $ends, 'payment_id' => (int) $p['id'], 'created_at' => $now]);
        Store::update('cars', (int) $p['car_id'], ['boosted_until' => $ends, 'updated_at' => $now]);
        Notifier::to((int) $p['user_id'], 'Boost aktif', ($car['name'] ?? 'Mobil') . ' tampil di urutan teratas sampai ' . date_id($ends) . '.', 'mitra/armada');
        Notifier::log((int) $p['user_id'], 'success', 'Boost ' . ($car['name'] ?? '') . ' ' . $p['days'] . ' hari dibeli', rupiah($p['amount']));
    }
}
