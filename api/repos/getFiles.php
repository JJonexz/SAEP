<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__.'/../../lib/auth.php';
require_once __DIR__.'/../../config.php';
require_approved();
$at=$_SESSION['access_token']; $fn=$_GET['repo']??''; $path=$_GET['path']??'';
if(!$fn){http_response_code(400);echo json_encode(['error'=>'repo requerido']);exit;}
$r=file_get_contents("https://api.github.com/repos/$fn/contents/$path",false,stream_context_create(['http'=>['method'=>'GET','header'=>"Authorization: Bearer $at\r\nUser-Agent: SAEP\r\nAccept: application/json\r\n"]]));
if(!$r){http_response_code(502);echo json_encode(['error'=>'Error']);exit;}
$items=json_decode($r,true);
if(isset($items['message'])){http_response_code(404);echo json_encode(['error'=>$items['message']]);exit;}
$result=array_map(fn($i)=>['name'=>$i['name'],'path'=>$i['path'],'type'=>$i['type'],'size'=>$i['size'],'sha'=>$i['sha'],'download_url'=>$i['download_url']??null],$items);
usort($result,fn($a,$b)=>$a['type']===$b['type']?strcmp($a['name'],$b['name']):($a['type']==='dir'?-1:1));
echo json_encode($result);
