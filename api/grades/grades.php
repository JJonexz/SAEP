<?php
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');
$me     = require_approved();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $alumno_id  = $_GET['alumno_id']  ?? null;
    $cupof      = isset($_GET['cupof']) ? (int)$_GET['cupof'] : null;

    // Alumno solo ve sus propias notas
    if ($me['role'] === 'alumno') $alumno_id = $me['id'];

    // Profesor solo ve notas de sus cátedras
    if ($me['role'] === 'profesor') {
        $dni_prof = (int) explode('_', $me['id'])[1];
        $mis_cupof = db_query(
            'SELECT cu.cupof FROM cupof cu
             JOIN revista rv ON rv.cupof = cu.cupof AND rv.fh = ?
             WHERE rv.dni_personal = ?',
            ['0000-00-00', $dni_prof]
        );
        $ids = array_column($mis_cupof, 'cupof');
        if ($cupof && !in_array($cupof, $ids)) { echo json_encode([]); exit; }
    }

    echo json_encode(db_list_grades($alumno_id, $cupof)); exit;
}

$me   = require_role(['admin','director','subdirector','profesor']);
$body = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    $id_asig  = (int)($body['id_asignacionesalumnos'] ?? 0);
    $cupof    = (int)($body['cupof']   ?? 0);
    $nota     = isset($body['nota']) && $body['nota'] !== '' ? $body['nota'] : '';
    $devol    = trim($body['devolucion'] ?? '');
    $periodo  = (int)($body['periodo'] ?? 1);
    $dni_pers = (int) explode('_', $me['id'])[1];

    if (!$id_asig || !$cupof) { http_response_code(400); echo json_encode(['error'=>'id_asig y cupof requeridos']); exit; }

    $exists = db_row('SELECT id FROM informe_periodo WHERE id_asignacionesalumnos=? AND cupof=? AND periodo=?', [$id_asig, $cupof, $periodo]);
    if ($exists) { http_response_code(409); echo json_encode(['error'=>'Ya existe nota para este período']); exit; }

    db_execute(
        'INSERT INTO informe_periodo (id_asignacionesalumnos, cupof, dni_personal, devolucion, nota, periodo) VALUES (?,?,?,?,?,?)',
        [$id_asig, $cupof, $dni_pers, $devol, $nota, $periodo]
    );
    echo json_encode(['success'=>true]); exit;
}

if ($method === 'PATCH') {
    $id    = (int)($body['id'] ?? 0);
    $nota  = $body['nota']      ?? null;
    $devol = $body['devolucion'] ?? null;
    $sets  = []; $params = [];
    if ($nota  !== null) { $sets[] = 'nota = ?';       $params[] = $nota; }
    if ($devol !== null) { $sets[] = 'devolucion = ?'; $params[] = $devol; }
    if ($sets) { $params[] = $id; db_execute('UPDATE informe_periodo SET '.implode(', ',$sets).' WHERE id=?', $params); }
    echo json_encode(['success'=>true]); exit;
}

if ($method === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    db_execute('DELETE FROM informe_periodo WHERE id=?', [$id]);
    echo json_encode(['success'=>true]); exit;
}

http_response_code(405); echo json_encode(['error'=>'Método no permitido']);
