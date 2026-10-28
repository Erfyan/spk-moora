-- ==========================================================
-- Schema Database SQLite: SPK Metode MOORA
-- Standar untuk Riset & Publikasi Jurnal Ilmiah
-- ==========================================================

PRAGMA foreign_keys = ON;

-- 1. Tabel Pengguna (Users)
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama TEXT NOT NULL,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel Kriteria (Criteria)
CREATE TABLE IF NOT EXISTS kriteria (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kode TEXT NOT NULL UNIQUE,
    nama TEXT NOT NULL,
    bobot REAL NOT NULL DEFAULT 1.0,
    jenis TEXT NOT NULL CHECK(jenis IN ('benefit', 'cost')) DEFAULT 'benefit',
    urutan INTEGER DEFAULT 0
);

-- 3. Tabel Alternatif (Alternatives)
CREATE TABLE IF NOT EXISTS alternatif (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kode TEXT NOT NULL UNIQUE,
    nama TEXT NOT NULL,
    keterangan TEXT DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 4. Tabel Nilai Matriks Keputusan (Decision Matrix: Alternatif x Kriteria)
CREATE TABLE IF NOT EXISTS nilai_alternatif (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    alternatif_id INTEGER NOT NULL,
    kriteria_id INTEGER NOT NULL,
    nilai REAL NOT NULL DEFAULT 0.0,
    FOREIGN KEY (alternatif_id) REFERENCES alternatif(id) ON DELETE CASCADE,
    FOREIGN KEY (kriteria_id) REFERENCES kriteria(id) ON DELETE CASCADE,
    UNIQUE(alternatif_id, kriteria_id)
);
