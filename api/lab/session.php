<?php
session_start();
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'no_session']);
    exit;
}

$user = db_find_user_by_id($_SESSION['user_id']);
if (!$user) { http_response_code(401); echo json_encode(['error' => 'no_user']); exit; }
if ($user['status'] !== 'approved') { http_response_code(403); echo json_encode(['error' => 'not_approved']); exit; }
if ($user['role'] === 'alumno') { http_response_code(403); echo json_encode(['error' => 'forbidden_role']); exit; }

$display = trim(($user['apellido'] ?? '') . ', ' . ($user['nombre'] ?? '')) ?: ($user['dni'] ?? 'Usuario');

echo json_encode([
    'id'       => $user['id'],
    'display'  => $display,
    'nombre'   => $user['nombre']   ?? '',
    'apellido' => $user['apellido'] ?? '',
    'avatar'   => null,
    'role'     => $user['role'],
    'isAdmin'  => in_array($user['role'], ['admin', 'director', 'subdirector']),
]);
