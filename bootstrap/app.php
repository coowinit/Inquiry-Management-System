<?php

declare(strict_types=1);

$appConfig = require __DIR__ . '/../config/app.php';
$dbConfig  = require __DIR__ . '/../config/database.php';

date_default_timezone_set($appConfig['timezone'] ?? 'UTC');

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

require_once __DIR__ . '/../app/Helpers/helpers.php';

use App\Core\Database;
use App\Core\Session;

Session::start($appConfig['session_name'] ?? 'ims_session');
Database::init($dbConfig);
