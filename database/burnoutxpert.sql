-- ============================================================
-- BurnoutXpert – Skema Database MySQL
-- Database: burnoutxpert_db
-- Dibuat untuk: Sistem Pakar Deteksi Burnout Karyawan
-- ============================================================

CREATE DATABASE IF NOT EXISTS burnoutxpert_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE burnoutxpert_db;

-- ── Tabel: users ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100)                  NOT NULL,
    email      VARCHAR(150)                  NOT NULL UNIQUE,
    password   VARCHAR(255)                  NOT NULL,       -- bcrypt hash
    role       ENUM('karyawan','hrd','admin') NOT NULL DEFAULT 'karyawan',
    divisi     VARCHAR(100)                  NULL,
    avatar     VARCHAR(255)                  NULL,
    is_active  TINYINT(1)                    NOT NULL DEFAULT 1,
    last_login DATETIME                      NULL,
    created_at TIMESTAMP                     DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP                     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_email (email)
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

-- ============================================================
-- DATA AWAL (Seed)
-- ============================================================

-- Users (password di-hash dengan bcrypt di aplikasi nyata)
INSERT INTO users (nama, email, password, role, divisi) VALUES
    ('Ahmad Fauzi',  'karyawan@burnoutxpert.com', '$2y$10$placeholder_karyawan_hash', 'karyawan', 'Engineering'),
    ('Siti Rahayu',  'hrd@burnoutxpert.com',      '$2y$10$placeholder_hrd_hash',      'hrd',      'Human Resources'),
    ('Budi Santoso', 'admin@burnoutxpert.com',     '$2y$10$placeholder_admin_hash',    'admin',    'IT Administration');

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
