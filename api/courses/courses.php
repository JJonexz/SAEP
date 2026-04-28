<?php
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');
$me     = require_approved();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $courses = [];
    $db_ok   = false;

    // Intentar leer desde MySQL
    try {
        $courses = db_list_courses();
        $db_ok   = true;
    } catch (Throwable $e) {
        // MySQL no disponible o tabla inexistente → fallback a JSON
    }

    // Si MySQL no devuelve nada, usar el JSON de datos de ejemplo
    if (empty($courses)) {
        $json_path = __DIR__ . '/../../data/courses.json';
        if (file_exists($json_path)) {
            $raw = json_decode(file_get_contents($json_path), true) ?: [];
            // Normalizar campos para que el frontend los entienda igual
            foreach ($raw as $c) {
                $courses[] = [
                    'id'          => $c['id'],
                    'anio'        => $c['anio'] ?? $c['ano'] ?? '',
                    'division'    => $c['division'] ?? '',
                    'turno'       => $c['turno'] ?? '',
                    'nombre'      => $c['nombre'] ?? '',
                    'orientacion' => $c['orientacion'] ?? '',
                    'alumnos'     => is_array($c['alumnos'] ?? null)
                                        ? count($c['alumnos'])
                                        : ($c['alumnos'] ?? 0),
                    'materias'    => $c['materias'] ?? [],
                    '_source'     => 'json',
                ];
            }
        }
    }

    echo json_encode(array_values($courses));
    exit;
}

$me   = require_role(['admin','director','subdirector']);
$body = json_decode(file_get_contents('php://input'), true);

// POST: crear curso en ciclo lectivo
if ($method === 'POST') {
    $ano      = (int)($body['ano']      ?? 0);
    $division = trim($body['division']  ?? '');
    $turno    = trim($body['turno']     ?? '');
    $ciclo    = (int)($body['ciclolectivo'] ?? date('Y'));
    if (!$ano || !$division || !$turno) { http_response_code(400); echo json_encode(['error'=>'Año, división y turno obligatorios']); exit; }

    try {
        $curso = db_row('SELECT id FROM cursos WHERE ano=? AND division=? AND turno=?', [$ano, $division, $turno]);
        if (!$curso) {
            $id_curso = db_execute('INSERT INTO cursos (division, ano, turno) VALUES (?,?,?)', [$division, $ano, $turno]);
        } else {
            $id_curso = $curso['id'];
        }
        $exists = db_row('SELECT id FROM cursosciclolectivo WHERE id_cursos=? AND ciclolectivo=?', [$id_curso, $ciclo]);
        if ($exists) { http_response_code(409); echo json_encode(['error'=>'Ya existe ese curso para ese año lectivo']); exit; }
        db_execute('INSERT INTO cursosciclolectivo (estado, id_cursos, ciclolectivo) VALUES (?,?,?)', ['A', $id_curso, $ciclo]);
        echo json_encode(['success'=>true]);
    } catch (Throwable $e) {
        http_response_code(500); echo json_encode(['error'=>'Error de base de datos: '.$e->getMessage()]);
    }
    exit;
}

// PATCH
if ($method === 'PATCH') {
    $id     = $body['id']     ?? null;
    $action = $body['action'] ?? null;
    if (!$id || !$action) { http_response_code(400); echo json_encode(['error'=>'id y action requeridos']); exit; }
    try {
        if ($action === 'add_alumno') {
            $dni = (int) explode('_', $body['user_id'] ?? '', 2)[1];
            $exists = db_row('SELECT id FROM asignacionesalumnos WHERE id_cursosciclolectivo=? AND dni_alumnos=?', [$id, $dni]);
            if (!$exists) db_execute('INSERT INTO asignacionesalumnos (id_cursosciclolectivo, dni_alumnos, id_grupos, estado) VALUES (?,?,?,?)', [$id, $dni, 0, '']);
        }
        if ($action === 'remove_alumno') {
            $dni = (int) explode('_', $body['user_id'] ?? '', 2)[1];
            db_execute('UPDATE asignacionesalumnos SET estado=? WHERE id_cursosciclolectivo=? AND dni_alumnos=?', ['E', $id, $dni]);
        }
        echo json_encode(['success'=>true]);
    } catch (Throwable $e) {
        http_response_code(500); echo json_encode(['error'=>'Error de base de datos: '.$e->getMessage()]);
    }
    exit;
}

// DELETE
if ($method === 'DELETE') {
    $id = $body['id'] ?? null;
    try {
        db_execute('UPDATE cursosciclolectivo SET estado=? WHERE id=?', ['H', $id]);
        echo json_encode(['success'=>true]);
    } catch (Throwable $e) {
        http_response_code(500); echo json_encode(['error'=>'Error de base de datos: '.$e->getMessage()]);
    }
    exit;
}

http_response_code(405); echo json_encode(['error'=>'Método no permitido']);
