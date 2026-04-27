<?php
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');
$me     = require_approved();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode(db_list_courses()); exit;
}

$me   = require_role(['admin','director','subdirector']);
$body = json_decode(file_get_contents('php://input'), true);

// POST: crear cursociclolectivo
if ($method === 'POST') {
    $ano      = (int)($body['ano']      ?? 0);
    $division = trim($body['division']  ?? '');
    $turno    = trim($body['turno']     ?? '');
    $ciclo    = (int)($body['ciclolectivo'] ?? date('Y'));
    if (!$ano || !$division || !$turno) { http_response_code(400); echo json_encode(['error'=>'Año, división y turno obligatorios']); exit; }

    // buscar o crear curso base
    $curso = db_row('SELECT id FROM cursos WHERE ano=? AND division=? AND turno=?', [$ano, $division, $turno]);
    if (!$curso) {
        $id_curso = db_execute('INSERT INTO cursos (division, ano, turno) VALUES (?,?,?)', [$division, $ano, $turno]);
    } else {
        $id_curso = $curso['id'];
    }

    $exists = db_row('SELECT id FROM cursosciclolectivo WHERE id_cursos=? AND ciclolectivo=?', [$id_curso, $ciclo]);
    if ($exists) { http_response_code(409); echo json_encode(['error'=>'Ya existe ese curso para ese año lectivo']); exit; }

    db_execute('INSERT INTO cursosciclolectivo (estado, id_cursos, ciclolectivo) VALUES (?,?,?)', ['A', $id_curso, $ciclo]);
    echo json_encode(['success'=>true]); exit;
}

// PATCH: asignar alumno o cambiar estado
if ($method === 'PATCH') {
    $id     = $body['id']     ?? null; // id de cursosciclolectivo
    $action = $body['action'] ?? null;
    if (!$id || !$action) { http_response_code(400); echo json_encode(['error'=>'id y action requeridos']); exit; }

    if ($action === 'add_alumno') {
        $dni = (int) explode('_', $body['user_id'] ?? '', 2)[1];
        $exists = db_row('SELECT id FROM asignacionesalumnos WHERE id_cursosciclolectivo=? AND dni_alumnos=?', [$id, $dni]);
        if (!$exists) db_execute('INSERT INTO asignacionesalumnos (id_cursosciclolectivo, dni_alumnos, id_grupos, estado) VALUES (?,?,?,?)', [$id, $dni, 0, '']);
    }
    if ($action === 'remove_alumno') {
        $dni = (int) explode('_', $body['user_id'] ?? '', 2)[1];
        db_execute('UPDATE asignacionesalumnos SET estado=? WHERE id_cursosciclolectivo=? AND dni_alumnos=?', ['E', $id, $dni]);
    }
    echo json_encode(['success'=>true]); exit;
}

// DELETE
if ($method === 'DELETE') {
    $id = $body['id'] ?? null;
    db_execute('UPDATE cursosciclolectivo SET estado=? WHERE id=?', ['H', $id]);
    echo json_encode(['success'=>true]); exit;
}

http_response_code(405); echo json_encode(['error'=>'Método no permitido']);
