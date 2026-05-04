<?php
/**
 * SAEP — Métricas: Notas / Materias
 * Promedio por materia, entregas por curso, aprobación.
 * Soporta filtros: ?curso_id=... y ?materia_id=...
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

$grades   = db_read(GRADES_FILE);
$courses  = db_read(COURSES_FILE);
$subjects = db_read(SUBJECTS_FILE);
$works    = db_read(WORKS_FILE);

$activos_ids = array_flip(array_column(
    array_filter($users, fn($u) => $u['role']==='alumno' && $u['status']==='approved'),
    'id'
));

$filtro_curso   = $_GET['curso_id']   ?? null;
$filtro_materia = $_GET['materia_id'] ?? null;

// ── Índice de materias (global subjects.json) ────────────────────────────
$materias_idx = [];
foreach ($subjects as $s) $materias_idx[$s['id']] = $s;

// También buscar materias dentro de courses (tienen su propio id por curso)
$materias_curso_idx = [];
foreach ($courses as $c) {
    foreach ($c['materias'] ?? [] as $m) {
        $materias_curso_idx[$m['id']] = array_merge($m, ['curso_id'=>$c['id'],'curso_nombre'=>$c['nombre']]);
    }
}

// ── Filtrar grades ────────────────────────────────────────────────────────
$grades_filtradas = array_filter($grades, function($g) use ($activos_ids, $filtro_curso, $filtro_materia) {
    if (!isset($activos_ids[$g['alumno_id']])) return false;
    if ($filtro_curso   && $g['curso_id']   !== $filtro_curso)   return false;
    if ($filtro_materia && $g['materia_id'] !== $filtro_materia) return false;
    return true;
});

// ── Agrupar por materia ───────────────────────────────────────────────────
$por_materia = [];
foreach ($grades_filtradas as $g) {
    $mid = $g['materia_id'];
    if (!isset($por_materia[$mid])) {
        // Buscar nombre de materia
        $nombre_mat = $materias_idx[$mid]['nombre']
            ?? $materias_curso_idx[$mid]['nombre']
            ?? 'Materia desconocida';
        $por_materia[$mid] = [
            'materia_id'   => $mid,
            'nombre'       => $nombre_mat,
            'notas'        => [],
            'asistencias'  => [],
            'aprobados'    => 0,
            'desaprobados' => 0,
            'pendientes'   => 0,
        ];
    }
    if (isset($g['nota']) && is_numeric($g['nota'])) {
        $por_materia[$mid]['notas'][] = (float)$g['nota'];
    }
    if (isset($g['asistencia']) && is_numeric($g['asistencia'])) {
        $por_materia[$mid]['asistencias'][] = (float)$g['asistencia'];
    }
    if ($g['estado'] === 'aprobado')    $por_materia[$mid]['aprobados']++;
    if ($g['estado'] === 'desaprobado') $por_materia[$mid]['desaprobados']++;
    if ($g['estado'] === 'pendiente')   $por_materia[$mid]['pendientes']++;
}

$materias_stats = [];
foreach ($por_materia as $mid => $m) {
    $n = count($m['notas']);
    $a = count($m['asistencias']);
    $te = $m['aprobados'] + $m['desaprobados'];
    $materias_stats[] = [
        'materia_id'       => $mid,
        'nombre'           => $m['nombre'],
        'promedio'         => $n > 0 ? round(array_sum($m['notas'])/$n, 2) : null,
        'prom_asistencia'  => $a > 0 ? round(array_sum($m['asistencias'])/$a, 1) : null,
        'aprobados'        => $m['aprobados'],
        'desaprobados'     => $m['desaprobados'],
        'pendientes'       => $m['pendientes'],
        'pct_aprobacion'   => $te > 0 ? round($m['aprobados']/$te*100,1) : null,
        'cant_notas'       => $n,
    ];
}
usort($materias_stats, fn($a,$b) => ($b['promedio'] ?? -1) <=> ($a['promedio'] ?? -1));

// ── Entregas por curso ────────────────────────────────────────────────────
$entregas_por_curso = [];
foreach ($works as $w) {
    if ($filtro_curso && $w['curso_id'] !== $filtro_curso) continue;
    $cid = $w['curso_id'];
    if (!isset($entregas_por_curso[$cid])) {
        $curso_obj = array_values(array_filter($courses, fn($c)=>$c['id']===$cid));
        $entregas_por_curso[$cid] = [
            'curso_id'    => $cid,
            'curso_nombre'=> $curso_obj[0]['nombre'] ?? 'Curso desconocido',
            'total'       => 0,
            'realizadas'  => 0,
        ];
    }
    foreach ($w['submissions'] ?? [] as $s) {
        if (!isset($activos_ids[$s['alumno_id']])) continue;
        $entregas_por_curso[$cid]['total']++;
        if (!empty($s['entregado'])) $entregas_por_curso[$cid]['realizadas']++;
    }
}
$entregas_por_curso = array_values($entregas_por_curso);
usort($entregas_por_curso, fn($a,$b) => $b['total'] <=> $a['total']);

// ── Totales generales ─────────────────────────────────────────────────────
$total_notas_val = array_sum(array_column($materias_stats, 'cant_notas'));
$total_aprobados = array_sum(array_column($materias_stats,'aprobados'));
$total_desaprob  = array_sum(array_column($materias_stats,'desaprobados'));
$te_global       = $total_aprobados + $total_desaprob;

echo json_encode([
    'materias'         => $materias_stats,
    'entregas_por_curso' => $entregas_por_curso,
    'total_aprobados'  => $total_aprobados,
    'total_desaprobados'=> $total_desaprob,
    'pct_aprobacion'   => $te_global > 0 ? round($total_aprobados/$te_global*100,1) : null,
    'filtros_activos'  => [
        'curso_id'   => $filtro_curso,
        'materia_id' => $filtro_materia,
    ],
], JSON_UNESCAPED_UNICODE);
