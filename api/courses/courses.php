<?php
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');
$me=$_method=$_SERVER['REQUEST_METHOD'];
$me=require_approved();
$method=$_SERVER['REQUEST_METHOD'];

if ($method==='GET') { echo json_encode(db_read(COURSES_FILE)); exit; }

require_role(['admin','director','subdirector']);
$body=json_decode(file_get_contents('php://input'),true);

if ($method==='POST') {
    $nombre=trim($body['nombre']??''); $anio=intval($body['anio']??0); $division=trim($body['division']??''); $turno=trim($body['turno']??'');
    if (!$nombre||!$anio||!$division||!$turno) { http_response_code(400); echo json_encode(['error'=>'Todos los campos obligatorios']); exit; }
    $courses=db_read(COURSES_FILE);
    $courses[]=['id'=>generate_id(),'nombre'=>$nombre,'anio'=>$anio,'division'=>$division,'turno'=>$turno,'materias'=>[],'alumnos'=>[],'profesores'=>[]];
    db_write(COURSES_FILE,$courses);
    echo json_encode(['success'=>true]); exit;
}

if ($method==='PATCH') {
    $id=$body['id']??null; $action=$body['action']??null;
    if (!$id||!$action) { http_response_code(400); echo json_encode(['error'=>'id y action requeridos']); exit; }
    $courses=db_read(COURSES_FILE); $idx=db_find_index($courses,'id',$id);
    if ($idx===-1) { http_response_code(404); echo json_encode(['error'=>'No encontrado']); exit; }
    if ($action==='add_materia') { $mat=trim($body['materia']??''); $pid=$body['profesor_id']??null; if (!$mat) { http_response_code(400); echo json_encode(['error'=>'Nombre requerido']); exit; } $courses[$idx]['materias'][]=['id'=>generate_id(),'nombre'=>$mat,'profesor_id'=>$pid]; }
    if ($action==='remove_materia') { $mid=$body['materia_id']??null; $courses[$idx]['materias']=array_values(array_filter($courses[$idx]['materias'],fn($m)=>$m['id']!==$mid)); }
    if ($action==='add_alumno') { $uid=$body['user_id']??null; if ($uid&&!in_array($uid,$courses[$idx]['alumnos'])) $courses[$idx]['alumnos'][]=$uid; }
    if ($action==='remove_alumno') { $uid=$body['user_id']??null; $courses[$idx]['alumnos']=array_values(array_filter($courses[$idx]['alumnos'],fn($a)=>$a!==$uid)); }
    if ($action==='add_profesor') { $uid=$body['user_id']??null; if ($uid&&!in_array($uid,$courses[$idx]['profesores'])) $courses[$idx]['profesores'][]=$uid; }
    if ($action==='remove_profesor') { $uid=$body['user_id']??null; $courses[$idx]['profesores']=array_values(array_filter($courses[$idx]['profesores'],fn($p)=>$p!==$uid)); }
    db_write(COURSES_FILE,$courses); echo json_encode(['success'=>true]); exit;
}

if ($method==='DELETE') {
    $id=$body['id']??null; $courses=db_read(COURSES_FILE); $idx=db_find_index($courses,'id',$id);
    if ($idx===-1) { http_response_code(404); echo json_encode(['error'=>'No encontrado']); exit; }
    array_splice($courses,$idx,1); db_write(COURSES_FILE,$courses); echo json_encode(['success'=>true]); exit;
}
http_response_code(405); echo json_encode(['error'=>'Método no permitido']);
