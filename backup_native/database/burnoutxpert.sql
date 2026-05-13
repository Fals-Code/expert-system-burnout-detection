-- ============================================================
-- BurnoutXpert – Skema Database MySQL
-- Database: burnoutxpert_db
-- ============================================================

CREATE DATABASE IF NOT EXISTS burnoutxpert_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE burnoutxpert_db;

-- ── Tabel: divisi ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS divisi (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(100) NOT NULL UNIQUE,
    deskripsi   TEXT         NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: users ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100)                  NOT NULL,
    email      VARCHAR(150)                  NOT NULL UNIQUE,
    password   VARCHAR(255)                  NOT NULL,
    role       ENUM('karyawan','hrd','admin') NOT NULL DEFAULT 'karyawan',
    divisi_id  INT UNSIGNED                  NULL,
    avatar     VARCHAR(255)                  NULL,
    is_active  TINYINT(1)                    NOT NULL DEFAULT 1,
    last_login DATETIME                      NULL,
    created_at TIMESTAMP                     DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP                     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (divisi_id) REFERENCES divisi(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: gejala ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS gejala (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode        VARCHAR(10)   NOT NULL UNIQUE,
    nama        VARCHAR(200)  NOT NULL,
    kategori    ENUM('fisik','emosional','perilaku','kognitif') NOT NULL,
    bobot       DECIMAL(4,2)  NOT NULL DEFAULT 1.00,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: diagnosa ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS diagnosa (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode            VARCHAR(10)  NOT NULL UNIQUE,
    nama            VARCHAR(200) NOT NULL,
    deskripsi       TEXT         NULL,
    tingkat         ENUM('RINGAN','SEDANG','BERAT') NOT NULL,
    rekomendasi     TEXT         NULL,
    color           VARCHAR(10)  NULL,
    bg_light        VARCHAR(10)  NULL,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: aturan ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS aturan (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode        VARCHAR(10)  NOT NULL UNIQUE,
    diagnosa_id INT UNSIGNED NOT NULL,
    cf_pakar    DECIMAL(4,2) NOT NULL DEFAULT 1.00,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (diagnosa_id) REFERENCES diagnosa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: aturan_gejala ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS aturan_gejala (
    aturan_id   INT UNSIGNED NOT NULL,
    gejala_id   INT UNSIGNED NOT NULL,
    PRIMARY KEY (aturan_id, gejala_id),
    FOREIGN KEY (aturan_id) REFERENCES aturan(id)  ON DELETE CASCADE,
    FOREIGN KEY (gejala_id) REFERENCES gejala(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: konsultasi ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS konsultasi (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    tanggal     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status      ENUM('proses','selesai','dibatalkan') NOT NULL DEFAULT 'proses',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: konsultasi_gejala ──────────────────────────────────
CREATE TABLE IF NOT EXISTS konsultasi_gejala (
    konsultasi_id INT UNSIGNED NOT NULL,
    gejala_id     INT UNSIGNED NOT NULL,
    cf_user       DECIMAL(4,2) NOT NULL DEFAULT 1.00,
    PRIMARY KEY (konsultasi_id, gejala_id),
    FOREIGN KEY (konsultasi_id) REFERENCES konsultasi(id)  ON DELETE CASCADE,
    FOREIGN KEY (gejala_id)     REFERENCES gejala(id)      ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: hasil_diagnosa ────────────────────────────────────
CREATE TABLE IF NOT EXISTS hasil_diagnosa (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    konsultasi_id   INT UNSIGNED    NOT NULL,
    diagnosa_id     INT UNSIGNED    NOT NULL,
    cf_combined     DECIMAL(5,4)    NOT NULL,
    persentase      DECIMAL(5,2)    NOT NULL,
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (konsultasi_id) REFERENCES konsultasi(id)  ON DELETE CASCADE,
    FOREIGN KEY (diagnosa_id)   REFERENCES diagnosa(id)    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: settings ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS settings (
    kunci       VARCHAR(100) PRIMARY KEY,
    nilai       TEXT         NULL,
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: notifications ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    category    ENUM('informasi','peringatan','pengingat') NOT NULL DEFAULT 'informasi',
    title       VARCHAR(200) NOT NULL,
    message     TEXT         NOT NULL,
    is_read     TINYINT(1)   NOT NULL DEFAULT 0,
    icon        VARCHAR(50)  NULL,
    color       VARCHAR(10)  NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: audit_logs ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS audit_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    action      VARCHAR(100) NOT NULL,
    entity      VARCHAR(50)  NOT NULL,
    deskripsi   TEXT         NOT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DATA AWAL (Seed)
-- ============================================================

-- Divisi
INSERT INTO divisi (nama, deskripsi) VALUES
    ('Engineering',    'Tim pengembangan teknis dan sistem'),
    ('Marketing',      'Tim pemasaran dan hubungan publik'),
    ('Finance',        'Tim keuangan dan akuntansi'),
    ('Human Resources','Tim manajemen sumber daya manusia'),
    ('Operations',     'Tim operasional harian');

-- Users (password: password)
INSERT INTO users (id, nama, email, password, role, divisi_id) VALUES
    (1, 'Ahmad Fauzi',  'karyawan@burnoutxpert.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'karyawan', 1),
    (2, 'Siti Rahayu',  'hrd@burnoutxpert.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hrd',      4),
    (3, 'Budi Santoso', 'admin@burnoutxpert.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',    1);

-- Gejala
INSERT INTO gejala (kode, nama, kategori, bobot) VALUES
    ('G001', 'Kelelahan Fisik Berkepanjangan',        'fisik',      0.85),
    ('G002', 'Sakit Kepala atau Migrain Sering',       'fisik',      0.70),
    ('G003', 'Gangguan Tidur (Insomnia/Hipersomnia)',  'fisik',      0.75),
    ('G004', 'Penurunan Imunitas (Sering Sakit)',      'fisik',      0.65),
    ('G005', 'Beban Kerja Fisik Berlebih',             'fisik',      0.60),
    ('G006', 'Kelelahan Emosional Mendalam',           'emosional',  0.92),
    ('G007', 'Sikap Sinis terhadap Pekerjaan',         'emosional',  0.80),
    ('G008', 'Merasa Tidak Dihargai atau Diabaikan',   'emosional',  0.70),
    ('G009', 'Putus Asa terhadap Target Kerja',        'emosional',  0.90),
    ('G010', 'Rasa Cemas Berlebih terkait Pekerjaan',  'emosional',  0.75),
    ('G011', 'Depersonalisasi (Tidak Peduli/Apatis)',  'perilaku',   0.88),
    ('G012', 'Penurunan Prestasi & Produktivitas',     'perilaku',   0.78),
    ('G013', 'Menghindari Tanggungjawab Kerja',        'perilaku',   0.72),
    ('G014', 'Isolasi Diri dari Rekan Kerja',          'perilaku',   0.68),
    ('G015', 'Terlambat atau Sering Absen',            'perilaku',   0.60),
    ('G016', 'Sulit Berkonsentrasi & Fokus',           'kognitif',   0.72),
    ('G017', 'Pelupa dan Sering Membuat Kesalahan',    'kognitif',   0.65),
    ('G018', 'Sulit Membuat Keputusan',                'kognitif',   0.70),
    ('G019', 'Sulit Memulai atau Menyelesaikan Tugas', 'kognitif',   0.75),
    ('G020', 'Hilang Kreativitas & Inisiatif',         'kognitif',   0.68);

-- Diagnosa
INSERT INTO diagnosa (id, kode, nama, tingkat, deskripsi, rekomendasi, color, bg_light) VALUES
    (1, 'D001', 'Burnout Tinggi', 'BERAT', 'Anda menunjukkan gejala burnout tingkat tinggi yang ditandai dengan kelelahan emosional berat, depersonalisasi, dan penurunan motivasi signifikan.', 'Segera konsultasi dengan psikolog/psikiater, pertimbangkan cuti panjang atau rotasi jabatan.', '#DC3545', '#FFF5F5'),
    (2, 'D002', 'Burnout Sedang', 'SEDANG', 'Anda menunjukkan tanda-tanda burnout tingkat sedang. Beberapa gejala mulai mengganggu produktivitas dan kesejahteraan.', 'Konsultasikan dengan HRD, pertimbangkan cuti, sesi konseling, manajemen waktu.', '#F59E0B', '#FFFBEB'),
    (3, 'D003', 'Burnout Rendah', 'RINGAN', 'Anda menunjukkan gejala burnout tingkat rendah berupa kelelahan fisik dan beban kerja awal.', 'Istirahat yang cukup, olahraga ringan, kurangi lembur, hobi sebagai relaksasi.', '#3B82F6', '#EFF6FF');

-- Aturan (Rules)
INSERT INTO aturan (id, kode, diagnosa_id, cf_pakar) VALUES
    (1, 'R001', 1, 0.95),
    (2, 'R002', 1, 0.88),
    (3, 'R003', 2, 0.75),
    (4, 'R004', 2, 0.70),
    (5, 'R005', 3, 0.50),
    (6, 'R006', 3, 0.45);

-- Relasi Aturan ke Gejala (Mapping)
-- R001 -> D001
INSERT INTO aturan_gejala (aturan_id, gejala_id) VALUES
    (1, 1), (1, 6), (1, 7), (1, 9), (1, 11), (1, 12), (1, 16),
    (2, 6), (2, 9), (2, 11), (2, 13), (2, 14), (2, 18),
    (3, 1), (3, 5), (3, 8), (3, 10), (3, 16), (3, 19),
    (4, 2), (4, 3), (4, 7), (4, 12), (4, 17), (4, 20),
    (5, 1), (5, 5), (5, 16),
    (6, 3), (6, 4), (6, 19);

-- Settings
INSERT INTO settings (kunci, nilai) VALUES
    ('app_name', 'BurnoutXpert'),
    ('threshold_high', '0.8'),
    ('maintenance_mode', '0');

