<?php
session_start();
require_once 'config.php';
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
$params = http_build_query(['client_id'=>GITHUB_CLIENT_ID,'redirect_uri'=>GITHUB_REDIRECT_URI,'scope'=>GITHUB_SCOPE,'state'=>$state]);
header('Location: https://github.com/login/oauth/authorize?' . $params);
exit;
