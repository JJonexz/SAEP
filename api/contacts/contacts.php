<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

header('Content-Type: application/json');
$me     = require_role(['admin', 'director', 'subdirector', 'preceptor', 'profesor']);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $alumno_id = $_GET['alumno_id'] ?? null;
    if (!$alumno_id) {
        http_response_code(400);
        echo json_encode(['error' => 'alumno_id requerido']);
        exit;
    }

    $contacts = db_read(CONTACTS_FILE);
    $entry    = null;
    foreach ($contacts as $c) {
        if ((string)$c['alumno_id'] === (string)$alumno_id) {
            $entry = $c;
            break;
        }
    }

    echo json_encode($entry ? $entry['contactos'] : []);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);