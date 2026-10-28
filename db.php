<?php
/**
 * Database & Application Bootstrap (Backward-Compatible & Modular)
 * Menggunakan SQLite PDO
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Helpers.php';
require_once __DIR__ . '/core/MooraEngine.php';

$pdo = \Core\Database::getConnection();
