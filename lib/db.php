<?php
/**
 * Capa de acceso a base de datos MySQL (escuela.sql)
 * Reemplaza el sistema de archivos JSON anterior.
 */
require_once __DIR__ . '/../config.php';

function db_connect(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

// ── Helpers genéricos ────────────────────────────────────────────────────────

function db_query(string $sql, array $params = []): array {
    $st = db_connect()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function db_execute(string $sql, array $params = []): int {
    $st = db_connect()->prepare($sql);
    $st->execute($params);
    return (int) db_connect()->lastInsertId();
}

function db_row(string $sql, array $params = []): ?array {
    $rows = db_query($sql, $params);
    return $rows ? $rows[0] : null;
}

// ── Usuarios: personal + alumnos unificados ──────────────────────────────────

/**
 * Busca un usuario por DNI en personal o alumnos.
 * Devuelve array normalizado con los campos que usa SAEP.
 */
function db_find_user_by_dni(int $dni): ?array {
    // Primero busca en personal (staff)
    $row = db_row('SELECT * FROM personal WHERE dni = ?', [$dni]);
    if ($row) {
        return _normalize_personal($row);
    }
    // Luego en alumnos
    $row = db_row('SELECT * FROM alumnos WHERE dni = ?', [$dni]);
    if ($row) {
        return _normalize_alumno($row);
    }
    return null;
}

function db_find_user_by_id(string $id): ?array {
    // id tiene formato "P_DNI" para personal o "A_DNI" para alumnos
    [$tipo, $dni] = explode('_', $id, 2);
    if ($tipo === 'P') {
        $row = db_row('SELECT * FROM personal WHERE dni = ?', [(int)$dni]);
        return $row ? _normalize_personal($row) : null;
    }
    $row = db_row('SELECT * FROM alumnos WHERE dni = ?', [(int)$dni]);
    return $row ? _normalize_alumno($row) : null;
}

function _normalize_personal(array $r): array {
    $tag = strtolower(trim($r['tag'] ?? ''));
    // El campo tag define el rol; si está vacío se usa 'profesor' por defecto
    $role = in_array($tag, ROLES) ? $tag : 'profesor';
    return [
        'id'            => 'P_' . $r['dni'],
        'dni'           => (string)$r['dni'],
        'nombre'        => $r['nombre'],
        'apellido'      => $r['apellido'],
        'email'         => $r['email'],
        'avatar'        => null,
        'role'          => $role,
        'status'        => 'approved',
        'manual'        => true,
        'password_hash' => $r['pass'],   // campo pass de la tabla personal
        'tipo'          => 'personal',
    ];
}

function _normalize_alumno(array $r): array {
    // Buscar email
    $email_row = db_row('SELECT email FROM email WHERE dni = ?', [(int)$r['dni']]);
    
    // Buscar padres/tutores
    $tutores = db_query(
        'SELECT pt.dni, pt.nombre, pt.apellido, pt.telefono, pt.domicilio, pa.parentesco
         FROM padresalumnos pal
         JOIN padrestutores pt ON pt.dni = pal.dni_padrestutores
         JOIN parentesco pa ON pa.id = pal.id_parentesco
         WHERE pal.dni_alumnos = ?',
        [(int)$r['dni']]
    );

    return [
        'id'            => 'A_' . $r['dni'],
        'dni'           => (string)$r['dni'],
        'nombre'        => $r['nombre'],
        'apellido'      => $r['apellido'],
        'email'         => $email_row ? $email_row['email'] : null,
        'fechan'        => $r['fechan'] ?? null,
        'domicilio'     => $r['domicilio'] ?? null,
        'avatar'        => null,
        'role'          => 'alumno',
        'status'        => 'approved',
        'manual'        => true,
        'password_hash' => $r['clave'],
        'tipo'          => 'alumno',
        'tutores'       => $tutores,
    ];
}

/**
 * Lista todos los usuarios (personal + alumnos) sin exponer el hash.
 */
function db_list_users(): array {
    $personal = db_query('SELECT * FROM personal ORDER BY apellido, nombre');
    $alumnos  = db_query('SELECT * FROM alumnos  ORDER BY apellido, nombre');
    $result   = [];
    foreach ($personal as $r) {
        $u = _normalize_personal($r);
        unset($u['password_hash']);
        $result[] = $u;
    }
    foreach ($alumnos as $r) {
        $u = _normalize_alumno($r);
        unset($u['password_hash']);
        $result[] = $u;
    }
    return $result;
}

// ── Cursos ───────────────────────────────────────────────────────────────────

function db_list_courses(bool $withDetails = false): array {
    // Query base optimizada - solo datos esenciales
    $ccl = db_query(
        'SELECT ccl.id, ccl.ciclolectivo, ccl.estado,
                c.id AS id_curso, c.ano, c.division, c.turno
         FROM cursosciclolectivo ccl
         JOIN cursos c ON c.id = ccl.id_cursos
         WHERE ccl.estado = "A"
         ORDER BY ccl.ciclolectivo DESC, c.ano, c.division'
    );
    
    // Si no necesita detalles, devolver solo datos básicos
    if (!$withDetails) {
        return array_map(fn($r) => [
            'id'           => $r['id'],
            'id_curso'     => $r['id_curso'],
            'ano'          => $r['ano'],
            'division'     => $r['division'],
            'turno'        => $r['turno'],
            'ciclolectivo' => $r['ciclolectivo'],
            'estado'       => $r['estado'],
            'nombre'       => $r['ano'] . '° ' . $r['division'] . ' - ' . $r['turno'] . ' (' . $r['ciclolectivo'] . ')',
            'anio'         => $r['ano'],
        ], $ccl);
    }
    
    // Con detalles - optimizar queries
    $courses = [];
    foreach ($ccl as $r) {
        // Contar alumnos en lugar de traer todos
        $alumCount = db_row(
            'SELECT COUNT(*) as c FROM asignacionesalumnos WHERE id_cursosciclolectivo = ? AND estado != ?',
            [$r['id'], 'E']
        );
        
        // Materias del curso con docentes
        $materias = db_query(
            'SELECT cu.cupof, m.id AS id_materia, m.nombre AS materia, m.abreviatura,
                    p.dni AS dni_profesor, p.apellido AS prof_apellido, p.nombre AS prof_nombre
             FROM cupof cu
             JOIN materias m ON m.id = cu.id_materias
             LEFT JOIN revista rv ON rv.cupof = cu.cupof AND rv.fh = ?
             LEFT JOIN personal p ON p.dni = rv.dni_personal
             WHERE cu.id_cursos = ?',
            ['0000-00-00', $r['id_curso']]
        );
        
        $courses[] = [
            'id'           => $r['id'],
            'id_curso'     => $r['id_curso'],
            'ano'          => $r['ano'],
            'division'     => $r['division'],
            'turno'        => $r['turno'],
            'ciclolectivo' => $r['ciclolectivo'],
            'estado'       => $r['estado'],
            'nombre' => $r['ano'] . '° ' . $r['division'] . ' - ' . $r['turno'] . ' (' . $r['ciclolectivo'] . ')',
            'anio'   => $r['ano'],
            'alumnos'      => $alumCount['c'] ?? 0, // Solo contar, no traer todos
            'materias'     => array_map(fn($m) => [
                'id'            => $m['cupof'],
                'id_materia'    => $m['id_materia'],
                'nombre'        => $m['materia'],
                'abreviatura'   => $m['abreviatura'],
                'profesor_id'   => $m['dni_profesor'] ? 'P_' . $m['dni_profesor'] : null,
                'profesor_nombre'=> $m['dni_profesor'] ? $m['prof_apellido'] . ', ' . $m['prof_nombre'] : null,
            ], $materias),
        ];
    }
    return $courses;
}

// ── Notas (informe_periodo) ──────────────────────────────────────────────────

function db_list_grades(?string $alumno_id = null, ?int $cupof = null): array {
    $where = []; $params = [];
    if ($alumno_id) {
        $dni = (int) explode('_', $alumno_id)[1];
        $where[] = 'aa.dni_alumnos = ?';
        $params[] = $dni;
    }
    if ($cupof) {
        $where[] = 'ip.cupof = ?';
        $params[] = $cupof;
    }
    $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    return db_query(
        "SELECT ip.id, ip.id_asignacionesalumnos, ip.cupof, ip.nota, ip.devolucion, ip.periodo,
                ip.dni_personal, aa.dni_alumnos,
                al.apellido, al.nombre,
                m.nombre AS materia
         FROM informe_periodo ip
         JOIN asignacionesalumnos aa ON aa.id = ip.id_asignacionesalumnos
         JOIN alumnos al ON al.dni = aa.dni_alumnos
         JOIN cupof cu ON cu.cupof = ip.cupof
         JOIN materias m ON m.id = cu.id_materias
         $whereStr
         ORDER BY al.apellido, al.nombre, ip.periodo",
        $params
    );
}

// ── Salones ──────────────────────────────────────────────────────────────────

function db_list_rooms(): array {
    $rows = db_query('SELECT * FROM salones ORDER BY piso, numero');
    return array_map(function($r) {
        $r['nombre'] = 'Aula ' . $r['numero'] . ' — ' . $r['tipo'];
        $r['id']     = $r['id_salones'];
        $r['ubicacion'] = 'Piso ' . $r['piso'] . ' · ' . $r['ubicacion'];
        $r['curso_id']    = null;
        $r['preceptor_id'] = null;
        return $r;
    }, $rows);
}

// ── Materias ─────────────────────────────────────────────────────────────────

function db_list_materias(): array {
    return db_query("SELECT * FROM materias WHERE estado = 'H' ORDER BY nombre");
}
