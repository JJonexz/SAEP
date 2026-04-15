<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__.'/../../lib/auth.php';
require_once __DIR__.'/../../config.php';
require_approved();
$at=$_SESSION['access_token']; $fn=$_GET['repo']??''; $path=$_GET['path']??'';
if(!$fn||!$path){http_response_code(400);echo json_encode(['error'=>'repo y path requeridos']);exit;}
$r=file_get_contents("https://api.github.com/repos/$fn/contents/$path",false,stream_context_create(['http'=>['method'=>'GET','header'=>"Authorization: Bearer $at\r\nUser-Agent: SAEP\r\nAccept: application/json\r\n"]]));
if(!$r){http_response_code(502);echo json_encode(['error'=>'Error']);exit;}
$f=json_decode($r,true);
if(isset($f['message'])){http_response_code(404);echo json_encode(['error'=>$f['message']]);exit;}
echo json_encode(['name'=>$f['name'],'path'=>$f['path'],'sha'=>$f['sha'],'content'=>base64_decode(str_replace("\n",'',$f['content']))]);
