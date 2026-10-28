<?php
/**
 * Cetak Laporan Hasil Riset SPK MOORA (Format Paper Jurnal / Laporan Akademik)
 * PILMAPRES UNIPOL 2026
 * Dioptimalkan untuk 1 Halaman A4 & Memiliki Klausul Integritas Lembar Pengesahan
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

use Core\Auth;
use Core\Database;
use Core\Helpers;
use Core\MooraEngine;

Auth::requireAuth();

$criteria = Database::fetchAll("SELECT * FROM kriteria ORDER BY urutan ASC, kode ASC");
$alternatives = Database::fetchAll("SELECT * FROM alternatif ORDER BY id ASC");
$allScores = Database::fetchAll("SELECT * FROM nilai_alternatif");

$scoreMatrix = [];
foreach ($allScores as $s) {
    $scoreMatrix[(int)$s['alternatif_id']][(int)$s['kriteria_id']] = (float)$s['nilai'];
}

$engine = new MooraEngine();
$moora = $engine->calculate($alternatives, $criteria, $scoreMatrix);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara &amp; Hasil Seleksi PILMAPRES UNIPOL 2026</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --green-900: #064e3b;
            --green-800: #065f46;
            --green-700: #047857;
            --green-600: #059669;
            --gold-600: #ca8a04;
            --gold-500: #eab308;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            background: #f1f5f9;
            padding: 1.5rem 1rem;
            margin: 0;
            line-height: 1.35;
        }

        /* ========== ACTION BAR (SCREEN ONLY) ========== */
        .action-bar {
            max-width: 860px;
            margin: 0 auto 1.25rem auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding: 0 0.5rem;
        }

        .btn-print {
            background: linear-gradient(135deg, var(--green-700) 0%, var(--green-600) 100%);
            border: 1px solid var(--green-700);
            color: #ffffff;
            font-weight: 600;
            padding: 0.5rem 1.4rem;
            border-radius: 9999px;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
            transition: all 0.2s ease;
            font-size: 0.85rem;
        }

        .btn-print:hover {
            background: linear-gradient(135deg, var(--green-800) 0%, var(--green-700) 100%);
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* ========== PAPER SHEET (SCREEN PREVIEW) ========== */
        .paper-sheet {
            background: #ffffff;
            width: 100%;
            max-width: 860px;
            margin: 0 auto;
            padding: 18mm 22mm;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            box-sizing: border-box;
            color: #0f172a;
        }

        /* ========== KOP SURAT AKADEMIK ========== */
        .academic-header {
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .academic-header .header-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .academic-header h4 {
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
            font-size: 10.5pt;
            color: #0f172a;
        }

        .academic-header h5 {
            font-weight: 700;
            margin-bottom: 2px;
            font-size: 9pt;
            color: #1e293b;
        }

        .academic-header p {
            margin-bottom: 0;
            font-size: 7.5pt;
            color: #475569;
        }

        /* ========== SECTION TITLE ========== */
        .section-title {
            font-size: 8.3pt;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 8px;
            margin-bottom: 4px;
            border-bottom: 1px solid #94a3b8;
            padding-bottom: 2px;
            color: #0f172a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title .section-meta {
            font-size: 7pt;
            font-weight: normal;
            color: #64748b;
            text-transform: none;
        }

        /* ========== TABLES ========== */
        .table-paper-wrapper {
            margin-bottom: 6px;
            overflow-x: auto;
        }

        .table-paper {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.8pt;
            line-height: 1.25;
        }

        .table-paper th, .table-paper td {
            border: 1px solid #94a3b8;
            padding: 3px 5px;
        }

        .table-paper th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: 700;
            color: #0f172a;
        }

        .badge-type-benefit {
            background-color: #ecfdf5;
            color: #065f46;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 7pt;
        }

        .badge-type-cost {
            background-color: #fefce8;
            color: #854d0e;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 7pt;
        }

        /* ========== KOTAK KEPUTUSAN ========== */
        .decision-box {
            margin-top: 6px;
            padding: 6px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            background-color: #f8fafc;
            font-size: 7.8pt;
            line-height: 1.35;
        }

        /* ========== LEMBAR PENGESAHAN (SIGNATURE BLOCK) ========== */
        .signature-section {
            margin-top: 10px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .integrity-stamp {
            border: 1px dashed #94a3b8;
            background: #f8fafc;
            border-radius: 4px;
            padding: 4px 8px;
            margin-bottom: 8px;
            font-size: 7pt;
            color: #334155;
            line-height: 1.3;
        }

        .signature-spacer {
            height: 38px;
        }

        /* ========== MEDIA PRINT (A4 OPTIMIZED) ========== */
        @page {
            size: A4 portrait;
            margin: 8mm 16mm 8mm 16mm; /* Clean left/right breathing margin */
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                color: #000000 !important;
                font-size: 7.8pt !important;
            }

            .action-bar, .no-print {
                display: none !important;
            }

            .paper-sheet {
                box-shadow: none !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border-radius: 0 !important;
            }

            .academic-header {
                border-bottom: 2px solid #000000 !important;
                padding-bottom: 6px !important;
                margin-bottom: 8px !important;
            }

            .academic-header .header-logo {
                width: 50px !important;
                height: 50px !important;
            }

            .table-paper th, .table-paper td {
                border: 0.8px solid #000000 !important;
                padding: 2.5px 4px !important;
            }

            .table-paper th {
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .section-title {
                margin-top: 6px !important;
                margin-bottom: 3px !important;
                border-bottom: 0.8px solid #000000 !important;
            }

            .decision-box {
                margin-top: 6px !important;
                padding: 5px 8px !important;
                border: 0.8px solid #000000 !important;
                background-color: #fafafa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .signature-section {
                margin-top: 8px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .signature-spacer {
                height: 34px !important;
            }

            .integrity-stamp {
                border: 0.8px dashed #444 !important;
                background: #fbfbfb !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<!-- Action Bar (Screen Only) -->
<div class="action-bar no-print">
    <div>
        <a href="perhitungan.php" class="btn btn-outline-secondary rounded-pill px-3 py-1 btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Sistem
        </a>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted small d-none d-sm-inline" style="font-size: 0.78rem;">
            <i class="fa-solid fa-circle-check text-success me-1"></i> Format Siap Cetak (A4)
        </span>
        <button onclick="window.print()" class="btn btn-print">
            <i class="fa-solid fa-print me-1"></i> Cetak / Simpan PDF
        </button>
    </div>
</div>

<!-- Lembar Dokumen Kertas A4 -->
<div class="paper-sheet">
    <!-- Academic Header / Kop Surat -->
    <div class="academic-header d-flex align-items-center justify-content-center gap-3">
        <img src="img/logo.png" alt="Logo UNIPOL" class="header-logo">
        <div class="text-center">
            <h4>BERITA ACARA &amp; LAPORAN HASIL SELEKSI</h4>
            <h5>PEMILIHAN MAHASISWA BERPRESTASI (PILMAPRES) TAHUN 2026</h5>
            <h5>UNIVERSITAS LAMAPPAPOLEONRO SOPPENG (UNIPOL)</h5>
            <p>Sistem Pendukung Keputusan Berbasis Metode MOORA &bull; Dicetak: <?= date('d F Y, H:i') ?> WITA</p>
        </div>
    </div>

    <!-- 1. Parameter Uji -->
    <div class="section-title">
        <span>1. Parameter Kriteria Penilaian &amp; Bobot</span>
        <span class="section-meta">Pedoman Capaian Unggulan (CU) &amp; IPK UNIPOL 2026</span>
    </div>
    <div class="table-paper-wrapper">
        <table class="table-paper">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 55px;">Kode</th>
                    <th>Nama Kriteria Penilaian</th>
                    <th style="width: 85px;">Sifat</th>
                    <th style="width: 80px;">Bobot (%)</th>
                    <th style="width: 100px;">Bobot Normal ($W_j$)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($criteria as $c): 
                    $nw = $moora['criteria'][$c['id']]['normalized_weight'] ?? 0;
                    $isBenefit = strtolower((string)$c['jenis']) === 'benefit';
                ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="text-center font-monospace fw-bold"><?= Helpers::e($c['kode']) ?></td>
                        <td><?= Helpers::e($c['nama']) ?></td>
                        <td class="text-center">
                            <span class="<?= $isBenefit ? 'badge-type-benefit' : 'badge-type-cost' ?>">
                                <?= $isBenefit ? 'Keuntungan' : 'Biaya/Beban' ?>
                            </span>
                        </td>
                        <td class="text-center font-monospace"><?= Helpers::formatNumber($c['bobot'], 1) ?>%</td>
                        <td class="text-center font-monospace"><?= Helpers::formatNumber($nw, 4) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 2. Matriks Keputusan -->
    <div class="section-title">
        <span>2. Matriks Nilai Awal Mahasiswa Kandidat</span>
        <span class="section-meta">Skor Portofolio Prestasi &amp; Akademik</span>
    </div>
    <div class="table-paper-wrapper">
        <table class="table-paper text-center">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th class="text-start">Kandidat Mahasiswa</th>
                    <?php foreach ($criteria as $c): ?>
                        <th><?= Helpers::e($c['kode']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($alternatives as $alt): $altId = (int)$alt['id']; ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="text-start fw-semibold"><?= Helpers::e($alt['kode']) ?> &mdash; <?= Helpers::e($alt['nama']) ?></td>
                        <?php foreach ($criteria as $c): $cId = (int)$c['id']; 
                            $val = $moora['decision_matrix'][$altId][$cId] ?? 0;
                        ?>
                            <td class="font-monospace">
                                <?= $c['kode'] === 'C5' ? Helpers::formatNumber($val, 2) : Helpers::formatNumber($val, 0) ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 3. Hasil Akhir & Ranking -->
    <div class="section-title">
        <span>3. Hasil Akhir Optimasi Multi-Objektif &amp; Peringkat Rekomendasi</span>
        <span class="section-meta">Nilai Akhir = Total Benefit &minus; Total Cost</span>
    </div>
    <div class="table-paper-wrapper">
        <table class="table-paper">
            <thead>
                <tr>
                    <th style="width: 45px;">Rank</th>
                    <th style="width: 55px;">Kode</th>
                    <th>Nama Mahasiswa Kandidat</th>
                    <th class="text-center" style="width: 105px;">Total Benefit</th>
                    <th class="text-center" style="width: 105px;">Total Cost</th>
                    <th class="text-end" style="width: 105px;">Skor Optimasi ($Y_i$)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($moora['ranking'] as $row): ?>
                    <tr <?= $row['rank'] === 1 ? 'style="background-color: #fefce8; font-weight: bold;"' : '' ?>>
                        <td class="text-center fw-bold">#<?= $row['rank'] ?></td>
                        <td class="text-center font-monospace"><?= Helpers::e($row['kode']) ?></td>
                        <td><?= Helpers::e($row['nama']) ?></td>
                        <td class="text-center font-monospace"><?= Helpers::formatNumber($row['benefit_sum'], 4) ?></td>
                        <td class="text-center font-monospace"><?= Helpers::formatNumber($row['cost_sum'], 4) ?></td>
                        <td class="text-end font-monospace fw-bold"><?= Helpers::formatNumber($row['score'], 4) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Kotak Keputusan Seleksi -->
    <div class="decision-box">
        <strong>Keputusan Seleksi:</strong> Berdasarkan perhitungan multi-kriteria metode MOORA terhadap portofolio Capaian Unggulan (CU) dan IPK, mahasiswa 
        <strong><?= Helpers::e($moora['ranking'][0]['nama'] ?? '-') ?> (<?= Helpers::e($moora['ranking'][0]['kode'] ?? '-') ?>)</strong> 
        meraih skor optimasi tertinggi (<strong><?= Helpers::formatNumber($moora['ranking'][0]['score'] ?? 0, 4) ?></strong>) dan secara sah direkomendasikan sebagai <strong>Mahasiswa Berprestasi Utama (Juara 1 PILMAPRES UNIPOL 2026)</strong>.
    </div>

    <!-- Lembar Pengesahan (Signature Block dengan Klausul Integritas Dokumen) -->
    <div class="signature-section">
        <!-- Klausul Integritas Dokumen (Menjamin keabsahan jika ter-enter ke halaman 2) -->
        <div class="integrity-stamp">
            <div class="d-flex justify-content-between align-items-center">
                <span>
                    <i class="fa-solid fa-stamp me-1"></i>
                    <strong>LEMBAR PENGESAHAN HASIL SELEKSI PILMAPRES UNIPOL 2026</strong>
                </span>
                <span class="font-monospace" style="font-size: 6.8pt; color: #64748b;">
                    No. Reg: BA/UNIPOL-PILMAPRES/<?= date('Y/m') ?> &bull; Sistem MOORA SQLite
                </span>
            </div>
            <div style="font-size: 6.8pt; color: #64748b; margin-top: 1px;">
                *Lembar pengesahan ini merupakan bagian yuridis dan akademis yang tidak terpisahkan dari Berita Acara &amp; Lampiran Matriks Penilaian Hasil Seleksi PILMAPRES Universitas Lamappapoleonro Soppeng.
            </div>
        </div>

        <div class="row text-center" style="font-size: 7.8pt;">
            <div class="col-4">
                <p class="mb-0">Mengetahui,<br><strong>Ketua Tim Seleksi</strong></p>
                <div class="signature-spacer"></div>
                <p class="mb-0">( __________________________ )<br><span class="text-muted" style="font-size: 7pt;">NIDN. .........................</span></p>
            </div>
            <div class="col-4">
                <p class="mb-0">Pengembang Sistem / Peneliti,<br>&nbsp;</p>
                <div class="signature-spacer"></div>
                <p class="mb-0">( <strong><?= Helpers::e(Auth::user()['nama'] ?? 'Administrator') ?></strong> )<br><span class="text-muted" style="font-size: 7pt;">NIM/NIDN. .........................</span></p>
            </div>
            <div class="col-4">
                <p class="mb-0">Menyetujui,<br><strong>Wakil Rektor Kemahasiswaan</strong></p>
                <div class="signature-spacer"></div>
                <p class="mb-0">( __________________________ )<br><span class="text-muted" style="font-size: 7pt;">NIDN. .........................</span></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
