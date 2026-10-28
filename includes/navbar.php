<?php
/**
 * Navigation Bar Component
 */

declare(strict_types=1);

$activePage = $activePage ?? '';
$currentUser = \Core\Auth::user();
?>
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container-fluid" style="max-width: 1320px; margin: 0 auto; padding-left: 2rem; padding-right: 2rem;">
        <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
            <img src="img/logo.png" alt="Logo UNIPOL" style="width: 36px; height: 36px; object-fit: contain; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.3));">
            <div class="d-flex flex-column lh-1">
                <span class="fw-bold text-white" style="font-size: 0.92rem;">PILMAPRES UNIPOL</span>
                <span style="font-size: 0.65rem; color: #fde047; font-weight: 600;">Universitas Lamappapoleonro Soppeng</span>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">
                        <i class="fa-solid fa-gauge-high me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'kriteria' ? 'active' : '' ?>" href="kriteria.php">
                        <i class="fa-solid fa-list-check me-1"></i> Kriteria
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'alternatif' ? 'active' : '' ?>" href="alternatif.php">
                        <i class="fa-solid fa-user-graduate me-1"></i> Mahasiswa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'perhitungan' ? 'active' : '' ?>" href="perhitungan.php">
                        <i class="fa-solid fa-calculator me-1"></i> Perhitungan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'cetak' ? 'active' : '' ?>" href="cetak.php" target="_blank">
                        <i class="fa-solid fa-print me-1"></i> Cetak
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if ($currentUser): ?>
                    <span class="d-none d-md-inline-flex align-items-center gap-2" style="color: rgba(255,255,255,0.7); font-size: 0.8rem;">
                        <i class="fa-regular fa-circle-user" style="color: #fde047;"></i>
                        <span class="text-white fw-semibold"><?= htmlspecialchars($currentUser['nama'] ?: $currentUser['username']) ?></span>
                        <?php if (($currentUser['role'] ?? 'user') === 'admin'): ?>
                            <span class="badge" style="background: #facc15; color: #064e3b; font-size: 0.65rem; font-weight: 700;">Admin</span>
                        <?php else: ?>
                            <span class="badge" style="background: rgba(255,255,255,0.2); color: #fef08a; font-size: 0.65rem; font-weight: 600;">Reviewer (Read-Only)</span>
                        <?php endif; ?>
                    </span>
                    <a href="logout.php" class="btn btn-outline-warning btn-sm rounded-pill px-3" style="font-size: 0.8rem;" 
                       onclick="return confirm('Apakah Anda yakin ingin logout?')">
                        <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="index.php" class="btn btn-primary btn-sm rounded-pill px-3" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php
$flash = \Core\Helpers::getFlash();
if ($flash):
    foreach ($flash as $type => $msg):
        $alertClass = ($type === 'error' || $type === 'danger') ? 'alert-danger' : (($type === 'warning') ? 'alert-warning' : 'alert-success');
?>
<div style="max-width: 1320px; margin: 0.75rem auto 0; padding: 0 1rem;">
    <div class="alert <?= $alertClass ?> alert-dismissible fade show mb-0" role="alert" style="border-radius: 10px; font-size: 0.875rem;">
        <i class="fa-solid fa-circle-info me-1"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size: 0.7rem;"></button>
    </div>
</div>
<?php 
    endforeach;
endif; 
?>
