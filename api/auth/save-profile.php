<?php
session_start();
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');
$user = require_auth();
$body = json_decode(file_get_contents('php://input'), true);
$nombre=$trim($body['nombre']??''); $apellido=trim($body['apellido']??''); $dni=trim($body['dni']??''); $telefono=trim($body['telefono']??'');
if (!$nombre||!$apellido||!$dni) { http_response_code(400); echo json_encode(['error'=>'Nombre, apellido y DNI obligatorios']); exit; }
if (!preg_match('/^\d{7,8}$/',$dni)) { http_response_code(400); echo json_encode(['error'=>'DNI inválido']); exit; }
$users=db_read(USERS_FILE);
$idx=db_find_index($users,'github_id',$_SESSION['github_id']);
if ($idx===-1) { http_response_code(404); echo json_encode(['error'=>'No encontrado']); exit; }
foreach ($users as $i=>$u) { if ($i!==$idx && $u['dni']===$dni) { http_response_code(409); echo json_encode(['error'=>'DNI ya registrado']); exit; } }
$users[$idx]['nombre']=$nombre; $users[$idx]['apellido']=$apellido; $users[$idx]['dni']=$dni; $users[$idx]['telefono']=$telefono; $users[$idx]['status']='pending_approval';
db_write(USERS_FILE,$users);
echo json_encode(['success'=>true]);
