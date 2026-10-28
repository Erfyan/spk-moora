<?php
/**
 * Manajemen Kriteria & Pembobotan
 * PILMAPRES UNIPOL 2026 - Tema: Hijau - Kuning
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

use Core\Auth;
use Core\Database;
use Core\Helpers;

Auth::requireAuth();

$isAdmin = Auth::isAdmin();

// ==========================================
// 1. Action: Tambah Kriteria
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!$isAdmin) {
        Helpers::setFlash('danger', 'Akses Ditolak: Anda login sebagai Reviewer (Read-Only). Hanya Administrator yang dapat menambah kriteria.');
        Helpers::redirect('kriteria.php');
    }

    $kode   = strtoupper(trim((string)($_POST['kode'] ?? '')));
    $nama   = trim((string)($_POST['nama'] ?? ''));
    $bobotRaw = trim((string)($_POST['bobot'] ?? ''));
    $jenis  = strtolower(trim((string)($_POST['jenis'] ?? 'benefit')));
    $urutan = (int)($_POST['urutan'] ?? 1);

    if ($kode === '' || !preg_match('/^[A-Z0-9_\-]+$/', $kode)) {
        Helpers::setFlash('danger', 'Kode kriteria wajib diisi huruf/angka tanpa spasi (contoh: C1, C2).');
    } elseif ($nama === '') {
        Helpers::setFlash('danger', 'Nama kriteria wajib diisi.');
    } elseif (!is_numeric($bobotRaw) || (float)$bobotRaw <= 0) {
        Helpers::setFlash('danger', 'Nilai bobot harus berupa angka positif lebih dari 0.');
    } else {
        $bobot = (float)$bobotRaw;
        $exists = Database::fetchOne("SELECT id FROM kriteria WHERE kode = :kode", [':kode' => $kode]);
        if ($exists) {
            Helpers::setFlash('danger', "Kode kriteria '{$kode}' sudah ada dalam sistem.");
        } else {
            Database::execute(
                "INSERT INTO kriteria (kode, nama, bobot, jenis, urutan) VALUES (:kode, :nama, :bobot, :jenis, :urutan)",
                [
                    ':kode'   => $kode,
                    ':nama'   => $nama,
                    ':bobot'  => $bobot,
                    ':jenis'  => in_array($jenis, ['benefit', 'cost']) ? $jenis : 'benefit',
                    ':urutan' => max(1, $urutan)
                ]
            );
            Helpers::setFlash('success', "Kriteria [{$kode}] {$nama} berhasil ditambahkan.");
            Helpers::redirect('kriteria.php');
        }
    }
}

// ==========================================
// 2. Action: Update Kriteria
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!$isAdmin) {
        Helpers::setFlash('danger', 'Akses Ditolak: Anda login sebagai Reviewer (Read-Only). Hanya Administrator yang dapat memperbarui kriteria.');
        Helpers::redirect('kriteria.php');
    }

    $id       = (int)($_POST['id'] ?? 0);
    $kode     = strtoupper(trim((string)($_POST['kode'] ?? '')));
    $nama     = trim((string)($_POST['nama'] ?? ''));
    $bobotRaw = trim((string)($_POST['bobot'] ?? ''));
    $jenis    = strtolower(trim((string)($_POST['jenis'] ?? 'benefit')));
    $urutan   = (int)($_POST['urutan'] ?? 1);

    if ($id <= 0) {
        Helpers::setFlash('danger', 'ID kriteria tidak valid.');
    } elseif ($kode === '' || !preg_match('/^[A-Z0-9_\-]+$/', $kode)) {
        Helpers::setFlash('danger', 'Kode kriteria harus berupa huruf/angka (contoh: C1).');
    } elseif ($nama === '') {
        Helpers::setFlash('danger', 'Nama kriteria tidak boleh kosong.');
    } elseif (!is_numeric($bobotRaw) || (float)$bobotRaw <= 0) {
        Helpers::setFlash('danger', 'Nilai bobot harus berupa angka positif lebih dari 0.');
    } else {
        $bobot = (float)$bobotRaw;
        $exists = Database::fetchOne("SELECT id FROM kriteria WHERE kode = :kode AND id != :id", [
            ':kode' => $kode,
            ':id'   => $id
        ]);
        if ($exists) {
            Helpers::setFlash('danger', "Kode kriteria '{$kode}' sudah digunakan kriteria lain.");
        } else {
            Database::execute(
                "UPDATE kriteria SET kode = :kode, nama = :nama, bobot = :bobot, jenis = :jenis, urutan = :urutan WHERE id = :id",
                [
                    ':kode'   => $kode,
                    ':nama'   => $nama,
                    ':bobot'  => $bobot,
                    ':jenis'  => in_array($jenis, ['benefit', 'cost']) ? $jenis : 'benefit',
                    ':urutan' => max(1, $urutan),
                    ':id'     => $id
                ]
            );
            Helpers::setFlash('success', "Kriteria [{$kode}] berhasil diperbarui.");
            Helpers::redirect('kriteria.php');
        }
    }
}

// ==========================================
// 3. Action: Hapus Kriteria
// ==========================================
if (isset($_GET['hapus'])) {
    if (!$isAdmin) {
        Helpers::setFlash('danger', 'Akses Ditolak: Anda login sebagai Reviewer (Read-Only). Hanya Administrator yang dapat menghapus kriteria.');
        Helpers::redirect('kriteria.php');
    }

    $id = (int)$_GET['hapus'];
    if ($id > 0) {
        Database::execute("DELETE FROM kriteria WHERE id = :id", [':id' => $id]);
        Helpers::setFlash('success', 'Kriteria berhasil dihapus beserta skor terkait.');
    }
    Helpers::redirect('kriteria.php');
}

// ==========================================
// 4. Action: Reset ke Data Benchmark UNIPOL
// ==========================================
if (isset($_GET['reset_benchmark'])) {
    if (!$isAdmin) {
        Helpers::setFlash('danger', 'Akses Ditolak: Anda login sebagai Reviewer (Read-Only). Hanya Administrator yang dapat mereset database.');
        Helpers::redirect('kriteria.php');
    }

    Database::execute("DELETE FROM kriteria");
    Database::execute("DELETE FROM alternatif");
    Database::execute("DELETE FROM nilai_alternatif");
    \initDatabase(Database::getConnection());
    Helpers::setFlash('success', 'Database berhasil di-reset ke standar PILMAPRES UNIPOL 2026.');
    Helpers::redirect('kriteria.php');
}

// Ambil data kriteria
$kriterias = Database::fetchAll("SELECT * FROM kriteria ORDER BY urutan ASC, kode ASC");

// Hitung total bobot dan bobot normalisasi
$totalBobot = 0.0;
foreach ($kriterias as $k) {
    $totalBobot += (float)$k['bobot'];
}

$editItem = null;
if ($isAdmin && isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editItem = Database::fetchOne("SELECT * FROM kriteria WHERE id = :id", [':id' => $editId]);
}

$pageTitle = 'Kriteria Penilaian - PILMAPRES UNIPOL 2026';
$activePage = 'kriteria';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <h4>
                <i class="fa-solid fa-list-check me-1" style="color: var(--green-600);"></i>
                Kriteria Penilaian
            </h4>
            <p>Parameter dan bobot evaluasi capaian unggulan PILMAPRES UNIPOL 2026</p>
        </div>
        <div>
            <?php if ($isAdmin): ?>
                <a href="kriteria.php?reset_benchmark=1" class="btn btn-outline-warning rounded-pill px-3 btn-sm" 
                   onclick="return confirm('Reset akan mengembalikan kriteria dan alternatif ke pedoman baku PILMAPRES UNIPOL 2026. Lanjutkan?')">
                    <i class="fa-solid fa-rotate-left me-1"></i> Reset Data Pedoman
                </a>
            <?php else: ?>
                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #065f46; font-size: 0.78rem; padding: 0.45rem 0.85rem; border-radius: 9999px;">
                    <i class="fa-solid fa-eye me-1"></i> Mode Reviewer (Read-Only)
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3">
        <!-- Kolom Kiri: Form untuk Admin, atau Info Panel untuk User -->
        <div class="col-lg-4">
            <?php if ($isAdmin): ?>
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="fa-solid <?= $editItem ? 'fa-pen-to-square' : 'fa-circle-plus' ?> me-1" style="color: <?= $editItem ? 'var(--gold-500)' : 'var(--green-600)' ?>;"></i>
                        <?= $editItem ? 'Edit Kriteria' : 'Tambah Kriteria Baru' ?>
                    </div>
                    <div class="p-3">
                        <form method="POST" action="kriteria.php">
                            <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'add' ?>">
                            <?php if ($editItem): ?>
                                <input type="hidden" name="id" value="<?= (int)$editItem['id'] ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Kode Kriteria</label>
                                <input type="text" name="kode" class="form-control" placeholder="Contoh: C1, C2" required
                                       pattern="[A-Za-z0-9_\-]+" title="Huruf dan angka saja"
                                       value="<?= Helpers::e($editItem['kode'] ?? ('C' . (count($kriterias) + 1))) ?>">
                                <div class="form-text text-muted">Format: C1, C2, C3, dst.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Kriteria</label>
                                <input type="text" name="nama" class="form-control" placeholder="Contoh: CU Bidang Kompetisi" required
                                       value="<?= Helpers::e($editItem['nama'] ?? '') ?>">
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Bobot</label>
                                    <?php 
                                    $bobotVal = '';
                                    if ($editItem && isset($editItem['bobot'])) {
                                        $bobotFloat = (float)$editItem['bobot'];
                                        $bobotVal = $bobotFloat == (int)$bobotFloat ? (string)(int)$bobotFloat : (string)round($bobotFloat, 2);
                                    }
                                    ?>
                                    <input type="number" step="0.01" min="0.01" max="100" name="bobot" class="form-control font-monospace" placeholder="20" required
                                           value="<?= Helpers::e($bobotVal) ?>">
                                    <div class="form-text text-muted">Angka (misal: 20)</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Sifat</label>
                                    <select name="jenis" class="form-select" required>
                                        <option value="benefit" <?= (($editItem['jenis'] ?? 'benefit') === 'benefit') ? 'selected' : '' ?>>Benefit (Maksimal)</option>
                                        <option value="cost" <?= (($editItem['jenis'] ?? '') === 'cost') ? 'selected' : '' ?>>Cost (Minimal)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nomor Urut</label>
                                <input type="number" name="urutan" min="1" step="1" class="form-control" placeholder="1"
                                       value="<?= isset($editItem['urutan']) ? (int)$editItem['urutan'] : (count($kriterias) + 1) ?>">
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 rounded-pill btn-sm">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> <?= $editItem ? 'Simpan Perubahan' : 'Tambahkan' ?>
                                </button>
                                <?php if ($editItem): ?>
                                    <a href="kriteria.php" class="btn btn-secondary rounded-pill px-3 btn-sm">Batal</a>
                                <?php endif; ?>
                            </div>
                        </form>
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
                            Dalam mode Reviewer, Anda memiliki izin <strong>Read-Only</strong> untuk mempelajari struktur pembobotan instrumen seleksi dan mengamati parameter evaluasi.
                        </p>
                        <p class="small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            Perubahan bobot, penambahan kriteria baru, atau reset database dibatasi khusus untuk akun <strong>Administrator</strong>.
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Panduan Singkat Sifat Kriteria -->
            <div class="card-custom p-3 mt-3" style="border-left: 4px solid var(--green-600);">
                <div class="fw-bold mb-2" style="color: var(--text-dark); font-size: 0.85rem;">
                    <i class="fa-solid fa-circle-info me-1" style="color: var(--green-600);"></i> Panduan Sifat Kriteria
                </div>
                <div class="small text-muted mb-1" style="font-size: 0.78rem;">
                    <strong style="color: var(--green-700);"><i class="fa-solid fa-arrow-up-long me-1"></i>Benefit (Keuntungan):</strong> Nilai lebih besar semakin diprioritaskan (misal: Prestasi Lomba, IPK).
                </div>
                <div class="small text-muted" style="font-size: 0.78rem;">
                    <strong style="color: var(--gold-600);"><i class="fa-solid fa-arrow-down-long me-1"></i>Cost (Biaya/Beban):</strong> Nilai lebih kecil semakin baik (misal: Poin Pelanggaran).
                </div>
            </div>
        </div>

        <!-- Tabel Data Kriteria -->
        <div class="col-lg-8">
            <div class="card-custom">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-table-list me-1" style="color: var(--green-600);"></i> Daftar Kriteria Penilaian</span>
                    <span class="badge px-2 py-1" style="background: var(--green-100); color: var(--green-700); font-size: 0.7rem;"><?= count($kriterias) ?> Kriteria</span>
                </div>
                <div class="p-0">
                    <div class="table-responsive">
                        <table class="table table-custom table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th style="width: 60px;">Kode</th>
                                    <th>Nama Kriteria</th>
                                    <th class="text-center" style="width: 75px;">Bobot</th>
                                    <th class="text-center" style="width: 95px;">Bobot Relatif</th>
                                    <th class="text-center" style="width: 85px;">Sifat</th>
                                    <?php if ($isAdmin): ?>
                                        <th style="width: 85px;" class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($kriterias)): ?>
                                    <tr>
                                        <td colspan="<?= $isAdmin ? 7 : 6 ?>" class="text-center py-4 text-muted small">
                                            Belum ada kriteria penilaian.
                                        </td>
                                    </tr>
                                <?php else: 
                                    $no = 1;
                                    $sumNorm = 0.0;
                                    foreach ($kriterias as $k): 
                                        $normWeight = $totalBobot > 0 ? ((float)$k['bobot'] / $totalBobot) : 0;
                                        $sumNorm += $normWeight;
                                        $displayBobot = (float)$k['bobot'] == (int)$k['bobot'] ? (int)$k['bobot'] : number_format((float)$k['bobot'], 2);
                                    ?>
                                        <tr>
                                            <td class="text-center text-muted"><?= $no++ ?></td>
                                            <td><span class="badge bg-light text-dark border font-monospace fw-bold" style="font-size: 0.75rem;"><?= Helpers::e($k['kode']) ?></span></td>
                                            <td class="fw-semibold" style="color: var(--text-dark); font-size: 0.83rem;"><?= Helpers::e($k['nama']) ?></td>
                                            <td class="text-center font-monospace fw-bold"><?= $displayBobot ?>%</td>
                                            <td class="text-center font-monospace fw-bold" style="color: var(--green-700);">
                                                <?= number_format($normWeight, 4) ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (strtolower($k['jenis']) === 'cost'): ?>
                                                    <span class="badge badge-cost"><i class="fa-solid fa-arrow-down-long me-1"></i>Cost</span>
                                                <?php else: ?>
                                                    <span class="badge badge-benefit"><i class="fa-solid fa-arrow-up-long me-1"></i>Benefit</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php if ($isAdmin): ?>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="kriteria.php?edit=<?= $k['id'] ?>" class="btn btn-outline-warning btn-sm" title="Edit" style="font-size: 0.72rem;">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                        <a href="kriteria.php?hapus=<?= $k['id'] ?>" class="btn btn-outline-danger btn-sm" title="Hapus" style="font-size: 0.72rem;"
                                                           onclick="return confirm('Hapus kriteria <?= Helpers::e($k['kode']) ?>? Seluruh nilai terkait akan ikut terhapus.')">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr style="background: #f8faf8;">
                                        <td colspan="3" class="text-end fw-bold small" style="color: var(--text-dark);">Total Bobot:</td>
                                        <td class="text-center font-monospace fw-bold" style="color: var(--text-dark);"><?= (float)$totalBobot == (int)$totalBobot ? (int)$totalBobot : number_format((float)$totalBobot, 2) ?>%</td>
                                        <td class="text-center font-monospace fw-bold" style="color: var(--green-700);"><?= number_format($sumNorm, 4) ?></td>
                                        <?php if ($isAdmin): ?>
                                            <td></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
