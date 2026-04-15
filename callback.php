<?php
session_start();
require_once 'config.php';
require_once 'lib/db.php';

if (!isset($_GET['code'],$_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) { http_response_code(403); exit('Solicitud inválida.'); }
unset($_SESSION['oauth_state']);

$tokenRes  = file_get_contents('https://github.com/login/oauth/access_token', false, stream_context_create(['http'=>['method'=>'POST','header'=>"Content-Type: application/x-www-form-urlencoded\r\nAccept: application/json\r\n",'content'=>http_build_query(['client_id'=>GITHUB_CLIENT_ID,'client_secret'=>GITHUB_CLIENT_SECRET,'code'=>$_GET['code'],'redirect_uri'=>GITHUB_REDIRECT_URI])]]));
$tokenData = json_decode($tokenRes, true);
if (!isset($tokenData['access_token'])) {
    $error = is_array($tokenData) && isset($tokenData['error_description']) ? $tokenData['error_description'] : ($tokenData['error'] ?? 'Respuesta inválida de GitHub');
    http_response_code(500);
    exit('Error al obtener token: ' . $error);
}
$accessToken = $tokenData['access_token'];

function gh_get(string $url, string $token): ?array {
    $res = file_get_contents($url, false, stream_context_create(['http'=>['method'=>'GET','header'=>"Authorization: token $token\r\nUser-Agent: SAEP\r\nAccept: application/json\r\n"]]));
    return $res ? json_decode($res, true) : null;
}

$ghUser = gh_get('https://api.github.com/user', $accessToken);
if (!isset($ghUser['id'])) { http_response_code(500); exit('Error al obtener usuario.'); }

$emails = gh_get('https://api.github.com/user/emails', $accessToken);
$primaryEmail = null;
if (is_array($emails)) { foreach ($emails as $e) { if ($e['primary'] && $e['verified']) { $primaryEmail = $e['email']; break; } } }

$users = db_read(USERS_FILE);
$idx   = db_find_index($users, 'github_id', $ghUser['id']);

if ($idx === -1) {
    $isFirst = count($users) === 0;
    $users[] = [
        'id'=>generate_id(),'github_id'=>$ghUser['id'],'username'=>$ghUser['login'],
        'avatar'=>$ghUser['avatar_url'],'email'=>$primaryEmail,
        'nombre'=>null,'apellido'=>null,'dni'=>null,'telefono'=>null,
        'role'=>$isFirst?'admin':null,'status'=>$isFirst?'approved':'pending_profile',
        'manual'=>false,
    ];
    db_write(USERS_FILE, $users);
    $user = end($users);
} else {
    $users[$idx]['avatar'] = $ghUser['avatar_url'];
    if ($primaryEmail) $users[$idx]['email'] = $primaryEmail;
    db_write(USERS_FILE, $users);
    $user = $users[$idx];
}

$_SESSION['access_token'] = $accessToken;
$_SESSION['github_id']    = $ghUser['id'];

if ($user['status'] === 'pending_profile')  header('Location: complete-profile.php');
elseif ($user['status'] === 'pending_approval') header('Location: pending.php');
else header('Location: dashboard.php');
exit;
