<?php
// Con MySQL los usuarios ya existen en la base de datos.
// Esta página ya no es necesaria; redirigir al dashboard si está autenticado.
session_start();
require_once 'config.php';
require_once 'lib/db.php';
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
header('Location: dashboard.php');
exit;
