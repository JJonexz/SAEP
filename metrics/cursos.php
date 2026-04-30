<?php
/**
 * SAEP — Métricas: Cursos
 * Ranking de cursos por promedio, asistencia, alumnos y entregas.
 * Soporta filtro ?curso_id=...
 */
session_start();
require_once '../config.php';
require_once '../lib/auth.php';
require_once '../lib/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['github_id'])) { http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
$users   = db_read(USERS_FILE);
$current = db_find($users, 'github_id', $_SESSION['github_id']);
if (!$current || !in_array($current['role'], ['admin','director','subdirector'])) {
    http_response_code(403); echo json_encode(['error'=>'Acceso denegado']); exit;
}

$grades  = db_read(GRADES_FILE);
$courses = db_read(COURSES_FILE);
$works   = db_read(WORKS_FILE);

$activos_ids = array_flip(array_column(
    array_filter($users, fn($u) => $u['role']==='alumno' && $u['status']==='approved'),
    'id'
));

$filtro_curso = $_GET['curso_id'] ?? null;

$resultado = [];

foreach ($courses as $c) {
    if ($filtro_curso && $c['id'] !== $filtro_curso) continue;

    $alumnos_del_curso = array_flip($c['alumnos'] ?? []);

    // Solo alumnos activos del curso
    $alumnos_activos_curso = array_filter(array_keys($alumnos_del_curso), fn($id) => isset($activos_ids[$id]));
    $cant_alumnos = count($alumnos_activos_curso);
    $alumnos_set  = array_flip($alumnos_activos_curso);

    // Notas del curso (solo activos)
    $notas_curso = array_filter($grades, fn($g) =>
        isset($alumnos_set[$g['alumno_id']]) && isset($g['nota']) && is_numeric($g['nota'])
    );
    $n = count($notas_curso);
    $promedio = $n > 0 ? round(array_sum(array_column($notas_curso,'nota'))/$n, 2) : null;

    // Asistencia
    $asist = array_filter($grades, fn($g) =>
        isset($alumnos_set[$g['alumno_id']]) && isset($g['asistencia']) && is_numeric($g['asistencia'])
    );
    $prom_asist = count($asist) > 0 ? round(array_sum(array_column($asist,'asistencia'))/count($asist),1) : null;

    // Aprobados/desaprobados
    $aprobados=$desaprobados=0;
    foreach ($grades as $g) {
        if (!isset($alumnos_set[$g['alumno_id']])) continue;
        if ($g['estado']==='aprobado')    $aprobados++;
        if ($g['estado']==='desaprobado') $desaprobados++;
    }
    $total_est = $aprobados+$desaprobados;
    $pct_aprobacion = $total_est > 0 ? round($aprobados/$total_est*100,1) : null;

    // Entregas del curso
    $entregas_total = $entregas_realizadas = 0;
    foreach ($works as $w) {
        if ($w['curso_id'] !== $c['id']) continue;
        foreach ($w['submissions'] ?? [] as $s) {
            if (!isset($alumnos_set[$s['alumno_id']])) continue;
            $entregas_total++;
            if (!empty($s['entregado'])) $entregas_realizadas++;
        }
    }

    $resultado[] = [
        'id'               => $c['id'],
        'nombre'           => $c['nombre'],
        'anio'             => $c['anio'],
        'division'         => $c['division'],
        'turno'            => $c['turno'],
        'cant_alumnos'     => $cant_alumnos,
        'promedio'         => $promedio,
        'prom_asistencia'  => $prom_asist,
        'aprobados'        => $aprobados,
        'desaprobados'     => $desaprobados,
        'pct_aprobacion'   => $pct_aprobacion,
        'entregas_total'   => $entregas_total,
        'entregas_realizadas' => $entregas_realizadas,
    ];
}

// Ordenar por promedio desc
usort($resultado, fn($a,$b) => ($b['promedio'] ?? -1) <=> ($a['promedio'] ?? -1));

// Highlights (solo cuando no hay filtro de un curso específico)
$highlights = null;
if (!$filtro_curso && count($resultado) > 0) {
    $con_promedio = array_filter($resultado, fn($r) => $r['promedio'] !== null);
    $con_asist    = array_filter($resultado, fn($r) => $r['prom_asistencia'] !== null);

    if ($con_promedio) {
        $mejor = array_reduce($con_promedio, fn($carry,$r) => (!$carry||$r['promedio']>$carry['promedio']) ? $r : $carry);
        $peor  = array_reduce($con_promedio, fn($carry,$r) => (!$carry||$r['promedio']<$carry['promedio']) ? $r : $carry);
    }
    if ($con_asist) {
        $mas_asist  = array_reduce($con_asist, fn($carry,$r) => (!$carry||$r['prom_asistencia']>$carry['prom_asistencia']) ? $r : $carry);
        $menos_asist= array_reduce($con_asist, fn($carry,$r) => (!$carry||$r['prom_asistencia']<$carry['prom_asistencia']) ? $r : $carry);
    }
    $mas_alumnos = array_reduce($resultado, fn($carry,$r) => (!$carry||$r['cant_alumnos']>$carry['cant_alumnos']) ? $r : $carry);

    $highlights = [
        'mejor_curso'         => $mejor['nombre'] ?? null,
        'peor_curso'          => $peor['nombre'] ?? null,
        'mayor_asistencia'    => $mas_asist['nombre'] ?? null,
        'menor_asistencia'    => $menos_asist['nombre'] ?? null,
        'mas_alumnos'         => $mas_alumnos['nombre'] ?? null,
    ];
}

echo json_encode([
    'cursos'     => $resultado,
    'highlights' => $highlights,
], JSON_UNESCAPED_UNICODE);
