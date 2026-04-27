<?php
/**
 * Pautas — ahora persisten en una tabla MySQL dedicada.
 * Si preferís seguir usando JSON, mantené el archivo original.
 * Esta versión crea la tabla si no existe.
 */
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');

// Crear tabla si no existe (primera vez)
db_connect()->exec("CREATE TABLE IF NOT EXISTS `pautas` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `texto`      TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    require_approved();
    echo json_encode(db_query('SELECT id, texto, created_at FROM pautas ORDER BY id'));
    exit;
}

require_role(['admin','director','subdirector']);
$body = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    $texto = trim($body['texto'] ?? '');
    if (!$texto) { http_response_code(400); echo json_encode(['error'=>'Texto requerido']); exit; }
    db_execute('INSERT INTO pautas (texto) VALUES (?)', [$texto]);
    echo json_encode(['success'=>true]); exit;
}

if ($method === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['error'=>'ID requerido']); exit; }
    db_execute('DELETE FROM pautas WHERE id=?', [$id]);
    echo json_encode(['success'=>true]); exit;
}

http_response_code(405); echo json_encode(['error'=>'Método no permitido']);
