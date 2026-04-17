<?php
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');

$me = require_role(['admin','director','subdirector']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error'=>'Método no permitido']); exit;
}

$body    = json_decode(file_get_contents('php://input'), true);
$ids     = $body['ids']     ?? [];
$subject = trim($body['subject'] ?? '');
$message = trim($body['message']  ?? '');

if (!$subject || !$message) {
    http_response_code(400); echo json_encode(['error'=>'Asunto y mensaje son obligatorios']); exit;
}
if (empty($ids) || !is_array($ids)) {
    http_response_code(400); echo json_encode(['error'=>'Seleccioná al menos un destinatario']); exit;
}

$users = db_read(USERS_FILE);
$recipients = array_filter($users, fn($u) => in_array($u['id'], $ids) && !empty($u['email']));
$recipients = array_values($recipients);

if (empty($recipients)) {
    http_response_code(400); echo json_encode(['error'=>'Ningún destinatario seleccionado tiene email registrado']); exit;
}

$from    = !empty($me['email']) ? $me['email'] : 'saep@escuela.edu.ar';
$headers = implode("\r\n", [
    "From: SAEP <{$from}>",
    "Reply-To: {$from}",
    "MIME-Version: 1.0",
    "Content-Type: text/plain; charset=UTF-8",
]);

$sent = 0; $failed = [];
foreach ($recipients as $u) {
    $ok = @mail($u['email'], $subject, $message, $headers);
    if ($ok) $sent++;
    else $failed[] = $u['email'];
}

echo json_encode([
    'success' => true,
    'sent'    => $sent,
    'failed'  => $failed,
    'total'   => count($recipients),
]);

echo json_encode([
    'success' => true,
    'sent'    => $sent,
    'failed'  => $failed,
    'total'   => count($recipients),
]);
