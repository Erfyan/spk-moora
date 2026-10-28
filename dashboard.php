<?php
/**
 * Dashboard Utama SPK Pemilihan Mahasiswa Berprestasi (PILMAPRES)
 * Universitas Lamappapoleonro Soppeng (UNIPOL) 2026
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

use Core\Auth;
use Core\Database;
use Core\MooraEngine;

Auth::requireAuth();

// Query Statistik
$criteria = Database::fetchAll("SELECT * FROM kriteria ORDER BY urutan ASC");
$alternatives = Database::fetchAll("SELECT * FROM alternatif ORDER BY id ASC");
$scores = Database::fetchAll("SELECT * FROM nilai_alternatif");

$totalCriteria = count($criteria);
$totalAlternatives = count($alternatives);

$benefitCount = 0;
$costCount = 0;
foreach ($criteria as $c) {
    if (strtolower($c['jenis']) === 'cost') {
        $costCount++;
    } else {
        $benefitCount++;
    }
}

// Jalankan kalkulasi MOORA
$matrix = [];
foreach ($scores as $s) {
    $matrix[$s['alternatif_id']][$s['kriteria_id']] = $s['nilai'];
}

$engine = new MooraEngine();
$mooraResult = $engine->calculate($alternatives, $criteria, $matrix);
$topAlternative = !empty($mooraResult['ranking']) ? $mooraResult['ranking'][0] : null;

$pageTitle = 'Dashboard - SPK PILMAPRES UNIPOL';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <h4>
                <i class="fa-solid fa-graduation-cap me-1" style="color: var(--green-600);"></i>
                Dashboard PILMAPRES UNIPOL 2026
            </h4>
            <p>
                Selamat Datang, <strong><?= htmlspecialchars(Auth::user()['nama'] ?? 'Pengguna') ?></strong>
                <span class="badge ms-1" style="background: <?= Auth::isAdmin() ? '#facc15' : 'rgba(16, 185, 129, 0.2)' ?>; color: <?= Auth::isAdmin() ? '#064e3b' : 'var(--green-800)' ?>; font-size: 0.68rem;">
                    <?= Auth::isAdmin() ? 'Administrator (Panitia Seleksi)' : 'Reviewer (Mode Baca)' ?>
                </span>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="perhitungan.php" class="btn btn-primary rounded-pill px-3 btn-sm">
                <i class="fa-solid fa-calculator me-1"></i> Lihat Perhitungan
            </a>
            <a href="cetak.php" target="_blank" class="btn btn-outline-warning rounded-pill px-3 btn-sm">
                <i class="fa-solid fa-print me-1"></i> Cetak Laporan
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <!-- Kriteria -->
        <div class="col-6 col-lg-3">
            <div class="card-custom p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Kriteria</div>
                        <h3 class="fw-bold my-1" style="color: var(--green-700);"><?= $totalCriteria ?></h3>
                        <span class="small text-muted" style="font-size: 0.72rem;">
                            <span style="color: var(--green-600); font-weight: 600;"><?= $benefitCount ?></span> Benefit &bull; 
                            <span style="color: var(--gold-600); font-weight: 600;"><?= $costCount ?></span> Cost
                        </span>
                    </div>
                    <div class="stat-icon stat-icon-green">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mahasiswa -->
        <div class="col-6 col-lg-3">
            <div class="card-custom p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Mahasiswa</div>
                        <h3 class="fw-bold my-1" style="color: var(--text-dark);"><?= $totalAlternatives ?></h3>
                        <span class="small text-muted" style="font-size: 0.72rem;">Kandidat UNIPOL</span>
                    </div>
                    <div class="stat-icon stat-icon-gold">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Rank -->
        <div class="col-6 col-lg-3">
            <div class="card-custom p-3 h-100" style="border-color: var(--gold-400);">
                <div class="d-flex justify-content-between align-items-center">
                    <div style="min-width: 0;">
                        <div class="fw-bold text-uppercase" style="font-size: 0.7rem; color: var(--gold-600);">Peringkat #1</div>
                        <h6 class="fw-bold my-1 text-truncate" style="color: var(--text-dark); max-width: 150px;">
                            <?= $topAlternative ? htmlspecialchars($topAlternative['nama']) : 'Belum Ada' ?>
                        </h6>
                        <span class="small text-muted" style="font-size: 0.72rem;">
                            Skor: <strong style="color: var(--green-600);"><?= $topAlternative ? number_format($topAlternative['score'], 4) : '-' ?></strong>
                        </span>
                    </div>
                    <div class="stat-icon" style="background: var(--gold-200); color: var(--gold-600);">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metode -->
        <div class="col-6 col-lg-3">
            <div class="card-custom p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Metode</div>
                        <h4 class="fw-bold my-1" style="color: var(--green-700);">MOORA</h4>
                        <span class="small" style="font-size: 0.72rem; color: var(--green-600);">Objektif &bull; Terstandarisasi</span>
                    </div>
                    <div class="stat-icon stat-icon-green">
                        <i class="fa-solid fa-square-poll-vertical"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row g-3">
        <!-- Peringkat -->
        <div class="col-lg-7">
            <div class="card-custom h-100">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fa-solid fa-medal me-1" style="color: var(--gold-500);"></i> Peringkat Mahasiswa Berprestasi
                    </span>
                    <a href="perhitungan.php" class="btn btn-sm btn-outline-success rounded-pill px-3" style="font-size: 0.75rem;">Detail</a>
                </div>
                <div class="p-0">
                    <?php if (empty($mooraResult['ranking'])): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fs-3 mb-2" style="color: #cbd5e1;"></i>
                            <p class="mb-0 small">Belum ada data penilaian mahasiswa.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-custom table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;" class="text-center">Rank</th>
                                        <th>Mahasiswa Kandidat</th>
                                        <th class="text-end" style="width: 100px;">Nilai Akhir</th>
                                        <th class="text-center" style="width: 120px;">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $topList = array_slice($mooraResult['ranking'], 0, 5);
                                    foreach ($topList as $item): 
                                        $badgeClass = ($item['rank'] == 1) ? 'rank-1' : (($item['rank'] == 2) ? 'rank-2' : (($item['rank'] == 3) ? 'rank-3' : 'rank-other'));
                                    ?>
                                        <tr>
                                            <td class="text-center">
                                                <span class="rank-badge <?= $badgeClass ?>"><?= $item['rank'] ?></span>
                                            </td>
                                            <td>
                                                <div class="fw-bold" style="color: var(--text-dark); font-size: 0.85rem;"><?= htmlspecialchars($item['nama']) ?></div>
                                                <div class="small text-muted text-truncate" style="max-width: 250px; font-size: 0.72rem;"><?= htmlspecialchars($item['keterangan']) ?></div>
                                            </td>
                                            <td class="text-end font-monospace fw-bold" style="color: var(--green-700); font-size: 0.85rem;">
                                                <?= number_format($item['score'], 4) ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($item['rank'] === 1): ?>
                                                    <span class="badge px-2 py-1" style="background: var(--gold-200); color: #854d0e; font-size: 0.68rem;">Mawapres</span>
                                                <?php elseif ($item['rank'] <= 3): ?>
                                                    <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.68rem;">Juara <?= $item['rank'] ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.68rem;">Finalis</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panduan Alur -->
        <div class="col-lg-5">
            <div class="card-custom h-100">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fa-solid fa-compass me-1" style="color: var(--green-600);"></i> Alur Penilaian
                    </span>
                    <span class="badge px-2 py-1" style="background: var(--green-100); color: var(--green-700); font-size: 0.68rem;">UNIPOL 2026</span>
                </div>
                <div class="p-3">
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-circle text-white" style="background: var(--green-600);">1</div>
                        <div>
                            <div class="fw-bold" style="color: var(--text-dark); font-size: 0.85rem;">Tetapkan Kriteria Penilaian</div>
                            <div class="small text-muted" style="font-size: 0.78rem;">Atur bobot capaian unggulan, kompetisi, karya, organisasi, dan IPK.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-3">
                        <div class="step-circle text-white" style="background: var(--green-600);">2</div>
                        <div>
                            <div class="fw-bold" style="color: var(--text-dark); font-size: 0.85rem;">Input Data Mahasiswa</div>
                            <div class="small text-muted" style="font-size: 0.78rem;">Masukkan skor portofolio prestasi setiap kandidat.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-3">
                        <div class="step-circle text-white" style="background: var(--green-600);">3</div>
                        <div>
                            <div class="fw-bold" style="color: var(--text-dark); font-size: 0.85rem;">Perhitungan Otomatis MOORA</div>
                            <div class="small text-muted" style="font-size: 0.78rem;">Sistem menghitung normalisasi dan skor optimasi secara otomatis.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-3">
                        <div class="step-circle fw-bold" style="background: var(--gold-200); color: var(--gold-600);">4</div>
                        <div>
                            <div class="fw-bold" style="color: var(--text-dark); font-size: 0.85rem;">Hasil Seleksi &amp; Cetak Laporan</div>
                            <div class="small text-muted" style="font-size: 0.78rem;">Unduh dan cetak Berita Acara hasil seleksi PILMAPRES.</div>
                        </div>
                    </div>

                    <div class="p-2 rounded-3 d-flex gap-2 flex-wrap" style="background: var(--green-50); border: 1px solid var(--green-100);">
                        <a href="kriteria.php" class="btn btn-sm btn-outline-success rounded-pill flex-fill text-center" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-list-check me-1"></i> Kriteria
                        </a>
                        <a href="alternatif.php" class="btn btn-sm btn-outline-success rounded-pill flex-fill text-center" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-user-graduate me-1"></i> Mahasiswa
                        </a>
                        <a href="perhitungan.php" class="btn btn-sm btn-success rounded-pill flex-fill text-center text-white" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-calculator me-1"></i> Hitung
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
