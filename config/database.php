<?php
/**
 * Konfigurasi Database SQLite & PDO Connection
 * Sistem Pendukung Keputusan (SPK) Metode MOORA
 */

declare(strict_types=1);

define('DB_PATH', __DIR__ . '/../data/moora.sqlite');
define('SCHEMA_PATH', __DIR__ . '/../data/schema.sql');

function getDbConnection(): PDO
{
    static $pdoInstance = null;
    if ($pdoInstance !== null) {
        return $pdoInstance;
    }

    $dbDir = dirname(DB_PATH);
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
    }

    $isNewDatabase = !file_exists(DB_PATH) || filesize(DB_PATH) === 0;

    $pdoInstance = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Aktifkan foreign key constraint & WAL mode untuk performa optimal
    $pdoInstance->exec('PRAGMA foreign_keys = ON;');
    $pdoInstance->exec('PRAGMA journal_mode = WAL;');

    // Jika database baru, inisialisasi schema dan seed data akademik
    if ($isNewDatabase) {
        initDatabase($pdoInstance);
    }

    return $pdoInstance;
}

/**
 * Inisialisasi Schema dan Seeder Data Akademik
 */
function initDatabase(PDO $pdo): void
{
    if (file_exists(SCHEMA_PATH)) {
        $schema = file_get_contents(SCHEMA_PATH);
        $pdo->exec($schema);
    }

    // Seed User Default (password_hash standar modern BCRYPT)
    // 1. admin_unipol / Unipol2026!   (Role: admin -> Full Access CRUD)
    // 2. reviewer_unipol / Reviewer2026! (Role: user  -> Reviewer / Read-Only & Cetak)
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        'Administrator (Panitia Seleksi)',
        'admin_unipol',
        password_hash('Unipol2026!', PASSWORD_BCRYPT),
        'admin'
    ]);
    $stmt->execute([
        'Tim Reviewer / Dosen Penilai',
        'reviewer_unipol',
        password_hash('Reviewer2026!', PASSWORD_BCRYPT),
        'user'
    ]);

    // Seed Kriteria Benchmark: Pedoman Capaian Unggulan (CU) PILMAPRES UNIPOL 2026
    $criteriaData = [
        ['kode' => 'C1', 'nama' => 'CU Bidang Kompetisi (Juara Lomba/Kejuaraan)',               'bobot' => 20.0, 'jenis' => 'benefit', 'urutan' => 1],
        ['kode' => 'C2', 'nama' => 'CU Bidang Hasil Karya & Publikasi (Paten/Buku/Jurnal)',    'bobot' => 20.0, 'jenis' => 'benefit', 'urutan' => 2],
        ['kode' => 'C3', 'nama' => 'CU Bidang Karier Organisasi & Pengakuan (Narasumber/Juri)','bobot' => 15.0, 'jenis' => 'benefit', 'urutan' => 3],
        ['kode' => 'C4', 'nama' => 'CU Bidang Kewirausahaan (NIB) & Aksi Kemanusiaan',          'bobot' => 15.0, 'jenis' => 'benefit', 'urutan' => 4],
        ['kode' => 'C5', 'nama' => 'Indeks Prestasi Kumulatif (IPK Akademik)',                  'bobot' => 20.0, 'jenis' => 'benefit', 'urutan' => 5],
        ['kode' => 'C6', 'nama' => 'Poin Pelanggaran Kedisiplinan & Tata Tertib',              'bobot' => 10.0, 'jenis' => 'cost',    'urutan' => 6],
    ];

    $stmtCrit = $pdo->prepare("INSERT INTO kriteria (kode, nama, bobot, jenis, urutan) VALUES (:kode, :nama, :bobot, :jenis, :urutan)");
    $criteriaMap = [];
    foreach ($criteriaData as $c) {
        $stmtCrit->execute($c);
        $criteriaMap[$c['kode']] = (int)$pdo->lastInsertId();
    }

    // Seed Alternatif Benchmark: Mahasiswa Kandidat PILMAPRES UNIPOL 2026
    $alternativesData = [
        [
            'kode'       => 'A1',
            'nama'       => 'Andi Muh. Rifqi Pratama',
            'keterangan' => 'NIM: 22025050101 | S1 Teknik Informatika (Fakultas Teknik UNIPOL)',
            'scores'     => ['C1' => 85, 'C2' => 80, 'C3' => 88, 'C4' => 75, 'C5' => 88, 'C6' => 5]
        ],
        [
            'kode'       => 'A2',
            'nama'       => 'Nur Fadillah Sari',
            'keterangan' => 'NIM: 22025050102 | S1 Sistem Informasi (Fakultas Teknik UNIPOL)',
            'scores'     => ['C1' => 80, 'C2' => 85, 'C3' => 82, 'C4' => 80, 'C5' => 85, 'C6' => 10]
        ],
        [
            'kode'       => 'A3',
            'nama'       => 'Citra Wahyuni Lestari',
            'keterangan' => 'NIM: 22025050103 | S1 Ilmu Administrasi Publik (FIA UNIPOL)',
            'scores'     => ['C1' => 95, 'C2' => 90, 'C3' => 92, 'C4' => 90, 'C5' => 95, 'C6' => 0]
        ],
        [
            'kode'       => 'A4',
            'nama'       => 'Muhammad Haikal',
            'keterangan' => 'NIM: 22025050104 | S1 Manajemen (Fakultas Ekonomi dan Bisnis UNIPOL)',
            'scores'     => ['C1' => 75, 'C2' => 78, 'C3' => 85, 'C4' => 88, 'C5' => 82, 'C6' => 15]
        ],
        [
            'kode'       => 'A5',
            'nama'       => 'Putri Ramadhani',
            'keterangan' => 'NIM: 22025050105 | S1 Pendidikan Bahasa Inggris (FKIP UNIPOL)',
            'scores'     => ['C1' => 88, 'C2' => 82, 'C3' => 75, 'C4' => 70, 'C5' => 80, 'C6' => 20]
        ],
    ];

    $stmtAlt = $pdo->prepare("INSERT INTO alternatif (kode, nama, keterangan) VALUES (:kode, :nama, :keterangan)");
    $stmtVal = $pdo->prepare("INSERT INTO nilai_alternatif (alternatif_id, kriteria_id, nilai) VALUES (?, ?, ?)");

    foreach ($alternativesData as $alt) {
        $stmtAlt->execute([
            ':kode'       => $alt['kode'],
            ':nama'       => $alt['nama'],
            ':keterangan' => $alt['keterangan']
        ]);
        $altId = (int)$pdo->lastInsertId();

        foreach ($alt['scores'] as $cKode => $score) {
            if (isset($criteriaMap[$cKode])) {
                $stmtVal->execute([$altId, $criteriaMap[$cKode], $score]);
            }
        }
    }
}

// Inisialisasi awal PDO untuk kompatibilitas
$pdo = getDbConnection();
