<?php

namespace App\Libraries;

/**
 * Validation rules + Indonesian messages for every form (single source).
 * Each method returns a CodeIgniter rule array: field => [label, rules, errors].
 */
class FormRules
{
    public const PHONE_RE    = '/^(\+62|62|0)8[0-9]{8,12}$/';
    public const IDENTITY_RE = '/^([^\s@]+@[^\s@]+\.[^\s@]+|(\+62|62|0)8[0-9]{8,12})$/';
    public const PASSWORD_RE = '/^(?=.*[A-Za-z])(?=.*\d).+$/';
    public const PLATE_RE    = '/^[A-Z]{1,2}\s?[0-9]{1,4}\s?[A-Z]{0,3}$/i';

    private static function phone(string $label = 'No. HP'): array
    {
        return ['label' => $label, 'rules' => 'required|regex_match[' . self::PHONE_RE . ']', 'errors' => [
            'required' => $label . ' wajib diisi.',
            'regex_match' => 'Nomor HP tidak valid. Contoh: 081234567890.',
        ]];
    }

    private static function email(string $label = 'Email'): array
    {
        return ['label' => $label, 'rules' => 'required|valid_email|max_length[100]', 'errors' => [
            'required' => $label . ' wajib diisi.',
            'valid_email' => 'Format email tidak valid. Contoh: nama@email.com.',
            'max_length' => $label . ' terlalu panjang.',
        ]];
    }

    private static function newPassword(): array
    {
        return ['label' => 'Password', 'rules' => 'required|min_length[8]|max_length[72]|regex_match[' . self::PASSWORD_RE . ']', 'errors' => [
            'required' => 'Password wajib diisi.',
            'min_length' => 'Password minimal 8 karakter.',
            'max_length' => 'Password maksimal 72 karakter.',
            'regex_match' => 'Password harus berisi huruf dan angka.',
        ]];
    }

    private static function image(string $label): array
    {
        return ['label' => $label, 'rules' => 'uploaded[FIELD]|is_image[FIELD]|max_size[FIELD,5120]|mime_in[FIELD,image/jpeg,image/png]', 'errors' => [
            'uploaded' => $label . ' wajib diunggah.',
            'is_image' => $label . ' harus berupa gambar JPG atau PNG.',
            'mime_in' => $label . ' harus berupa gambar JPG atau PNG.',
            'max_size' => $label . ' maksimal 5 MB.',
        ]];
    }

    /** Fill the FIELD placeholder in file rules. */
    private static function files(array $fields): array
    {
        $out = [];
        foreach ($fields as $name => $label) {
            $def          = self::image($label);
            $def['rules'] = str_replace('FIELD', $name, $def['rules']);
            $out[$name]   = $def;
        }

        return $out;
    }

    public static function otpRequest(): array
    {
        return ['identity' => self::login()['identity']];
    }

    public static function otpCode(): array
    {
        return ['code' => ['label' => 'Kode OTP', 'rules' => 'required|exact_length[6]|numeric', 'errors' => [
            'required' => 'Kode OTP wajib diisi.',
            'exact_length' => 'Kode OTP terdiri dari 6 angka.',
            'numeric' => 'Kode OTP hanya berisi angka.',
        ]]];
    }

    public static function login(): array
    {
        return [
            'identity' => ['label' => 'Email / No. HP', 'rules' => 'required|max_length[100]|regex_match[' . self::IDENTITY_RE . ']', 'errors' => [
                'required' => 'Email atau nomor HP wajib diisi.',
                'regex_match' => 'Isi email atau nomor HP yang valid.',
            ]],
            'password' => ['label' => 'Password', 'rules' => 'required|min_length[6]|max_length[72]', 'errors' => [
                'required' => 'Password wajib diisi.',
                'min_length' => 'Password minimal 6 karakter.',
            ]],
        ];
    }

    public static function register(): array
    {
        return [
            'name' => ['label' => 'Nama lengkap', 'rules' => "required|min_length[3]|max_length[60]|regex_match[/^[\\p{L} .'-]+\$/u]", 'errors' => [
                'required' => 'Nama lengkap wajib diisi.',
                'min_length' => 'Nama minimal 3 karakter.',
                'regex_match' => 'Nama hanya boleh berisi huruf.',
            ]],
            'email' => self::email(),
            'phone' => self::phone(),
            'password' => self::newPassword(),
        ];
    }

    public static function mitraRegister(): array
    {
        return [
            'business' => ['label' => 'Nama bisnis', 'rules' => 'required|min_length[3]|max_length[80]', 'errors' => [
                'required' => 'Nama bisnis wajib diisi.',
                'min_length' => 'Nama bisnis minimal 3 karakter.',
            ]],
            'email' => self::email('Email bisnis'),
            'phone' => self::phone('No. HP bisnis'),
            'password' => self::newPassword(),
            'document' => ['label' => 'Dokumen bisnis', 'rules' => 'uploaded[document]|max_size[document,5120]|ext_in[document,png,jpg,jpeg,pdf]', 'errors' => [
                'uploaded' => 'Dokumen bisnis (SIUP/NIB/KTP) wajib diunggah.',
                'ext_in' => 'Dokumen harus berformat JPG, PNG, atau PDF.',
                'max_size' => 'Dokumen maksimal 5 MB.',
            ]],
        ];
    }

    public static function verifikasi(): array
    {
        return self::files(['ktp' => 'Foto KTP', 'sim' => 'Foto SIM', 'selfie' => 'Foto selfie']) + [
            'confirm' => ['label' => 'Konfirmasi', 'rules' => 'required', 'errors' => ['required' => 'Centang untuk menyatakan data kamu benar.']],
        ];
    }

    public static function mitraVerifikasi(): array
    {
        return self::files(['ktp' => 'Foto KTP', 'sim' => 'Foto SIM', 'selfie' => 'Foto selfie']) + [
            'rental' => ['label' => 'Nama rental', 'rules' => 'required|min_length[3]|max_length[80]', 'errors' => ['required' => 'Nama rental wajib diisi.', 'min_length' => 'Nama rental minimal 3 karakter.']],
            'address' => ['label' => 'Alamat rental', 'rules' => 'required|min_length[10]|max_length[200]', 'errors' => ['required' => 'Alamat rental wajib diisi.', 'min_length' => 'Alamat terlalu pendek. Tulis jalan, nomor, dan kota.']],
            'whatsapp' => self::phone('Nomor WhatsApp'),
            'account' => ['label' => 'Nomor rekening', 'rules' => 'required|regex_match[/^[0-9]{8,16}$/]', 'errors' => ['required' => 'Nomor rekening wajib diisi.', 'regex_match' => 'Nomor rekening 8–16 digit, hanya angka.']],
        ];
    }

    public static function booking(): array
    {
        return [
            'start' => ['label' => 'Tanggal mulai', 'rules' => 'required|valid_date[Y-m-d]', 'errors' => ['required' => 'Tanggal mulai wajib diisi.', 'valid_date' => 'Tanggal mulai tidak valid.']],
            'end' => ['label' => 'Tanggal selesai', 'rules' => 'required|valid_date[Y-m-d]', 'errors' => ['required' => 'Tanggal selesai wajib diisi.', 'valid_date' => 'Tanggal selesai tidak valid.']],
            'driver' => ['label' => 'Pengemudi', 'rules' => 'required|in_list[0,1]', 'errors' => ['required' => 'Pilih dengan atau tanpa pengemudi.', 'in_list' => 'Pilihan pengemudi tidak valid.']],
            'area' => ['label' => 'Wilayah', 'rules' => 'required|in_list[' . implode(',', Drivana::AREAS) . ']', 'errors' => ['required' => 'Pilih wilayah pengantaran.', 'in_list' => 'Wilayah tidak valid.']],
            'address' => ['label' => 'Alamat pengantaran', 'rules' => 'required|min_length[5]|max_length[200]', 'errors' => ['required' => 'Alamat pengantaran wajib diisi.', 'min_length' => 'Alamat terlalu pendek.', 'max_length' => 'Alamat maksimal 200 karakter.']],
            'note' => ['label' => 'Catatan', 'rules' => 'permit_empty|max_length[200]', 'errors' => ['max_length' => 'Catatan maksimal 200 karakter.']],
        ];
    }

    public static function fleet(): array
    {
        $in = fn (array $a) => implode(',', $a);

        return [
            'name' => ['label' => 'Nama kendaraan', 'rules' => 'required|min_length[3]|max_length[80]', 'errors' => ['required' => 'Nama kendaraan wajib diisi.', 'min_length' => 'Nama kendaraan minimal 3 karakter.']],
            'year' => ['label' => 'Tahun', 'rules' => 'required|is_natural_no_zero|greater_than_equal_to[1950]|less_than_equal_to[' . ((int) date('Y') + 1) . ']', 'errors' => ['required' => 'Tahun wajib diisi.', 'is_natural_no_zero' => 'Tahun harus berupa angka.', 'greater_than_equal_to' => 'Tahun minimal 1950.', 'less_than_equal_to' => 'Tahun tidak valid.']],
            'plate' => ['label' => 'Plat nomor', 'rules' => 'required|regex_match[' . self::PLATE_RE . ']', 'errors' => ['required' => 'Plat nomor wajib diisi.', 'regex_match' => 'Format plat tidak valid. Contoh: B 1234 ABC.']],
            'type' => ['label' => 'Tipe mobil', 'rules' => 'required|in_list[' . $in(Drivana::CAR_TYPES) . ']', 'errors' => ['required' => 'Pilih tipe mobil.', 'in_list' => 'Tipe mobil tidak valid.']],
            'seats' => ['label' => 'Kapasitas', 'rules' => 'required|in_list[' . $in(Drivana::SEAT_OPTIONS) . ']', 'errors' => ['required' => 'Pilih kapasitas penumpang.', 'in_list' => 'Kapasitas tidak valid.']],
            'transmission' => ['label' => 'Transmisi', 'rules' => 'required|in_list[' . $in(Drivana::TRANSMISSION) . ']', 'errors' => ['required' => 'Pilih transmisi.', 'in_list' => 'Transmisi tidak valid.']],
            'fuel' => ['label' => 'BBM', 'rules' => 'required|in_list[' . $in(Drivana::FUEL) . ']', 'errors' => ['required' => 'Pilih jenis BBM.', 'in_list' => 'Jenis BBM tidak valid.']],
            'driver' => ['label' => 'Pengemudi', 'rules' => 'required|in_list[0,1,2]', 'errors' => ['required' => 'Pilih opsi pengemudi.', 'in_list' => 'Opsi pengemudi tidak valid.']],
            'area' => ['label' => 'Wilayah', 'rules' => 'required|in_list[' . $in(Drivana::AREAS) . ']', 'errors' => ['required' => 'Pilih wilayah.', 'in_list' => 'Wilayah tidak valid.']],
            'description' => ['label' => 'Deskripsi', 'rules' => 'permit_empty|max_length[400]', 'errors' => ['max_length' => 'Deskripsi maksimal 400 karakter.']],
            'price' => ['label' => 'Harga sewa', 'rules' => 'required|regex_match[/^[0-9]{5,9}$/]|greater_than_equal_to[50000]', 'errors' => [
                'required' => 'Harga sewa wajib diisi.',
                'regex_match' => 'Isi angka saja tanpa titik. Contoh: 350000.',
                'greater_than_equal_to' => 'Harga sewa minimal Rp 50.000 per hari.',
            ]],
        ];
    }

    public static function filter(): array
    {
        $in = fn (array $a) => implode(',', $a);

        return [
            'type' => ['label' => 'Tipe', 'rules' => 'permit_empty|in_list[' . $in(array_merge(['Semua'], Drivana::CAR_TYPES)) . ']', 'errors' => ['in_list' => 'Tipe mobil tidak valid.']],
            'seats' => ['label' => 'Kapasitas', 'rules' => 'permit_empty|in_list[' . $in(Drivana::SEAT_OPTIONS) . ']', 'errors' => ['in_list' => 'Kapasitas tidak valid.']],
            'min' => ['label' => 'Harga min', 'rules' => 'permit_empty|is_natural', 'errors' => ['is_natural' => 'Harga min harus berupa angka.']],
            'max' => ['label' => 'Harga max', 'rules' => 'permit_empty|is_natural', 'errors' => ['is_natural' => 'Harga max harus berupa angka.']],
            'driver' => ['label' => 'Pengemudi', 'rules' => 'permit_empty|in_list[0,1]', 'errors' => ['in_list' => 'Pilihan tidak valid.']],
            'area' => ['label' => 'Wilayah', 'rules' => 'permit_empty|in_list[' . $in(Drivana::AREAS) . ']', 'errors' => ['in_list' => 'Wilayah tidak valid.']],
            'start' => ['label' => 'Tanggal mulai', 'rules' => 'permit_empty|valid_date[Y-m-d]', 'errors' => ['valid_date' => 'Tanggal mulai tidak valid.']],
            'end' => ['label' => 'Tanggal selesai', 'rules' => 'permit_empty|valid_date[Y-m-d]', 'errors' => ['valid_date' => 'Tanggal selesai tidak valid.']],
            'sort' => ['label' => 'Urutan', 'rules' => 'permit_empty|in_list[rekomendasi,termurah,termahal,rating]', 'errors' => ['in_list' => 'Urutan tidak valid.']],
            'page' => ['label' => 'Halaman', 'rules' => 'permit_empty|is_natural_no_zero', 'errors' => ['is_natural_no_zero' => 'Halaman tidak valid.']],
            'q' => ['label' => 'Pencarian', 'rules' => 'permit_empty|max_length[60]', 'errors' => ['max_length' => 'Kata kunci maksimal 60 karakter.']],
        ];
    }

    public static function checkout(): array
    {
        $methods = implode(',', array_column(Drivana::PAYMENT_METHODS, 'key'));

        return [
            'method' => ['label' => 'Metode pembayaran', 'rules' => 'required|in_list[' . $methods . ']', 'errors' => ['required' => 'Pilih metode pembayaran.', 'in_list' => 'Metode pembayaran tidak valid.']],
            'promo' => ['label' => 'Kode promo', 'rules' => 'permit_empty|max_length[20]|alpha_numeric', 'errors' => ['max_length' => 'Kode promo terlalu panjang.', 'alpha_numeric' => 'Kode promo hanya huruf dan angka.']],
        ];
    }

    public static function review(): array
    {
        return [
            'rating' => ['label' => 'Rating', 'rules' => 'required|in_list[1,2,3,4,5]', 'errors' => ['required' => 'Pilih rating bintang.', 'in_list' => 'Rating tidak valid.']],
            'comment' => ['label' => 'Ulasan', 'rules' => 'permit_empty|max_length[400]', 'errors' => ['max_length' => 'Ulasan maksimal 400 karakter.']],
        ];
    }

    public static function reason(string $label = 'Alasan'): array
    {
        return ['reason' => ['label' => $label, 'rules' => 'required|min_length[5]|max_length[200]', 'errors' => [
            'required' => $label . ' wajib diisi.', 'min_length' => $label . ' minimal 5 karakter.', 'max_length' => $label . ' maksimal 200 karakter.',
        ]]];
    }

    public static function cancel(): array
    {
        return ['reason' => ['label' => 'Alasan', 'rules' => 'permit_empty|max_length[200]', 'errors' => ['max_length' => 'Alasan maksimal 200 karakter.']]];
    }

    public static function profile(): array
    {
        return [
            'name' => ['label' => 'Nama', 'rules' => "required|min_length[3]|max_length[60]|regex_match[/^[\\p{L} .'-]+\$/u]", 'errors' => ['required' => 'Nama wajib diisi.', 'min_length' => 'Nama minimal 3 karakter.', 'regex_match' => 'Nama hanya boleh berisi huruf.']],
            'phone' => self::phone(),
            'address' => ['label' => 'Alamat', 'rules' => 'permit_empty|min_length[5]|max_length[200]', 'errors' => ['min_length' => 'Alamat terlalu pendek.', 'max_length' => 'Alamat maksimal 200 karakter.']],
            'area' => ['label' => 'Wilayah', 'rules' => 'permit_empty|in_list[' . implode(',', Drivana::AREAS) . ']', 'errors' => ['in_list' => 'Wilayah tidak valid.']],
        ];
    }

    public static function mitraProfile(): array
    {
        return self::profile() + [
            'business_name' => ['label' => 'Nama bisnis', 'rules' => 'required|min_length[3]|max_length[80]', 'errors' => ['required' => 'Nama bisnis wajib diisi.', 'min_length' => 'Nama bisnis minimal 3 karakter.']],
            'business_address' => ['label' => 'Alamat bisnis', 'rules' => 'required|min_length[10]|max_length[200]', 'errors' => ['required' => 'Alamat bisnis wajib diisi.', 'min_length' => 'Alamat terlalu pendek. Tulis jalan, nomor, dan kota.']],
            'whatsapp' => self::phone('Nomor WhatsApp'),
            'bank_account' => ['label' => 'Nomor rekening', 'rules' => 'required|regex_match[/^[0-9]{8,16}$/]', 'errors' => ['required' => 'Nomor rekening wajib diisi.', 'regex_match' => 'Nomor rekening 8–16 digit, hanya angka.']],
        ];
    }

    public static function password(): array
    {
        return [
            'current' => ['label' => 'Password saat ini', 'rules' => 'required', 'errors' => ['required' => 'Password saat ini wajib diisi.']],
            'password' => self::newPassword(),
            'confirm' => ['label' => 'Konfirmasi password', 'rules' => 'required|matches[password]', 'errors' => ['required' => 'Ulangi password baru.', 'matches' => 'Konfirmasi tidak sama dengan password baru.']],
        ];
    }

    public static function complaint(): array
    {
        return [
            'subject' => ['label' => 'Subjek', 'rules' => 'required|min_length[5]|max_length[120]', 'errors' => ['required' => 'Subjek wajib diisi.', 'min_length' => 'Subjek minimal 5 karakter.', 'max_length' => 'Subjek maksimal 120 karakter.']],
            'message' => ['label' => 'Pesan', 'rules' => 'required|min_length[10]|max_length[1000]', 'errors' => ['required' => 'Ceritakan masalahmu.', 'min_length' => 'Pesan minimal 10 karakter.', 'max_length' => 'Pesan maksimal 1000 karakter.']],
            'booking' => ['label' => 'Kode booking', 'rules' => 'permit_empty|max_length[24]', 'errors' => ['max_length' => 'Kode booking tidak valid.']],
        ];
    }

    public static function reply(): array
    {
        return ['reply' => ['label' => 'Balasan', 'rules' => 'required|min_length[5]|max_length[1000]', 'errors' => ['required' => 'Balasan wajib diisi.', 'min_length' => 'Balasan minimal 5 karakter.']]];
    }

    public static function subscription(): array
    {
        $methods = implode(',', array_column(Drivana::PAYMENT_METHODS, 'key'));

        return [
            'plan' => ['label' => 'Paket', 'rules' => 'required|in_list[' . implode(',', array_keys(Drivana::PLANS)) . ']', 'errors' => ['required' => 'Pilih paket langganan.', 'in_list' => 'Paket tidak valid.']],
            'method' => ['label' => 'Metode pembayaran', 'rules' => 'required|in_list[' . $methods . ']', 'errors' => ['required' => 'Pilih metode pembayaran.', 'in_list' => 'Metode pembayaran tidak valid.']],
        ];
    }

    public static function boost(): array
    {
        $methods = implode(',', array_column(Drivana::PAYMENT_METHODS, 'key'));

        return [
            'car' => ['label' => 'Mobil', 'rules' => 'required|is_natural_no_zero', 'errors' => ['required' => 'Pilih mobil yang mau di-boost.', 'is_natural_no_zero' => 'Mobil tidak valid.']],
            'days' => ['label' => 'Durasi', 'rules' => 'required|in_list[' . implode(',', array_keys(Drivana::BOOSTS)) . ']', 'errors' => ['required' => 'Pilih durasi boost.', 'in_list' => 'Durasi tidak valid.']],
            'method' => ['label' => 'Metode pembayaran', 'rules' => 'required|in_list[' . $methods . ']', 'errors' => ['required' => 'Pilih metode pembayaran.', 'in_list' => 'Metode pembayaran tidak valid.']],
        ];
    }

    public static function chat(): array
    {
        return ['body' => ['label' => 'Pesan', 'rules' => 'required|max_length[1000]', 'errors' => ['required' => 'Pesan tidak boleh kosong.', 'max_length' => 'Pesan maksimal 1000 karakter.']]];
    }

    public static function document(string $field = 'file'): array
    {
        return self::files([$field => 'Dokumen']);
    }
}
