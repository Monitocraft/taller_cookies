<?php
// =============================================
// db.php — Conexión a la Base de Datos
// =============================================

function env(string $key, ?string $default = null): ?string {
    $value = getenv($key);
    return $value === false ? $default : $value;
}

function parseDatabaseUrl(string $url): array {
    $parts = parse_url($url);
    if ($parts === false) {
        return [];
    }

    return [
        'host'     => $parts['host'] ?? null,
        'user'     => $parts['user'] ?? null,
        'pass'     => $parts['pass'] ?? null,
        'dbname'   => isset($parts['path']) ? ltrim($parts['path'], '/') : null,
    ];
}

$dbUrl = env('DATABASE_URL') ?? env('MYSQL_URL');
if ($dbUrl !== null) {
    $parsed = parseDatabaseUrl($dbUrl);
    define('DB_HOST', env('DB_HOST', $parsed['host'] ?? 'localhost'));
    define('DB_USER', env('DB_USER', $parsed['user'] ?? 'root'));
    define('DB_PASS', env('DB_PASS', $parsed['pass'] ?? ''));
    define('DB_NAME', env('DB_NAME', $parsed['dbname'] ?? 'taller_cookies'));
} else {
    define('DB_HOST', env('DB_HOST', 'localhost'));
    define('DB_USER', env('DB_USER', 'root'));
    define('DB_PASS', env('DB_PASS', ''));
    define('DB_NAME', env('DB_NAME', 'taller_cookies'));
}
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST
             . ";dbname=" . DB_NAME
             . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("DB Connection Error: " . $e->getMessage());
            die("Error: No se pudo conectar a la base de datos.");
        }
    }

    return $pdo;
}