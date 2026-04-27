<?php
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');

$me = require_role(['admin','director','subdirector']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'Método no permitido']); exit; }

$body    = json_decode(file_get_contents('php://input'), true);
$ids     = $body['ids']     ?? [];
$subject = trim($body['subject'] ?? '');
$message = trim($body['message']  ?? '');

if (!$subject || !$message) { http_response_code(400); echo json_encode(['error'=>'Asunto y mensaje obligatorios']); exit; }
if (empty($ids)) { http_response_code(400); echo json_encode(['error'=>'Seleccioná al menos un destinatario']); exit; }

// Buscar emails en personal y en tabla email
$emails = [];
foreach ($ids as $id) {
    [$tipo, $dni] = explode('_', $id, 2);
    $dni = (int)$dni;
    if ($tipo === 'P') {
        $row = db_row('SELECT email FROM personal WHERE dni=? AND email != ?', [$dni, '0']);
        if ($row) $emails[] = $row['email'];
    } else {
        $row = db_row('SELECT email FROM email WHERE dni=?', [$dni]);
        if ($row) $emails[] = $row['email'];
    }
}

if (empty($emails)) { http_response_code(400); echo json_encode(['error'=>'Ningún destinatario tiene email registrado']); exit; }

$from    = !empty($me['email']) ? $me['email'] : 'saep@escuela.edu.ar';
$headers = implode("\r\n", ["From: SAEP <{$from}>", "Reply-To: {$from}", "MIME-Version: 1.0", "Content-Type: text/plain; charset=UTF-8"]);

$sent = 0; $failed = [];
foreach ($emails as $email) {
    if (@mail($email, $subject, $message, $headers)) $sent++;
    else $failed[] = $email;
}

echo json_encode(['success'=>true, 'sent'=>$sent, 'failed'=>$failed, 'total'=>count($emails)]);
