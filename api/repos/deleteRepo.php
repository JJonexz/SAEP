<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__.'/../../lib/auth.php';
require_once __DIR__.'/../config/config.php';
require_approved();
$at=$_SESSION['access_token'];
$body=json_decode(file_get_contents('php://input'),true);
if(empty($body['full_name'])){http_response_code(400);echo json_encode(['error'=>'full_name requerido']);exit;}
@file_get_contents("https://api.github.com/repos/{$body['full_name']}",false,stream_context_create(['http'=>['method'=>'DELETE','header'=>"Authorization: Bearer $at\r\nUser-Agent: SAEP\r\nAccept: application/json\r\n"]]));
$st=$http_response_header[0]??'';
echo strpos($st,'204')!==false?json_encode(['success'=>true]):json_encode(['error'=>'No se pudo eliminar']);
