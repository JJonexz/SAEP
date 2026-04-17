<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__.'/../../lib/auth.php';
require_once __DIR__.'/../../config.php';
require_approved();
$at=$_SESSION['access_token'];
$body=json_decode(file_get_contents('php://input'),true);
if(empty($body['name'])){http_response_code(400);echo json_encode(['error'=>'Nombre requerido']);exit;}
$r=file_get_contents('https://api.github.com/user/repos',false,stream_context_create(['http'=>['method'=>'POST','header'=>"Authorization: Bearer $at\r\nUser-Agent: SAEP\r\nContent-Type: application/json\r\nAccept: application/json\r\n",'content'=>json_encode(['name'=>$body['name'],'description'=>$body['description']??'','private'=>$body['private']??false,'auto_init'=>true])]]));
if(!$r){http_response_code(502);echo json_encode(['error'=>'Error al crear']);exit;}
$repo=json_decode($r,true);
if(isset($repo['errors'])||(isset($repo['message'])&&!isset($repo['id']))){http_response_code(422);echo json_encode(['error'=>$repo['message']??'Error']);exit;}
echo json_encode(['success'=>true,'name'=>$repo['name']]);
