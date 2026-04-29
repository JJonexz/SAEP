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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Barlow+Condensed:wght@600;700;800&display=swap" rel="stylesheet">
<link href="assets/css/global.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
<link href="assets/css/lab.css" rel="stylesheet">
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
        
        <div class="nav-item" onclick="nav('laboratorios')" id="nav-rooms">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>Aulas y Lab.
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

<!-- Modal cargar nota (campos ocultos reutilizados) -->
<div id="gc-hidden-fields" style="display:none">
    <select id="gc-curso"></select>
    <select id="gc-materia"></select>
    <select id="gc-alumno"></select>
    <select id="gc-cuatri"><option value="1">1° Informe</option><option value="2">1°</option><option value="3">2° Informe</option><option value="4">2°</option></select>
    <input type="number" id="gc-nota">
    <select id="gc-concepto"><option value="">—</option><option value="TED">TED</option><option value="TEP">TEP</option><option value="TEA">TEA</option></select>
    <input type="number" id="gc-asist">
    <select id="gc-estado"><option value="pendiente">Pendiente</option><option value="aprobado">Aprobado</option><option value="desaprobado">Desaprobado</option></select>
    <div id="gc-err"></div>
    <!-- campos para gv (ver) que siguen funcionando internamente -->
    <select id="gv-curso"></select>
    <input type="text" id="gv-search">
    <div id="grades-tbl"></div>
</div>

<!-- MIS NOTAS (alumno) -->
<?php if($role==='alumno'): ?>
<div class="panel" id="panel-my-grades">
    <div class="ph"><h2>Mis calificaciones</h2></div>
    <div class="pb">
        <div class="field mb1" style="max-width:200px"><label>Cuatrimestre</label><select id="mg-cuatri" onchange="loadMyGrades()"><option value="">Todos</option><option value="1_informe">1° Informe</option><option value="1">1°</option><option value="2_informe">2° Informe</option><option value="2">2°</option></select></div>
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
            <input type="text" id="uf-search" placeholder="Buscar por nombre, DNI, email..." oninput="filterUsers()" style="font-family:var(--font);font-size:.78rem;padding:.4rem .7rem;border:0.052vw solid var(--border);border-radius:var(--radius);color:var(--text);width:200px">
            <select id="uf-status" onchange="filterUsers()" style="font-family:var(--font);font-size:.78rem;padding:.4rem .7rem;border:0.052vw solid var(--border);border-radius:var(--radius);color:var(--text2)">
                <option value="">Todos</option><option value="pending_approval">Pendientes</option><option value="approved">Aprobados</option><option value="rejected">Rechazados</option>
            </select>
        </div>
    </div>
    <div class="pb">
        <div class="tbl-wrap" id="users-tbl"></div>
        <div id="users-pagination" style="display:flex;justify-content:space-between;align-items:center;padding-top:1rem;margin-top:1rem;border-top:1px solid var(--border);">
            <div style="font-size:.75rem;color:var(--muted)" id="users-page-info"></div>
            <div class="btn-group">
                <button class="btn btn-outline" id="users-prev-btn" onclick="prevUsersPage()" style="font-size:.7rem;padding:.28rem .55rem">Anterior</button>
                <button class="btn btn-outline" id="users-next-btn" onclick="nextUsersPage()" style="font-size:.7rem;padding:.28rem .55rem">Siguiente</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- CURSOS -->
<?php if(in_array($role,['admin','director','subdirector'])): ?>
<div class="panel" id="panel-courses">
    <div class="ph"><h2>Cursos y materias</h2><button class="btn btn-navy" onclick="openCourseModal()">+ Nuevo curso</button></div>
    <div class="pb">
        <div class="courses-filter-row" id="courses-filter">
            <button class="filter-btn filter-btn-active" onclick="filterCourses(null)">Todos</button>
            <button class="filter-btn filter-btn-prog" onclick="filterCourses('prog')">Programación</button>
            <button class="filter-btn filter-btn-mmo" onclick="filterCourses('mmo')">MMO</button>
            <button class="filter-btn filter-btn-turismo" onclick="filterCourses('turismo')">Turismo</button>
            <button class="filter-btn filter-btn-basico" onclick="filterCourses('basico')">Ciclo Básico</button>
        </div>
        <div class="cards" id="courses-cards"></div>
    </div>
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

<!-- ═══════════════════════════════════════════════════════════════════════
     PANEL LABORATORIOS — integrado desde laboratorios/index.html
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="panel" id="panel-laboratorios">

<a class="skip-link" href="#main-content">Saltar al contenido</a>

<!-- BARRA DE NAVEGACIÓN (page-nav con tabs — sin header SAEP) -->
<nav class="page-nav" role="navigation" aria-label="Navegación del módulo">
  <div class="pnav-left">
    <div class="pnav-user" id="pnav-user">
      <div class="pnav-avatar" id="pnav-avatar" aria-hidden="true">?</div>
      <div class="pnav-info">
        <div class="pnav-name" id="pnav-name">Cargando…</div>
        <div class="pnav-role" id="pnav-role">docente</div>
      </div>
    </div>
  </div>
  <div class="pnav-tabs" role="tablist">
    <button class="pnav-tab active" data-page="calendario" onclick="irA('calendario')" role="tab" aria-selected="true">
      📅 Calendario
    </button>
    <button class="pnav-tab" data-page="mis-reservas" onclick="irA('mis-reservas')" role="tab" aria-selected="false">
      📋 Mis reservas
    </button>
    <button class="pnav-tab" data-page="seguimiento" onclick="irA('seguimiento')" role="tab" aria-selected="false">
      📊 Seguimiento
    </button>
    <button class="pnav-tab admin-only" data-page="admin" onclick="irA('admin')" role="tab" aria-selected="false" style="display:none;position:relative;">
      ⚙️ Admin
      <span class="admin-badge" id="admin-badge" role="status" aria-live="polite" style="display:none;"></span>
    </button>
  </div>
  <div class="pnav-right">
    <div class="session-widget" role="navigation" aria-label="Sesión de usuario">
      <button class="session-trigger" id="session-trigger" onclick="toggleSessionMenu()"
              aria-haspopup="true" aria-expanded="false" aria-controls="session-menu">
        <div class="s-avatar" id="s-avatar" aria-hidden="true">?</div>
        <span class="s-name" id="s-name">Cargando...</span>
        <span class="s-role" id="s-role">docente</span>
        <span class="s-caret" aria-hidden="true">▾</span>
      </button>
      <div class="session-menu" id="session-menu" role="menu" aria-labelledby="session-trigger">
        <div class="sm-header">
          <div class="sm-name" id="sm-name">—</div>
          <div class="sm-role" id="sm-role">—</div>
        </div>
        <button class="sm-item" role="menuitem" onclick="irA('mis-reservas');closeSessionMenu()"><span aria-hidden="true">📋</span> Mis reservas</button>
        <div class="sm-sep" role="separator"></div>
        <button class="sm-item danger" role="menuitem" onclick="cerrarSesion()"><span aria-hidden="true">🚪</span> Cerrar sesión</button>
      </div>
    </div>
  </div>
</nav>

<!-- FILTRO ORIENTACIONES -->
<div class="orient-bar" role="tablist" aria-label="Filtrar por orientación">
  <div class="orient-tab all sel" role="tab" aria-selected="true" tabindex="0" onclick="selOrient(this,'all')" onkeydown="if(event.key==='Enter'||event.key===' ')selOrient(this,'all')">
    <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><rect x="1" y="1" width="8" height="8" rx="1"/><rect x="11" y="1" width="8" height="8" rx="1"/><rect x="1" y="11" width="8" height="8" rx="1"/><rect x="11" y="11" width="8" height="8" rx="1"/></svg>
    Todas
  </div>
  <div class="orient-tab info" role="tab" aria-selected="false" tabindex="0" onclick="selOrient(this,'info')" onkeydown="if(event.key==='Enter'||event.key===' ')selOrient(this,'info')">
    <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><rect x="1" y="3" width="18" height="12" rx="2"/><rect x="5" y="17" width="10" height="2" rx="1"/></svg>
    Informática
  </div>
  <div class="orient-tab const" role="tab" aria-selected="false" tabindex="0" onclick="selOrient(this,'const')" onkeydown="if(event.key==='Enter'||event.key===' ')selOrient(this,'const')">
    <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 2L3 8v10h5v-5h4v5h5V8L10 2z"/></svg>
    Construcción
  </div>
  <div class="orient-tab tur" role="tab" aria-selected="false" tabindex="0" onclick="selOrient(this,'tur')" onkeydown="if(event.key==='Enter'||event.key===' ')selOrient(this,'tur')">
    <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><circle cx="10" cy="10" r="8" fill="none" stroke="currentColor" stroke-width="2"/><path d="M10 2v8l5 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/></svg>
    Turismo
  </div>
  <div class="orient-tab bas" role="tab" aria-selected="false" tabindex="0" onclick="selOrient(this,'bas')" onkeydown="if(event.key==='Enter'||event.key===' ')selOrient(this,'bas')">
    <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><rect x="3" y="2" width="11" height="14" rx="1"/><rect x="5" y="4" width="14" height="14" rx="1"/></svg>
    Básico
  </div>
</div>

<!-- CONTENIDO PRINCIPAL -->
<main class="content" id="main-content" tabindex="-1">

  <!-- PÁGINA: CALENDARIO -->
  <div id="page-calendario" class="page active" role="region" aria-label="Calendario diario">
    <div class="cal-header">
      <div class="cal-title" id="cal-title-text" aria-live="polite">Cargando…</div>
      <div class="cal-controls">
        <div class="lab-filter-bar" role="group" aria-label="Filtrar por laboratorio">
          <button class="lab-filter-btn active" id="filt-todos" aria-pressed="true" onclick="setLabFilter('todos')">Todos</button>
          <span id="lab-filter-btns"></span>
        </div>
        <div class="week-nav" role="group" aria-label="Navegar semanas">
          <button class="week-btn" onclick="navSemana(-1)" aria-label="Semana anterior">‹ Sem. ant.</button>
          <button class="week-btn hoy" onclick="irHoy()" aria-label="Ir a hoy">Hoy</button>
          <button class="week-btn" onclick="navSemana(1)" aria-label="Semana siguiente">Sem. sig. ›</button>
        </div>
      </div>
    </div>

    <!-- Barra días -->
    <div class="day-nav-container">
      <button class="day-nav-arrow" onclick="navDia(-1)" aria-label="Día anterior">‹</button>
      <div class="day-nav-bar" id="day-nav-bar" role="group" aria-label="Seleccionar día"></div>
      <button class="day-nav-arrow" onclick="navDia(1)" aria-label="Día siguiente">›</button>
    </div>

    <div class="cal-wrap">
      <div id="cal-body"></div>
    </div>
  </div>

  <!-- PÁGINA: SEGUIMIENTO -->
  <div id="page-seguimiento" class="page" role="region" aria-label="Seguimiento">
    <div class="page-header">
      <div class="page-title-wrap">
        <div class="page-title">Seguimiento</div>
        <div class="page-sub">Lista de espera y vencimientos de ciclos</div>
      </div>
      <div id="seguimiento-stats" class="seguimiento-stats"></div>
    </div>

    <div class="seguimiento-grid">
      <section class="seguimiento-section" aria-label="Lista de espera">
        <div class="seguimiento-section-header">
          <div class="seguimiento-section-title">
            <span>📋</span> Lista de espera
            <span class="pending-count-badge" id="espera-count" style="display:none;"></span>
          </div>
          <button class="pnav-espera" onclick="abrirModalEspera()" style="font-size:.78rem;padding:.32rem .7rem;">+ Anotarme</button>
        </div>
        <div id="espera-lista"></div>
      </section>

      <section class="seguimiento-section" aria-label="Próximos vencimientos">
        <div class="seguimiento-section-header">
          <div class="seguimiento-section-title">
            <span>⏰</span> Próximos vencimientos
            <span class="pending-count-badge" id="venc-count" style="display:none;background:var(--amber);"></span>
          </div>
        </div>
        <div id="venc-lista"></div>
      </section>
    </div>
  </div>

  <!-- PÁGINA: MIS RESERVAS -->
  <div id="page-mis-reservas" class="page" role="region" aria-label="Mis reservas">
    <div class="page-header">
      <div class="page-title-wrap">
        <div class="page-title" id="mis-reservas-title">Mis reservas</div>
        <div class="page-sub" id="mis-reservas-sub"></div>
      </div>
      <button class="cta-btn" style="width:auto;padding:9px 20px;" onclick="abrirModalReserva()">+ Nueva solicitud</button>
    </div>
    <div class="stats-strip" id="mis-stats-strip" aria-label="Estadísticas de mis reservas"></div>
    <div id="mis-reservas-list" aria-live="polite"></div>
    <div id="mis-reservas-empty" style="display:none;text-align:center;padding:60px 20px;color:var(--muted);" role="status">
      <div style="font-size:48px;margin-bottom:12px;" aria-hidden="true">📭</div>
      <div style="font-weight:700;font-size:15px;color:var(--text);margin-bottom:6px;">No tenés reservas activas</div>
      <div style="font-size:13px;">Hacé clic en "+ Nueva solicitud" para comenzar.</div>
    </div>
  </div>

  <!-- PÁGINA: ADMINISTRACIÓN -->
  <div id="page-admin" class="page" role="region" aria-label="Administración">
    <div class="page-header">
      <div class="page-title-wrap">
        <div class="page-title">Administración</div>
        <div class="page-sub">Panel directivo · EEST N°1</div>
      </div>
      <div style="display:flex;gap:.4rem;flex-wrap:wrap;align-items:center;">
        <button onclick="exportarDB()" style="font-family:var(--font);font-size:.8rem;font-weight:600;padding:.42rem .85rem;border-radius:4px;border:1px solid var(--border);background:#fff;color:var(--text2);cursor:pointer;">⬇ Exportar BD</button>
        <button onclick="importarDB()" style="font-family:var(--font);font-size:.8rem;font-weight:600;padding:.42rem .85rem;border-radius:4px;border:1px solid var(--border);background:#fff;color:var(--text2);cursor:pointer;">⬆ Importar BD</button>
        <button onclick="resetearDB()" style="font-family:var(--font);font-size:.8rem;font-weight:600;padding:.42rem .85rem;border-radius:4px;border:1px solid var(--red);background:#fff;color:var(--red);cursor:pointer;">↺ Resetear</button>
      </div>
    </div>
    <div class="stats-strip" aria-label="Estadísticas generales">
      <div class="stat-card az"><div class="stat-card-n" id="s-semana">—</div><div class="stat-card-l">Reservas activas</div></div>
      <div class="stat-card am"><div class="stat-card-n" id="s-pendientes">—</div><div class="stat-card-l">Solicitudes pendientes</div></div>
      <div class="stat-card vd"><div class="stat-card-n" id="s-docs">—</div><div class="stat-card-l">Docentes activos</div></div>
      <div class="stat-card rj"><div class="stat-card-n" id="s-labs">—</div><div class="stat-card-l">Laboratorios</div></div>
    </div>
    <div class="admin-section admin-section-highlight">
      <div class="admin-section-header">
        <div class="admin-section-title"><span aria-hidden="true">⏳</span> Solicitudes pendientes <span id="solicitudes-count" class="pending-count-badge" aria-live="polite"></span></div>
      </div>
      <div id="solicitudes-cards-area" style="padding:.75rem 1rem;"><div id="solicitudes-tbody"></div></div>
    </div>
    <div class="admin-section">
      <div class="admin-section-header">
        <div class="admin-section-title"><span aria-hidden="true">🏫</span> Espacios / Laboratorios</div>
        <button class="cta-btn" style="width:auto;padding:8px 18px;font-size:13px;" onclick="abrirModalLab()">+ Agregar</button>
      </div>
      <div id="labs-config-list" role="list"></div>
    </div>
    <div class="admin-section">
      <div class="admin-section-header">
        <div class="admin-section-title"><span aria-hidden="true">📅</span> Todas las reservas</div>
        <select class="search-input" style="max-width:210px;padding:8px 11px;font-size:13px;" id="admin-filter-orient" onchange="renderAdminReservas()">
          <option value="all">Todas las orientaciones</option>
          <option value="info">Informática</option>
          <option value="const">Construcción</option>
          <option value="tur">Turismo</option>
          <option value="bas">Básico</option>
        </select>
      </div>
      <div id="admin-reservas-area" style="padding:.75rem 1rem;"></div>
    </div>
    <div class="admin-section">
      <div class="admin-section-header">
        <div class="admin-section-title"><span aria-hidden="true">📌</span> Pautas del aula</div>
        <button class="cta-btn" style="width:auto;padding:8px 18px;font-size:13px;" onclick="abrirModalPauta()">+ Agregar</button>
      </div>
      <div id="pautas-admin-list" role="list"></div>
    </div>
  </div>

</main>

<!-- MODALES -->

<!-- MODAL PAUTAS DEL AULA (inicio de sesión) -->
<div class="modal-overlay pautas-overlay" id="modal-pautas-inicio" role="dialog" aria-modal="true" aria-labelledby="pautas-inicio-title">
  <div class="modal modal-pautas">
    <div class="modal-header" style="border-radius:6px 6px 0 0;">
      <h3 id="pautas-inicio-title" style="font-size:1.08rem;">
        <span aria-hidden="true">📌</span> Pautas del aula · Laboratorios EEST N°1
      </h3>
    </div>
    <div class="modal-body" style="padding:1.25rem 1.5rem;">
      <p style="font-size:.88rem;color:var(--muted);margin-bottom:1rem;line-height:1.55;">
        Antes de utilizar el sistema, leé y aceptá las pautas de uso de los laboratorios informáticos.
      </p>
      <ul id="pautas-inicio-list" style="list-style:none;display:flex;flex-direction:column;gap:.55rem;margin-bottom:1.5rem;"></ul>
      <label class="pautas-accept-label" id="pautas-accept-label">
        <input type="checkbox" id="pautas-checkbox" onchange="togglePautasBtn()" style="width:16px;height:16px;accent-color:var(--navy);cursor:pointer;flex-shrink:0;">
        <span>Leí y acepto todas las pautas del aula</span>
      </label>
    </div>
    <div class="modal-footer" style="border-radius:0 0 6px 6px;justify-content:flex-end;">
      <button class="btn-ok" id="pautas-continuar-btn" onclick="aceptarPautas()" disabled style="opacity:.45;cursor:not-allowed;transition:opacity .2s;">
        Continuar →
      </button>
    </div>
  </div>
</div>

<!-- Solicitar turno -->
<div class="modal-overlay" id="modal-reserva" role="dialog" aria-modal="true" aria-labelledby="modal-reserva-title">
  <div class="modal">
    <div class="modal-header">
      <h3 id="modal-reserva-title"><span aria-hidden="true">📅</span> Solicitar turno</h3>
      <button class="modal-close" onclick="cerrarModal('modal-reserva')" aria-label="Cerrar modal">✕</button>
    </div>
    <div class="modal-body">
      <div class="modal-info-hint" id="reserva-hint" role="note">
        <span aria-hidden="true">ℹ️</span> Tu solicitud será enviada al directivo para aprobación.
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="f-lab">Laboratorio</label>
          <select class="form-control" id="f-lab" required></select>
        </div>
        <div class="form-group">
          <label class="form-label" for="f-dia">Día</label>
          <select class="form-control" id="f-dia" required></select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="f-modulo">Módulo de inicio</label>
          <select class="form-control" id="f-modulo" required></select>
        </div>
        <div class="form-group">
          <label class="form-label" for="f-periodo">Duración</label>
          <select class="form-control" id="f-periodo">
            <option value="1">1 hora (módulo individual)</option>
            <option value="2">2 horas consecutivas</option>
            <option value="4">4 horas consecutivas</option>
            <option value="turno_Mañana">🌅 Turno completo Mañana</option>
            <option value="turno_Tarde">☀️ Turno completo Tarde</option>
            <option value="turno_Vespertino">🌆 Turno completo Vespertino</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="f-curso">Curso</label>
          <select class="form-control" id="f-curso" required>
            <option value="">Seleccionar curso…</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="f-orient">Orientación</label>
          <select class="form-control" id="f-orient">
            <option value="info">💻 Informática</option>
            <option value="const">🏗️ Construcción</option>
            <option value="tur">🌐 Turismo</option>
            <option value="bas">📚 Básico</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="f-secuencia">Secuencia didáctica</label>
        <textarea class="form-control" id="f-secuencia" rows="3" placeholder="¿Qué harán en el laboratorio?" required></textarea>
      </div>
      <div class="conflict-warning" id="conflict-msg" role="alert">
        ⚠️ Ese horario ya está ocupado o tiene una solicitud pendiente.
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="cerrarModal('modal-reserva')">Cancelar</button>
      <button class="btn-ok" onclick="guardarReserva()">Enviar solicitud</button>
    </div>
  </div>
</div>

<!-- Ver detalle -->
<div class="modal-overlay" id="modal-detalle" role="dialog" aria-modal="true" aria-labelledby="modal-detalle-title">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3 id="modal-detalle-title"><span aria-hidden="true">📋</span> Detalle del turno</h3>
      <button class="modal-close" onclick="cerrarModal('modal-detalle')" aria-label="Cerrar detalle">✕</button>
    </div>
    <div class="modal-body" id="modal-detalle-body"></div>
    <div class="modal-footer" id="modal-detalle-footer"></div>
  </div>
</div>

<!-- Editar recreo -->
<div class="modal-overlay" id="modal-recreo" role="dialog" aria-modal="true" aria-labelledby="modal-recreo-title">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3 id="modal-recreo-title">☕ Recreo</h3>
      <button class="modal-close" onclick="cerrarModal('modal-recreo')" aria-label="Cerrar">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="recreo-modulo-id" value="">
      <div class="form-group">
        <label class="form-label" for="recreo-evento">Nombre del evento</label>
        <input class="form-control" id="recreo-evento" type="text" placeholder="Ej: Recreo de mañana" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="recreo-notas">Notas (lugar, duración, etc.)</label>
        <input class="form-control" id="recreo-notas" type="text" placeholder="Ej: 30 min · patio">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="cerrarModal('modal-recreo')">Cancelar</button>
      <button class="btn-ok" onclick="guardarRecreo()">Guardar</button>
    </div>
  </div>
</div>

<!-- Agregar/editar docente -->
<div class="modal-overlay" id="modal-docente" role="dialog" aria-modal="true" aria-labelledby="modal-docente-title">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3 id="modal-docente-title"><span aria-hidden="true">👤</span> Agregar docente</h3>
      <button class="modal-close" onclick="cerrarModal('modal-docente')" aria-label="Cerrar">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-row">
        <div class="form-group"><label class="form-label" for="doc-apellido">Apellido</label><input class="form-control" id="doc-apellido" type="text" placeholder="Ej: González" required></div>
        <div class="form-group"><label class="form-label" for="doc-nombre">Nombre</label><input class="form-control" id="doc-nombre" type="text" placeholder="Ej: María" required></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label" for="doc-materia">Materia</label><input class="form-control" id="doc-materia" type="text" placeholder="Ej: Programación" required></div>
        <div class="form-group"><label class="form-label" for="doc-orient">Orientación</label><select class="form-control" id="doc-orient"><option value="info">Informática</option><option value="const">Construcción</option><option value="tur">Turismo</option><option value="bas">Básico</option></select></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="cerrarModal('modal-docente')">Cancelar</button>
      <button class="btn-ok" onclick="guardarDocente()">Guardar</button>
    </div>
  </div>
</div>

<!-- Agregar/editar espacio -->
<div class="modal-overlay" id="modal-lab" role="dialog" aria-modal="true" aria-labelledby="modal-lab-title">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3 id="modal-lab-title"><span aria-hidden="true">🏫</span> Agregar espacio</h3>
      <button class="modal-close" onclick="cerrarModal('modal-lab')" aria-label="Cerrar">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-group"><label class="form-label" for="lab-nombre">Nombre del espacio</label><input class="form-control" id="lab-nombre" type="text" placeholder="Ej: Lab. Informático C" required></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label" for="lab-capacidad">Capacidad</label><input class="form-control" id="lab-capacidad" type="number" min="1" placeholder="Ej: 20"></div>
        <div class="form-group"><label class="form-label" for="lab-ubicacion">Ubicación</label><input class="form-control" id="lab-ubicacion" type="text" placeholder="Ej: a31"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label" for="lab-estado">Estado</label><select class="form-control" id="lab-estado"><option value="libre">Disponible</option><option value="ocupado">En mantenimiento</option></select></div>
        <div class="form-group"><label class="form-label" for="lab-notas">Equipamiento / notas</label><input class="form-control" id="lab-notas" type="text" placeholder="Ej: Windows 11, Packet Tracer"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="cerrarModal('modal-lab')">Cancelar</button>
      <button class="btn-ok" onclick="guardarLab()">Guardar</button>
    </div>
  </div>
</div>

<!-- Lista de espera -->
<div class="modal-overlay" id="modal-espera" role="dialog" aria-modal="true" aria-labelledby="modal-espera-title">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3 id="modal-espera-title"><span aria-hidden="true">📋</span> Anotarme en espera</h3>
      <button class="modal-close" onclick="cerrarModal('modal-espera')" aria-label="Cerrar">✕</button>
    </div>
    <div class="modal-body">
      <p style="font-size:.86rem;color:var(--muted);margin-bottom:1rem;">Te avisaremos cuando el turno quede disponible.</p>
      <div class="form-row">
        <div class="form-group"><label class="form-label" for="espera-lab">Laboratorio</label><select class="form-control" id="espera-lab" required></select></div>
        <div class="form-group"><label class="form-label" for="espera-dia">Día</label><select class="form-control" id="espera-dia" required></select></div>
      </div>
      <div class="form-group"><label class="form-label" for="espera-modulo">Módulo horario</label><select class="form-control" id="espera-modulo" required></select></div>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="cerrarModal('modal-espera')">Cancelar</button>
      <button class="btn-ok" onclick="guardarEspera()">Anotarme</button>
    </div>
  </div>
</div>

<!-- Pauta (admin) -->
<div class="modal-overlay" id="modal-pauta" role="dialog" aria-modal="true" aria-labelledby="modal-pauta-title">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3 id="modal-pauta-title"><span aria-hidden="true">📌</span> Agregar pauta</h3>
      <button class="modal-close" onclick="cerrarModal('modal-pauta')" aria-label="Cerrar">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-group"><label class="form-label" for="pauta-texto">Texto de la pauta</label><textarea class="form-control" id="pauta-texto" rows="2" placeholder="Ej: Apagar equipos al finalizar" required></textarea></div>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="cerrarModal('modal-pauta')">Cancelar</button>
      <button class="btn-ok" onclick="guardarPauta()">Guardar</button>
    </div>
  </div>
</div>

<!-- Confirmar -->
<div class="modal-overlay" id="modal-confirm" role="dialog" aria-modal="true" aria-labelledby="modal-confirm-title">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3 id="modal-confirm-title">Confirmar acción</h3>
      <button class="modal-close" onclick="cerrarModal('modal-confirm')" aria-label="Cancelar">✕</button>
    </div>
    <div class="modal-body"><div id="confirm-body"></div></div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="cerrarModal('modal-confirm')">Cancelar</button>
      <button class="btn-ok" id="confirm-ok-btn">Confirmar</button>
    </div>
  </div>
</div>

<button class="mobile-fab" onclick="abrirModalReserva()" aria-label="Nueva solicitud">
  <span aria-hidden="true">+</span> Solicitar turno
</button>

<div id="toast-container" role="region" aria-label="Notificaciones" aria-live="polite"></div>

</div>
<!-- ═══════════════════════════════════════════════════════════════════════
     FIN PANEL LABORATORIOS
     ═══════════════════════════════════════════════════════════════════════ -->

</main>
</div>
<div id="modal-root"></div>

<script>
window.ROLE = '<?=$role?>';
window.MY_ID = '<?=$user['id']?>';
</script>
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/lab.js"></script>
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