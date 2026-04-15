<?php
session_start();
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
header('Content-Type: application/json');
$me=require_approved();
$method=$_SERVER['REQUEST_METHOD'];

// ── GET ────────────────────────────────────────────────────────────────────
if ($method==='GET') {
    $works=db_read(WORKS_FILE);
    $id=$_GET['id']??null;
    if ($id) {
        $work=db_find($works,'id',$id);
        if (!$work) { http_response_code(404); echo json_encode(['error'=>'No encontrado']); exit; }
        // students see only their own submission
        if ($me['role']==='alumno') {
            $work['submissions']=array_values(array_filter($work['submissions']??[],fn($s)=>$s['alumno_id']===$me['id']));
        }
        echo json_encode($work); exit;
    }

    $curso_id=$_GET['curso_id']??null;
    $materia_id=$_GET['materia_id']??null;

    if ($me['role']==='alumno') {
        // only works for courses the student belongs to
        $courses=db_read(COURSES_FILE);
        $myCourses=array_filter($courses,fn($c)=>in_array($me['id'],$c['alumnos']??[]));
        $myCourseIds=array_column(array_values($myCourses),'id');
        $works=array_filter($works,fn($w)=>in_array($w['curso_id'],$myCourseIds));
    } elseif ($me['role']==='profesor') {
        $courses=db_read(COURSES_FILE); $myMats=[];
        foreach ($courses as $c) foreach ($c['materias']??[] as $m) if ($m['profesor_id']===$me['id']) $myMats[]=$m['id'];
        $works=array_filter($works,fn($w)=>in_array($w['materia_id'],$myMats));
    }

    if ($curso_id)   $works=array_filter($works,fn($w)=>$w['curso_id']===$curso_id);
    if ($materia_id) $works=array_filter($works,fn($w)=>$w['materia_id']===$materia_id);

    // For list view, don't send all submission content
    $result=array_map(fn($w)=>[
        'id'=>$w['id'],'titulo'=>$w['titulo'],'descripcion'=>$w['descripcion'],
        'curso_id'=>$w['curso_id'],'materia_id'=>$w['materia_id'],
        'fecha_entrega'=>$w['fecha_entrega'],'estado'=>$w['estado'],
        'submissions_count'=>count($w['submissions']??[]),
        'created_by'=>$w['created_by'],'created_at'=>$w['created_at'],
    ], array_values($works));

    echo json_encode(array_values($result)); exit;
}

// ── POST: create work assignment ───────────────────────────────────────────
if ($method==='POST') {
    $me=require_role(['admin','director','subdirector','profesor']);
    $body=json_decode(file_get_contents('php://input'),true);
    $action=$body['action']??'create';

    // Submit grade + feedback for a submission
    if ($action==='grade_submission') {
        $work_id=$body['work_id']??null; $alumno_id=$body['alumno_id']??null;
        $nota=isset($body['nota'])&&$body['nota']!==''?floatval($body['nota']):null;
        $devolucion=trim($body['devolucion']??'');
        if (!$work_id||!$alumno_id) { http_response_code(400); echo json_encode(['error'=>'work_id y alumno_id requeridos']); exit; }
        if ($nota!==null&&($nota<1||$nota>10)) { http_response_code(400); echo json_encode(['error'=>'Nota 1-10']); exit; }

        $works=db_read(WORKS_FILE);
        $wIdx=db_find_index($works,'id',$work_id);
        if ($wIdx===-1) { http_response_code(404); echo json_encode(['error'=>'Trabajo no encontrado']); exit; }

        $sIdx=null;
        foreach ($works[$wIdx]['submissions']??[] as $i=>$s) {
            if ($s['alumno_id']===$alumno_id) { $sIdx=$i; break; }
        }
        if ($sIdx===null) { http_response_code(404); echo json_encode(['error'=>'Entrega no encontrada']); exit; }

        // Add/update this teacher's grade
        $grades=$works[$wIdx]['submissions'][$sIdx]['notas_profesores']??[];
        $gIdx=null;
        foreach ($grades as $i=>$g) { if ($g['profesor_id']===$me['id']) { $gIdx=$i; break; } }

        $gradeEntry=['profesor_id'=>$me['id'],'nota'=>$nota,'devolucion'=>$devolucion,'fecha'=>date('Y-m-d H:i:s')];
        if ($gIdx!==null) $grades[$gIdx]=$gradeEntry;
        else $grades[]=$gradeEntry;

        $works[$wIdx]['submissions'][$sIdx]['notas_profesores']=$grades;

        // Calculate average note
        $notaVals=array_filter(array_column($grades,'nota'),fn($n)=>$n!==null);
        $works[$wIdx]['submissions'][$sIdx]['nota_promedio']=count($notaVals)>0?round(array_sum($notaVals)/count($notaVals),2):null;
        $avg=$works[$wIdx]['submissions'][$sIdx]['nota_promedio'];
        $works[$wIdx]['submissions'][$sIdx]['estado_calificacion']=($avg===null)?'sin_calificar':($avg>=6?'aprobado':'desaprobado');

        db_write(WORKS_FILE,$works);
        echo json_encode(['success'=>true,'nota_promedio'=>$avg]); exit;
    }

    // Create new work assignment
    $titulo=trim($body['titulo']??''); $curso_id=$body['curso_id']??null; $materia_id=$body['materia_id']??null;
    if (!$titulo||!$curso_id||!$materia_id) { http_response_code(400); echo json_encode(['error'=>'titulo, curso_id y materia_id requeridos']); exit; }

    // Build empty submissions for each student in the course
    $courses=db_read(COURSES_FILE);
    $course=db_find($courses,'id',$curso_id);
    $submissions=[];
    foreach ($course['alumnos']??[] as $alumnoId) {
        $submissions[]=['alumno_id'=>$alumnoId,'entregado'=>false,'contenido'=>null,'archivo_url'=>null,'fecha_entrega'=>null,'notas_profesores'=>[],'nota_promedio'=>null,'estado_calificacion'=>'sin_calificar'];
    }

    $works=db_read(WORKS_FILE);
    $works[]=['id'=>generate_id(),'titulo'=>$titulo,'descripcion'=>$body['descripcion']??'','curso_id'=>$curso_id,'materia_id'=>$materia_id,'fecha_entrega'=>$body['fecha_entrega']??null,'estado'=>'activo','submissions'=>$submissions,'created_by'=>$me['id'],'created_at'=>date('Y-m-d H:i:s')];
    db_write(WORKS_FILE,$works);
    echo json_encode(['success'=>true]); exit;
}

// ── PUT: student submits work ──────────────────────────────────────────────
if ($method==='PUT') {
    $me=require_role(['alumno']);
    $body=json_decode(file_get_contents('php://input'),true);
    $work_id=$body['work_id']??null; $contenido=trim($body['contenido']??'');
    if (!$work_id) { http_response_code(400); echo json_encode(['error'=>'work_id requerido']); exit; }

    $works=db_read(WORKS_FILE);
    $wIdx=db_find_index($works,'id',$work_id);
    if ($wIdx===-1) { http_response_code(404); echo json_encode(['error'=>'Trabajo no encontrado']); exit; }

    $sIdx=null;
    foreach ($works[$wIdx]['submissions']??[] as $i=>$s) {
        if ($s['alumno_id']===$me['id']) { $sIdx=$i; break; }
    }
    if ($sIdx===null) { http_response_code(403); echo json_encode(['error'=>'No estás asignado a este trabajo']); exit; }

    $works[$wIdx]['submissions'][$sIdx]['entregado']=true;
    $works[$wIdx]['submissions'][$sIdx]['contenido']=$contenido;
    $works[$wIdx]['submissions'][$sIdx]['fecha_entrega']=date('Y-m-d H:i:s');

    db_write(WORKS_FILE,$works);
    echo json_encode(['success'=>true]); exit;
}

// ── DELETE ─────────────────────────────────────────────────────────────────
if ($method==='DELETE') {
    $me=require_role(['admin','director','subdirector','profesor']);
    $body=json_decode(file_get_contents('php://input'),true);
    $id=$body['id']??null;
    $works=db_read(WORKS_FILE); $idx=db_find_index($works,'id',$id);
    if ($idx===-1) { http_response_code(404); echo json_encode(['error'=>'No encontrado']); exit; }
    array_splice($works,$idx,1); db_write(WORKS_FILE,$works);
    echo json_encode(['success'=>true]); exit;
}

http_response_code(405); echo json_encode(['error'=>'Método no permitido']);
