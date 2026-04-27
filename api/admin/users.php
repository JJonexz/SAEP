<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

header('Content-Type: application/json');
$me     = require_role(['admin','director','subdirector']);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $q      = trim($_GET['q']      ?? '');
    $role   = trim($_GET['role']   ?? '');
    $status = trim($_GET['status'] ?? '');

    // Sin ningún filtro: devolver vacío
    if (!$q && !$role && !$status) { echo json_encode([]); exit; }

    $result = [];

    // Personal
    $where = []; $params = [];
    if ($role && $role !== 'alumno') { $where[] = 'tag = ?'; $params[] = $role; }
    if ($q) { $where[] = '(apellido LIKE ? OR nombre LIKE ? OR dni LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%"; }
    if ($role !== 'alumno') {
        $sql = 'SELECT * FROM personal' . ($where ? ' WHERE '.implode(' AND ',$where) : '') . ' ORDER BY apellido LIMIT 50';
        foreach (db_query($sql, $params) as $r) {
            $u = _normalize_personal($r); unset($u['password_hash']); $result[] = $u;
        }
    }

    // Alumnos
    if (!$role || $role === 'alumno') {
        $where2 = []; $params2 = [];
        if ($q) { $where2[] = '(apellido LIKE ? OR nombre LIKE ? OR dni LIKE ?)'; $params2[] = "%$q%"; $params2[] = "%$q%"; $params2[] = "%$q%"; }
        $sql2 = 'SELECT * FROM alumnos' . ($where2 ? ' WHERE '.implode(' AND ',$where2) : '') . ' ORDER BY apellido LIMIT 50';
        $rows = db_query($sql2, $params2);
        if ($rows) {
            $dnis = array_column($rows, 'dni');
            $placeholders = implode(',', array_fill(0, count($dnis), '?'));
            $emails = db_query("SELECT dni, email FROM email WHERE dni IN ($placeholders)", $dnis);
            $emailMap = array_column($emails, 'email', 'dni');
            $tutores = db_query(
                "SELECT pal.dni_alumnos, pt.nombre, pt.apellido, pt.telefono, pt.domicilio, pa.parentesco
                 FROM padresalumnos pal
                 JOIN padrestutores pt ON pt.dni = pal.dni_padrestutores
                 JOIN parentesco pa ON pa.id = pal.id_parentesco
                 WHERE pal.dni_alumnos IN ($placeholders)", $dnis
            );
            $tutoresMap = [];
            foreach ($tutores as $t) $tutoresMap[$t['dni_alumnos']][] = $t;
            foreach ($rows as $r) {
                $u = _normalize_alumno($r, $emailMap, $tutoresMap); unset($u['password_hash']); $result[] = $u;
            }
        }
    }

    echo json_encode($result); exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$pdo  = db_connect();

// CREATE
if ($method === 'POST') {
    if (!is_admin($me)) { http_response_code(403); echo json_encode(['error'=>'Solo admin puede crear usuarios']); exit; }
    $nombre   = trim($body['nombre']   ?? '');
    $apellido = trim($body['apellido'] ?? '');
    $dni      = (int)($body['dni']     ?? 0);
    $email    = trim($body['email']    ?? '');
    $role     = $body['role']          ?? 'profesor';
    $password = trim($body['password'] ?? '');
    $tipo     = $body['tipo']          ?? 'personal'; // 'personal' o 'alumno'

    if (!$nombre || !$apellido || !$dni) { http_response_code(400); echo json_encode(['error'=>'Nombre, apellido y DNI obligatorios']); exit; }
    if (!$password) { http_response_code(400); echo json_encode(['error'=>'Contraseña obligatoria']); exit; }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    if ($tipo === 'alumno') {
        $exists = db_row('SELECT dni FROM alumnos WHERE dni = ?', [$dni]);
        if ($exists) { http_response_code(409); echo json_encode(['error'=>'DNI ya registrado']); exit; }
        db_execute(
            'INSERT INTO alumnos (dni, apellido, nombre, clave) VALUES (?, ?, ?, ?)',
            [$dni, strtoupper($apellido), strtoupper($nombre), $hash]
        );
    } else {
        $exists = db_row('SELECT dni FROM personal WHERE dni = ?', [$dni]);
        if ($exists) { http_response_code(409); echo json_encode(['error'=>'DNI ya registrado']); exit; }
        db_execute(
            'INSERT INTO personal (dni, apellido, nombre, tipo_doc, sexo, domicilio, cp, fechan, email, pass, id_localidades, tag)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$dni, strtoupper($apellido), strtoupper($nombre), 'DNI', '', '', 0, date('Y-m-d'), $email, $hash, 0, $role]
        );
    }
    echo json_encode(['success'=>true]); exit;
}

// UPDATE
if ($method === 'PATCH') {
    $id = $body['id'] ?? null;
    if (!$id) { http_response_code(400); echo json_encode(['error'=>'id requerido']); exit; }

    [$tipo, $dni] = explode('_', $id, 2);
    $dni = (int)$dni;

    if ($tipo === 'P') {
        $sets = []; $params = [];
        if (isset($body['nombre']))   { $sets[] = 'nombre = ?';   $params[] = strtoupper($body['nombre']); }
        if (isset($body['apellido'])) { $sets[] = 'apellido = ?'; $params[] = strtoupper($body['apellido']); }
        if (isset($body['email']))    { $sets[] = 'email = ?';    $params[] = $body['email']; }
        if (isset($body['role']))     {
            if (!in_array($body['role'], ROLES)) { http_response_code(400); echo json_encode(['error'=>'Rol inválido']); exit; }
            if ($body['role'] === 'admin' && !is_admin($me)) { http_response_code(403); echo json_encode(['error'=>'Solo admin puede asignar admin']); exit; }
            $sets[] = 'tag = ?'; $params[] = $body['role'];
        }
        if (!empty($body['password'])) { $sets[] = 'pass = ?'; $params[] = password_hash($body['password'], PASSWORD_DEFAULT); }
        if ($sets) {
            $params[] = $dni;
            db_execute('UPDATE personal SET ' . implode(', ', $sets) . ' WHERE dni = ?', $params);
        }
    } else {
        $sets = []; $params = [];
        if (isset($body['nombre']))   { $sets[] = 'nombre = ?';   $params[] = strtoupper($body['nombre']); }
        if (isset($body['apellido'])) { $sets[] = 'apellido = ?'; $params[] = strtoupper($body['apellido']); }
        if (!empty($body['password'])) { $sets[] = 'clave = ?'; $params[] = password_hash($body['password'], PASSWORD_DEFAULT); }
        if ($sets) {
            $params[] = $dni;
            db_execute('UPDATE alumnos SET ' . implode(', ', $sets) . ' WHERE dni = ?', $params);
        }
    }
    echo json_encode(['success'=>true]); exit;
}

// DELETE
if ($method === 'DELETE') {
    if (!is_admin($me)) { http_response_code(403); echo json_encode(['error'=>'Solo admin puede eliminar']); exit; }
    $id = $body['id'] ?? null;
    if (!$id) { http_response_code(400); echo json_encode(['error'=>'id requerido']); exit; }
    if ($id === $me['id']) { http_response_code(400); echo json_encode(['error'=>'No podés eliminarte']); exit; }

    [$tipo, $dni] = explode('_', $id, 2);
    $dni = (int)$dni;
    if ($tipo === 'P') db_execute('DELETE FROM personal WHERE dni = ?', [$dni]);
    else               db_execute('DELETE FROM alumnos  WHERE dni = ?', [$dni]);
    echo json_encode(['success'=>true]); exit;
}

http_response_code(405); echo json_encode(['error'=>'Método no permitido']);
