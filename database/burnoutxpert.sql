-- ============================================================
-- BurnoutXpert – Skema Database MySQL
-- Database: burnoutxpert_db
-- Dibuat untuk: Sistem Pakar Deteksi Burnout Karyawan
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
    password   VARCHAR(255)                  NOT NULL,       -- bcrypt hash
    role       ENUM('karyawan','hrd','admin') NOT NULL DEFAULT 'karyawan',
    divisi_id  INT UNSIGNED                  NULL,
    avatar     VARCHAR(255)                  NULL,
    is_active  TINYINT(1)                    NOT NULL DEFAULT 1,
    last_login DATETIME                      NULL,
    created_at TIMESTAMP                     DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP                     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_email (email),
    FOREIGN KEY (divisi_id) REFERENCES divisi(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ── Tabel: gejala (basis pengetahuan – fakta) ────────────────
CREATE TABLE IF NOT EXISTS gejala (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode        VARCHAR(10)   NOT NULL UNIQUE,   -- G001, G002, ...
    nama        VARCHAR(200)  NOT NULL,
    deskripsi   TEXT          NULL,
    kategori    ENUM('fisik','emosional','perilaku','kognitif') NOT NULL,
    bobot       DECIMAL(4,2)  NOT NULL DEFAULT 1.00,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: diagnosa (basis pengetahuan – konklusi) ───────────
CREATE TABLE IF NOT EXISTS diagnosa (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode            VARCHAR(10)  NOT NULL UNIQUE,   -- D001, D002, ...
    nama            VARCHAR(200) NOT NULL,
    deskripsi       TEXT         NULL,
    tingkat         ENUM('ringan','sedang','berat') NOT NULL,
    rekomendasi     TEXT         NULL,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: aturan (basis pengetahuan – rules / IF-THEN) ──────
CREATE TABLE IF NOT EXISTS aturan (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode        VARCHAR(10)  NOT NULL UNIQUE,   -- R001, R002, ...
    diagnosa_id INT UNSIGNED NOT NULL,
    cf_pakar    DECIMAL(4,2) NOT NULL DEFAULT 1.00,  -- Certainty Factor
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (diagnosa_id) REFERENCES diagnosa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: aturan_gejala (many-to-many: aturan <-> gejala) ──
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
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_tanggal (user_id, tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: konsultasi_gejala (gejala yang dipilih user) ──────
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
    cf_combined     DECIMAL(5,4)    NOT NULL,   -- CF gabungan (0.0000 – 1.0000)
    persentase      DECIMAL(5,2)    NOT NULL,   -- 0.00 – 100.00
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (konsultasi_id) REFERENCES konsultasi(id)  ON DELETE CASCADE,
    FOREIGN KEY (diagnosa_id)   REFERENCES diagnosa(id)    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: notifikasi ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifikasi (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    type        ENUM('info','warning','critical') NOT NULL DEFAULT 'info',
    title       VARCHAR(200) NOT NULL,
    message     TEXT         NOT NULL,
    is_read     TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: audit_logs ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS audit_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    action      VARCHAR(100) NOT NULL,  -- e.g., 'update_knowledge', 'delete_user'
    entity      VARCHAR(50)  NOT NULL,  -- e.g., 'gejala', 'aturan'
    deskripsi   TEXT         NOT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: settings ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS settings (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key    VARCHAR(50) NOT NULL UNIQUE,
    setting_value  TEXT        NULL,
    deskripsi      VARCHAR(255) NULL,
    updated_at     TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel: password_resets ────────────────────────────────────
CREATE TABLE IF NOT EXISTS password_resets (
    email      VARCHAR(150) NOT NULL,
    token      VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (email, token)
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

-- Users (password di-hash dengan bcrypt di aplikasi nyata)
INSERT INTO users (nama, email, password, role, divisi_id) VALUES
    ('Ahmad Fauzi',  'karyawan@burnoutxpert.com', '$2y$10$placeholder_karyawan_hash', 'karyawan', 1),
    ('Siti Rahayu',  'hrd@burnoutxpert.com',      '$2y$10$placeholder_hrd_hash',      'hrd',      4),
    ('Budi Santoso', 'admin@burnoutxpert.com',     '$2y$10$placeholder_admin_hash',    'admin',    1);


-- Gejala Burnout (berdasarkan MBI – Maslach Burnout Inventory)
INSERT INTO gejala (kode, nama, kategori, bobot) VALUES
    ('G001', 'Merasa kelelahan fisik setelah bekerja',                'fisik',      1.00),
    ('G002', 'Sakit kepala atau nyeri otot yang sering',              'fisik',      0.80),
    ('G003', 'Gangguan tidur (insomnia atau tidur berlebih)',         'fisik',      0.90),
    ('G004', 'Penurunan imunitas (sering sakit)',                     'fisik',      0.75),
    ('G005', 'Merasa hampa dan tidak bersemangat',                    'emosional',  1.00),
    ('G006', 'Mudah marah atau tersinggung',                          'emosional',  0.85),
    ('G007', 'Merasa tidak dihargai dalam pekerjaan',                 'emosional',  0.80),
    ('G008', 'Kehilangan empati terhadap rekan atau pelanggan',       'emosional',  0.90),
    ('G009', 'Menghindari interaksi sosial di tempat kerja',          'perilaku',   0.85),
    ('G010', 'Penurunan produktivitas kerja yang signifikan',         'perilaku',   0.95),
    ('G011', 'Sering absen atau terlambat masuk kerja',               'perilaku',   0.80),
    ('G012', 'Menggunakan alkohol/obat sebagai pelarian stres',       'perilaku',   0.70),
    ('G013', 'Sulit berkonsentrasi atau membuat keputusan',           'kognitif',   0.90),
    ('G014', 'Pelupa dan menurunnya daya ingat jangka pendek',        'kognitif',   0.75),
    ('G015', 'Merasa pekerjaan tidak memiliki makna',                 'kognitif',   1.00),
    ('G016', 'Pikiran negatif terus-menerus tentang pekerjaan',       'kognitif',   0.85);

-- Diagnosa
INSERT INTO diagnosa (kode, nama, tingkat, rekomendasi) VALUES
    ('D001', 'Burnout Ringan',  'ringan', 'Istirahat yang cukup, olahraga ringan, kurangi lembur, hobi sebagai relaksasi.'),
    ('D002', 'Burnout Sedang',  'sedang', 'Konsultasikan dengan HRD, pertimbangkan cuti, sesi konseling, manajemen waktu.'),
    ('D003', 'Burnout Berat',   'berat',  'Segera konsultasi dengan psikolog/psikiater, pertimbangkan cuti panjang atau rotasi jabatan.');

-- Aturan (forward chaining)
INSERT INTO aturan (kode, diagnosa_id, cf_pakar) VALUES
    ('R001', 1, 0.60),
    ('R002', 1, 0.65),
    ('R003', 2, 0.75),
    ('R004', 2, 0.80),
    ('R005', 3, 0.90),
    ('R006', 3, 0.95);

-- Pengaturan Global
INSERT INTO settings (setting_key, setting_value, deskripsi) VALUES
    ('app_name',         'BurnoutXpert',            'Nama aplikasi utama'),
    ('maintenance_mode', '0',                       'Status perbaikan sistem (0=Aktif, 1=Maintenance)'),
    ('alert_threshold',   '0.85',                    'Ambang batas Certainty Factor untuk notifikasi kritis'),
    ('max_daily_test',    '3',                       'Batas maksimal deteksi harian per karyawan');

-- Audit Log Awal
INSERT INTO audit_logs (user_id, action, entity, deskripsi) VALUES
    (3, 'SYSTEM_INIT', 'database', 'Inisialisasi skema database sistem pakar v2.0');

