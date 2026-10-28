<?php
/**
 * Logout Handler
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

\Core\Auth::logout();
\Core\Helpers::redirect('index.php');
