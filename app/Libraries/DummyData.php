<?php

namespace App\Libraries;

/**
 * THE single source of dummy data for the whole app. Store loads this on first use (or after /demo/reset).
 *
 * Logins (password in brackets):
 *   admin@drivana.test [Admin#123]            maulida@drivana.test [Customer#123]  (verified, has live orders)
 *   budi@jayaabadi.test [Mitra#123]  (Premium)  rani@drivana.test    [Customer#123]  (not verified yet)
 *   sari@sarimotor.test [Mitra#123]  (no plan)  citra@citrarent.test [Mitra#123]     (documents waiting for admin)
 */
class DummyData
{
    public const VERSION = 'demo-2026-10-13-b';

    /** Unsplash photo credits (file in public/assets/img/cars/). */
    private const PHOTOS = [
        'land-cruiser-prado' => ['6c5B82Gwzfo', 'Mohammad jahidul Islam'], 'honda-cr-v' => ['lVb8bdMLEQQ', 'Egor Litvinov'],
        'mazda-cx-5' => ['nsKQWNucrPE', 'Crosby Hinze'], 'hyundai-tucson' => ['9TUHjKs81I8', 'Hyundai Motor Group'],
        'ford-explorer' => ['a4S6KUuLeoM', 'Sven D'], 'tesla-model-y' => ['Zn7gb942k90', 'Tyler Casey'],
        'chrysler-pacifica' => ['TBSGddq5q40', 'Isaac Martin'], 'hyundai-staria' => ['KRWJs7Zg_pI', 'Hyundai Motor Group'],
        'mercedes-sprinter' => ['p_ILi6tlMwM', 'Fachy Marín'], 'mazda-3' => ['PHabU0OHcOY', 'Janosch Jost'],
        'honda-civic-type-r' => ['M8r4DW4VxD0', 'Erik Mclean'], 'fiat-500' => ['8tzIxRAKZVQ', 'Michael Martinelli'],
        'hyundai-santro' => ['SwJuiyGABEw', 'Zoshua Colah'], 'bmw-7-series' => ['eJc4FVnEy44', 'Salman Sidheek'],
        'mercedes-amg' => ['gFLEMnR09NI', 'Roland Denes'], 'jeep-wrangler-rubicon' => ['X7lXQDss8wc', 'Josh Duke'],
        'jeep-wrangler-unlimited' => ['rNZ3Gf9y1j4', 'Jason Pofahl'], 'land-cruiser-fj40' => ['3i5tJ8CKmcw', 'Ethan Sexton'],
        'rolls-royce-cullinan' => ['lzqNaWri3r0', 'Josué Soto'],
    ];

    public static function seed(): array
    {
        helper('ui');
        mt_srand(2026);
        $s = ['_v' => self::VERSION, 'seeded_on' => date('Y-m-d'), 'seq' => [], 't' => []];
        $ins = function (string $t, array $row) use (&$s): int {
            $id = ($s['seq'][$t] ?? 0) + 1;
            $s['seq'][$t] = $id;
            $row['id'] = $id;
            $s['t'][$t][$id] = $row;

            return $id;
        };
        $ago = fn (string $mod) => date('Y-m-d H:i:s', strtotime($mod));
        $now = Drivana::now();
        $hash = fn (string $p) => password_hash($p, PASSWORD_BCRYPT, ['cost' => 10]);
        [$hCust, $hMitra, $hAdmin] = [$hash('Customer#123'), $hash('Mitra#123'), $hash('Admin#123')];
        $phone = 81200000100;

        $user = function (string $role, string $name, string $email, string $pw, array $x = []) use ($ins, $ago, $now, &$phone): int {
            return $ins('users', $x + [
                'role' => $role, 'name' => $name, 'email' => $email, 'phone' => '0' . $phone++, 'password_hash' => $pw,
                'address' => null, 'area' => null, 'business_name' => null, 'business_address' => null, 'whatsapp' => null, 'bank_account' => null,
                'status' => 'active', 'verification_status' => 'unverified', 'verification_note' => null,
                'last_login_at' => null, 'created_at' => $ago('-' . mt_rand(20, 200) . ' days'), 'updated_at' => $now,
            ]);
        };

        /* ───────── users ───────── */
        $admin = $user('admin', 'Admin Drivana', 'admin@drivana.test', $hAdmin, ['phone' => '081200000000', 'verification_status' => 'verified']);
        $mBudi = $user('mitra', 'Budi Santoso', 'budi@jayaabadi.test', $hMitra, ['phone' => '081311223344', 'business_name' => 'Rental Jaya Abadi', 'business_address' => 'Jl. Ahmad Yani KM 7, Jakarta Selatan', 'whatsapp' => '081311223344', 'bank_account' => '1234567890', 'area' => 'Jakarta Selatan']);
        $mSari = $user('mitra', 'Sari Wulandari', 'sari@sarimotor.test', $hMitra, ['business_name' => 'Sari Motor', 'business_address' => 'Jl. A. Yani KM 4, Banjarmasin', 'whatsapp' => '081355550001', 'bank_account' => '9876543210', 'area' => 'Banjarmasin Tengah']);
        $mCitra = $user('mitra', 'Citra Maharani', 'citra@citrarent.test', $hMitra, ['business_name' => 'Citra Rent', 'business_address' => 'Jl. Margonda Raya No. 21, Depok', 'whatsapp' => '081355550002', 'bank_account' => '5550001234', 'area' => 'Depok Margonda']);
        $maulida = $user('customer', 'Maulida Rahmah', 'maulida@drivana.test', $hCust, ['phone' => '081234567890', 'address' => 'Jl. Sudirman No. 12, Jakarta', 'area' => 'Jakarta Selatan']);
        $verified = [$maulida];
        foreach (['Dewi Lestari', 'Fajar Nugroho', 'Gita Permata', 'Hendra Wijaya', 'Intan Kusuma', 'Joko Prasetyo', 'Kartika Sari', 'Lukman Hakim', 'Mega Anggraini', 'Nanda Pratama', 'Putri Ayu'] as $n) {
            $verified[] = $user('customer', $n, strtolower(explode(' ', $n)[0]) . '@mail.drivana.test', $hCust, ['area' => Drivana::AREAS[mt_rand(0, 7)]]);
        }
        $rani  = $user('customer', 'Rani Putri', 'rani@drivana.test', $hCust, ['area' => 'Jakarta Pusat']);
        $andi  = $user('customer', 'Andi Pratama', 'andi@mail.drivana.test', $hCust, ['phone' => '081234560003']);
        $siti  = $user('customer', 'Siti Rahma', 'siti@mail.drivana.test', $hCust);
        $budiH = $user('customer', 'Budi Hartono', 'budi.h@mail.drivana.test', $hCust);
        $rian  = $user('customer', 'Rian Hidayat', 'rian@mail.drivana.test', $hCust);

        /* ───────── documents (fake sample images drawn with GD) ───────── */
        self::sampleImages();
        $doc = function (int $uid, string $type, string $status, ?string $reason = null, string $when = '-2 hours') use ($ins, $ago, $admin): void {
            $ins('documents', [
                'user_id' => $uid, 'type' => $type, 'path' => "seed/{$type}.png", 'original_name' => "{$type}.png", 'mime' => 'image/png', 'status' => $status,
                'reject_reason' => $reason, 'reviewed_by' => $status === 'pending' ? null : $admin, 'reviewed_at' => $status === 'pending' ? null : $ago('-1 day'),
                'created_at' => $ago($when), 'updated_at' => $ago($when),
            ]);
        };
        foreach (array_merge($verified, [$mBudi, $mSari]) as $uid) {
            foreach (in_array($uid, [$mBudi, $mSari], true) ? ['ktp', 'sim', 'selfie', 'business'] : ['ktp', 'sim', 'selfie'] as $t) {
                $doc($uid, $t, 'approved', null, '-30 days');
            }
        }
        foreach (['ktp', 'sim', 'selfie'] as $t) {
            $doc($andi, $t, 'pending', null, '-15 minutes');
            $doc($rian, $t, 'pending', null, '-50 minutes');
        }
        $doc($siti, 'ktp', 'approved', null, '-3 hours');
        $doc($siti, 'sim', 'pending', null, '-35 minutes');
        $doc($siti, 'selfie', 'pending', null, '-35 minutes');
        $doc($budiH, 'ktp', 'rejected', 'Foto KTP buram dan terpotong', '-1 day');
        $doc($budiH, 'sim', 'approved', null, '-1 day');
        $doc($budiH, 'selfie', 'approved', null, '-1 day');
        foreach (['ktp', 'sim', 'selfie', 'business'] as $t) {
            $doc($mCitra, $t, 'pending', null, '-20 minutes');
        }
        foreach ($s['t']['users'] as $uid => $u) {
            if ($u['role'] === 'admin') {
                continue;
            }
            $byType = [];
            foreach ($s['t']['documents'] as $d) {
                if ($d['user_id'] === $uid) {
                    $byType[$d['type']] = $d;
                }
            }
            [$st, $note] = Drivana::verificationFrom($byType, $u['role']);
            $s['t']['users'][$uid]['verification_status'] = $st;
            $s['t']['users'][$uid]['verification_note'] = $note;
        }

        /* ───────── cars ───────── */
        // slug, name, year, type, seats, transmission, fuel, plate, price, with_driver, driver_only, area, description, owner, [state]
        $fleet = [
            ['land-cruiser-prado', 'Toyota Land Cruiser Prado', 2022, 'SUV', 7, 'Automatic', 'Diesel', 'B 1209 PRD', 950000, 1, 0, 'Jakarta Selatan', 'SUV tangguh untuk keluarga besar dan perjalanan jauh.', $mBudi],
            ['honda-cr-v', 'Honda CR-V', 2021, 'SUV', 5, 'Automatic', 'Bensin', 'B 2201 CRV', 550000, 1, 0, 'Jakarta Pusat', 'Irit dan nyaman, cocok untuk dalam kota maupun luar kota.', $mBudi],
            ['mazda-cx-5', 'Mazda CX-5', 2022, 'SUV', 5, 'Automatic', 'Bensin', 'B 5105 MZD', 600000, 0, 0, 'Tangerang Selatan', 'Kabin tenang dan handling stabil.', $mBudi],
            ['hyundai-tucson', 'Hyundai Tucson', 2023, 'SUV', 5, 'Automatic', 'Bensin', 'B 7788 HYT', 580000, 1, 0, 'Bekasi Utara', 'SUV modern dengan fitur keselamatan lengkap.', $mBudi],
            ['ford-explorer', 'Ford Explorer', 2020, 'SUV', 7, 'Automatic', 'Bensin', 'B 3399 FRD', 1100000, 1, 0, 'Depok Margonda', 'Tujuh kursi lega untuk rombongan.', $mBudi],
            ['chrysler-pacifica', 'Chrysler Pacifica', 2021, 'MPV', 7, 'Automatic', 'Bensin', 'B 8123 CHR', 700000, 1, 0, 'Jakarta Pusat', 'MPV luas dengan pintu geser, nyaman untuk keluarga.', $mBudi],
            ['hyundai-staria', 'Hyundai Staria', 2023, 'Minibus', 9, 'Automatic', 'Diesel', 'D 4410 STR', 1000000, 1, 0, 'Bandung Kota', 'Sembilan kursi dengan kabin lapang dan AC dingin.', $mBudi],
            ['mazda-3', 'Mazda 3 Hatchback', 2022, 'City Car', 5, 'Automatic', 'Bensin', 'B 3303 MZ', 380000, 0, 0, 'Jakarta Selatan', 'Lincah di kota, parkir mudah.', $mBudi],
            ['jeep-wrangler-rubicon', 'Jeep Wrangler Rubicon', 2021, 'Off-road', 4, 'Manual', 'Bensin', 'D 1901 JPW', 900000, 0, 0, 'Bandung Kota', 'Siap medan berat. SIM A wajib.', $mBudi],
            ['tesla-model-y', 'Tesla Model Y', 2023, 'SUV', 5, 'Automatic', 'Listrik', 'B 1010 TSL', 1300000, 0, 0, 'Jakarta Selatan', 'SUV listrik senyap dengan jarak tempuh jauh.', $mSari],
            ['mercedes-sprinter', 'Mercedes-Benz Sprinter', 2020, 'Minibus', 12, 'Manual', 'Diesel', 'DA 7012 SP', 1500000, 1, 1, 'Banjarmasin Tengah', 'Untuk rombongan besar. Sudah termasuk pengemudi.', $mSari],
            ['hyundai-santro', 'Hyundai Santro', 2019, 'City Car', 5, 'Manual', 'Bensin', 'DA 4455 HS', 250000, 0, 0, 'Banjarbaru', 'Hemat BBM dan mudah dikendarai.', $mSari],
            ['fiat-500', 'Fiat 500 Classic', 1971, 'City Car', 4, 'Manual', 'Bensin', 'B 5000 FT', 450000, 0, 0, 'Jakarta Pusat', 'Mobil klasik untuk foto prewedding dan acara spesial.', $mSari, 'pending'],
            ['honda-civic-type-r', 'Honda Civic Type R', 2019, 'City Car', 5, 'Manual', 'Bensin', 'B 9917 CTR', 750000, 0, 0, 'Bekasi Utara', 'Hot hatch bertenaga untuk pecinta kecepatan.', $mSari, 'pending'],
            ['bmw-7-series', 'BMW 7 Series', 2023, 'Sedan', 5, 'Automatic', 'Bensin', 'B 7007 BMW', 1800000, 1, 1, 'Jakarta Pusat', 'Sedan eksekutif dengan pengemudi profesional.', $mSari],
            ['mercedes-amg', 'Mercedes-AMG Sedan', 2021, 'Sedan', 4, 'Automatic', 'Bensin', 'B 6300 AMG', 2000000, 1, 0, 'Jakarta Selatan', 'Sedan performa tinggi untuk momen istimewa.', $mSari],
            ['land-cruiser-fj40', 'Toyota Land Cruiser FJ40', 1978, 'Off-road', 5, 'Manual', 'Bensin', 'DA 4040 FJ', 800000, 0, 0, 'Banjarmasin Tengah', 'Klasik tahan banting. SIM A wajib.', $mSari],
            ['jeep-wrangler-unlimited', 'Jeep Wrangler Unlimited', 2020, 'Off-road', 5, 'Automatic', 'Bensin', 'DA 2020 JW', 950000, 0, 0, 'Banjarbaru', 'Empat pintu, lega untuk petualangan.', $mSari, 'servis'],
            ['rolls-royce-cullinan', 'Rolls-Royce Cullinan', 2022, 'Luxury', 4, 'Automatic', 'Bensin', 'B 1 RR', 6000000, 1, 1, 'Jakarta Pusat', 'Kemewahan tertinggi. Termasuk chauffeur.', $mSari],
        ];
        $carIds = [];
        $bookable = [];
        foreach ($fleet as $c) {
            [$slug, $name, $year, $type, $seats, $trans, $fuel, $plate, $price, $wd, $do, $area, $desc, $owner] = $c;
            $state = $c[14] ?? 'approved';
            [$uid, $who] = self::PHOTOS[$slug];
            $id = $ins('cars', [
                'mitra_id' => $owner, 'name' => $name, 'year' => $year, 'type' => $type, 'seats' => $seats, 'transmission' => $trans, 'fuel' => $fuel, 'plate' => $plate,
                'price' => $price, 'with_driver' => $wd, 'driver_only' => $do, 'area' => $area, 'description' => $desc,
                'image' => "assets/img/cars/{$slug}.jpg", 'image_credit' => $who, 'image_credit_url' => "https://unsplash.com/photos/{$uid}",
                'ops_status' => $state === 'servis' ? 'servis' : 'tersedia', 'service_until' => $state === 'servis' ? date('Y-m-d', strtotime('+5 days')) : null,
                'listing_status' => $state === 'pending' ? 'pending' : 'approved', 'reject_reason' => null, 'boosted_until' => null, 'deleted_at' => null,
                'created_at' => $ago('-60 days'), 'updated_at' => $now,
            ]);
            $carIds[$slug] = $id;
            if ($state === 'approved') {
                $bookable[] = $id;
            }
        }

        /* ───────── bookings: 90 days of history + live ones ───────── */
        $occupied = [];
        $free = function (int $car, string $a, string $b) use (&$occupied): bool {
            foreach ($occupied[$car] ?? [] as [$x, $y]) {
                if ($x < $b && $y > $a) {
                    return false;
                }
            }

            return true;
        };
        $reviewTexts = ['Mobil bersih dan nyaman, pengemudi ramah.', 'Proses cepat, mitra responsif. Recommended!', 'Sesuai deskripsi, AC dingin dan mesin halus.', 'Harga sebanding dengan kualitasnya.', 'Pengantaran tepat waktu. Terima kasih!', 'Mobil terawat, akan sewa lagi.', 'Lumayan, tapi pengembalian agak ribet.', 'Sangat membantu perjalanan keluarga kami.'];
        $codes = [];
        $payN = 4096;
        $addBooking = function (int $cust, int $car, string $start, int $days, string $status, bool $withReview = true, ?string $area = null) use (&$s, $ins, $ago, $now, &$occupied, &$codes, &$payN, $reviewTexts): int {
            $c = $s['t']['cars'][$car];
            $end = date('Y-m-d', strtotime("$start +$days days"));
            $wd = ($c['driver_only'] || ($c['with_driver'] && mt_rand(0, 2) === 0)) ? 1 : 0;
            $q = BookingService::quote($c, $days, (bool) $wd);
            do {
                $code = 'DRV-' . date('ymd', strtotime($start)) . '-' . strtoupper(dechex(mt_rand(0x1000, 0xffff)));
            } while (isset($codes[$code]));
            $codes[$code] = true;
            $area = $area ?? (mt_rand(1, 100) <= 65 ? $c['area'] : Drivana::AREAS[min(7, (int) (abs(mt_rand() / mt_getrandmax() - mt_rand() / mt_getrandmax()) * 8))]);
            $created = date('Y-m-d H:i:s', strtotime("$start -" . mt_rand(1, 6) . ' days ' . mt_rand(8, 20) . ':00'));
            $bid = $ins('bookings', [
                'code' => $code, 'customer_id' => $cust, 'car_id' => $car, 'mitra_id' => $c['mitra_id'], 'start_date' => $start, 'end_date' => $end, 'days' => $days,
                'with_driver' => $q['with_driver'], 'area' => $area,
                'address' => 'Jl. ' . ['Sudirman', 'Thamrin', 'Gatot Subroto', 'Margonda Raya', 'Ahmad Yani', 'Merdeka'][mt_rand(0, 5)] . ' No. ' . mt_rand(1, 99) . ', ' . $area,
                'note' => null, 'rent_total' => $q['rent'], 'driver_total' => $q['driver'], 'service_fee' => $q['fee'], 'discount' => 0, 'total' => $q['total'], 'promo_code' => null,
                'status' => $status, 'cancel_reason' => $status === 'ditolak' ? 'Mobil tidak tersedia di tanggal tersebut' : ($status === 'dibatalkan' ? 'Rencana berubah' : null),
                'confirmed_at' => in_array($status, ['dikonfirmasi', 'aktif', 'selesai'], true) ? $created : null,
                'started_at' => in_array($status, ['aktif', 'selesai'], true) ? $start . ' 09:00:00' : null,
                'finished_at' => $status === 'selesai' ? $end . ' 17:00:00' : null, 'expires_at' => null, 'created_at' => $created, 'updated_at' => $now,
            ]);
            if (! in_array($status, ['dibatalkan', 'ditolak'], true)) {
                $occupied[$car][] = [$start, $end];
            }
            $paid = ! in_array($status, ['dibatalkan', 'ditolak'], true);
            $ins('payments', [
                'ref' => 'PAY-' . date('ymd', strtotime($created)) . '-' . strtoupper(dechex(++$payN)), 'user_id' => $cust, 'kind' => 'booking', 'booking_id' => $bid, 'plan' => null, 'car_id' => null, 'days' => null,
                'amount' => $q['total'], 'method' => ['bank', 'wallet', 'card'][mt_rand(0, 2)], 'status' => $paid ? 'paid' : 'refunded', 'paid_at' => $created,
                'refunded_at' => $paid ? null : $created, 'created_at' => $created, 'updated_at' => $created,
            ]);
            if ($status === 'selesai' && $withReview) {
                $ins('reviews', ['booking_id' => $bid, 'car_id' => $car, 'customer_id' => $cust, 'rating' => [5, 5, 5, 4, 4, 4, 3][mt_rand(0, 6)],
                    'comment' => $reviewTexts[mt_rand(0, count($reviewTexts) - 1)], 'created_at' => $end . ' 20:00:00']);
            }

            return $bid;
        };

        for ($d = 90; $d >= 1; $d--) {
            $date = strtotime("-$d days");
            $dow = (int) date('w', $date);
            $n = in_array($dow, [0, 5, 6], true) ? mt_rand(2, 3) : mt_rand(0, 2);
            for ($i = 0; $i < $n; $i++) {
                $car = $bookable[mt_rand(0, count($bookable) - 1)];
                $days = mt_rand(1, 4);
                $start = date('Y-m-d', $date);
                $end = date('Y-m-d', strtotime("$start +$days days"));
                if ($end >= date('Y-m-d') || ! $free($car, $start, $end)) {
                    continue;
                }
                $roll = mt_rand(1, 100);
                $status = $roll <= 82 ? 'selesai' : ($roll <= 92 ? 'dibatalkan' : 'ditolak');
                $addBooking($verified[mt_rand(1, count($verified) - 1)], $car, $start, $days, $status, mt_rand(1, 100) <= 70);
            }
        }
        $c = $carIds;
        $addBooking($maulida, $c['honda-cr-v'], date('Y-m-d', strtotime('-1 day')), 3, 'aktif', false, 'Jakarta Selatan');
        $addBooking($maulida, $c['mazda-3'], date('Y-m-d', strtotime('+5 days')), 2, 'menunggu', false, 'Jakarta Selatan');
        $reviewMe = $addBooking($maulida, $c['hyundai-tucson'], date('Y-m-d', strtotime('-12 days')), 3, 'selesai', false, 'Bekasi Utara');
        $addBooking($maulida, $c['mazda-cx-5'], date('Y-m-d', strtotime('-30 days')), 2, 'selesai', true, 'Tangerang Selatan');
        $addBooking($verified[2], $c['hyundai-tucson'], date('Y-m-d', strtotime('+3 days')), 2, 'menunggu', false, 'Bekasi Utara');
        $addBooking($verified[3], $c['ford-explorer'], date('Y-m-d', strtotime('+6 days')), 3, 'menunggu', false, 'Depok Margonda');
        $addBooking($verified[4], $c['chrysler-pacifica'], date('Y-m-d', strtotime('+2 days')), 2, 'dikonfirmasi', false, 'Jakarta Pusat');
        $addBooking($verified[5], $c['land-cruiser-prado'], date('Y-m-d', strtotime('-2 days')), 4, 'aktif', false, 'Jakarta Selatan');
        $addBooking($verified[6], $c['mercedes-sprinter'], date('Y-m-d', strtotime('+4 days')), 2, 'menunggu', false, 'Banjarmasin Tengah');
        $addBooking($verified[7], $c['bmw-7-series'], date('Y-m-d', strtotime('-1 day')), 2, 'aktif', false, 'Jakarta Pusat');

        /* ───────── premium plan + boost ───────── */
        $pSub = $ins('payments', ['ref' => 'PAY-SUB-0001', 'user_id' => $mBudi, 'kind' => 'subscription', 'booking_id' => null, 'plan' => 'monthly', 'car_id' => null, 'days' => null, 'amount' => 149000, 'method' => 'bank', 'status' => 'paid', 'paid_at' => $ago('-10 days'), 'refunded_at' => null, 'created_at' => $ago('-10 days'), 'updated_at' => $ago('-10 days')]);
        $ins('subscriptions', ['mitra_id' => $mBudi, 'plan' => 'monthly', 'price' => 149000, 'starts_at' => $ago('-10 days'), 'ends_at' => $ago('+20 days'), 'payment_id' => $pSub, 'created_at' => $ago('-10 days')]);
        $pBst = $ins('payments', ['ref' => 'PAY-BST-0001', 'user_id' => $mBudi, 'kind' => 'boost', 'booking_id' => null, 'plan' => null, 'car_id' => $c['land-cruiser-prado'], 'days' => 7, 'amount' => 90000, 'method' => 'wallet', 'status' => 'paid', 'paid_at' => $ago('-2 days'), 'refunded_at' => null, 'created_at' => $ago('-2 days'), 'updated_at' => $ago('-2 days')]);
        $ins('boosts', ['car_id' => $c['land-cruiser-prado'], 'mitra_id' => $mBudi, 'days' => 7, 'price' => 90000, 'starts_at' => $ago('-2 days'), 'ends_at' => $ago('+5 days'), 'payment_id' => $pBst, 'created_at' => $ago('-2 days')]);
        $s['t']['cars'][$c['land-cruiser-prado']]['boosted_until'] = $ago('+5 days');

        /* ───────── chats ───────── */
        $conv = function (int $cust, int $mitra, ?int $car, array $msgs) use ($ins, $ago): void {
            $cid = $ins('conversations', ['customer_id' => $cust, 'mitra_id' => $mitra, 'car_id' => $car, 'created_at' => $ago('-2 days'), 'last_message_at' => $ago($msgs[count($msgs) - 1][2])]);
            foreach ($msgs as [$from, $text, $when, $read]) {
                $ins('messages', ['conversation_id' => $cid, 'sender_id' => $from, 'body' => $text, 'kind' => 'text', 'created_at' => $ago($when), 'read_at' => $read ? $ago($when) : null]);
            }
        };
        $conv($maulida, $mBudi, $c['honda-cr-v'], [
            [$mBudi, 'Halo! Terima kasih sudah booking Honda CR-V. Ada yang bisa kami bantu?', '-26 hours', true],
            [$maulida, 'Mau tanya, apakah mobil bisa diantar ke Bandara Soetta?', '-25 hours', true],
            [$mBudi, 'Bisa kak. Mohon share lokasi kedatangan ya.', '-25 hours', true],
            [$maulida, 'Siap, terima kasih!', '-24 hours', true],
        ]);
        $conv($verified[2], $mBudi, $c['hyundai-tucson'], [
            [$verified[2], 'Selamat siang, apakah Tucson-nya ready untuk tanggal booking saya?', '-3 hours', false],
            [$verified[2], 'Mohon konfirmasinya ya, terima kasih.', '-2 hours', false],
        ]);
        $conv($verified[3], $mBudi, $c['ford-explorer'], [[$verified[3], 'Apakah bisa tanpa pengemudi?', '-5 hours', false]]);
        $conv($maulida, $mSari, $c['tesla-model-y'], [[$maulida, 'Apakah Tesla bisa disewa 3 hari dengan charger?', '-30 minutes', false]]);

        /* ───────── notifications, activity, promo ───────── */
        $notif = fn (int $u, string $t, string $b, string $url, string $w, bool $read = false) => $ins('notifications', ['user_id' => $u, 'title' => $t, 'body' => $b, 'url' => $url, 'created_at' => $ago($w), 'read_at' => $read ? $ago($w) : null]);
        $notif($maulida, 'Sewa dimulai', 'Selamat jalan! Honda CR-V sudah bersamamu.', 'pesanan', '-1 day');
        $notif($maulida, 'Sewa selesai', 'Bagaimana pengalamanmu? Beri ulasan untuk Hyundai Tucson.', 'pesanan', '-9 days');
        $notif($mBudi, 'Pesanan baru', 'Ada pesanan masuk untuk Hyundai Tucson. Konfirmasi sekarang.', 'mitra/reservasi', '-4 hours');
        $notif($mBudi, 'Pesanan baru', 'Ada pesanan masuk untuk Ford Explorer. Konfirmasi sekarang.', 'mitra/reservasi', '-2 hours');
        $notif($mBudi, 'Boost aktif', 'Land Cruiser Prado tampil di urutan teratas.', 'mitra/armada', '-2 days', true);
        foreach ([$andi => 'Andi Pratama', $rian => 'Rian Hidayat', $siti => 'Siti Rahma', $mCitra => 'Citra Rent'] as $uid => $nm) {
            $notif($admin, 'Dokumen baru perlu diperiksa', "$nm mengirim dokumen verifikasi.", 'admin/verifikasi', '-20 minutes');
        }
        $notif($admin, 'Mobil baru perlu ditinjau', 'Sari Motor menambahkan Fiat 500 Classic.', 'admin/mobil', '-3 hours');
        foreach ([['success', 'Admin menyetujui dokumen Dewi Lestari', 'Selfie, KTP, SIM', '-9 minutes'], ['info', 'Pengguna baru mengunggah dokumen KTP', 'Andi Pratama', '-15 minutes'], ['danger', 'Dokumen ditolak: foto buram dan terpotong', 'Budi Hartono · notifikasi terkirim', '-28 minutes'], ['success', 'Mobil Honda CR-V disetujui', 'Rental Jaya Abadi', '-3 hours'], ['info', 'Mitra baru mendaftar', 'Citra Rent', '-4 hours'], ['success', 'Langganan Premium Bulanan dibeli', 'Rental Jaya Abadi · Rp 149.000', '-10 days']] as [$tone, $text, $meta, $w]) {
            $ins('activity_logs', ['actor_id' => $admin, 'tone' => $tone, 'text' => $text, 'meta' => $meta, 'created_at' => $ago($w)]);
        }
        $ins('promos', ['code' => 'DRIVANA20', 'percent' => 20, 'active' => 1, 'expires_at' => null]);

        return $s;
    }

    /** Clearly fake sample ID/business documents (GD), so the admin has real images to inspect. */
    private static function sampleImages(): void
    {
        $dir = VerificationService::DIR . 'seed/';
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        foreach (['ktp' => 'KARTU TANDA PENDUDUK', 'sim' => 'SURAT IZIN MENGEMUDI', 'selfie' => 'SELFIE', 'business' => 'NOMOR INDUK BERUSAHA'] as $type => $title) {
            if (is_file($dir . $type . '.png')) {
                continue;
            }
            [$w, $h] = [640, 400];
            $im = imagecreatetruecolor($w, $h);
            imagefill($im, 0, 0, imagecolorallocate($im, ...['ktp' => [214, 232, 247], 'sim' => [247, 238, 214], 'selfie' => [236, 238, 241], 'business' => [244, 244, 244]][$type]));
            $ink = imagecolorallocate($im, 30, 41, 59);
            $mut = imagecolorallocate($im, 100, 116, 139);
            imagerectangle($im, 8, 8, $w - 9, $h - 9, imagecolorallocate($im, 148, 163, 184));
            if ($type === 'selfie') {
                imagefilledellipse($im, 320, 175, 190, 240, imagecolorallocate($im, 203, 213, 225));
                imagefilledellipse($im, 320, 360, 330, 220, imagecolorallocate($im, 203, 213, 225));
            } else {
                imagestring($im, 5, 24, 22, $title, $ink);
                imagestring($im, 3, 24, 46, 'REPUBLIK INDONESIA (CONTOH)', $mut);
                imagefilledrectangle($im, 440, 90, 600, 260, imagecolorallocate($im, 203, 213, 225));
                foreach (['NIK', 'NAMA', 'TEMPAT/TGL LAHIR', 'ALAMAT'] as $i => $lbl) {
                    imagestring($im, 3, 24, 100 + $i * 42, $lbl, $mut);
                    imagefilledrectangle($im, 24, 118 + $i * 42, 360 - $i * 20, 126 + $i * 42, imagecolorallocate($im, 148, 163, 184));
                }
            }
            imagestring($im, 4, 24, $h - 40, 'CONTOH - DATA FIKTIF, BUKAN DOKUMEN ASLI', imagecolorallocate($im, 185, 28, 28));
            $big = imagecreatetruecolor($w * 2, $h * 2);
            imagecopyresampled($big, $im, 0, 0, 0, 0, $w * 2, $h * 2, $w, $h);
            imagepng($big, $dir . $type . '.png');
        }
    }
}
