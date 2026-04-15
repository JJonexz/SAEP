<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__.'/../../lib/auth.php';
require_once __DIR__.'/../config/config.php';
require_approved();
$at=$_SESSION['access_token'];
$r=file_get_contents('https://api.github.com/user/repos?per_page=100&sort=updated&affiliation=owner',false,stream_context_create(['http'=>['method'=>'GET','header'=>"Authorization: Bearer $at\r\nUser-Agent: SAEP\r\nAccept: application/json\r\n"]]));
if (!$r){http_response_code(502);echo json_encode(['error'=>'Error GitHub']);exit;}
$repos=json_decode($r,true);
echo json_encode(array_map(fn($r)=>['name'=>$r['name'],'full_name'=>$r['full_name'],'description'=>$r['description'],'private'=>$r['private'],'language'=>$r['language'],'updated_at'=>$r['updated_at']],$repos));
