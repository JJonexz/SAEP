<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

function session_user(): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    return db_find_user_by_id($_SESSION['user_id']);
}
function require_auth(): array {
    $user = session_user();
    if (!$user) { http_response_code(401); echo json_encode(['error'=>'No autenticado']); exit; }
    return $user;
}
function require_approved(): array {
    $user = require_auth();
    if ($user['status'] !== 'approved') { http_response_code(403); echo json_encode(['error'=>'Cuenta pendiente']); exit; }
    return $user;
}
function require_role(array $roles): array {
    $user = require_approved();
    if (!in_array($user['role'], $roles)) { http_response_code(403); echo json_encode(['error'=>'Sin permisos']); exit; }
    return $user;
}
function is_admin(array $user): bool { return $user['role'] === 'admin'; }
function can_manage_users(array $user): bool { return in_array($user['role'], ['admin','director','subdirector']); }
function can_manage_courses(array $user): bool { return in_array($user['role'], ['admin','director','subdirector']); }
function can_grade(array $user): bool { return in_array($user['role'], ['admin','director','subdirector','profesor']); }
