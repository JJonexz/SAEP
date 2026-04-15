<?php
session_start();
require_once 'config.php'; require_once 'lib/db.php'; require_once 'lib/auth.php';
if (!isset($_SESSION['github_id'])) { header('Location: index.php'); exit; }
$users=db_read(USERS_FILE); $user=db_find($users,'github_id',$_SESSION['github_id']);
if (!$user) { header('Location: index.php'); exit; }
if ($user['status']==='pending_profile')  { header('Location: complete-profile.php'); exit; }
if ($user['status']==='pending_approval') { header('Location: pending.php'); exit; }
$role=$user['role'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>SAEP</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="assets/dashboard.css" rel="stylesheet">
</head>
<body>
<div class="app">

<!-- Header -->
<header>
    <div class="hdr-left">
        <button class="hamburger" id="hamburger-btn" aria-label="Menú" onclick="toggleSidebar()">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
        <div class="logo-badge">S</div>
        <div><div class="brand-name">SAEP</div><div class="brand-sub">Sistema de Administración Educativa y Proyectos</div></div>
    </div>
    <div class="hdr-right">
        <?php if($user['avatar']): ?><img class="hdr-av" src="<?=htmlspecialchars($user['avatar'])?>" alt=""><?php endif; ?>
        <span class="hdr-name"><?=htmlspecialchars(($user['nombre']??'')?($user['apellido'].' '.$user['nombre']):$user['username'])?></span>
        <span class="role-pill <?=$role?>"><?=$role?></span>
        <button class="btn-out" onclick="location.href='logout.php'">Salir</button>
    </div>
</header>

<!-- Sidebar overlay (mobile) -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside id="main-sidebar">
    <div class="nav-sec">
        <div class="nav-lbl">Principal</div>
        <div class="nav-item active" onclick="nav('inicio')" id="nav-inicio">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Inicio
        </div>
        <div class="nav-item" onclick="nav('repos')" id="nav-repos">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22"/></svg>Repositorios
        </div>
        <?php if(in_array($role,['admin','director','subdirector','profesor','preceptor'])): ?>
        <div class="nav-item" onclick="nav('works')" id="nav-works">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>Trabajos
        </div>
        <div class="nav-item" onclick="nav('grades')" id="nav-grades">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>Calificaciones
        </div>
        <?php endif; ?>
        <?php if($role==='alumno'): ?>
        <div class="nav-item" onclick="nav('my-works')" id="nav-my-works">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Mis trabajos
        </div>
        <div class="nav-item" onclick="nav('my-grades')" id="nav-my-grades">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>Mis notas
        </div>
        <?php endif; ?>
    </div>
    <?php if(in_array($role,['admin','director','subdirector'])): ?>
    <div class="nav-sec">
        <div class="nav-lbl">Institución</div>
        <div class="nav-item" onclick="nav('users')" id="nav-users">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>Usuarios
        </div>
        <div class="nav-item" onclick="nav('courses')" id="nav-courses">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>Cursos
        </div>
        <div class="nav-item" onclick="nav('rooms')" id="nav-rooms">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>Aulas
        </div>
        <div class="nav-item" onclick="nav('mail')" id="nav-mail">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/></svg>Correos
        </div>
    </div>
    <?php endif; ?>
</aside>

<main id="main">

<!-- INICIO -->
<div class="panel visible" id="panel-inicio">
    <div class="ph"><div><h2>Inicio</h2><div class="sub">Bienvenido, <?=htmlspecialchars(($user['nombre']??$user['username']))?></div></div></div>
    <div class="pb">
        <div class="stats" id="stats-row"></div>
        <div id="pending-alerts"></div>
    </div>
</div>

<!-- REPOS -->
<div class="panel" id="panel-repos">
    <div class="ph"><div><h2>Repositorios</h2></div><button class="btn btn-navy" onclick="openCreateRepoModal()">+ Nuevo repositorio</button></div>
    <div class="repo-layout">
        <div class="repo-sidebar" id="repo-sidebar"><div class="empty">Cargando...</div></div>
        <div class="repo-main-area" id="repo-main-area"><div class="empty">Seleccioná un repositorio</div></div>
    </div>
</div>

<!-- TRABAJOS (staff) -->
<?php if(in_array($role,['admin','director','subdirector','profesor','preceptor'])): ?>
<div class="panel" id="panel-works">
    <div class="ph"><div><h2>Trabajos y entregas</h2></div><button class="btn btn-navy" onclick="openCreateWorkModal()">+ Nuevo trabajo</button></div>
    <div class="pb">
        <div class="fgrid mb1" style="max-width:29.167vw">
            <div class="field"><label>Curso</label><select id="wf-curso" onchange="loadWorks()"><option value="">Todos</option></select></div>
            <div class="field"><label>Materia</label><select id="wf-materia" onchange="loadWorks()"><option value="">Todas</option></select></div>
        </div>
        <div id="works-list"></div>
    </div>
</div>
<?php endif; ?>

<!-- MIS TRABAJOS (alumno) -->
<?php if($role==='alumno'): ?>
<div class="panel" id="panel-my-works">
    <div class="ph"><div><h2>Mis trabajos</h2></div></div>
    <div class="pb"><div id="my-works-list"></div></div>
</div>
<?php endif; ?>

<!-- CALIFICACIONES (staff) -->
<?php if(in_array($role,['admin','director','subdirector','profesor','preceptor'])): ?>
<div class="panel" id="panel-grades">
    <div class="ph"><h2>Calificaciones cuatrimestrales</h2></div>
    <div class="pb">
        <div class="tabs">
            <div class="tab active" onclick="gTab('ver')">Ver calificaciones</div>
            <?php if(in_array($role,['admin','director','subdirector','profesor'])): ?>
            <div class="tab" onclick="gTab('cargar')">Cargar nota</div>
            <?php endif; ?>
        </div>
        <div class="tab-content visible" id="gtab-ver">
            <div class="fgrid mb1" style="max-width:29.167vw">
                <div class="field"><label>Curso</label><select id="gv-curso" onchange="loadGradesTable()"><option value="">Todos</option></select></div>
                <div class="field"><label>Cuatrimestre</label><select id="gv-cuatri" onchange="loadGradesTable()"><option value="">Ambos</option><option value="1">1°</option><option value="2">2°</option></select></div>
            </div>
            <div class="tbl-wrap" id="grades-tbl"></div>
        </div>
        <?php if(in_array($role,['admin','director','subdirector','profesor'])): ?>
        <div class="tab-content" id="gtab-cargar">
            <div class="fgrid" style="max-width:31.25vw">
                <div class="field"><label>Curso</label><select id="gc-curso" onchange="gcLoadStudents()"><option value="">Seleccionar...</option></select></div>
                <div class="field"><label>Materia</label><select id="gc-materia"><option value="">Seleccionar...</option></select></div>
                <div class="field"><label>Alumno</label><select id="gc-alumno"><option value="">Seleccionar...</option></select></div>
                <div class="field"><label>Cuatrimestre</label><select id="gc-cuatri"><option value="1">1°</option><option value="2">2°</option></select></div>
                <div class="field"><label>Nota (1–10)</label><input type="number" id="gc-nota" min="1" max="10" step="0.1" placeholder="7"></div>
                <div class="field"><label>Concepto</label><select id="gc-concepto"><option value="">—</option><option>MB</option><option>B</option><option>R</option><option>M</option></select></div>
                <div class="field"><label>Asistencia %</label><input type="number" id="gc-asist" min="0" max="100" placeholder="80"></div>
                <div class="field"><label>Estado</label><select id="gc-estado"><option value="pendiente">Pendiente</option><option value="aprobado">Aprobado</option><option value="desaprobado">Desaprobado</option></select></div>
            </div>
            <div class="err-msg" id="gc-err"></div>
            <button class="btn btn-navy" onclick="saveGrade()" style="margin-top:.75rem">Guardar calificación</button>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- MIS NOTAS (alumno) -->
<?php if($role==='alumno'): ?>
<div class="panel" id="panel-my-grades">
    <div class="ph"><h2>Mis calificaciones</h2></div>
    <div class="pb">
        <div class="field mb1" style="max-width:10.417vw"><label>Cuatrimestre</label><select id="mg-cuatri" onchange="loadMyGrades()"><option value="">Ambos</option><option value="1">1°</option><option value="2">2°</option></select></div>
        <div class="tbl-wrap" id="my-grades-tbl"></div>
    </div>
</div>
<?php endif; ?>

<!-- USUARIOS -->
<?php if(in_array($role,['admin','director','subdirector'])): ?>
<div class="panel" id="panel-users">
    <div class="ph">
        <div><h2>Gestión de usuarios</h2></div>
        <div class="btn-group">
            <?php if($role==='admin'): ?>
            <button class="btn btn-navy" onclick="openManualUserModal()">+ Crear usuario</button>
            <?php endif; ?>
            <select id="uf-status" onchange="filterUsers()" style="font-family:var(--font);font-size:.78rem;padding:.4rem .7rem;border:0.052vw solid var(--border);border-radius:var(--radius);color:var(--text2)">
                <option value="">Todos</option><option value="pending_approval">Pendientes</option><option value="approved">Aprobados</option><option value="rejected">Rechazados</option>
            </select>
        </div>
    </div>
    <div class="pb"><div class="tbl-wrap" id="users-tbl"></div></div>
</div>
<?php endif; ?>

<!-- CURSOS -->
<?php if(in_array($role,['admin','director','subdirector'])): ?>
<div class="panel" id="panel-courses">
    <div class="ph"><h2>Cursos y materias</h2><button class="btn btn-navy" onclick="openCourseModal()">+ Nuevo curso</button></div>
    <div class="pb"><div class="cards" id="courses-cards"></div></div>
</div>
<?php endif; ?>

<!-- AULAS -->
<?php if(in_array($role,['admin','director','subdirector'])): ?>
<div class="panel" id="panel-rooms">
    <div class="ph"><h2>Aulas</h2><button class="btn btn-navy" onclick="openRoomModal()">+ Nueva aula</button></div>
    <div class="pb"><div class="tbl-wrap" id="rooms-tbl"></div></div>
</div>
<?php endif; ?>

<!-- CORREOS -->
<?php if(in_array($role,['admin','director','subdirector'])): ?>
<div class="panel" id="panel-mail">
    <div class="ph">
        <div><h2>Enviar correo</h2><div class="sub">Enviá mensajes a usuarios del sistema</div></div>
        <div id="ml-counter" style="font-size:.78rem;font-weight:600;color:var(--navy);background:var(--navy-faint);padding:.3rem .75rem;border-radius:1.042vw;display:none"></div>
    </div>
    <div class="pb ml-container">

        <!-- ── Columna izquierda: selector de usuarios ── -->
        <div class="ml-sidebar">

            <!-- Filtros -->
            <div style="padding:.75rem;border-bottom:0.093vh solid var(--border);display:flex;flex-direction:column;gap:.5rem">
                <input id="ml-search" type="text" placeholder="Buscar por nombre, email, DNI..."
                    oninput="mlFilter()"
                    style="width:100%;border:0.052vw solid var(--border);border-radius:var(--radius);padding:.45rem .65rem;font-family:var(--font);font-size:.78rem;color:var(--text)">

                <div style="display:flex;gap:.4rem;flex-wrap:wrap">
                    <select id="ml-f-rol" onchange="mlFilter()" style="flex:1;font-family:var(--font);font-size:.73rem;padding:.3rem .45rem;border:0.052vw solid var(--border);border-radius:var(--radius);color:var(--text2)">
                        <option value="">Todos los roles</option>
                        <option value="admin">Admin</option>
                        <option value="director">Director</option>
                        <option value="subdirector">Subdirector</option>
                        <option value="profesor">Profesor</option>
                        <option value="preceptor">Preceptor</option>
                        <option value="alumno">Alumno</option>
                    </select>
                    <select id="ml-f-estado" onchange="mlFilter()" style="flex:1;font-family:var(--font);font-size:.73rem;padding:.3rem .45rem;border:0.052vw solid var(--border);border-radius:var(--radius);color:var(--text2)">
                        <option value="">Todos los estados</option>
                        <option value="approved">Aprobados</option>
                        <option value="pending_approval">Pendientes</option>
                        <option value="rejected">Rechazados</option>
                        <option value="pending_profile">Sin perfil</option>
                    </select>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center">
                    <label style="display:flex;align-items:center;gap:.35rem;font-size:.73rem;color:var(--muted);cursor:pointer">
                        <input type="checkbox" id="ml-chk-all" onchange="mlToggleAll(this.checked)" style="cursor:pointer">
                        Seleccionar todos los visibles
                    </label>
                    <span id="ml-visible-count" style="font-size:.7rem;color:var(--muted)"></span>
                </div>
            </div>

            <!-- Lista de usuarios -->
            <div id="ml-user-list" style="overflow-y:auto;flex:1"></div>
        </div>

        <!-- ── Columna derecha: redactar ── -->
        <div class="ml-compose">

            <!-- Chips de seleccionados -->
            <div id="ml-chips" style="display:none;background:var(--white);border:0.052vw solid var(--border);border-radius:0.417vw;padding:.6rem .75rem">
                <div style="font-size:.68rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem">Destinatarios seleccionados</div>
                <div id="ml-chips-inner" style="display:flex;flex-wrap:wrap;gap:.3rem"></div>
            </div>

            <div class="field" style="margin:0">
                <label>Asunto</label>
                <input id="ml-subject" placeholder="Reunión de padres — viernes 18/04" style="width:100%;border:0.052vw solid var(--border);border-radius:var(--radius);padding:.55rem .75rem;font-family:var(--font);font-size:.82rem;color:var(--text)">
            </div>

            <div class="field" style="margin:0;display:flex;flex-direction:column">
                <label>Mensaje</label>
                <textarea id="ml-body" placeholder="Escribí el mensaje aquí..." oninput="autoGrow(this)"
                    style="min-height:12.963vh;max-height:59.222vh;width:100%;border:0.052vw solid var(--border);border-radius:var(--radius);padding:.55rem .75rem;font-family:var(--font);font-size:.82rem;color:var(--text);resize:none;overflow:hidden"></textarea>
            </div>

            <div id="ml-err" class="err-msg"></div>
            <div id="ml-ok"  style="display:none;color:var(--green);font-size:.8rem;font-weight:600;padding:.5rem .75rem;background:var(--green-dim);border:0.052vw solid var(--green);border-radius:var(--radius)"></div>

            <div>
                <button class="btn btn-navy" onclick="sendMail()" id="ml-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Enviar correo.
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

</main>
</div>
<div id="modal-root"></div>

<script>
window.ROLE = '<?=$role?>';
window.MY_ID = '<?=$user['id']?>';
</script>
<script src="assets/dashboard.js"></script>
<script>
function toggleSidebar(){
    const aside = document.getElementById('main-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const isOpen = aside.classList.toggle('open');
    overlay.classList.toggle('visible', isOpen);
    document.body.style.overflow = isOpen ? 'hidden' : '';
}
// Close sidebar when a nav item is clicked on mobile
document.querySelectorAll('.nav-item').forEach(function(el){
    el.addEventListener('click', function(){
        if(window.innerWidth <= 768){
            const aside = document.getElementById('main-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            aside.classList.remove('open');
            overlay.classList.remove('visible');
            document.body.style.overflow = '';
        }
    });
});
</script>
</body>
</html>
