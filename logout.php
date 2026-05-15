<?php
// =============================================
// logout.php — Cierre de Sesión Completo
// RF-2: Los 3 pasos: vaciar, cookie, destruir
// =============================================

session_start();

// 1. Vaciar el array de sesión
$_SESSION = [];

// 2. Eliminar cookie de sesión
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 3. Destruir la sesión
session_destroy();

// Redirigir al login
header('Location: login.php');
exit;