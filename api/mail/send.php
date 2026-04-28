<?php
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');

$me = require_role(['admin','director','subdirector']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// ── Leer body ──────────────────────────────────────────────────────────────
$body    = json_decode(file_get_contents('php://input'), true);
$ids     = $body['ids']     ?? [];
$subject = trim($body['subject'] ?? '');
$message = trim($body['message']  ?? '');

// ── Validaciones básicas ───────────────────────────────────────────────────
if (!$subject || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'Asunto y mensaje son obligatorios']);
    exit;
}
if (empty($ids) || !is_array($ids)) {
    http_response_code(400);
    echo json_encode(['error' => 'Seleccioná al menos un destinatario']);
    exit;
}

// ── Resolver destinatarios ─────────────────────────────────────────────────
$users      = db_read(USERS_FILE);
$recipients = array_values(
    array_filter($users, fn($u) => in_array($u['id'], $ids) && !empty($u['email']))
);

if (empty($recipients)) {
    http_response_code(400);
    echo json_encode(['error' => 'Ningún destinatario seleccionado tiene email registrado']);
    exit;
}

// ── Resend ─────────────────────────────────────────────────────────────────
// Cargá la clave desde una variable de entorno o desde config.php.     
// Ejemplo con variable de entorno: putenv("RESEND_API_KEY=re_xxx") en el servidor.
$apiKey = 're_dGE1jwUr_H8imrmsSPzi4DuKhq3haxETP';

if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'API key de Resend no configurada']);
    exit;
}

$from        = 'SAEP <onboarding@resend.dev>';
$htmlMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

$sent   = 0;
$failed = [];

foreach ($recipients as $u) {
    $toAddress = trim($u['email']);
    $toName    = trim(($u['apellido'] ?? '') . ' ' . ($u['nombre'] ?? $u['username'] ?? ''));

    $payload = [
        'from'    => $from,
        'to'      => [empty($toName) ? $toAddress : "{$toName} <{$toAddress}>"],
        'subject' => $subject,
        'html'    => "<p>{$htmlMessage}</p>",
    ];

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer {$apiKey}",
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response  = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Resend devuelve 200/201 en éxito
    if (!$curlError && in_array($httpCode, [200, 201])) {
        $sent++;
    } else {
        $failed[] = $toAddress;
        // Loguear el error real para debugging (no lo exponemos al cliente)
        error_log("Resend error [{$httpCode}] para {$toAddress}: " . ($curlError ?: $response));
    }
}

echo json_encode([
    'success' => true,
    'sent'    => $sent,
    'failed'  => $failed,
    'total'   => count($recipients),
]);
