<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

header('Content-Type: application/json');
$me = require_role(['admin','director','subdirector']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); 
    echo json_encode(['error'=>'Método no permitido']); 
    exit;
}

$users = db_read(USERS_FILE);
$courses = db_read(COURSES_FILE);
$rooms = db_read(ROOMS_FILE);

$approved = 0;
$pending = 0;

foreach ($users as $u) {
    if (isset($u['status'])) {
        if ($u['status'] === 'approved') {
            $approved++;
        } elseif ($u['status'] === 'pending_approval') {
            $pending++;
        }
    }
}

echo json_encode([
    'users' => [
        'approved' => $approved,
        'pending' => $pending
    ],
    'courses' => is_array($courses) ? count($courses) : 0,
    'rooms' => is_array($rooms) ? count($rooms) : 0
]);
