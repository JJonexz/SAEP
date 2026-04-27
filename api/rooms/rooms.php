<?php
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');
require_approved();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') { echo json_encode(db_list_rooms()); exit; }

require_role(['admin','director','subdirector']);
$body = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    $piso  = (int)($body['piso']      ?? 0);
    $num   = (int)($body['numero']    ?? 0);
    $tipo  = trim($body['tipo']       ?? 'AULA');
    $cap   = (int)($body['capacidad'] ?? 0);
    $ubic  = trim($body['ubicacion']  ?? '');
    db_execute('INSERT INTO salones (piso, numero, tipo, capacidad, corriente, televisor, pizarron, ubicacion) VALUES (?,?,?,?,?,?,?,?)',
        [$piso, $num, $tipo, $cap, 'NO', 'NO', 'NO', $ubic]);
    echo json_encode(['success'=>true]); exit;
}
if ($method === 'PATCH') {
    $id   = (int)($body['id'] ?? 0);
    $sets = []; $params = [];
    foreach (['piso','numero','tipo','capacidad','ubicacion'] as $f) {
        if (array_key_exists($f, $body)) { $sets[] = "$f = ?"; $params[] = $body[$f]; }
    }
    if ($sets) { $params[] = $id; db_execute('UPDATE salones SET '.implode(', ',$sets).' WHERE id_salones=?', $params); }
    echo json_encode(['success'=>true]); exit;
}
if ($method === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    db_execute('DELETE FROM salones WHERE id_salones=?', [$id]);
    echo json_encode(['success'=>true]); exit;
}
http_response_code(405); echo json_encode(['error'=>'Método no permitido']);
