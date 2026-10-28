<?php
/**
 * Perhitungan Lengkap Metode MOORA
 * SPK PILMAPRES UNIPOL 2026
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

use Core\Auth;
use Core\Database;
use Core\Helpers;
use Core\MooraEngine;

Auth::requireAuth();

// Ambil data dari database SQLite
$criteria = Database::fetchAll("SELECT * FROM kriteria ORDER BY urutan ASC, kode ASC");
$alternatives = Database::fetchAll("SELECT * FROM alternatif ORDER BY id ASC");
$allScores = Database::fetchAll("SELECT * FROM nilai_alternatif");

$scoreMatrix = [];
foreach ($allScores as $s) {
    $scoreMatrix[(int)$s['alternatif_id']][(int)$s['kriteria_id']] = (float)$s['nilai'];
}

// Eksekusi kalkulasi algoritma MOORA
$engine = new MooraEngine();
$moora = $engine->calculate($alternatives, $criteria, $scoreMatrix);

$pageTitle = 'Hasil Perhitungan MOORA - PILMAPRES UNIPOL 2026';
$activePage = 'perhitungan';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <h4>
                <i class="fa-solid fa-calculator me-1" style="color: var(--green-600);"></i>
                Perhitungan &amp; Hasil Seleksi
            </h4>
            <p>Tahapan perhitungan metode MOORA berdasarkan pedoman PILMAPRES UNIPOL 2026</p>
        </div>
        <div class="d-flex gap-2 btn-no-print flex-wrap">
            <a href="cetak.php" target="_blank" class="btn btn-outline-warning rounded-pill px-3 btn-sm">
                <i class="fa-solid fa-print me-1"></i> Cetak Laporan
            </a>
            <a href="kriteria.php" class="btn btn-outline-success rounded-pill px-3 btn-sm">
                <i class="fa-solid fa-sliders me-1"></i> Ubah Kriteria
            </a>
        </div>
    </div>

    <?php if (!$moora['has_data'] || empty($alternatives) || empty($criteria)): ?>
        <div class="card-custom p-5 text-center my-4">
            <i class="fa-solid fa-circle-exclamation fs-1 mb-3" style="color: var(--gold-500);"></i>
            <h5 class="fw-bold">Data Belum Lengkap</h5>
            <p class="text-muted small">Pastikan Anda telah menginput minimal 1 kriteria dan 1 mahasiswa beserta nilainya.</p>
            <div class="d-flex justify-content-center gap-2 mt-2">
                <a href="kriteria.php" class="btn btn-primary rounded-pill px-4 btn-sm">Kelola Kriteria</a>
                <a href="alternatif.php" class="btn btn-success rounded-pill px-4 btn-sm">Kelola Mahasiswa</a>
            </div>
        </div>
    <?php else: ?>

        <!-- ========== HASIL AKHIR: PERINGKAT ========== -->
        <div class="card-custom mb-4" style="border: 2px solid var(--green-600);">
            <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: var(--green-900); color: #fff;">
                <span class="fw-bold" style="font-size: 0.95rem;">
                    <i class="fa-solid fa-trophy me-1" style="color: var(--gold-400);"></i> Hasil Akhir: Peringkat Mahasiswa Berprestasi
                </span>
                <span class="badge px-2 py-1" style="background: rgba(255,255,255,0.15); color: #fff; font-size: 0.7rem;">Skor Tertinggi = Rekomendasi Utama</span>
            </div>
            <div class="p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 65px;" class="text-center">Rank</th>
                                <th style="width: 55px;">Kode</th>
                                <th>Nama Mahasiswa</th>
                                <th class="text-center" style="width: 95px;">Benefit</th>
                                <th class="text-center" style="width: 95px;">Cost</th>
                                <th class="text-end fw-bold" style="width: 110px;">Nilai Akhir</th>
                                <th class="text-center" style="width: 125px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($moora['ranking'] as $row): 
                                $badgeClass = ($row['rank'] == 1) ? 'rank-1' : (($row['rank'] == 2) ? 'rank-2' : (($row['rank'] == 3) ? 'rank-3' : 'rank-other'));
                            ?>
                                <tr <?= $row['rank'] === 1 ? 'style="background: var(--gold-50);"' : '' ?>>
                                    <td class="text-center">
                                        <span class="rank-badge <?= $badgeClass ?>"><?= $row['rank'] ?></span>
                                    </td>
                                    <td><span class="badge bg-light text-dark border font-monospace" style="font-size: 0.72rem;"><?= Helpers::e($row['kode']) ?></span></td>
                                    <td>
                                        <div class="fw-bold" style="color: var(--text-dark); font-size: 0.85rem;"><?= Helpers::e($row['nama']) ?></div>
                                        <div class="small text-muted text-truncate" style="max-width: 200px; font-size: 0.7rem;"><?= Helpers::e($row['keterangan'] ?: '-') ?></div>
                                    </td>
                                    <td class="text-center font-monospace" style="color: var(--green-700); font-size: 0.82rem;"><?= Helpers::formatNumber($row['benefit_sum'], 6) ?></td>
                                    <td class="text-center font-monospace text-danger" style="font-size: 0.82rem;"><?= Helpers::formatNumber($row['cost_sum'], 6) ?></td>
                                    <td class="text-end font-monospace fw-bold" style="color: var(--green-900); font-size: 0.9rem;">
                                        <?= Helpers::formatNumber($row['score'], 6) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['rank'] === 1): ?>
                                            <span class="badge px-2 py-1" style="background: var(--green-600); color: #fff; font-size: 0.68rem;"><i class="fa-solid fa-star me-1"></i>Mawapres Utama</span>
                                        <?php elseif ($row['rank'] <= 3): ?>
                                            <span class="badge px-2 py-1" style="background: var(--green-100); color: var(--green-800); font-size: 0.68rem;">Direkomendasikan</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.68rem;">Dipertimbangkan</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="p-3 small text-muted" style="background: #f8faf8; border-top: 1px solid var(--green-100); font-size: 0.78rem;">
                <i class="fa-solid fa-circle-check me-1" style="color: var(--green-600);"></i>
                <strong>Hasil Seleksi:</strong> Mahasiswa berprestasi utama (Juara 1 PILMAPRES UNIPOL 2026) adalah
                <strong style="color: var(--text-dark);"><?= Helpers::e($moora['ranking'][0]['nama']) ?> (<?= Helpers::e($moora['ranking'][0]['kode']) ?>)</strong> 
                dengan nilai optimasi <?= Helpers::formatNumber($moora['ranking'][0]['score'], 6) ?>.
            </div>
        </div>

        <!-- ========== LANGKAH 1: MATRIKS KEPUTUSAN ========== -->
        <div class="card-custom mb-4">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-table-cells me-1" style="color: var(--green-600);"></i> Langkah 1: Matriks Keputusan &amp; Bobot Kriteria</span>
                <span class="badge bg-light text-dark border" style="font-size: 0.68rem;"><?= count($alternatives) ?> &times; <?= count($criteria) ?></span>
            </div>
            <div class="p-3">
                <div class="info-box">
                    Matriks keputusan menampilkan nilai evaluasi setiap mahasiswa kandidat terhadap masing-masing kriteria penilaian.
                </div>
                <div class="table-responsive">
                    <table class="table table-custom table-bordered table-hover text-center align-middle">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle" style="width: 40px;">#</th>
                                <th rowspan="2" class="align-middle text-start">Mahasiswa</th>
                                <?php foreach ($criteria as $c): ?>
                                    <th style="min-width: 65px;">
                                        <?= Helpers::e($c['kode']) ?>
                                        <div class="small fw-normal text-muted" style="font-size: 0.65rem;">(<?= Helpers::e($c['nama']) ?>)</div>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                            <tr style="background: var(--green-50);">
                                <?php foreach ($criteria as $c): ?>
                                    <th style="font-size: 0.72rem;">
                                        <span class="badge <?= strtolower($c['jenis']) === 'cost' ? 'badge-cost' : 'badge-benefit' ?>">
                                            <?= strtoupper($c['jenis']) ?>
                                        </span>
                                        <div class="mt-1 font-monospace" style="font-size: 0.7rem;">w=<?= Helpers::formatNumber($c['bobot'], 0) ?></div>
                                        <div class="font-monospace" style="color: var(--green-700); font-size: 0.7rem;">W=<?= Helpers::formatNumber($moora['criteria'][$c['id']]['normalized_weight'], 4) ?></div>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($alternatives as $alt): $altId = (int)$alt['id']; ?>
                                <tr>
                                    <td class="text-muted"><?= $no++ ?></td>
                                    <td class="text-start fw-bold" style="color: var(--text-dark); font-size: 0.82rem;">
                                        <?= Helpers::e($alt['kode']) ?> &mdash; <?= Helpers::e($alt['nama']) ?>
                                    </td>
                                    <?php foreach ($criteria as $c): $cId = (int)$c['id']; ?>
                                        <td class="font-monospace" style="font-size: 0.82rem;">
                                            <?= Helpers::formatNumber($moora['decision_matrix'][$altId][$cId] ?? 0, 2) ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot style="background: var(--green-50);">
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end small">Penyebut Rasio:</td>
                                <?php foreach ($criteria as $c): $cId = (int)$c['id']; ?>
                                    <td class="font-monospace" style="color: var(--green-700); font-size: 0.78rem;">
                                        <?= Helpers::formatNumber($moora['denominators'][$cId] ?? 0, 4) ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========== LANGKAH 2: NORMALISASI ========== -->
        <div class="card-custom mb-4">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-arrows-split-up-and-left me-1" style="color: var(--gold-600);"></i> Langkah 2: Normalisasi Matriks Rasio</span>
                <span class="badge px-2 py-1" style="background: var(--gold-100); color: var(--gold-600); font-size: 0.68rem;">Vector Normalization</span>
            </div>
            <div class="p-3">
                <div class="info-box" style="border-left-color: var(--gold-500);">
                    Setiap nilai dibagi dengan akar kuadrat jumlah kuadrat seluruh nilai pada kriteria tersebut, menghasilkan nilai tanpa dimensi (0–1).
                </div>
                <div class="table-responsive">
                    <table class="table table-custom table-bordered table-hover text-center align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th class="text-start">Mahasiswa</th>
                                <?php foreach ($criteria as $c): ?>
                                    <th style="min-width: 70px;"><?= Helpers::e($c['kode']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($alternatives as $alt): $altId = (int)$alt['id']; ?>
                                <tr>
                                    <td class="text-muted"><?= $no++ ?></td>
                                    <td class="text-start fw-bold" style="color: var(--text-dark); font-size: 0.82rem;">
                                        <?= Helpers::e($alt['kode']) ?> &mdash; <?= Helpers::e($alt['nama']) ?>
                                    </td>
                                    <?php foreach ($criteria as $c): $cId = (int)$c['id']; ?>
                                        <td class="font-monospace" style="font-size: 0.8rem;">
                                            <?= Helpers::formatNumber($moora['normalized_matrix'][$altId][$cId] ?? 0, 6) ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========== LANGKAH 3: NORMALISASI TERBOBOT ========== -->
        <div class="card-custom mb-4">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-scale-balanced me-1" style="color: var(--gold-500);"></i> Langkah 3: Nilai Terbobot</span>
                <span class="badge px-2 py-1" style="background: var(--gold-100); color: var(--gold-600); font-size: 0.68rem;">v = W &times; x*</span>
            </div>
            <div class="p-3">
                <div class="info-box" style="border-left-color: var(--gold-500);">
                    Nilai ternormalisasi dikalikan dengan bobot relatif masing-masing kriteria.
                </div>
                <div class="table-responsive">
                    <table class="table table-custom table-bordered table-hover text-center align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th class="text-start">Mahasiswa</th>
                                <?php foreach ($criteria as $c): ?>
                                    <th style="min-width: 70px;">
                                        <?= Helpers::e($c['kode']) ?>
                                        <div class="small fw-normal" style="font-size: 0.6rem; color: <?= strtolower($c['jenis']) === 'cost' ? '#dc2626' : 'var(--green-600)' ?>;">
                                            (<?= strtoupper($c['jenis']) ?>)
                                        </div>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($alternatives as $alt): $altId = (int)$alt['id']; ?>
                                <tr>
                                    <td class="text-muted"><?= $no++ ?></td>
                                    <td class="text-start fw-bold" style="color: var(--text-dark); font-size: 0.82rem;">
                                        <?= Helpers::e($alt['kode']) ?> &mdash; <?= Helpers::e($alt['nama']) ?>
                                    </td>
                                    <?php foreach ($criteria as $c): $cId = (int)$c['id']; ?>
                                        <td class="font-monospace" style="font-size: 0.8rem;">
                                            <?= Helpers::formatNumber($moora['weighted_matrix'][$altId][$cId] ?? 0, 6) ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========== LANGKAH 4: NILAI OPTIMASI ========== -->
        <div class="card-custom mb-4">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-square-root-variable me-1" style="color: var(--green-600);"></i> Langkah 4: Nilai Optimasi (Benefit &minus; Cost)</span>
                <span class="badge px-2 py-1" style="background: var(--green-100); color: var(--green-700); font-size: 0.68rem;">Y = &sum;Benefit &minus; &sum;Cost</span>
            </div>
            <div class="p-3">
                <div class="info-box">
                    Skor akhir dihitung dari selisih total nilai benefit dikurangi total nilai cost untuk setiap mahasiswa.
                </div>
                <div class="table-responsive">
                    <table class="table table-custom table-bordered table-hover text-center align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th class="text-start">Mahasiswa</th>
                                <th style="color: var(--green-700); width: 120px;">Total Benefit</th>
                                <th class="text-danger" style="width: 120px;">Total Cost</th>
                                <th class="fw-bold" style="width: 130px; background: var(--green-50); color: var(--green-900);">Nilai Akhir (Y)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($alternatives as $alt): $altId = (int)$alt['id']; ?>
                                <tr>
                                    <td class="text-muted"><?= $no++ ?></td>
                                    <td class="text-start fw-bold" style="color: var(--text-dark); font-size: 0.82rem;">
                                        <?= Helpers::e($alt['kode']) ?> &mdash; <?= Helpers::e($alt['nama']) ?>
                                    </td>
                                    <td class="font-monospace" style="color: var(--green-700); font-size: 0.82rem;">
                                        <?= Helpers::formatNumber($moora['benefit_sums'][$altId] ?? 0, 6) ?>
                                    </td>
                                    <td class="font-monospace text-danger" style="font-size: 0.82rem;">
                                        <?= Helpers::formatNumber($moora['cost_sums'][$altId] ?? 0, 6) ?>
                                    </td>
                                    <td class="font-monospace fw-bold" style="color: var(--green-900); font-size: 0.9rem; background: var(--green-50);">
                                        <?= Helpers::formatNumber($moora['scores'][$altId] ?? 0, 6) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
