<?php
// =============================================
// seed_usuarios.php — Usuarios de Prueba
// EJECUTAR UNA SOLA VEZ y luego eliminar/proteger
// =============================================

require_once 'db.php';

$usuarios = [
    [
        'nombre'   => 'Administrador',
        'email'    => 'admin@taller.com',
        'password' => 'Admin1234!',
        'rol'      => 'admin',
    ],
    [
        'nombre'   => 'Juan Pérez',
        'email'    => 'juan@taller.com',
        'password' => 'Usuario123!',
        'rol'      => 'usuario',
    ],
    [
        'nombre'   => 'María García',
        'email'    => 'maria@taller.com',
        'password' => 'Maria456!',
        'rol'      => 'usuario',
    ],
];

$pdo  = getDB();
$stmt = $pdo->prepare(
    "INSERT INTO usuarios (nombre, email, password, rol)
     VALUES (:nombre, :email, :password, :rol)
     ON DUPLICATE KEY UPDATE nombre = nombre"  // Evita duplicados
);

echo "<pre>\n";
echo "=== Insertando usuarios de prueba ===\n\n";

foreach ($usuarios as $u) {
    $hash = password_hash($u['password'], PASSWORD_BCRYPT);
    $stmt->execute([
        ':nombre'   => $u['nombre'],
        ':email'    => $u['email'],
        ':password' => $hash,
        ':rol'      => $u['rol'],
    ]);
    echo "✅ {$u['nombre']} ({$u['email']}) — Contraseña: {$u['password']}\n";
}

echo "\n✅ Listo. Puedes eliminar este archivo.\n";
echo "</pre>";