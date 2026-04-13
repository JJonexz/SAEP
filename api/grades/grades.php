<?php
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');
$me=require_approved();
$method=$_SERVER['REQUEST_METHOD'];
$ESTADOS=['aprobado','desaprobado','pendiente'];

if ($method==='GET') {
    $grades=db_read(GRADES_FILE);
    $curso_id=$_GET['curso_id']??null; $alumno_id=$_GET['alumno_id']??null; $materia_id=$_GET['materia_id']??null;
    if ($me['role']==='alumno') $grades=array_filter($grades,fn($g)=>$g['alumno_id']===$me['id']);
    elseif ($me['role']==='profesor') {
        $courses=db_read(COURSES_FILE); $myMats=[];
        foreach ($courses as $c) foreach ($c['materias'] as $m) if ($m['profesor_id']===$me['id']) $myMats[]=$m['id'];
        $grades=array_filter($grades,fn($g)=>in_array($g['materia_id'],$myMats));
    }
    if ($curso_id)   $grades=array_filter($grades,fn($g)=>$g['curso_id']===$curso_id);
    if ($alumno_id)  $grades=array_filter($grades,fn($g)=>$g['alumno_id']===$alumno_id);
    if ($materia_id) $grades=array_filter($grades,fn($g)=>$g['materia_id']===$materia_id);
    echo json_encode(array_values($grades)); exit;
}

$me=require_role(['admin','director','subdirector','profesor']);
$body=json_decode(file_get_contents('php://input'),true);

if ($method==='POST') {
    $required=['alumno_id','curso_id','materia_id','cuatrimestre'];
    foreach ($required as $r) { if (empty($body[$r])) { http_response_code(400); echo json_encode(['error'=>"$r requerido"]); exit; } }
    if (!in_array((int)$body['cuatrimestre'],[1,2])) { http_response_code(400); echo json_encode(['error'=>'Cuatrimestre inválido']); exit; }
    $nota=isset($body['nota'])&&$body['nota']!==''?floatval($body['nota']):null;
    if ($nota!==null&&($nota<1||$nota>10)) { http_response_code(400); echo json_encode(['error'=>'Nota 1-10']); exit; }
    $grades=db_read(GRADES_FILE);
    $existing=array_filter($grades,fn($g)=>$g['alumno_id']===$body['alumno_id']&&$g['materia_id']===$body['materia_id']&&(int)$g['cuatrimestre']===(int)$body['cuatrimestre']);
    if (!empty($existing)) { http_response_code(409); echo json_encode(['error'=>'Ya existe calificación para este alumno/materia/cuatrimestre']); exit; }
    $grades[]=['id'=>generate_id(),'alumno_id'=>$body['alumno_id'],'curso_id'=>$body['curso_id'],'materia_id'=>$body['materia_id'],'cuatrimestre'=>(int)$body['cuatrimestre'],'nota'=>$nota,'concepto'=>$body['concepto']??null,'asistencia'=>isset($body['asistencia'])&&$body['asistencia']!==''?(int)$body['asistencia']:null,'estado'=>in_array($body['estado']??'',$ESTADOS)?$body['estado']:'pendiente','fecha'=>date('Y-m-d'),'registrado_por'=>$me['id']];
    db_write(GRADES_FILE,$grades); echo json_encode(['success'=>true]); exit;
}
if ($method==='PATCH') {
    $id=$body['id']??null; $grades=db_read(GRADES_FILE); $idx=db_find_index($grades,'id',$id);
    if ($idx===-1) { http_response_code(404); echo json_encode(['error'=>'No encontrada']); exit; }
    foreach (['nota','concepto','asistencia','estado'] as $f) if (array_key_exists($f,$body)) $grades[$idx][$f]=$body[$f];
    $grades[$idx]['fecha']=date('Y-m-d'); $grades[$idx]['registrado_por']=$me['id'];
    db_write(GRADES_FILE,$grades); echo json_encode(['success'=>true]); exit;
}
if ($method==='DELETE') {
    $id=$body['id']??null; $grades=db_read(GRADES_FILE); $idx=db_find_index($grades,'id',$id);
    if ($idx===-1) { http_response_code(404); echo json_encode(['error'=>'No encontrada']); exit; }
    array_splice($grades,$idx,1); db_write(GRADES_FILE,$grades); echo json_encode(['success'=>true]); exit;
}
http_response_code(405); echo json_encode(['error'=>'Método no permitido']);
