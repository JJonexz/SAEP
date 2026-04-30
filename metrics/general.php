<?php
/**
 * SAEP — Métricas: General
 * Devuelve resumen institucional completo (alumnos activos, promedios, etc.)
 */
session_start();
require_once '../config.php';
require_once '../lib/auth.php';
require_once '../lib/db.php';

header('Content-Type: application/json; charset=utf-8');

// ── Autenticación ──────────────────────────────────────────────────────────
if (!isset($_SESSION['github_id'])) { http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
$users   = db_read(USERS_FILE);
$current = db_find($users, 'github_id', $_SESSION['github_id']);
if (!$current || !in_array($current['role'], ['admin','director','subdirector'])) {
    http_response_code(403); echo json_encode(['error'=>'Acceso denegado']); exit;
}

// ── Cargar datos ───────────────────────────────────────────────────────────
$courses  = db_read(COURSES_FILE);
$grades   = db_read(GRADES_FILE);
$works    = db_read(WORKS_FILE);
$rooms    = db_read(ROOMS_FILE);

// ── Alumnos activos (role=alumno, status=approved) ─────────────────────────
$alumnos_activos = array_filter($users, fn($u) => $u['role'] === 'alumno' && $u['status'] === 'approved');
$alumnos_ids     = array_column($alumnos_activos, 'id');
$alumnos_ids_set = array_flip($alumnos_ids);

$total_alumnos  = count($alumnos_activos);
$total_cursos   = count($courses);

// ── Notas (solo alumnos activos, solo con nota numérica) ──────────────────
$notas_validas = array_filter($grades, fn($g) =>
    isset($g['nota']) && is_numeric($g['nota']) && isset($alumnos_ids_set[$g['alumno_id']])
);

$suma_notas   = array_sum(array_column($notas_validas, 'nota'));
$cant_notas   = count($notas_validas);
$promedio_gral = $cant_notas > 0 ? round($suma_notas / $cant_notas, 2) : 0;

// ── Aprobados / Desaprobados (estado en grade, solo activos) ──────────────
$aprobados     = 0;
$desaprobados  = 0;
$con_asistencia = [];

foreach ($grades as $g) {
    if (!isset($alumnos_ids_set[$g['alumno_id']])) continue;
    if ($g['estado'] === 'aprobado')    $aprobados++;
    if ($g['estado'] === 'desaprobado') $desaprobados++;
    if (isset($g['asistencia']) && is_numeric($g['asistencia'])) {
        $con_asistencia[] = (float)$g['asistencia'];
    }
}

$total_estados   = $aprobados + $desaprobados;
$pct_aprobacion  = $total_estados > 0 ? round($aprobados / $total_estados * 100, 1) : 0;
$prom_asistencia = count($con_asistencia) > 0 ? round(array_sum($con_asistencia) / count($con_asistencia), 1) : null;

// ── Entregas (submissions entregadas) ─────────────────────────────────────
$total_entregas    = 0;
$entregas_realizadas = 0;
foreach ($works as $w) {
    foreach ($w['submissions'] ?? [] as $s) {
        if (!isset($alumnos_ids_set[$s['alumno_id']])) continue;
        $total_entregas++;
        if (!empty($s['entregado'])) $entregas_realizadas++;
    }
}

// ── Aulas / Ocupación ─────────────────────────────────────────────────────
$capacidad_total = array_sum(array_column($rooms, 'capacidad'));
// Alumnos asignados a aulas (rooms tienen curso_id)
$alumnos_en_aulas = 0;
foreach ($rooms as $r) {
    if (!empty($r['curso_id'])) {
        $curso = array_values(array_filter($courses, fn($c) => $c['id'] === $r['curso_id']));
        if ($curso) $alumnos_en_aulas += count($curso[0]['alumnos'] ?? []);
    }
}
$pct_ocupacion = $capacidad_total > 0 ? round($alumnos_en_aulas / $capacidad_total * 100, 1) : 0;

// ── Promedio por curso (para gráfico de barras) ───────────────────────────
$cursos_stats = [];
foreach ($courses as $c) {
    $alumnos_curso = array_flip($c['alumnos'] ?? []);
    $notas_curso   = array_filter($grades, fn($g) =>
        isset($alumnos_curso[$g['alumno_id']]) && isset($alumnos_ids_set[$g['alumno_id']])
        && isset($g['nota']) && is_numeric($g['nota'])
    );
    $n = count($notas_curso);
    $cursos_stats[] = [
        'nombre'   => $c['nombre'],
        'promedio' => $n > 0 ? round(array_sum(array_column($notas_curso, 'nota')) / $n, 2) : null,
        'alumnos'  => count($c['alumnos'] ?? []),
    ];
}
// Ordenar por promedio desc (nulls al final)
usort($cursos_stats, fn($a,$b) => ($b['promedio'] ?? -1) <=> ($a['promedio'] ?? -1));

echo json_encode([
    'total_alumnos'       => $total_alumnos,
    'total_cursos'        => $total_cursos,
    'promedio_general'    => $promedio_gral,
    'porcentaje_aprobacion' => $pct_aprobacion,
    'aprobados'           => $aprobados,
    'desaprobados'        => $desaprobados,
    'total_entregas'      => $total_entregas,
    'entregas_realizadas' => $entregas_realizadas,
    'promedio_asistencia' => $prom_asistencia,
    'capacidad_total'     => $capacidad_total,
    'alumnos_en_aulas'    => $alumnos_en_aulas,
    'porcentaje_ocupacion'=> $pct_ocupacion,
    'cursos_stats'        => $cursos_stats,
], JSON_UNESCAPED_UNICODE);
