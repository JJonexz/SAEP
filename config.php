<?php
// ── Base de datos MySQL ──────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'escuela');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ── Roles del sistema ────────────────────────────────────────────────────────
// Los roles se mapean desde la tabla personal (tag) y alumnos
define('ROLES',       ['admin', 'director', 'subdirector', 'profesor', 'preceptor', 'alumno']);
define('ROLES_STAFF', ['admin', 'director', 'subdirector', 'profesor', 'preceptor']);
