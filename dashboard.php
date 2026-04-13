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

            <!-- Sidebar -->
            <aside>
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
                        <div class="fgrid mb1" style="max-width:560px">
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
                            <div class="fgrid mb1" style="max-width:560px">
                                <div class="field"><label>Curso</label><select id="gv-curso" onchange="loadGradesTable()"><option value="">Todos</option></select></div>
                                <div class="field"><label>Cuatrimestre</label><select id="gv-cuatri" onchange="loadGradesTable()"><option value="">Ambos</option><option value="1">1°</option><option value="2">2°</option></select></div>
                            </div>
                            <div class="tbl-wrap" id="grades-tbl"></div>
                        </div>
                        <?php if(in_array($role,['admin','director','subdirector','profesor'])): ?>
                        <div class="tab-content" id="gtab-cargar">
                            <div class="fgrid" style="max-width:600px">
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
                        <div class="field mb1" style="max-width:200px"><label>Cuatrimestre</label><select id="mg-cuatri" onchange="loadMyGrades()"><option value="">Ambos</option><option value="1">1°</option><option value="2">2°</option></select></div>
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
                            <select id="uf-status" onchange="filterUsers()" style="font-family:var(--font);font-size:.78rem;padding:.4rem .7rem;border:1px solid var(--border);border-radius:var(--radius);color:var(--text2)">
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

            </main>
        </div>
        <div id="modal-root"></div>

        <script>
            window.ROLE = '<?=$role?>';
            window.MY_ID = '<?=$user['id']?>';
        </script>
        <script src="assets/dashboard.js"></script>
    </body>
</html>