<?php
session_start();
require_once 'config.php'; require_once 'lib/db.php';
if (!isset($_SESSION['github_id'])) { header('Location: index.php'); exit; }
$users=db_read(USERS_FILE); $user=db_find($users,'github_id',$_SESSION['github_id']);
if (!$user) { header('Location: index.php'); exit; }
if ($user['status']==='approved') { header('Location: dashboard.php'); exit; }
if ($user['status']==='pending_profile') { header('Location: complete-profile.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><title>SAEP — Pendiente</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="assets/pending.css" rel="stylesheet">
</head>
<body>
<div class="card">
    <div class="icon">⏳</div>
    <h2>Cuenta en revisión</h2>
    <p>Tu solicitud fue enviada correctamente. Un administrador revisará tus datos y activará tu cuenta a la brevedad.</p>
    <div class="info">
        <div class="info-row"><span>Usuario</span><strong><?=htmlspecialchars($user['username'])?></strong></div>
        <div class="info-row"><span>Nombre</span><strong><?=htmlspecialchars(($user['nombre']??'').' '.($user['apellido']??''))?></strong></div>
        <div class="info-row"><span>DNI</span><strong><?=htmlspecialchars($user['dni']??'—')?></strong></div>
        <div class="info-row"><span>Estado</span><strong><span class="dot"></span>Pendiente de aprobación</strong></div>
    </div>
    <div class="btns">
        <button class="btn" onclick="check()">Verificar estado</button>
        <button class="btn" onclick="location.href='logout.php'">Salir</button>
    </div>
</div>
<script src="assets/pending.js"></script>
</body>
</html>
