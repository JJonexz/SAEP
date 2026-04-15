<?php
session_start();
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');
require_approved();
$method=$_SERVER['REQUEST_METHOD'];
if ($method==='GET') { echo json_encode(db_read(ROOMS_FILE)); exit; }
require_role(['admin','director','subdirector']);
$body=json_decode(file_get_contents('php://input'),true);
if ($method==='POST') {
    $nombre=trim($body['nombre']??'');
    if (!$nombre) { http_response_code(400); echo json_encode(['error'=>'Nombre requerido']); exit; }
    $rooms=db_read(ROOMS_FILE);
    $rooms[]=['id'=>generate_id(),'nombre'=>$nombre,'capacidad'=>intval($body['capacidad']??0),'ubicacion'=>trim($body['ubicacion']??''),'curso_id'=>null,'preceptor_id'=>null];
    db_write(ROOMS_FILE,$rooms); echo json_encode(['success'=>true]); exit;
}
if ($method==='PATCH') {
    $id=$body['id']??null; $rooms=db_read(ROOMS_FILE); $idx=db_find_index($rooms,'id',$id);
    if ($idx===-1) { http_response_code(404); echo json_encode(['error'=>'No encontrada']); exit; }
    foreach (['nombre','capacidad','ubicacion','curso_id','preceptor_id'] as $f) if (array_key_exists($f,$body)) $rooms[$idx][$f]=$body[$f];
    db_write(ROOMS_FILE,$rooms); echo json_encode(['success'=>true]); exit;
}
if ($method==='DELETE') {
    $id=$body['id']??null; $rooms=db_read(ROOMS_FILE); $idx=db_find_index($rooms,'id',$id);
    if ($idx===-1) { http_response_code(404); echo json_encode(['error'=>'No encontrada']); exit; }
    array_splice($rooms,$idx,1); db_write(ROOMS_FILE,$rooms); echo json_encode(['success'=>true]); exit;
}
http_response_code(405); echo json_encode(['error'=>'Método no permitido']);
