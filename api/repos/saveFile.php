<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__.'/../../lib/auth.php';
require_once __DIR__.'/../../config.php';
require_approved();
$at=$_SESSION['access_token'];
$body=json_decode(file_get_contents('php://input'),true);
if(empty($body['repo'])||empty($body['path'])||!isset($body['content'])){http_response_code(400);echo json_encode(['error'=>'repo, path y content requeridos']);exit;}
$payload=['message'=>$body['message']??'Actualizado desde SAEP','content'=>base64_encode($body['content'])];
if(!empty($body['sha']))$payload['sha']=$body['sha'];
$r=file_get_contents("https://api.github.com/repos/{$body['repo']}/contents/{$body['path']}",false,stream_context_create(['http'=>['method'=>'PUT','header'=>"Authorization: Bearer $at\r\nUser-Agent: SAEP\r\nContent-Type: application/json\r\nAccept: application/json\r\n",'content'=>json_encode($payload)]]));
if(!$r){http_response_code(502);echo json_encode(['error'=>'Error al guardar']);exit;}
$res=json_decode($r,true);
if(isset($res['message'])&&!isset($res['content'])){http_response_code(422);echo json_encode(['error'=>$res['message']]);exit;}
echo json_encode(['success'=>true,'sha'=>$res['content']['sha']]);
