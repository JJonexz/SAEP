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

$body = json_decode(file_get_contents('php://input'), true);

// CREATE contact for an alumno
if ($method === 'POST') {
    $alumno_id = $body['alumno_id'] ?? null;
    if (!$alumno_id) { http_response_code(400); echo json_encode(['error' => 'alumno_id requerido']); exit; }

    $nombre     = trim($body['nombre']     ?? '');
    $apellido   = trim($body['apellido']   ?? '');
    $parentesco = trim($body['parentesco'] ?? '');
    if (!$nombre || !$apellido || !$parentesco) {
        http_response_code(400);
        echo json_encode(['error' => 'Nombre, apellido y parentesco son obligatorios']);
        exit;
    }

    $nuevo = [
        'id'         => generate_id(),
        'nombre'     => strtoupper($nombre),
        'apellido'   => strtoupper($apellido),
        'parentesco' => strtoupper($parentesco),
        'ocupacion'  => isset($body['ocupacion'])  && $body['ocupacion']  !== '' ? trim($body['ocupacion'])  : null,
        'sexo'       => isset($body['sexo'])       && $body['sexo']       !== '' ? $body['sexo']             : null,
        'telefono'   => isset($body['telefono'])   && $body['telefono']   !== '' ? trim($body['telefono'])   : null,
        'domicilio'  => isset($body['domicilio'])  && $body['domicilio']  !== '' ? trim($body['domicilio'])  : null,
    ];

    $contacts = db_read(CONTACTS_FILE);
    $found = false;
    foreach ($contacts as &$entry) {
        if ((string)$entry['alumno_id'] === (string)$alumno_id) {
            $entry['contactos'][] = $nuevo;
            $found = true;
            break;
        }
    }
    unset($entry);

    if (!$found) {
        $contacts[] = ['alumno_id' => $alumno_id, 'contactos' => [$nuevo]];
    }

    db_write(CONTACTS_FILE, $contacts);
    echo json_encode(['success' => true, 'contact' => $nuevo]);
    exit;
}

// UPDATE contact
if ($method === 'PATCH') {
    $alumno_id  = $body['alumno_id']  ?? null;
    $contact_id = $body['contact_id'] ?? null;
    if (!$alumno_id || !$contact_id) { http_response_code(400); echo json_encode(['error' => 'alumno_id y contact_id requeridos']); exit; }

    $contacts = db_read(CONTACTS_FILE);
    $updated  = false;
    $campos   = ['nombre','apellido','parentesco','ocupacion','sexo','telefono','domicilio'];

    foreach ($contacts as &$entry) {
        if ((string)$entry['alumno_id'] === (string)$alumno_id) {
            foreach ($entry['contactos'] as &$c) {
                if ((string)$c['id'] === (string)$contact_id) {
                    foreach ($campos as $f) {
                        if (array_key_exists($f, $body)) {
                            $val = $body[$f];
                            if (in_array($f, ['nombre','apellido','parentesco']) && $val !== null) {
                                $val = strtoupper(trim($val));
                            }
                            $c[$f] = $val;
                        }
                    }
                    $updated = true;
                    break 2;
                }
            }
            unset($c);
        }
    }
    unset($entry);

    if (!$updated) { http_response_code(404); echo json_encode(['error' => 'Contacto no encontrado']); exit; }

    db_write(CONTACTS_FILE, $contacts);
    echo json_encode(['success' => true]);
    exit;
}

// DELETE contact
if ($method === 'DELETE') {
    $alumno_id  = $body['alumno_id']  ?? null;
    $contact_id = $body['contact_id'] ?? null;
    if (!$alumno_id || !$contact_id) { http_response_code(400); echo json_encode(['error' => 'alumno_id y contact_id requeridos']); exit; }

    $contacts = db_read(CONTACTS_FILE);
    $deleted  = false;

    foreach ($contacts as &$entry) {
        if ((string)$entry['alumno_id'] === (string)$alumno_id) {
            $before = count($entry['contactos']);
            $entry['contactos'] = array_values(array_filter($entry['contactos'], fn($c) => (string)$c['id'] !== (string)$contact_id));
            if (count($entry['contactos']) < $before) { $deleted = true; }
            break;
        }
    }
    unset($entry);

    if (!$deleted) { http_response_code(404); echo json_encode(['error' => 'Contacto no encontrado']); exit; }

    db_write(CONTACTS_FILE, $contacts);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
