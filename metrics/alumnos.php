<?php
/**
 * SAEP — Métricas: Alumnos
 * Estadísticas detalladas sobre alumnos activos vs inactivos, rendimiento, etc.
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

// ── Separar alumnos activos / inactivos ───────────────────────────────────
$todos_alumnos  = array_filter($users, fn($u) => $u['role'] === 'alumno');
$activos        = array_filter($todos_alumnos, fn($u) => $u['status'] === 'approved');
$inactivos      = array_filter($todos_alumnos, fn($u) => $u['status'] !== 'approved');

$total          = count($todos_alumnos);
$total_activos  = count($activos);
$total_inactivos= count($inactivos);

$activos_ids    = array_flip(array_column($activos, 'id'));

// ── Notas de activos ──────────────────────────────────────────────────────
$notas_val = array_filter($grades, fn($g) =>
    isset($activos_ids[$g['alumno_id']]) && isset($g['nota']) && is_numeric($g['nota'])
);
$cant_notas    = count($notas_val);
$promedio_gral = $cant_notas > 0 ? round(array_sum(array_column($notas_val,'nota')) / $cant_notas, 2) : 0;

// ── Asistencia ────────────────────────────────────────────────────────────
$asist_vals = array_filter(array_column(
    array_filter($grades, fn($g) => isset($activos_ids[$g['alumno_id']]) && isset($g['asistencia']) && is_numeric($g['asistencia'])),
    'asistencia'
), 'is_numeric');
$prom_asistencia = count($asist_vals) > 0 ? round(array_sum($asist_vals)/count($asist_vals), 1) : null;

// ── Estados (aprobado / desaprobado) ──────────────────────────────────────
$aprobados = $desaprobados = 0;
foreach ($grades as $g) {
    if (!isset($activos_ids[$g['alumno_id']])) continue;
    if ($g['estado'] === 'aprobado')    $aprobados++;
    if ($g['estado'] === 'desaprobado') $desaprobados++;
}
$total_est    = $aprobados + $desaprobados;
$pct_aprobados = $total_est > 0 ? round($aprobados/$total_est*100,1) : 0;
$pct_desaprob  = $total_est > 0 ? round($desaprobados/$total_est*100,1) : 0;

// ── Distribución de rendimiento (rangos de nota) ─────────────────────────
$rangos = ['1-3'=>0,'4-5'=>0,'6-7'=>0,'8-9'=>0,'10'=>0];
foreach ($notas_val as $g) {
    $n = (float)$g['nota'];
    if ($n <= 3)      $rangos['1-3']++;
    elseif ($n <= 5)  $rangos['4-5']++;
    elseif ($n <= 7)  $rangos['6-7']++;
    elseif ($n < 10)  $rangos['8-9']++;
    else              $rangos['10']++;
}

// ── Relación alumnos vs capacidad (rooms) ─────────────────────────────────
$rooms = db_read(ROOMS_FILE);
$capacidad = array_sum(array_column($rooms, 'capacidad'));
$pct_ocupacion = $capacidad > 0 ? round($total_activos / $capacidad * 100, 1) : null;

echo json_encode([
    'total'            => $total,
    'activos'          => $total_activos,
    'inactivos'        => $total_inactivos,
    'promedio_general' => $promedio_gral,
    'promedio_asistencia' => $prom_asistencia,
    'aprobados'        => $aprobados,
    'desaprobados'     => $desaprobados,
    'pct_aprobados'    => $pct_aprobados,
    'pct_desaprobados' => $pct_desaprob,
    'distribucion_rendimiento' => $rangos,
    'capacidad_total'  => $capacidad,
    'pct_ocupacion'    => $pct_ocupacion,
], JSON_UNESCAPED_UNICODE);
