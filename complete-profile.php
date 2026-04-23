<?php
session_start();
require_once 'config.php'; require_once 'lib/db.php';
if (!isset($_SESSION['github_id'])) { header('Location: index.php'); exit; }
$users=db_read(USERS_FILE); $user=db_find($users,'github_id',$_SESSION['github_id']);
if (!$user||$user['status']==='approved') { header('Location: dashboard.php'); exit; }
if ($user['status']==='pending_approval') { header('Location: pending.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>SAEP — Completar perfil</title>

<link href="assets/css/global.css" rel="stylesheet">
    <link href="assets/css/complete-profile.css" rel="stylesheet">
</head>
<body>
<div class="card">
    <div class="card-header">
        <img class="av" src="<?=htmlspecialchars($user['avatar']??'')?>" alt="">
        <div><div class="uname"><?=htmlspecialchars($user['username'])?></div><div class="uemail"><?=htmlspecialchars($user['email']??'')?></div></div>
    </div>
    <h2>Completar perfil institucional</h2>
    <p class="hint">Completá tus datos para que el administrador pueda verificar tu identidad y asignarte un rol.</p>
    <div class="grid">
        <div><label>Nombre</label><input id="nombre" placeholder="Juan" autocomplete="given-name"></div>
        <div><label>Apellido</label><input id="apellido" placeholder="Pérez" autocomplete="family-name"></div>
        <div class="full"><label>DNI</label><input id="dni" placeholder="12345678" maxlength="8" inputmode="numeric"></div>
        <div class="full"><label>Teléfono (opcional)</label><input id="telefono" placeholder="2241xxxxxx" inputmode="numeric"></div>
    </div>
    <div class="err" id="err"></div>
    <button class="btn" id="btn" onclick="submit()">Enviar solicitud</button>
</div>
<script src="assets/js/complete-profile.js"></script>
</body>
</html>
