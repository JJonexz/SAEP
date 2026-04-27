<?php
/**
 * Trabajos/tareas — ahora usa la tabla `tareas` y `tareas_alumnos`
 * junto con `informe_periodo` para calificaciones.
 */
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');
$me     = require_approved();
$method = $_SERVER['REQUEST_METHOD'];

// ── GET ──────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $id    = $_GET['id']    ?? null;
    $cupof = isset($_GET['cupof']) ? (int)$_GET['cupof'] : null;

    if ($id) {
        $tarea = db_row('SELECT t.*, rv.dni_personal FROM tareas t JOIN revista rv ON rv.id=t.id_revista WHERE t.id=?', [(int)$id]);
        if (!$tarea) { http_response_code(404); echo json_encode(['error'=>'No encontrado']); exit; }
        $entregas = db_query(
            'SELECT ta.*, al.apellido, al.nombre
             FROM tareas_alumnos ta
             JOIN asignacionesalumnos aa ON aa.id = ta.id_asignacionesalumnos
             JOIN alumnos al ON al.dni = aa.dni_alumnos
             WHERE ta.id_tarea = ? AND ta.borrado_fisico = 0',
            [(int)$id]
        );
        if ($me['role'] === 'alumno') {
            $dni = (int) explode('_', $me['id'])[1];
            $entregas = array_filter($entregas, fn($e) => $e['nombre'] && $e['apellido'] &&
                db_row('SELECT aa.id FROM asignacionesalumnos aa WHERE aa.id=? AND aa.dni_alumnos=?',
                    [$e['id_asignacionesalumnos'], $dni]) !== null
            );
        }
        $tarea['entregas'] = array_values($entregas);
        echo json_encode($tarea); exit;
    }

    // Lista
    if ($me['role'] === 'alumno') {
        $dni = (int) explode('_', $me['id'])[1];
        $tareas = db_query(
            'SELECT DISTINCT t.id, t.titulo, t.descripcion, t.fecha_entrega, rv.cupof
             FROM tareas t
             JOIN revista rv ON rv.id = t.id_revista
             JOIN asignacionesalumnos aa ON aa.id_cursosciclolectivo IN (
                 SELECT id_cursosciclolectivo FROM asignacionesalumnos WHERE dni_alumnos=? AND estado != ?
             )
             WHERE aa.dni_alumnos = ? AND aa.estado != ?
             ORDER BY t.fecha_entrega DESC',
            [$dni, 'E', $dni, 'E']
        );
    } elseif ($me['role'] === 'profesor') {
        $dni_prof = (int) explode('_', $me['id'])[1];
        $tareas = db_query(
            'SELECT t.id, t.titulo, t.descripcion, t.fecha_entrega, rv.cupof
             FROM tareas t
             JOIN revista rv ON rv.id = t.id_revista AND rv.fh = ?
             WHERE rv.dni_personal = ?
             ORDER BY t.fecha_entrega DESC',
            ['0000-00-00', $dni_prof]
        );
    } else {
        $tareas = db_query(
            'SELECT t.id, t.titulo, t.descripcion, t.fecha_entrega, rv.cupof, rv.dni_personal
             FROM tareas t JOIN revista rv ON rv.id = t.id_revista
             ORDER BY t.fecha_entrega DESC'
        );
    }
    if ($cupof) $tareas = array_values(array_filter($tareas, fn($t) => (int)$t['cupof'] === $cupof));
    echo json_encode(array_values($tareas)); exit;
}

// ── POST: crear tarea ────────────────────────────────────────────────────────
if ($method === 'POST') {
    $me = require_role(['admin','director','subdirector','profesor']);
    $body = json_decode(file_get_contents('php://input'), true);

    $titulo  = trim($body['titulo']      ?? '');
    $desc    = trim($body['descripcion'] ?? '');
    $entrega = trim($body['fecha_entrega'] ?? date('Y-m-d'));
    $cupof   = (int)($body['cupof']      ?? 0);

    if (!$titulo || !$cupof) { http_response_code(400); echo json_encode(['error'=>'Título y cupof requeridos']); exit; }

    $dni_prof = (int) explode('_', $me['id'])[1];
    $revista  = db_row('SELECT id FROM revista WHERE cupof=? AND dni_personal=? AND fh=?', [$cupof, $dni_prof, '0000-00-00']);
    if (!$revista && !is_admin($me)) { http_response_code(403); echo json_encode(['error'=>'No sos docente de esa cátedra']); exit; }
    // admin puede asignar igual usando cualquier revista del cupof
    if (!$revista) $revista = db_row('SELECT id FROM revista WHERE cupof=?', [$cupof]);
    if (!$revista) { http_response_code(404); echo json_encode(['error'=>'Cátedra no encontrada']); exit; }

    $id_tarea = db_execute(
        'INSERT INTO tareas (titulo, descripcion, tamanio, nombre_archivo, tipo, fecha_subida, fecha_entrega, id_revista)
         VALUES (?,?,0,?,?,?,?,?)',
        [$titulo, $desc, '', 'texto', date('Y-m-d'), $entrega, $revista['id']]
    );

    // Crear registros de entrega para los alumnos del curso
    $ccl = db_row('SELECT id_cursosciclolectivo FROM revista rv JOIN cupof cu ON cu.cupof=rv.cupof WHERE rv.id=?', [$revista['id']]);
    if ($ccl) {
        $asigs = db_query('SELECT id FROM asignacionesalumnos WHERE id_cursosciclolectivo=? AND estado != ?', [$ccl['id_cursosciclolectivo'], 'E']);
        foreach ($asigs as $a) {
            db_execute('INSERT INTO tareas_alumnos (id_tarea, id_asignacionesalumnos, fecha, nombre_archivo) VALUES (?,?,?,?)',
                [$id_tarea, $a['id'], '0000-00-00', '']);
        }
    }
    echo json_encode(['success'=>true, 'id'=>$id_tarea]); exit;
}

// ── PUT: alumno entrega tarea ────────────────────────────────────────────────
if ($method === 'PUT') {
    $me   = require_role(['alumno']);
    $body = json_decode(file_get_contents('php://input'), true);
    $tarea_id = (int)($body['id_tarea'] ?? 0);
    $archivo  = trim($body['nombre_archivo'] ?? '');
    $dni      = (int) explode('_', $me['id'])[1];

    $asig = db_row(
        'SELECT aa.id FROM asignacionesalumnos aa
         JOIN tareas_alumnos ta ON ta.id_asignacionesalumnos = aa.id
         WHERE ta.id_tarea=? AND aa.dni_alumnos=?',
        [$tarea_id, $dni]
    );
    if (!$asig) { http_response_code(403); echo json_encode(['error'=>'No estás asignado a esta tarea']); exit; }

    db_execute('UPDATE tareas_alumnos SET fecha=?, nombre_archivo=? WHERE id_tarea=? AND id_asignacionesalumnos=?',
        [date('Y-m-d'), $archivo, $tarea_id, $asig['id']]);
    echo json_encode(['success'=>true]); exit;
}

// ── DELETE ───────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    require_role(['admin','director','subdirector','profesor']);
    $body = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($body['id'] ?? 0);
    db_execute('UPDATE tareas_alumnos SET borrado_fisico=1 WHERE id_tarea=?', [$id]);
    db_execute('DELETE FROM tareas WHERE id=?', [$id]);
    echo json_encode(['success'=>true]); exit;
}

http_response_code(405); echo json_encode(['error'=>'Método no permitido']);
