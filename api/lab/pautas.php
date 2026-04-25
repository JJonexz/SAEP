<?php
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');

define('PAUTAS_FILE', __DIR__.'/../../data/pautas.json');

$method = $_SERVER['REQUEST_METHOD'];

// GET: listar todas las pautas (lectura pública)
if ($method === 'GET') {
    require_approved();
    $pautas = file_exists(PAUTAS_FILE) ? json_decode(file_get_contents(PAUTAS_FILE), true) : [];
    echo json_encode($pautas ?: []);
    exit;
}

// POST/DELETE: solo admin
require_role(['admin','director','subdirector']);
$body = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    $texto = trim($body['texto'] ?? '');
    if (!$texto) {
        http_response_code(400);
        echo json_encode(['error' => 'Texto requerido']);
        exit;
    }
    
    $pautas = file_exists(PAUTAS_FILE) ? json_decode(file_get_contents(PAUTAS_FILE), true) : [];
    $pautas[] = [
        'id' => generate_id(),
        'texto' => $texto,
        'createdAt' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents(PAUTAS_FILE, json_encode($pautas, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'DELETE') {
    $id = $body['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID requerido']);
        exit;
    }
    
    $pautas = file_exists(PAUTAS_FILE) ? json_decode(file_get_contents(PAUTAS_FILE), true) : [];
    $pautas = array_values(array_filter($pautas, fn($p) => $p['id'] !== $id));
    
    file_put_contents(PAUTAS_FILE, json_encode($pautas, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
