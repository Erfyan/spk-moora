<?php
/**
 * Header Layout Reusable
 * Sistem Pendukung Keputusan PILMAPRES UNIPOL (Metode MOORA)
 * Tema: Hijau - Kuning (Emerald & Gold)
 */

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'SPK Pemilihan Mahasiswa Berprestasi (PILMAPRES) - Metode MOORA';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/logo.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <!-- Custom Theme Styling: Hijau - Kuning (Emerald & Gold) -->
    <style>
        :root {
            --green-900: #064e3b;
            --green-800: #065f46;
            --green-700: #047857;
            --green-600: #059669;
            --green-500: #10b981;
            --green-400: #34d399;
            --green-100: #d1fae5;
            --green-50: #ecfdf5;
            
            --gold-600: #ca8a04;
            --gold-500: #eab308;
            --gold-400: #facc15;
            --gold-300: #fde047;
            --gold-200: #fef08a;
            --gold-100: #fef9c3;
            --gold-50: #fefce8;
            
            --bg-body: #f0f5f1;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --text-dark: #1e293b;
            --text-body: #475569;
            --text-muted: #94a3b8;
        }

        * { box-sizing: border-box; }

        html {
            overflow-x: hidden;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-body);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* ========== NAVBAR ========== */
        .navbar-custom {
            background: linear-gradient(135deg, var(--green-900) 0%, var(--green-800) 50%, var(--green-700) 100%);
            border-bottom: 3px solid var(--gold-500);
            box-shadow: 0 2px 16px rgba(6, 78, 59, 0.2);
            padding: 0.5rem 0;
        }

        .navbar-custom .navbar-brand {
            font-weight: 700;
            color: #fff;
            font-size: 1rem;
        }

        .navbar-custom .nav-link {
            color: rgba(255,255,255,0.85);
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .navbar-custom .nav-link:hover {
            color: var(--gold-200);
            background: rgba(255,255,255,0.08);
        }

        .navbar-custom .nav-link.active {
            color: var(--gold-200);
            background: rgba(234,179,8,0.18);
            font-weight: 600;
        }

        .navbar-toggler { border: none; padding: 0.4rem; }
        .navbar-toggler:focus { box-shadow: none; }
        .navbar-toggler i { color: #fff; font-size: 1.2rem; }

        /* ========== MAIN CONTENT WRAPPER ========== */
        .main-content {
            width: 100%;
            max-width: 1320px;
            margin: 0 auto;
            padding: 1.5rem 2rem;
            flex: 1;
        }

        /* ========== CARDS ========== */
        .card-custom {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            box-shadow: 0 1px 8px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        .card-header-custom {
            background: #f8faf8;
            border-bottom: 1px solid #e8f0e8;
            padding: 0.9rem 1.15rem;
            font-weight: 700;
            font-size: 0.925rem;
            color: var(--text-dark);
        }

        /* ========== BUTTONS (Green-Gold theme) ========== */
        .btn-primary,
        .btn-success {
            background: linear-gradient(135deg, var(--green-700) 0%, var(--green-600) 100%);
            border: 1px solid var(--green-700);
            color: #fff;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .btn-primary:hover, .btn-primary:focus,
        .btn-success:hover, .btn-success:focus {
            background: linear-gradient(135deg, var(--green-800) 0%, var(--green-700) 100%);
            border-color: var(--green-800);
            color: #fff;
            box-shadow: 0 3px 10px rgba(5,150,105,0.25);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--gold-600) 0%, var(--gold-500) 100%);
            border: 1px solid var(--gold-600);
            color: #fff;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #a16207 0%, var(--gold-600) 100%);
            color: #fff;
        }

        .btn-outline-primary,
        .btn-outline-success {
            color: var(--green-700);
            border-color: var(--green-600);
            font-weight: 600;
            font-size: 0.875rem;
        }

        .btn-outline-primary:hover,
        .btn-outline-success:hover {
            background-color: var(--green-700);
            border-color: var(--green-700);
            color: #fff;
        }

        .btn-outline-warning {
            color: var(--gold-600);
            border-color: var(--gold-500);
            font-weight: 600;
            font-size: 0.875rem;
        }

        .btn-outline-warning:hover {
            background-color: var(--gold-500);
            border-color: var(--gold-500);
            color: #fff;
        }

        /* ========== FORM INPUTS ========== */
        .form-control, .form-select {
            border-radius: 8px;
            font-size: 0.9rem;
            border: 1px solid #d1d5db;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--green-600);
            box-shadow: 0 0 0 3px rgba(16,185,129,0.15);
        }

        .form-label {
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.825rem;
            margin-bottom: 0.3rem;
        }

        .form-text {
            font-size: 0.75rem;
        }

        /* ========== TABLES ========== */
        .table-custom {
            margin-bottom: 0;
            width: 100%;
            font-size: 0.85rem;
        }

        .table-custom thead th {
            background-color: var(--green-50);
            color: var(--green-900);
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 2px solid var(--green-100);
            padding: 0.7rem 0.6rem;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table-custom tbody td {
            padding: 0.65rem 0.6rem;
            vertical-align: middle;
            color: var(--text-body);
        }

        .table-custom tbody tr:hover {
            background-color: var(--green-50);
        }

        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }

        /* ========== BADGES ========== */
        .badge-benefit {
            background-color: var(--green-100);
            color: var(--green-800);
            border: 1px solid var(--green-400);
            font-weight: 600;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
        }

        .badge-cost {
            background-color: var(--gold-100);
            color: #92400e;
            border: 1px solid var(--gold-400);
            font-weight: 600;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
        }

        .rank-badge {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .rank-1 { background: var(--gold-200); color: #854d0e; border: 2px solid var(--gold-500); }
        .rank-2 { background: #e2e8f0; color: #334155; border: 2px solid #cbd5e1; }
        .rank-3 { background: #fed7aa; color: #9a3412; border: 2px solid #fdba74; }
        .rank-other { background: #f1f5f9; color: #64748b; }

        /* ========== STAT CARDS ========== */
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .stat-icon-green { background: var(--green-100); color: var(--green-700); }
        .stat-icon-gold { background: var(--gold-100); color: var(--gold-600); }

        /* ========== PAGE HEADER ========== */
        .page-header {
            margin-bottom: 1.25rem;
        }

        .page-header h4 {
            font-weight: 700;
            font-size: 1.15rem;
            color: var(--text-dark);
            margin-bottom: 0.15rem;
        }

        .page-header p {
            font-size: 0.825rem;
            color: var(--text-muted);
            margin-bottom: 0;
        }

        /* ========== STEP INDICATOR ========== */
        .step-circle {
            width: 28px;
            height: 28px;
            min-width: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
        }

        /* ========== HELPER ========== */
        .info-box {
            background: var(--green-50);
            border-left: 4px solid var(--green-600);
            padding: 0.75rem 1rem;
            border-radius: 0 8px 8px 0;
            font-size: 0.825rem;
            color: var(--text-body);
            margin-bottom: 1rem;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 991.98px) {
            .main-content {
                padding: 1.25rem 1.5rem;
            }
        }

        @media (max-width: 767.98px) {
            .main-content {
                padding: 1rem 1rem;
            }

            .page-header h4 {
                font-size: 1rem;
            }

            .table-custom {
                font-size: 0.78rem;
            }

            .table-custom thead th, .table-custom tbody td {
                padding: 0.5rem 0.4rem;
            }

            .card-header-custom {
                padding: 0.75rem 0.9rem;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 575.98px) {
            .main-content {
                padding: 0.75rem 0.75rem;
            }

            .table-custom {
                font-size: 0.72rem;
            }
        }

        /* ========== PRINT ========== */
        @media print {
            .navbar-custom, .btn-no-print, .alert-dismissible, .no-print {
                display: none !important;
            }
            body {
                background: #fff !important;
                font-size: 11pt;
            }
            .main-content {
                max-width: 100%;
                padding: 0;
            }
            .card-custom {
                border: 1px solid #000 !important;
                box-shadow: none !important;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
