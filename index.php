<?php
session_start();
require_once 'lib/db.php';
require_once 'config/config.php';
if (isset($_SESSION['github_id'])) {
    $users = db_read(USERS_FILE);
    $user  = db_find($users, 'github_id', $_SESSION['github_id']);
    if ($user) {
        if ($user['status']==='pending_profile')  { header('Location: complete-profile.php'); exit; }
        if ($user['status']==='pending_approval') { header('Location: pending.php'); exit; }
        if ($user['status']==='approved')         { header('Location: dashboard.php'); exit; }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SAEP — Acceso</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="assets/index.css" rel="stylesheet">
</head>
<body>
<div class="top-bar">
    <div class="brand">
        <div class="logo-circle">S</div>
        <div><h1>SAEP</h1><p>Sistema de Administración Educativa y Proyectos</p></div>
    </div>
</div>
<div class="box">
    <div class="box-logo">S</div>
    <h2>Bienvenido a SAEP</h2>
    <p>Sistema de Administración Educativa y Proyectos.<br>Iniciá sesión con tu cuenta de GitHub institucional.</p>
    <a href="login.php" class="btn-gh">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/></svg>
        Entrar con GitHub
    </a>
</div>
</body>
</html>
