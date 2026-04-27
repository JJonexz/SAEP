<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/db.php';
header('Content-Type: application/json');

$body     = json_decode(file_get_contents('php://input'), true);
$dni      = trim($body['dni']      ?? '');
$password = trim($body['password'] ?? '');

if (!$dni || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'DNI y contraseña requeridos']);
    exit;
}

$user = db_find_user_by_dni((int)$dni);

if (!$user || empty($user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'DNI o contraseña incorrectos']);
    exit;
}

$hash = $user['password_hash'];

// Soporta tanto contraseñas planas (legacy, ej: "1234") como hashes bcrypt
$ok = password_verify($password, $hash) || $password === $hash;

if (!$ok) {
    http_response_code(401);
    echo json_encode(['error' => 'DNI o contraseña incorrectos']);
    exit;
}

$_SESSION['user_id'] = $user['id'];

echo json_encode(['redirect' => 'dashboard.php']);
