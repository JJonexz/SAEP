<?php
// ══════════════════════════════════════════════════════
//  send.debug.php  —  VERSION DEBUG (no usar en prod)
// ══════════════════════════════════════════════════════
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');

$debug = [];   // acumula toda la info para devolver al cliente

$me = require_role(['admin','director','subdirector']);
$debug['step_auth'] = [
    'ok'   => true,
    'user' => $me['username'] ?? $me['id'] ?? '?',
    'role' => $me['role']     ?? '?',
];

// ── Método ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido', 'debug' => $debug]);
    exit;
}

// ── Body raw ───────────────────────────────────────────
$rawBody = file_get_contents('php://input');
$body    = json_decode($rawBody, true);
$debug['step_body'] = [
    'raw'          => $rawBody,
    'json_decoded' => $body,
    'json_error'   => json_last_error_msg(),
];

$ids     = $body['ids']     ?? [];
$subject = trim($body['subject'] ?? '');
$message = trim($body['message']  ?? '');

$debug['step_input'] = [
    'ids_received' => $ids,
    'ids_count'    => count($ids),
    'subject'      => $subject,
    'message_len'  => strlen($message),
];

// ── Validaciones ───────────────────────────────────────
if (!$subject || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'Asunto y mensaje son obligatorios', 'debug' => $debug]);
    exit;
}
if (empty($ids) || !is_array($ids)) {
    http_response_code(400);
    echo json_encode(['error' => 'Seleccioná al menos un destinatario', 'debug' => $debug]);
    exit;
}

// ── Resolver destinatarios ─────────────────────────────
$users = db_read(USERS_FILE);
$debug['step_users'] = [
    'total_in_db' => count($users),
    'ids_buscados' => $ids,
];

$recipients = array_values(
    array_filter($users, fn($u) => in_array($u['id'], $ids) && !empty($u['email']))
);

$sinEmail = array_values(
    array_filter($users, fn($u) => in_array($u['id'], $ids) && empty($u['email']))
);

$debug['step_recipients'] = [
    'con_email'   => array_map(fn($u) => ['id'=>$u['id'],'email'=>$u['email'],'nombre'=>($u['nombre']??'')], $recipients),
    'sin_email'   => array_map(fn($u) => ['id'=>$u['id'],'username'=>$u['username']??''], $sinEmail),
    'no_encontrados' => array_values(array_filter($ids, fn($id) => !in_array($id, array_column($users,'id')))),
];

if (empty($recipients)) {
    http_response_code(400);
    echo json_encode(['error' => 'Ningún destinatario seleccionado tiene email registrado', 'debug' => $debug]);
    exit;
}

// ── API Key ────────────────────────────────────────────
$apiKey = 're_dGE1jwUr_H8imrmsSPzi4DuKhq3haxETP';  // hardcodeada para debug
$from   = 'SAEP <onboarding@resend.dev>';            // dominio sin verificar → usar resend.dev

$debug['step_config'] = [
    'api_key_set'    => !empty($apiKey),
    'api_key_prefix' => substr($apiKey, 0, 8) . '...',  // no exponemos la key completa
    'from'           => $from,
];

if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'API key no configurada', 'debug' => $debug]);
    exit;
}

// ── Envíos ─────────────────────────────────────────────
$sent      = 0;
$failed    = [];
$sendLog   = [];

$htmlMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

foreach ($recipients as $u) {
    $toAddress = trim($u['email']);
    $toName    = trim(($u['apellido'] ?? '') . ' ' . ($u['nombre'] ?? $u['username'] ?? ''));
    $toFull    = empty($toName) ? $toAddress : "{$toName} <{$toAddress}>";

    $payload = [
        'from'    => $from,
        'to'      => [$toFull],
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
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT    => 10,
    ]);

    $response  = curl_exec($ch);
    $curlError = curl_error($ch);
    $curlErrNo = curl_errno($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlInfo  = curl_getinfo($ch);
    curl_close($ch);

    $resendBody = json_decode($response, true);
    $success    = !$curlError && in_array($httpCode, [200, 201]);

    if ($success) $sent++;
    else $failed[] = $toAddress;

    $sendLog[] = [
        'to'            => $toFull,
        'payload'       => $payload,
        'http_code'     => $httpCode,
        'curl_error'    => $curlError ?: null,
        'curl_errno'    => $curlErrNo ?: null,
        'resend_raw'    => $response,
        'resend_parsed' => $resendBody,
        'total_time_ms' => round($curlInfo['total_time'] * 1000),
        'success'       => $success,
    ];
}

$debug['step_send_log'] = $sendLog;

// ── Respuesta final ────────────────────────────────────
$result = [
    'success' => true,
    'sent'    => $sent,
    'failed'  => $failed,
    'total'   => count($recipients),
    'debug'   => $debug,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
