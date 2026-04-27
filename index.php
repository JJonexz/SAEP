<?php
session_start();
require_once 'lib/db.php';
require_once 'config.php';
if (isset($_SESSION['user_id'])) {
    $users = db_read(USERS_FILE);
    $user  = db_find($users, 'id', $_SESSION['user_id']);
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
<link href="assets/css/global.css" rel="stylesheet">
<link href="assets/css/index.css" rel="stylesheet">
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
    <p>Sistema de Administración Educativa y Proyectos.<br>Iniciá sesión con tu DNI y contraseña institucional.</p>
    <div id="err" class="err" style="display:none"></div>
    <div style="display:flex;flex-direction:column;gap:12px;width:100%">
        <input id="dni" type="text" placeholder="DNI (sin puntos)" inputmode="numeric" maxlength="8"
               style="padding:10px 14px;border-radius:8px;border:1px solid var(--border,#d1d5db);font-size:1rem;width:100%;box-sizing:border-box">
        <input id="pass" type="password" placeholder="Contraseña"
               style="padding:10px 14px;border-radius:8px;border:1px solid var(--border,#d1d5db);font-size:1rem;width:100%;box-sizing:border-box">
        <button id="btn" onclick="doLogin()"
                style="padding:11px;border-radius:8px;background:#24292e;color:#fff;border:none;font-size:1rem;cursor:pointer;font-weight:600">
            Ingresar
        </button>
    </div>
</div>
<script>
async function doLogin() {
    const dni  = document.getElementById('dni').value.trim();
    const pass = document.getElementById('pass').value;
    const err  = document.getElementById('err');
    const btn  = document.getElementById('btn');
    err.style.display = 'none';
    if (!dni || !pass) { err.textContent = 'Ingresá tu DNI y contraseña.'; err.style.display = 'block'; return; }
    btn.disabled = true; btn.textContent = 'Ingresando...';
    const r = await fetch('api/auth/login.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({dni, password: pass})
    });
    const data = await r.json();
    if (data.redirect) { window.location.href = data.redirect; return; }
    err.textContent = data.error || 'Error al iniciar sesión.';
    err.style.display = 'block';
    btn.disabled = false; btn.textContent = 'Ingresar';
}
document.addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });
</script>
</body>
</html>
