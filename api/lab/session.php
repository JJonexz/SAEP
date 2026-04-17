<?php
/**
 * laboratorios/api/session.php
 * Expone el usuario actual de SAEP como JSON para el gestor de laboratorios.
 * Ruta esperada: /laboratorios/api/session.php
 */

session_start();

// Subimos dos niveles: laboratorios/api/ → raíz del proyecto SAEP
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

// Sin sesión GitHub → 401
if (!isset($_SESSION['github_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'no_session']);
    exit;
}

// Buscamos el usuario en users.json
$users = db_read(USERS_FILE);
$user  = db_find($users, 'github_id', $_SESSION['github_id']);

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'no_user']);
    exit;
}

// Usuario no aprobado → redirigir al flujo de SAEP
if ($user['status'] !== 'approved') {
    http_response_code(403);
    echo json_encode(['error' => 'not_approved', 'status' => $user['status']]);
    exit;
}

// Roles que tienen permisos de administración en el gestor
// admin / director / subdirector → modo admin
// profesor / preceptor           → modo docente
// alumno                         → sin acceso al gestor
$rolesSinAcceso = ['alumno'];
if (in_array($user['role'], $rolesSinAcceso)) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden_role']);
    exit;
}

// Construimos el nombre para mostrar
$display = '';
if (!empty($user['apellido']) && !empty($user['nombre'])) {
    $display = $user['apellido'] . ', ' . $user['nombre'];
} elseif (!empty($user['apellido'])) {
    $display = $user['apellido'];
} elseif (!empty($user['nombre'])) {
    $display = $user['nombre'];
} else {
    $display = $user['username'] ?? 'Usuario';
}

// Respuesta normalizada para el gestor
echo json_encode([
    'id'       => $user['id'],
    'display'  => $display,
    'nombre'   => $user['nombre']   ?? '',
    'apellido' => $user['apellido'] ?? '',
    'username' => $user['username'] ?? '',
    'avatar'   => $user['avatar']   ?? null,
    'role'     => $user['role'],       // rol real de SAEP
    'isAdmin'  => in_array($user['role'], ['admin', 'director', 'subdirector']),
]);