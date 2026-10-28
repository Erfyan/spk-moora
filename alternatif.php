<?php
/**
 * Manajemen Data Mahasiswa Kandidat & Matriks Penilaian
 * SPK PILMAPRES UNIPOL - Metode MOORA
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

use Core\Auth;
use Core\Database;
use Core\Helpers;

Auth::requireAuth();

$isAdmin = Auth::isAdmin();

// Ambil semua kriteria aktif
$criteria = Database::fetchAll("SELECT * FROM kriteria ORDER BY urutan ASC, kode ASC");

// ==========================================
// 1. Action: Tambah Alternatif
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!$isAdmin) {
        Helpers::setFlash('danger', 'Akses Ditolak: Anda login sebagai Reviewer (Read-Only). Hanya Administrator yang dapat menambah data mahasiswa.');
        Helpers::redirect('alternatif.php');
    }

    $kode       = strtoupper(trim((string)($_POST['kode'] ?? '')));
    $nama       = trim((string)($_POST['nama'] ?? ''));
    $keterangan = trim((string)($_POST['keterangan'] ?? ''));
    $scores     = $_POST['scores'] ?? [];

    $hasError = false;
    if ($kode === '' || $nama === '') {
        Helpers::setFlash('danger', 'Kode dan Nama mahasiswa wajib diisi.');
        $hasError = true;
    }
    
    // Validasi semua skor harus numerik dan non-negatif
    if (!$hasError) {
        foreach ($criteria as $c) {
            $cId = (int)$c['id'];
            $val = $scores[$cId] ?? '';
            if ($val === '' || !is_numeric($val) || (float)$val < 0) {
                Helpers::setFlash('danger', 'Semua nilai kriteria wajib diisi angka positif (minimal 0).');
                $hasError = true;
                break;
            }
        }
    }

    if (!$hasError) {
        $exists = Database::fetchOne("SELECT id FROM alternatif WHERE kode = :kode", [':kode' => $kode]);
        if ($exists) {
            Helpers::setFlash('danger', "Kode alternatif '{$kode}' sudah ada dalam sistem.");
        } else {
            Database::beginTransaction();
            try {
                Database::execute(
                    "INSERT INTO alternatif (kode, nama, keterangan) VALUES (:kode, :nama, :keterangan)",
                    [':kode' => $kode, ':nama' => $nama, ':keterangan' => $keterangan]
                );
                $altId = (int)Database::lastInsertId();

                $stmtScore = Database::getConnection()->prepare(
                    "INSERT INTO nilai_alternatif (alternatif_id, kriteria_id, nilai) VALUES (?, ?, ?)"
                );

                foreach ($criteria as $c) {
                    $cId = (int)$c['id'];
                    $val = (float)($scores[$cId] ?? 0);
                    $stmtScore->execute([$altId, $cId, $val]);
                }

                Database::commit();
                Helpers::setFlash('success', "Mahasiswa [{$kode}] {$nama} berhasil ditambahkan.");
                Helpers::redirect('alternatif.php');
            } catch (\Exception $e) {
                Database::rollBack();
                Helpers::setFlash('danger', 'Terjadi kesalahan saat menyimpan data.');
            }
        }
    }
}

// ==========================================
// 2. Action: Update Alternatif
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!$isAdmin) {
        Helpers::setFlash('danger', 'Akses Ditolak: Anda login sebagai Reviewer (Read-Only). Hanya Administrator yang dapat memperbarui data mahasiswa.');
        Helpers::redirect('alternatif.php');
    }

    $id         = (int)($_POST['id'] ?? 0);
    $kode       = strtoupper(trim((string)($_POST['kode'] ?? '')));
    $nama       = trim((string)($_POST['nama'] ?? ''));
    $keterangan = trim((string)($_POST['keterangan'] ?? ''));
    $scores     = $_POST['scores'] ?? [];

    $hasError = false;
    if ($id <= 0 || $kode === '' || $nama === '') {
        Helpers::setFlash('danger', 'Formulir edit tidak valid.');
        $hasError = true;
    }

    // Validasi semua skor
    if (!$hasError) {
        foreach ($criteria as $c) {
            $cId = (int)$c['id'];
            $val = $scores[$cId] ?? '';
            if ($val === '' || !is_numeric($val) || (float)$val < 0) {
                Helpers::setFlash('danger', 'Semua nilai kriteria wajib diisi angka positif (minimal 0).');
                $hasError = true;
                break;
            }
        }
    }

    if (!$hasError) {
        $exists = Database::fetchOne("SELECT id FROM alternatif WHERE kode = :kode AND id != :id", [
            ':kode' => $kode,
            ':id'   => $id
        ]);
        if ($exists) {
            Helpers::setFlash('danger', "Kode alternatif '{$kode}' sudah digunakan.");
        } else {
            Database::beginTransaction();
            try {
                Database::execute(
                    "UPDATE alternatif SET kode = :kode, nama = :nama, keterangan = :keterangan WHERE id = :id",
                    [':kode' => $kode, ':nama' => $nama, ':keterangan' => $keterangan, ':id' => $id]
                );

                $stmtScore = Database::getConnection()->prepare(
                    "INSERT INTO nilai_alternatif (alternatif_id, kriteria_id, nilai) 
                     VALUES (?, ?, ?) 
                     ON CONFLICT(alternatif_id, kriteria_id) DO UPDATE SET nilai = excluded.nilai"
                );

                foreach ($criteria as $c) {
                    $cId = (int)$c['id'];
                    $val = (float)($scores[$cId] ?? 0);
                    $stmtScore->execute([$id, $cId, $val]);
                }

                Database::commit();
                Helpers::setFlash('success', "Mahasiswa [{$kode}] berhasil diperbarui.");
                Helpers::redirect('alternatif.php');
            } catch (\Exception $e) {
                Database::rollBack();
                Helpers::setFlash('danger', 'Gagal memperbarui data.');
            }
        }
    }
}

// ==========================================
// 3. Action: Hapus Alternatif
// ==========================================
if (isset($_GET['hapus'])) {
    if (!$isAdmin) {
        Helpers::setFlash('danger', 'Akses Ditolak: Anda login sebagai Reviewer (Read-Only). Hanya Administrator yang dapat menghapus data mahasiswa.');
        Helpers::redirect('alternatif.php');
    }

    $id = (int)$_GET['hapus'];
    if ($id > 0) {
        Database::execute("DELETE FROM alternatif WHERE id = :id", [':id' => $id]);
        Helpers::setFlash('success', 'Data mahasiswa berhasil dihapus.');
    }
    Helpers::redirect('alternatif.php');
}

// Ambil semua alternatif
$alternatives = Database::fetchAll("SELECT * FROM alternatif ORDER BY id ASC");

// Ambil seluruh nilai matriks
$allScores = Database::fetchAll("SELECT * FROM nilai_alternatif");
$scoreMatrix = [];
foreach ($allScores as $s) {
    $scoreMatrix[(int)$s['alternatif_id']][(int)$s['kriteria_id']] = (float)$s['nilai'];
}

// Data Edit (khusus Admin)
$editItem = null;
$editScores = [];
if ($isAdmin && isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editItem = Database::fetchOne("SELECT * FROM alternatif WHERE id = :id", [':id' => $editId]);
    if ($editItem) {
        $editScores = $scoreMatrix[$editId] ?? [];
    }
}

$pageTitle = 'Data Mahasiswa Kandidat - PILMAPRES UNIPOL 2026';
$activePage = 'alternatif';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <h4>
                <i class="fa-solid fa-user-graduate me-1" style="color: var(--green-600);"></i>
                Data Mahasiswa Kandidat
            </h4>
            <p>Kelola data kandidat Mahasiswa Berprestasi UNIPOL beserta skor penilaian portofolio</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <?php if (!$isAdmin): ?>
                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #065f46; font-size: 0.78rem; padding: 0.45rem 0.85rem; border-radius: 9999px;">
                    <i class="fa-solid fa-eye me-1"></i> Mode Reviewer (Read-Only)
                </span>
            <?php endif; ?>
            <a href="perhitungan.php" class="btn btn-primary rounded-pill px-3 btn-sm">
                <i class="fa-solid fa-calculator me-1"></i> Lihat Perhitungan
            </a>
        </div>
    </div>

    <div class="row g-3">
        <!-- Kolom Kiri: Form untuk Admin, atau Info Panel untuk Reviewer -->
        <div class="col-lg-4">
            <?php if ($isAdmin): ?>
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="fa-solid <?= $editItem ? 'fa-user-pen' : 'fa-user-plus' ?> me-1" style="color: <?= $editItem ? 'var(--gold-500)' : 'var(--green-600)' ?>;"></i>
                        <?= $editItem ? 'Edit Data Mahasiswa' : 'Tambah Mahasiswa Baru' ?>
                    </div>
                    <div class="p-3">
                        <?php if (empty($criteria)): ?>
                            <div class="alert alert-warning small mb-0" style="font-size: 0.82rem;">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Belum ada kriteria. Tambahkan terlebih dahulu di menu <a href="kriteria.php" class="alert-link">Kriteria</a>.
                            </div>
                        <?php else: ?>
                            <form method="POST" action="alternatif.php">
                                <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'add' ?>">
                                <?php if ($editItem): ?>
                                    <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="form-label">Kode Alternatif</label>
                                    <input type="text" name="kode" class="form-control" placeholder="A1, A2, dst." required
                                           pattern="[A-Za-z0-9_\-]+" title="Huruf dan angka saja"
                                           value="<?= Helpers::e($editItem['kode'] ?? ('A' . (count($alternatives) + 1))) ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nama Mahasiswa</label>
                                    <input type="text" name="nama" class="form-control" placeholder="Nama lengkap mahasiswa" required
                                           value="<?= Helpers::e($editItem['nama'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Keterangan (NIM / Prodi)</label>
                                    <input type="text" name="keterangan" class="form-control" placeholder="NIM: 22025xxx | S1 Teknik Informatika"
                                           value="<?= Helpers::e($editItem['keterangan'] ?? '') ?>">
                                </div>

                                <hr class="my-2" style="border-color: #e2e8f0;">
                                <div class="fw-bold mb-2" style="color: var(--text-dark); font-size: 0.85rem;">
                                    <i class="fa-solid fa-sliders me-1" style="color: var(--green-600);"></i> Nilai Kriteria
                                </div>

                                <div class="row g-2 mb-3">
                                    <?php foreach ($criteria as $c): 
                                        $cId = (int)$c['id'];
                                        $rawVal = $editItem ? ($editScores[$cId] ?? 0) : '';
                                        if ($editItem && $rawVal !== '') {
                                            $fVal = (float)$rawVal;
                                            $displayVal = ($fVal == (int)$fVal) ? (string)(int)$fVal : (string)round($fVal, 2);
                                        } else {
                                            $displayVal = '';
                                        }
                                    ?>
                                        <div class="col-6 mb-1">
                                            <label class="form-label d-flex justify-content-between align-items-center mb-1">
                                                <span style="font-size: 0.78rem;"><?= Helpers::e($c['kode']) ?>: <?= Helpers::e($c['nama']) ?></span>
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="0.01" min="0" max="100" name="scores[<?= $cId ?>]" class="form-control font-monospace" required
                                                       placeholder="0" value="<?= Helpers::e($displayVal) ?>">
                                                <span class="input-group-text" style="font-size: 0.65rem; background: <?= strtolower($c['jenis']) === 'cost' ? 'var(--gold-100)' : 'var(--green-100)' ?>; color: <?= strtolower($c['jenis']) === 'cost' ? '#92400e' : 'var(--green-800)' ?>; border-color: #d1d5db;">
                                                    <?= strtoupper(substr($c['jenis'], 0, 1)) ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100 rounded-pill btn-sm">
                                        <i class="fa-solid fa-floppy-disk me-1"></i> <?= $editItem ? 'Simpan Perubahan' : 'Tambahkan' ?>
                                    </button>
                                    <?php if ($editItem): ?>
                                        <a href="alternatif.php" class="btn btn-secondary rounded-pill px-3 btn-sm">Batal</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Panel Khusus Role User (Reviewer) -->
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="fa-solid fa-shield-halved me-1" style="color: var(--green-600);"></i> Peran Pengguna: Reviewer
                    </div>
                    <div class="p-3">
                        <div class="alert alert-success py-2 px-3 small border-0 mb-3" style="background: #ecfdf5; color: #065f46; border-radius: 8px;">
                            <i class="fa-solid fa-user-check me-1"></i> Anda login sebagai <strong>Reviewer / Penilai</strong>.
                        </div>
                        <p class="small text-muted mb-2" style="font-size: 0.8rem; line-height: 1.5;">
                            Anda memiliki izin <strong>Read-Only</strong> untuk meninjau seluruh data kandidat mahasiswa dan capaian nilai portofolio pada tabel matriks penilaian di samping.
                        </p>
                        <p class="small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            Untuk mendaftarkan mahasiswa baru, mengubah nilai capaian, atau menghapus kandidat, diperlukan hak akses dari akun <strong>Administrator</strong>.
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card-custom p-3 mt-3" style="border-left: 4px solid var(--gold-500);">
                <div class="fw-bold mb-1" style="color: var(--text-dark); font-size: 0.85rem;">
                    <i class="fa-solid fa-circle-question me-1" style="color: var(--gold-600);"></i> Informasi Penilaian
                </div>
                <div class="small text-muted" style="font-size: 0.78rem;">
                    Skor portofolio mengacu pada rubrik Capaian Unggulan (CU) dan IPK PILMAPRES UNIPOL 2026. Data pada tabel ini menjadi matriks keputusan ($X$) yang dihitung pada menu Perhitungan.
                </div>
            </div>
        </div>

        <!-- Tabel Matriks Penilaian -->
        <div class="col-lg-8">
            <div class="card-custom">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-table-cells me-1" style="color: var(--green-600);"></i> Matriks Penilaian Mahasiswa</span>
                    <span class="badge px-2 py-1" style="background: var(--green-100); color: var(--green-700); font-size: 0.7rem;"><?= count($alternatives) ?> Mahasiswa</span>
                </div>
                <div class="p-0">
                    <div class="table-responsive">
                        <table class="table table-custom table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th style="width: 55px;">Kode</th>
                                    <th>Nama Mahasiswa</th>
                                    <?php foreach ($criteria as $c): ?>
                                        <th class="text-center font-monospace" style="min-width: 55px;" title="<?= Helpers::e($c['nama']) ?>">
                                            <?= Helpers::e($c['kode']) ?>
                                            <div style="font-size: 0.6rem; color: <?= strtolower($c['jenis']) === 'cost' ? '#dc2626' : 'var(--green-600)' ?>;">
                                                (<?= strtoupper(substr($c['jenis'], 0, 1)) ?>)
                                            </div>
                                        </th>
                                    <?php endforeach; ?>
                                    <?php if ($isAdmin): ?>
                                        <th style="width: 80px;" class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($alternatives)): ?>
                                    <tr>
                                        <td colspan="<?= count($criteria) + ($isAdmin ? 4 : 3) ?>" class="text-center py-4 text-muted small">
                                            Belum ada data mahasiswa kandidat.
                                        </td>
                                    </tr>
                                <?php else: 
                                    $no = 1;
                                    foreach ($alternatives as $alt): 
                                        $altId = (int)$alt['id'];
                                    ?>
                                        <tr>
                                            <td class="text-center text-muted"><?= $no++ ?></td>
                                            <td><span class="badge bg-light text-dark border font-monospace fw-bold" style="font-size: 0.72rem;"><?= Helpers::e($alt['kode']) ?></span></td>
                                            <td>
                                                <div class="fw-semibold" style="color: var(--text-dark); font-size: 0.83rem;"><?= Helpers::e($alt['nama']) ?></div>
                                                <?php if (!empty($alt['keterangan'])): ?>
                                                    <div class="small text-muted text-truncate" style="max-width: 220px; font-size: 0.7rem;"><?= Helpers::e($alt['keterangan']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <?php foreach ($criteria as $c): 
                                                $cId = (int)$c['id'];
                                                $scoreVal = $scoreMatrix[$altId][$cId] ?? 0.0;
                                                $displayScore = ($scoreVal == (int)$scoreVal) ? (int)$scoreVal : number_format($scoreVal, 2);
                                            ?>
                                                <td class="text-center font-monospace" style="font-size: 0.83rem;">
                                                    <?= $displayScore ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <?php if ($isAdmin): ?>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="alternatif.php?edit=<?= $altId ?>" class="btn btn-outline-warning btn-sm" title="Edit" style="font-size: 0.72rem;">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                        <a href="alternatif.php?hapus=<?= $altId ?>" class="btn btn-outline-danger btn-sm" title="Hapus" style="font-size: 0.72rem;"
                                                           onclick="return confirm('Hapus data <?= Helpers::e($alt['nama']) ?>?')">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
