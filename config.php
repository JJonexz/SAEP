<?php
define('GITHUB_CLIENT_ID',     'Ov23liym4HLdzf6OhhYh');
define('GITHUB_CLIENT_SECRET', '465b344404cd4e7a23a2fd7605df93cea847bdc3');

$github_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$github_host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$github_base   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
define('GITHUB_REDIRECT_URI',  "$github_scheme://$github_host$github_base/callback.php");
define('GITHUB_SCOPE',         'read:user user:email repo delete_repo');

define('DATA_DIR',      __DIR__ . '/data/');
define('USERS_FILE',    DATA_DIR . 'users.json');
define('COURSES_FILE',  DATA_DIR . 'courses.json');
define('SUBJECTS_FILE', DATA_DIR . 'subjects.json');
define('ROOMS_FILE',    DATA_DIR . 'rooms.json');
define('GRADES_FILE',    DATA_DIR . 'grades.json');
define('WORKS_FILE',     DATA_DIR . 'works.json');
define('CONTACTS_FILE',  DATA_DIR . 'contacts.json');

define('ROLES', ['admin', 'director', 'subdirector', 'profesor', 'preceptor', 'alumno']);
define('ROLES_STAFF', ['admin', 'director', 'subdirector', 'profesor', 'preceptor']);