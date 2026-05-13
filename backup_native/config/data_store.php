<?php
// ============================================================
//  BurnoutXpert – Database-Driven Data Store (MySQL PDO)
//  Menggantikan Session-based storage untuk persistensi riil.
// ============================================================

require_once __DIR__ . '/config.php';

/**
 * Inisialisasi store. 
 * Untuk versi database, kita pastikan koneksi bisa dibuat.
 */
function bx_init_store(): void {
    // Session tetap digunakan untuk menyimpan data login user
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// ── Helper Functions: USER ─────────────────────────────────────

/**
 * Cari user berdasarkan email.
 */
function find_user_by_email(string $email): ?array {
    $db = getDBConnection();
    if (!$db) return null;

    $stmt = $db->prepare("
        SELECT u.*, d.nama as divisi_nama 
        FROM users u 
        LEFT JOIN divisi d ON u.divisi_id = d.id 
        WHERE LOWER(u.email) = LOWER(?) AND u.is_active = 1
    ");
    $stmt->execute([$email]);
    return $stmt->fetch() ?: null;
}

/**
 * Cari user berdasarkan ID.
 */
function find_user_by_id(string $id): ?array {
    $db = getDBConnection();
    if (!$db) return null;

    $stmt = $db->prepare("
        SELECT u.*, d.nama as divisi_nama 
        FROM users u 
        LEFT JOIN divisi d ON u.divisi_id = d.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/**
 * Update password user.
 */
function update_user_password(string $email, string $new_password): bool {
    $db = getDBConnection();
    if (!$db) return false;

    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
    return $stmt->execute([$hash, $email]);
}

/**
 * Update foto profil.
 */
function update_user_photo($user_id, $photo_path) {
    $db = getDBConnection();
    if (!$db) return false;

    $stmt = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
    return $stmt->execute([$photo_path, $user_id]);
}

// ── Helper Functions: DETECTION & HISTORY ────────────────────────

/**
 * Ambil riwayat deteksi seorang user.
 */
function get_user_history(int $user_id): array {
    $db = getDBConnection();
    if (!$db) return [];

    $stmt = $db->prepare("
        SELECT 
            k.id as konsultasi_id,
            k.tanggal,
            h.cf_combined,
            h.persentase,
            d.nama as diagnosa_nama,
            d.tingkat,
            d.rekomendasi,
            d.deskripsi as diagnosa_desc
        FROM konsultasi k
        JOIN hasil_diagnosa h ON k.id = h.konsultasi_id
        JOIN diagnosa d ON h.diagnosa_id = d.id
        WHERE k.user_id = ? AND k.status = 'selesai'
        ORDER BY k.tanggal DESC
    ");
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll();

    // Mapping ke format yang diharapkan UI
    $history = [];
    foreach ($rows as $row) {
        $level_db = strtoupper($row['tingkat']);
        $level = ($level_db === 'RINGAN') ? 'RENDAH' : $level_db;
        $color = ($level === 'BERAT' || $level === 'TINGGI') ? '#DC3545' : (($level === 'SEDANG') ? '#F59E0B' : '#3B82F6');
        
        $history[] = [
            'id'           => 'BX-' . date('Ymd', strtotime($row['tanggal'])) . '-' . $row['konsultasi_id'],
            'tanggal'      => date('d M Y', strtotime($row['tanggal'])),
            'timestamp'    => $row['tanggal'],
            'level'        => $level,
            'label'        => 'BURNOUT ' . $level,
            'confidence'   => round($row['persentase']),
            'color'        => $color,
            'desc'         => $row['diagnosa_desc'],
            'rekomendasi_text' => $row['rekomendasi']
        ];
    }
    return $history;
}

/**
 * Simpan hasil deteksi ke database.
 */
function save_detection_result_db(int $user_id, int $diagnosa_id, float $cf, array $gejala_terpilih): bool {
    $db = getDBConnection();
    if (!$db) return false;

    try {
        $db->beginTransaction();

        // 1. Insert Konsultasi
        $stmt = $db->prepare("INSERT INTO konsultasi (user_id, status) VALUES (?, 'selesai')");
        $stmt->execute([$user_id]);
        $konsultasi_id = $db->lastInsertId();

        // 2. Insert Konsultasi Gejala
        $stmtGejala = $db->prepare("INSERT INTO konsultasi_gejala (konsultasi_id, gejala_id, cf_user) VALUES (?, ?, 1.0)");
        foreach ($gejala_terpilih as $g_id) {
            $stmtGejala->execute([$konsultasi_id, $g_id]);
        }

        // 3. Insert Hasil Diagnosa
        $persentase = $cf * 100;
        $stmtHasil = $db->prepare("INSERT INTO hasil_diagnosa (konsultasi_id, diagnosa_id, cf_combined, persentase) VALUES (?, ?, ?, ?)");
        $stmtHasil->execute([$konsultasi_id, $diagnosa_id, $cf, $persentase]);

        $db->commit();
        
        // Log Audit
        append_log($user_id, 'DETEKSI_SELESAI', 'konsultasi', "Hasil: ID Diagnosa $diagnosa_id (CF: $cf)");
        
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Gagal simpan deteksi: " . $e->getMessage());
        return false;
    }
}

// ── Helper Functions: HRD & ADMIN ──────────────────────────────

/**
 * Ambil semua karyawan untuk dashboard HRD.
 */
function get_all_karyawan(): array {
    $db = getDBConnection();
    if (!$db) return [];

    $sql = "
        SELECT 
            u.id, u.nama, u.email, d.nama as divisi,
            (SELECT k.tanggal FROM konsultasi k WHERE k.user_id = u.id AND k.status = 'selesai' ORDER BY k.tanggal DESC LIMIT 1) as last_deteksi,
            (SELECT diag.tingkat FROM konsultasi k 
             JOIN hasil_diagnosa h ON k.id = h.konsultasi_id 
             JOIN diagnosa diag ON h.diagnosa_id = diag.id 
             WHERE k.user_id = u.id AND k.status = 'selesai' 
             ORDER BY k.tanggal DESC LIMIT 1) as last_level
        FROM users u
        LEFT JOIN divisi d ON u.divisi_id = d.id
        WHERE u.role = 'karyawan'
    ";
    $stmt = $db->query($sql);
    $rows = $stmt->fetchAll();

    $result = [];
    foreach ($rows as $r) {
        $lvl_db = strtoupper($r['last_level'] ?? 'Belum Deteksi');
        $lvl = ($lvl_db === 'RINGAN') ? 'RENDAH' : $lvl_db;
        $color = '#9CA3AF'; // Default gray
        if ($lvl === 'BERAT' || $lvl === 'TINGGI') $color = '#DC3545';
        if ($lvl === 'SEDANG') $color = '#F59E0B';
        if ($lvl === 'RENDAH') $color = '#3B82F6';

        $result[] = [
            'id'           => $r['id'],
            'nama'         => $r['nama'],
            'email'        => $r['email'],
            'divisi'       => $r['divisi'] ?: '-',
            'last_deteksi' => $r['last_deteksi'] ? date('d M Y', strtotime($r['last_deteksi'])) : '-',
            'last_level'   => $lvl,
            'last_color'   => $color
        ];
    }
    return $result;
}

/**
 * Ambil semua pengaturan sistem.
 */
function get_bx_settings(): array {
    $db = getDBConnection();
    if (!$db) return [];

    $stmt = $db->query("SELECT kunci, nilai FROM settings");
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
}

/**
 * Update pengaturan sistem.
 */
function update_bx_setting(string $key, string $value): bool {
    $db = getDBConnection();
    if (!$db) return false;

    $stmt = $db->prepare("INSERT INTO settings (kunci, nilai) VALUES (?, ?) ON DUPLICATE KEY UPDATE nilai = ?");
    return $stmt->execute([$key, $value, $value]);
}

/**
 * Ambil notifikasi untuk user tertentu.
 */
function get_user_notifications(int $user_id, bool $only_unread = false): array {
    $db = getDBConnection();
    if (!$db) return [];

    $sql = "SELECT * FROM notifications WHERE user_id = ?";
    if ($only_unread) $sql .= " AND is_read = 0";
    $sql .= " ORDER BY created_at DESC LIMIT 50";

    $stmt = $db->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

/**
 * Buat notifikasi baru.
 */
function create_notification(int $user_id, string $title, string $message, string $category = 'informasi', string $icon = null, string $color = null): bool {
    $db = getDBConnection();
    if (!$db) return false;

    $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, category, icon, color) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $title, $message, $category, $icon, $color]);
}

/**
 * Tandai notifikasi sebagai dibaca.
 */
function mark_notifications_read(int $user_id): bool {
    $db = getDBConnection();
    if (!$db) return false;

    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    return $stmt->execute([$user_id]);
}

/**
 * Tambahkan log audit.
 */
function append_log(int $user_id, string $action, string $entity, string $desc): void {
    $db = getDBConnection();
    if (!$db) return;

    $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, entity, deskripsi) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $entity, $desc]);
}

/**
 * Generate Report ID (Legacy Support)
 */
function generate_report_id(string $timestamp): string {
    $date = date('Ymd', strtotime($timestamp));
    $hash = substr(md5($timestamp . ($_SESSION['user']['id'] ?? '0')), 0, 4);
    return "BX-{$date}-" . strtoupper($hash);
}
