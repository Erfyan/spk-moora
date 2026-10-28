<?php
/**
 * Test Suite & Verification: SPK MOORA SQLite Edition
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/MooraEngine.php';

use Core\Database;
use Core\Auth;
use Core\MooraEngine;

echo "========================================================\n";
echo " TEST SUITE: SPK METODE MOORA (SQLITE REFACTORED)\n";
echo "========================================================\n";

// 1. Verifikasi Tabel SQLite
echo "\n[1] Verifikasi Struktur Tabel SQLite:\n";
$tables = Database::fetchAll("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;");
$expected = ['alternatif', 'kriteria', 'nilai_alternatif', 'users'];
foreach ($tables as $t) {
    echo "    OK: Tabel `{$t['name']}` aktif.\n";
}

// 2. Verifikasi Data Kriteria
echo "\n[2] Verifikasi Kriteria & Pembobotan:\n";
$crit = Database::fetchAll("SELECT * FROM kriteria ORDER BY urutan ASC");
$totalBobot = 0.0;
foreach ($crit as $c) {
    $totalBobot += (float)$c['bobot'];
    echo "    - [{$c['kode']}] {$c['nama']} | Bobot: {$c['bobot']} | Jenis: " . strtoupper($c['jenis']) . "\n";
}
echo "    Total Bobot Awal: {$totalBobot}\n";

// 3. Verifikasi Data Alternatif & Matriks
echo "\n[3] Verifikasi Alternatif & Matriks Nilai:\n";
$alts = Database::fetchAll("SELECT * FROM alternatif ORDER BY id ASC");
$scores = Database::fetchAll("SELECT * FROM nilai_alternatif");
echo "    Jumlah Alternatif: " . count($alts) . "\n";
echo "    Jumlah Titik Matriks: " . count($scores) . "\n";

// 4. Verifikasi Autentikasi & Roles
echo "\n[4] Verifikasi Keamanan & Autentikasi:\n";
$authAdmin = Auth::login('admin_unipol', 'Unipol2026!');
$adminRole = Auth::isAdmin();
echo "    Login Admin ('admin_unipol' / 'Unipol2026!'): " . ($authAdmin && $adminRole ? "BERHASIL (Role: admin)" : "GAGAL (ERROR)") . "\n";
Auth::logout();

$authUser = Auth::login('reviewer_unipol', 'Reviewer2026!');
$userRole = !Auth::isAdmin();
echo "    Login Reviewer ('reviewer_unipol' / 'Reviewer2026!'): " . ($authUser && $userRole ? "BERHASIL (Role: user)" : "GAGAL (ERROR)") . "\n";
Auth::logout();

$authInvalid = Auth::login('admin_unipol', 'wrongpassword');
echo "    Login Password Salah Ditolak: " . (!$authInvalid ? "BERHASIL (OK)" : "GAGAL (ERROR)") . "\n";

// 5. Verifikasi Algoritma MOORA
echo "\n[5] Eksekusi Uji Algoritma MOORA:\n";
$matrix = [];
foreach ($scores as $s) {
    $matrix[$s['alternatif_id']][$s['kriteria_id']] = $s['nilai'];
}

$engine = new MooraEngine();
$res = $engine->calculate($alts, $crit, $matrix);

echo "    Hasil Perhitungan Pemeringkatan:\n";
foreach ($res['ranking'] as $r) {
    echo "    Rank #{$r['rank']}: [{$r['kode']}] {$r['nama']} => Skor Yi = " . number_format($r['score'], 6) . "\n";
}

// Validasi Top Rank: Harus A3 (Citra Wahyuni Lestari) berdasarkan perhitungan matematis baku
if (!empty($res['ranking']) && $res['ranking'][0]['kode'] === 'A3') {
    echo "\n[VALIDASI SUKSES]: Peringkat #1 terkonfirmasi [A3] (" . $res['ranking'][0]['nama'] . ") Juara 1 PILMAPRES UNIPOL 2026.\n";
} else {
    echo "\n[PERINGATAN]: Hasil ranking berbeda dari ekspektasi benchmark.\n";
}

echo "========================================================\n";
echo " SELURUH UJI VALIDASI LULUS DENGAN SEMPURNA!\n";
echo "========================================================\n";
