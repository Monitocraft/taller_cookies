<?php
// =============================================
// dashboard.php — Panel Principal (Protegido)
// =============================================

session_start();
require_once 'db.php';

// ── Protección: verificar sesión ──────────────
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// ── Contador de visitas ───────────────────────
// ── Datos del usuario desde sesión ─────────────
$usuario_id = (int)$_SESSION['usuario_id'];
$nombre     = htmlspecialchars($_SESSION['nombre']);
$rol        = htmlspecialchars($_SESSION['rol']);
$inicio     = htmlspecialchars($_SESSION['inicio']);

// ── Contador de visitas únicas (una vez por login) ──
$pdo = getDB();
if (empty($_SESSION['visita_contada'])) {
    $pdo->prepare("UPDATE usuarios SET visitas = visitas + 1 WHERE usuario_id = :id")
        ->execute([':id' => $usuario_id]);
    $_SESSION['visita_contada'] = true;
}
$stmt = $pdo->prepare("SELECT visitas FROM usuarios WHERE usuario_id = :id");
$stmt->execute([':id' => $usuario_id]);
$visitas = (int)($stmt->fetchColumn() ?? 0);

// ── Roles disponibles en la BD ────────────────
$rolesDisponibles = ['admin', 'usuario'];

// ── Manejo de acciones AJAX ───────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    header('Content-Type: application/json');

    switch ($_POST['accion']) {

        // Cambiar rol
        case 'cambiar_rol':
            $nuevoRol = $_POST['rol'] ?? '';
            if (in_array($nuevoRol, $rolesDisponibles, true)) {
                $pdo  = getDB();
                $stmt = $pdo->prepare(
                    "UPDATE usuarios SET rol = :rol WHERE usuario_id = :id"
                );
                $stmt->execute([':rol' => $nuevoRol, ':id' => $usuario_id]);
                $_SESSION['rol'] = $nuevoRol;
                echo json_encode(['ok' => true, 'rol' => $nuevoRol]);
            } else {
                echo json_encode(['ok' => false, 'msg' => 'Rol inválido']);
            }
            exit;

        // Logout
        case 'logout':
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $p = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $p['path'], $p['domain'], $p['secure'], $p['httponly']
                );
            }
            session_destroy();
            echo json_encode(['ok' => true]);
            exit;
    }
}

// ── Preferencias de cookie (tema / idioma) ────
$tema   = $_COOKIE['tema']   ?? 'claro';
$idioma = $_COOKIE['idioma'] ?? 'es';

// Textos bilingüe
$t = [
    'es' => [
        'dashboard'       => 'Dashboard',
        'configuracion'   => 'Configuración',
        'bienvenida'      => 'Bienvenido',
        'tu_rol'          => 'Tu Rol',
        'inicio_sesion'   => 'Inicio de Sesión',
        'visitas'         => 'Visitas esta Sesión',
        'cambiar_rol'     => 'Cambiar Rol',
        'tema'            => 'Tema Visual',
        'claro'           => 'Claro',
        'oscuro'          => 'Oscuro',
        'idioma'          => 'Idioma',
        'es'              => 'Español',
        'en'              => 'English',
        'logout'          => 'Cerrar Sesión',
        'confirmar'       => '¿Cerrar sesión?',
        'confirmar_msg'   => '¿Estás seguro de que deseas cerrar tu sesión?',
        'si'              => 'Sí, cerrar sesión',
        'no'              => 'No, quedarme',
        'guardar'         => 'Guardar cambios',
        'guardado'        => '¡Cambios guardados!',
        'administrador'   => 'Administrador',
        'usuario_label'   => 'Usuario',
    ],
    'en' => [
        'dashboard'       => 'Dashboard',
        'configuracion'   => 'Settings',
        'bienvenida'      => 'Welcome',
        'tu_rol'          => 'Your Role',
        'inicio_sesion'   => 'Session Start',
        'visitas'         => 'Visits this Session',
        'cambiar_rol'     => 'Change Role',
        'tema'            => 'Visual Theme',
        'claro'           => 'Light',
        'oscuro'          => 'Dark',
        'idioma'          => 'Language',
        'es'              => 'Español',
        'en'              => 'English',
        'logout'          => 'Log Out',
        'confirmar'       => 'Log out?',
        'confirmar_msg'   => 'Are you sure you want to end your session?',
        'si'              => 'Yes, log out',
        'no'              => 'No, stay',
        'guardar'         => 'Save changes',
        'guardado'        => 'Changes saved!',
        'administrador'   => 'Administrator',
        'usuario_label'   => 'User',
    ],
];

$lang = isset($t[$idioma]) ? $idioma : 'es';
$tx   = $t[$lang];

// Etiqueta de rol bonita
$rolLabel = ($rol === 'admin') ? $tx['administrador'] : $tx['usuario_label'];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tx['dashboard'] ?> — Taller Cookies</title>

    <!-- CSS dinámico según tema -->
    <link rel="stylesheet" id="tema-css"
          href="assets/css/dashboard_<?= htmlspecialchars($tema) ?>.css">
    <link rel="stylesheet" href="assets/css/dashboard_base.css">
</head>
<body class="tema-<?= htmlspecialchars($tema) ?>">

<!-- ══════════════════════════════════════
     SIDEBAR
══════════════════════════════════════ -->
<aside class="sidebar" id="sidebar">

    <div class="sidebar-brand">
        <svg class="brand-icon" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="20" cy="20" r="19" stroke="currentColor" stroke-width="2"/>
            <path d="M13 20.5L18 25.5L27 15" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span class="brand-name">TallerApp</span>
    </div>

    <nav class="sidebar-nav" role="navigation">
        <button class="nav-item active" data-tab="dashboard" aria-current="page">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>
            </svg>
            <span><?= $tx['dashboard'] ?></span>
        </button>

        <button class="nav-item" data-tab="configuracion">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
            </svg>
            <span><?= $tx['configuracion'] ?></span>
        </button>
    </nav>

    <!-- Logout al final del sidebar -->
    <div class="sidebar-footer">
        <button class="btn-logout" id="btnLogout">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span><?= $tx['logout'] ?></span>
        </button>
    </div>
</aside>

<!-- ══════════════════════════════════════
     MAIN CONTENT
══════════════════════════════════════ -->
<main class="main-content">

    <!-- ── TAB: Dashboard ── -->
    <section class="tab-panel active" id="tab-dashboard">

        <header class="page-header">
            <div>
                <h2><?= $tx['bienvenida'] ?>, <span class="highlight"><?= $nombre ?></span></h2>
                <p class="page-subtitle"><?= date('l, d \d\e F \d\e Y') ?></p>
            </div>
        </header>

        <div class="cards-grid">

            <!-- Nombre -->
            <div class="card">
                <div class="card-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div class="card-body">
                    <span class="card-label"><?= $tx['bienvenida'] ?></span>
                    <span class="card-value"><?= $nombre ?></span>
                </div>
            </div>

            <!-- Rol -->
            <div class="card">
                <div class="card-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <div class="card-body">
                    <span class="card-label"><?= $tx['tu_rol'] ?></span>
                    <span class="card-value">
                        <?= $rolLabel ?>
                        <span class="badge badge-<?= $rol ?>"><?= $rol ?></span>
                    </span>
                </div>
            </div>

            <!-- Inicio sesión -->
            <div class="card">
                <div class="card-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="card-body">
                    <span class="card-label"><?= $tx['inicio_sesion'] ?></span>
                    <span class="card-value"><?= $inicio ?></span>
                </div>
            </div>

            <!-- Visitas -->
            <div class="card card-featured">
                <div class="card-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </div>
                <div class="card-body">
                    <span class="card-label"><?= $tx['visitas'] ?></span>
                    <span class="card-value card-value--big"><?= $visitas ?></span>
                </div>
            </div>

        </div>
    </section>

    <!-- ── TAB: Configuración ── -->
    <section class="tab-panel" id="tab-configuracion">

        <header class="page-header">
            <div>
                <h2><?= $tx['configuracion'] ?></h2>
                <p class="page-subtitle">Personaliza tu experiencia</p>
            </div>
        </header>

        <div class="settings-list">

            <!-- Cambiar rol -->
            <div class="setting-card">
                <div class="setting-info">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <div>
                        <strong><?= $tx['cambiar_rol'] ?></strong>
                        <small>Rol actual: <em id="rolActual"><?= $rol ?></em></small>
                    </div>
                </div>
                <div class="setting-control">
                    <select id="selectRol" class="select-input">
                        <?php foreach ($rolesDisponibles as $r): ?>
                            <option value="<?= $r ?>" <?= $r === $rol ? 'selected' : '' ?>>
                                <?= ucfirst($r) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn-save" id="btnGuardarRol"><?= $tx['guardar'] ?></button>
                </div>
            </div>

            <!-- Tema -->
            <div class="setting-card">
                <div class="setting-info">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"/>
                        <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                        <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                    </svg>
                    <div>
                        <strong><?= $tx['tema'] ?></strong>
                        <small id="temaLabel"><?= $tema === 'claro' ? $tx['claro'] : $tx['oscuro'] ?></small>
                    </div>
                </div>
                <div class="setting-control">
                    <span class="switch-label"><?= $tx['claro'] ?></span>
                    <label class="switch" aria-label="<?= $tx['tema'] ?>">
                        <input type="checkbox" id="switchTema" <?= $tema === 'oscuro' ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                    <span class="switch-label"><?= $tx['oscuro'] ?></span>
                </div>
            </div>

            <!-- Idioma -->
            <div class="setting-card">
                <div class="setting-info">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="2" y1="12" x2="22" y2="12"/>
                        <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
                    </svg>
                    <div>
                        <strong><?= $tx['idioma'] ?></strong>
                        <small id="idiomaLabel"><?= $lang === 'es' ? $tx['es'] : $tx['en'] ?></small>
                    </div>
                </div>
                <div class="setting-control">
                    <span class="switch-label"><?= $tx['es'] ?></span>
                    <label class="switch" aria-label="<?= $tx['idioma'] ?>">
                        <input type="checkbox" id="switchIdioma" <?= $lang === 'en' ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                    <span class="switch-label"><?= $tx['en'] ?></span>
                </div>
            </div>

        </div>

        <!-- Toast de confirmación -->
        <div class="toast" id="toast" role="status" aria-live="polite">
            <?= $tx['guardado'] ?>
        </div>

    </section>
</main>

<!-- ══════════════════════════════════════
     MODAL LOGOUT
══════════════════════════════════════ -->
<div class="modal-overlay" id="modalLogout" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal">
        <div class="modal-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
        </div>
        <h3 id="modalTitle"><?= $tx['confirmar'] ?></h3>
        <p><?= $tx['confirmar_msg'] ?></p>
        <div class="modal-actions">
            <button class="btn-modal btn-modal--cancel" id="btnCancelarLogout"><?= $tx['no'] ?></button>
            <button class="btn-modal btn-modal--confirm" id="btnConfirmarLogout"><?= $tx['si'] ?></button>
        </div>
    </div>
</div>

<!-- JS -->
<script>
    // Datos del servidor a JS
    window.APP = {
        tema:   <?= json_encode($tema) ?>,
        idioma: <?= json_encode($idioma) ?>
    };
</script>
<script src="assets/js/dashboard.js"></script>
</body>
</html>