<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__.'/../../lib/auth.php';
require_once __DIR__.'/../../config.php';
require_approved();
$at=$_SESSION['access_token'];
$body=json_decode(file_get_contents('php://input'),true);
if(empty($body['repo'])||empty($body['path'])||empty($body['sha'])){http_response_code(400);echo json_encode(['error'=>'repo, path y sha requeridos']);exit;}
@file_get_contents("https://api.github.com/repos/{$body['repo']}/contents/{$body['path']}",false,stream_context_create(['http'=>['method'=>'DELETE','header'=>"Authorization: Bearer $at\r\nUser-Agent: SAEP\r\nContent-Type: application/json\r\nAccept: application/json\r\n",'content'=>json_encode(['message'=>'Eliminado desde SAEP','sha'=>$body['sha']])]]));
$st=$http_response_header[0]??'';
echo strpos($st,'200')!==false?json_encode(['success'=>true]):json_encode(['error'=>'No se pudo eliminar']);
