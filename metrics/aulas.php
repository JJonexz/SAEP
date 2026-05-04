<?php
/**
 * SAEP — Métricas: Aulas
 * Capacidad, ocupación y uso por aula.
 * Preparado para horarios futuros.
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

$rooms   = db_read(ROOMS_FILE);
$courses = db_read(COURSES_FILE);

$cursos_idx = [];
foreach ($courses as $c) $cursos_idx[$c['id']] = $c;

$aulas = [];
$capacidad_total = 0;
$alumnos_total   = 0;

foreach ($rooms as $r) {
    $capacidad = (int)($r['capacidad'] ?? 0);
    $capacidad_total += $capacidad;

    // Contar alumnos del curso asignado al aula
    $cant_alumnos = 0;
    $curso_nombre = null;
    if (!empty($r['curso_id']) && isset($cursos_idx[$r['curso_id']])) {
        $curso        = $cursos_idx[$r['curso_id']];
        $cant_alumnos = count($curso['alumnos'] ?? []);
        $curso_nombre = $curso['nombre'];
    }
    $alumnos_total += $cant_alumnos;

    $pct_ocupacion = $capacidad > 0 ? round($cant_alumnos / $capacidad * 100, 1) : 0;

    $aulas[] = [
        'id'           => $r['id'],
        'nombre'       => $r['nombre'],
        'capacidad'    => $capacidad,
        'ubicacion'    => $r['ubicacion'] ?? null,
        'curso_id'     => $r['curso_id'] ?? null,
        'curso_nombre' => $curso_nombre,
        'cant_alumnos' => $cant_alumnos,
        'pct_ocupacion'=> $pct_ocupacion,
        // Preparado para horarios futuros
        'horarios'     => [],
        'horas_libres' => null,
    ];
}

// Ordenar por ocupación desc
usort($aulas, fn($a,$b) => $b['pct_ocupacion'] <=> $a['pct_ocupacion']);

$pct_ocupacion_gral = $capacidad_total > 0 ? round($alumnos_total / $capacidad_total * 100, 1) : 0;

// Highlights
$mas_usada    = count($aulas) > 0 ? $aulas[0]['nombre'] : null;
$menos_usada  = count($aulas) > 0 ? end($aulas)['nombre'] : null;
$sin_asignar  = count(array_filter($aulas, fn($a) => !$a['curso_id']));

echo json_encode([
    'aulas'               => $aulas,
    'capacidad_total'     => $capacidad_total,
    'alumnos_total'       => $alumnos_total,
    'pct_ocupacion_gral'  => $pct_ocupacion_gral,
    'aula_mas_usada'      => $mas_usada,
    'aula_menos_usada'    => $menos_usada,
    'aulas_sin_asignar'   => $sin_asignar,
    'total_aulas'         => count($aulas),
], JSON_UNESCAPED_UNICODE);
