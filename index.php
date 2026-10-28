<?php
/**
 * Halaman Login Sistem Pendukung Keputusan PILMAPRES UNIPOL (Metode MOORA)
 * Tema: Hijau - Kuning (Emerald & Gold)
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

use Core\Auth;
use Core\Helpers;

// Jika sudah login, langsung alihkan ke dashboard
if (Auth::check()) {
    Helpers::redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Silakan isi username dan password.';
    } else {
        if (Auth::login($username, $password)) {
            Helpers::redirect('dashboard.php');
        } else {
            $error = 'Username atau Password salah! Periksa kembali kredensial Anda.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SPK Pemilihan Mahasiswa Berprestasi (PILMAPRES) UNIPOL</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/logo.png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #022c22 0%, #064e3b 40%, #065f46 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
        }

        .login-card {
            background: rgba(6, 78, 59, 0.45);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(234, 179, 8, 0.35);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            width: 100%;
            max-width: 440px;
            padding: 2.5rem;
            color: #ffffff;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-4px);
            border-color: rgba(234, 179, 8, 0.6);
        }

        .login-header-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #d97706 0%, #eab308 100%);
            color: #064e3b;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 1.25rem auto;
            box-shadow: 0 10px 20px rgba(234, 179, 8, 0.35);
        }

        .form-control-custom {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 12px;
            color: #ffffff;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }

        .form-control-custom:focus {
            background: rgba(255, 255, 255, 0.18);
            border-color: #facc15;
            box-shadow: 0 0 0 4px rgba(234, 179, 8, 0.25);
            color: #ffffff;
        }

        .form-control-custom::placeholder {
            color: rgba(255, 255, 255, 0.55);
        }

        .btn-custom-login {
            background: linear-gradient(135deg, #15803d 0%, #16a34a 60%, #eab308 100%);
            border: 1px solid #facc15;
            border-radius: 12px;
            padding: 0.85rem;
            font-weight: 700;
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(21, 128, 61, 0.4);
            transition: all 0.2s ease;
        }

        .btn-custom-login:hover {
            opacity: 0.95;
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(234, 179, 8, 0.4);
            color: #ffffff;
        }

        .account-hint {
            background: rgba(0, 0, 0, 0.2);
            border: 1px dashed rgba(234, 179, 8, 0.4);
            border-radius: 12px;
            padding: 0.85rem;
            font-size: 0.825rem;
            color: rgba(255, 255, 255, 0.85);
        }

        .account-hint code {
            color: #fef08a;
            background: rgba(0, 0, 0, 0.3);
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center">
        <div class="mb-3">
            <img src="img/logo.png" alt="Logo UNIPOL" style="width: 84px; height: 84px; object-fit: contain; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.4));">
        </div>
        <h3 class="fw-bold mb-1 text-white">PILMAPRES UNIPOL</h3>
        <p class="text-warning small mb-3 fw-semibold">Universitas Lamappapoleonro Soppeng &bull; Metode MOORA</p>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger py-2 px-3 small border-0 mb-4" role="alert" style="background: rgba(239, 68, 68, 0.25); color: #fecaca; border-radius: 10px;">
            <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label small text-white-50 fw-semibold">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-0 text-warning position-absolute" style="z-index: 5; top: 9px; left: 6px;">
                    <i class="fa-regular fa-user"></i>
                </span>
                <input type="text" name="username" class="form-control form-control-custom ps-5" placeholder="Masukkan username" required autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small text-white-50 fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-0 text-warning position-absolute" style="z-index: 5; top: 9px; left: 6px;">
                    <i class="fa-solid fa-lock"></i>
                </span>
                <input type="password" name="password" class="form-control form-control-custom ps-5" placeholder="Masukkan password" required>
            </div>
        </div>

        <button type="submit" name="login" class="btn btn-custom-login w-100 mb-0">
            <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Masuk ke Sistem
        </button>
    </form>

    <div class="text-center mt-4 pt-3 border-top" style="border-color: rgba(255,255,255,0.15) !important; font-size: 0.75rem; color: rgba(255,255,255,0.65);">
        <span>&copy; <?= date('Y') ?> Universitas Lamappapoleonro Soppeng (UNIPOL)</span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
