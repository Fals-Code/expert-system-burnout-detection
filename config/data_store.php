<?php
// ============================================================
//  BurnoutXpert – Session-Based Persistent Data Store
//  Menggantikan semua mock data statis di seluruh modul.
//  Seluruh data persisten via $_SESSION['bx_store'].
// ============================================================

/**
 * Inisialisasi store jika belum ada.
 * Dipanggil sekali di setiap halaman yang butuh data store.
 */
function bx_init_store(): void {
    if (isset($_SESSION['bx_store'])) return;

    $_SESSION['bx_store'] = [
        // Daftar pengguna sistem (karyawan, hrd, admin)
        'users' => [
            ['id' => 'U001', 'nama' => 'Andi Wijaya',   'email' => 'andi@company.com',    'password' => 'karyawan', 'role' => 'karyawan', 'divisi' => 'IT',              'posisi' => 'Senior Developer'],
            ['id' => 'U002', 'nama' => 'Maria Ulfa',    'email' => 'maria@company.com',   'password' => 'karyawan', 'role' => 'karyawan', 'divisi' => 'Marketing',       'posisi' => 'SEO Specialist'],
            ['id' => 'U003', 'nama' => 'Bambang S.',    'email' => 'bambang@company.com', 'password' => 'karyawan', 'role' => 'karyawan', 'divisi' => 'Finance',         'posisi' => 'Accountant'],
            ['id' => 'U004', 'nama' => 'Citra Dewi',    'email' => 'citra@company.com',   'password' => 'karyawan', 'role' => 'karyawan', 'divisi' => 'HR',              'posisi' => 'HR Generalist'],
            ['id' => 'U005', 'nama' => 'Dedi Kurnia',   'email' => 'dedi@company.com',    'password' => 'karyawan', 'role' => 'karyawan', 'divisi' => 'IT',              'posisi' => 'UI/UX Designer'],
            ['id' => 'U006', 'nama' => 'Eka Pratiwi',   'email' => 'eka@company.com',     'password' => 'karyawan', 'role' => 'karyawan', 'divisi' => 'Operasional',     'posisi' => 'Admin Ops'],
            ['id' => 'U007', 'nama' => 'Farhan Rizky',  'email' => 'farhan@company.com',  'password' => 'karyawan', 'role' => 'karyawan', 'divisi' => 'Marketing',       'posisi' => 'Copywriter'],
            ['id' => 'U008', 'nama' => 'Gita Nuraini',  'email' => 'gita@company.com',    'password' => 'karyawan', 'role' => 'karyawan', 'divisi' => 'Finance',         'posisi' => 'Tax Officer'],
            ['id' => 'U009', 'nama' => 'Budi Santoso',  'email' => 'hrd@burnout.com',     'password' => 'hrd',      'role' => 'hrd',      'divisi' => 'Human Resources', 'posisi' => 'HRD Manager'],
            ['id' => 'U010', 'nama' => 'Admin BurnoutXpert', 'email' => 'admin@burnout.com', 'password' => 'admin', 'role' => 'admin',    'divisi' => '-',               'posisi' => 'System Administrator'],
        ],

        // Riwayat deteksi: array berindeks user_id
        // Setiap entry berisi hasil deteksi lengkap
        'history' => [
            // Seed data demo untuk user U001 (Andi Wijaya)
            'U001' => [
                [
                    'id'               => 'BX-20260410-001',
                    'tanggal'          => '10 April 2026',
                    'timestamp'        => '2026-04-10 09:30:00',
                    'level'            => 'SEDANG',
                    'label'            => 'BURNOUT SEDANG',
                    'confidence'       => 62,
                    'color'            => '#F59E0B',
                    'bg_light'         => '#FFFBEB',
                    'desc'             => 'Tanda-tanda burnout sedang terdeteksi.',
                    'gejala_terdeteksi'=> ['Kelelahan Fisik Berkepanjangan (Sering)', 'Beban Kerja Fisik Berlebih (Sering)'],
                    'rekomendasi'      => [
                        ['icon' => '⚖️', 'judul' => 'Manajemen Waktu', 'isi' => 'Prioritaskan tugas penting.'],
                    ],
                ],
                [
                    'id'               => 'BX-20260315-001',
                    'tanggal'          => '15 Maret 2026',
                    'timestamp'        => '2026-03-15 14:15:00',
                    'level'            => 'RENDAH',
                    'label'            => 'BURNOUT RENDAH',
                    'confidence'       => 35,
                    'color'            => '#3B82F6',
                    'bg_light'         => '#EFF6FF',
                    'desc'             => 'Gejala awal burnout terdeteksi.',
                    'gejala_terdeteksi'=> ['Kelelahan Fisik Berkepanjangan (Kadang)'],
                    'rekomendasi'      => [
                        ['icon' => '😴', 'judul' => 'Jaga Kualitas Tidur', 'isi' => 'Pastikan tidur 7-9 jam setiap malam.'],
                    ],
                ],
            ],
        ],

        // Log aktivitas sistem
        'logs' => [
            [
                'id'     => 1,
                'user'   => 'Admin BurnoutXpert',
                'action' => 'SYSTEM_INIT',
                'entity' => 'App',
                'desc'   => 'Sistem BurnoutXpert berhasil diinisialisasi.',
                'time'   => date('d M Y H:i:s'),
            ],
        ],

        // Notifikasi untuk HRD
        'hrd_alerts' => [],

        // Token reset password: [email => ['token'=>..., 'expires'=>timestamp]]
        'reset_tokens' => [],
    ];
}

// ── Helper Functions ──────────────────────────────────────────

/**
 * Ambil riwayat deteksi seorang user berdasarkan user_id.
 * Mengembalikan array riwayat, terbaru di depan.
 */
function get_user_history(string $user_id): array {
    return $_SESSION['bx_store']['history'][$user_id] ?? [];
}

/**
 * Simpan hasil deteksi baru ke store.
 * Otomatis menambahkan ke histori user dan mencatat log.
 */
function save_detection_result(string $user_id, string $user_nama, array $hasil): void {
    if (!isset($_SESSION['bx_store']['history'][$user_id])) {
        $_SESSION['bx_store']['history'][$user_id] = [];
    }
    // Prepend (terbaru di depan)
    array_unshift($_SESSION['bx_store']['history'][$user_id], $hasil);

    // Log aksi
    append_log($user_nama, 'DETEKSI_BURNOUT', "User#{$user_id}", "Deteksi selesai: {$hasil['label']} (CF: {$hasil['confidence']}%)");
}

/**
 * Tambahkan entri ke log aktivitas.
 */
function append_log(string $user, string $action, string $entity, string $desc): void {
    $logs = &$_SESSION['bx_store']['logs'];
    $new_id = empty($logs) ? 1 : ($logs[0]['id'] + 1);
    array_unshift($logs, [
        'id'     => $new_id,
        'user'   => $user,
        'action' => $action,
        'entity' => $entity,
        'desc'   => $desc,
        'time'   => date('d M Y H:i:s'),
    ]);
    // Batasi 100 log terakhir
    $_SESSION['bx_store']['logs'] = array_slice($_SESSION['bx_store']['logs'], 0, 100);
}

/**
 * Cari user berdasarkan email (untuk login & lupa password).
 */
function find_user_by_email(string $email): ?array {
    foreach ($_SESSION['bx_store']['users'] as $u) {
        if (strtolower($u['email']) === strtolower($email)) return $u;
    }
    return null;
}

/**
 * Cari user berdasarkan ID.
 */
function find_user_by_id(string $id): ?array {
    foreach ($_SESSION['bx_store']['users'] as $u) {
        if ($u['id'] === $id) return $u;
    }
    return null;
}

/**
 * Perbarui password user berdasarkan email.
 */
function update_user_password(string $email, string $new_password): bool {
    foreach ($_SESSION['bx_store']['users'] as &$u) {
        if (strtolower($u['email']) === strtolower($email)) {
            $u['password'] = $new_password;
            return true;
        }
    }
    return false;
}

/**
 * Ambil semua karyawan (role = karyawan) beserta status deteksi terakhir.
 */
function get_all_karyawan(): array {
    $result = [];
    foreach ($_SESSION['bx_store']['users'] as $u) {
        if ($u['role'] !== 'karyawan') continue;
        $history = get_user_history($u['id']);
        $last    = !empty($history) ? $history[0] : null;
        $result[] = [
            'id'             => $u['id'],
            'nama'           => $u['nama'],
            'email'          => $u['email'],
            'divisi'         => $u['divisi'],
            'posisi'         => $u['posisi'],
            'last_deteksi'   => $last ? $last['tanggal'] : '-',
            'last_level'     => $last ? $last['level']   : 'Belum Deteksi',
            'last_color'     => $last ? $last['color']   : '#9CA3AF',
        ];
    }
    return $result;
}

/**
 * Generate ID laporan unik.
 */
function generate_report_id(string $timestamp): string {
    $date = date('Ymd', strtotime($timestamp));
    $seq  = rand(100, 999);
    return "BX-{$date}-{$seq}";
}
