<?php
session_start();
require_once __DIR__.'/../config/config.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

header('Content-Type: application/json');
$me     = require_role(['admin','director','subdirector']);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $users  = db_read(USERS_FILE);
    $result = array_map(fn($u) => array_diff_key($u, ['github_id'=>1]), $users);
    echo json_encode(array_values($result)); exit;
}

$body = json_decode(file_get_contents('php://input'), true);

// CREATE manual user
if ($method === 'POST') {
    if (!is_admin($me)) { http_response_code(403); echo json_encode(['error'=>'Solo admin puede crear usuarios manuales']); exit; }
    $nombre   = trim($body['nombre']   ?? '');
    $apellido = trim($body['apellido'] ?? '');
    $dni      = trim($body['dni']      ?? '');
    $email    = trim($body['email']    ?? '');
    $role     = $body['role']   ?? 'alumno';
    $username = trim($body['username'] ?? '');

    if (!$nombre||!$apellido||!$dni) { http_response_code(400); echo json_encode(['error'=>'Nombre, apellido y DNI obligatorios']); exit; }
    if (!preg_match('/^\d{7,8}$/', $dni)) { http_response_code(400); echo json_encode(['error'=>'DNI inválido']); exit; }
    if (!in_array($role, ROLES)) { http_response_code(400); echo json_encode(['error'=>'Rol inválido']); exit; }

    $users = db_read(USERS_FILE);
    foreach ($users as $u) {
        if ($u['dni'] === $dni) { http_response_code(409); echo json_encode(['error'=>'DNI ya registrado']); exit; }
    }

    $users[] = [
        'id'=>generate_id(),'github_id'=>null,'username'=>$username ?: 'manual_'.generate_id(),
        'avatar'=>null,'email'=>$email,'nombre'=>$nombre,'apellido'=>$apellido,
        'dni'=>$dni,'telefono'=>$body['telefono']??null,
        'role'=>$role,'status'=>'approved','manual'=>true,
    ];
    db_write(USERS_FILE, $users);
    echo json_encode(['success'=>true]); exit;
}

if ($method === 'PATCH') {
    $id = $body['id'] ?? null;
    if (!$id) { http_response_code(400); echo json_encode(['error'=>'id requerido']); exit; }
    $users = db_read(USERS_FILE);
    $idx   = db_find_index($users, 'id', $id);
    if ($idx === -1) { http_response_code(404); echo json_encode(['error'=>'Usuario no encontrado']); exit; }

    if (isset($body['role'])) {
        if (!in_array($body['role'], ROLES)) { http_response_code(400); echo json_encode(['error'=>'Rol inválido']); exit; }
        if ($body['role']==='admin' && !is_admin($me)) { http_response_code(403); echo json_encode(['error'=>'Solo admin puede asignar admin']); exit; }
        $users[$idx]['role'] = $body['role'];
    }
    if (isset($body['status'])) {
        if (!in_array($body['status'],['approved','rejected','pending_approval'])) { http_response_code(400); echo json_encode(['error'=>'Estado inválido']); exit; }
        $users[$idx]['status'] = $body['status'];
    }
    foreach (['nombre','apellido','dni','email','telefono'] as $f) {
        if (isset($body[$f])) $users[$idx][$f] = $body[$f];
    }
    db_write(USERS_FILE, $users);
    echo json_encode(['success'=>true]); exit;
}

if ($method === 'DELETE') {
    if (!is_admin($me)) { http_response_code(403); echo json_encode(['error'=>'Solo admin puede eliminar']); exit; }
    $id    = $body['id'] ?? null;
    $users = db_read(USERS_FILE);
    $idx   = db_find_index($users, 'id', $id);
    if ($idx===-1) { http_response_code(404); echo json_encode(['error'=>'No encontrado']); exit; }
    if ($users[$idx]['id']===$me['id']) { http_response_code(400); echo json_encode(['error'=>'No podés eliminarte']); exit; }
    array_splice($users, $idx, 1);
    db_write(USERS_FILE, $users);
    echo json_encode(['success'=>true]); exit;
}

http_response_code(405); echo json_encode(['error'=>'Método no permitido']);
