## Diagrama de Flujo Logico de la Base de Datos

[Inicio]
   ↓
[Usuario accede al sistema]
   ↓
¿Tiene cuenta?
 ├── No → [Registrarse]
 │          ↓
 │     [Guardar datos]
 │          ↓
 │     [Cuenta creada]
 │
 └── Sí → [Iniciar sesión]
              ↓
        ¿Credenciales válidas?
         ├── No → [Mostrar error] → (volver a login)
         └── Sí
              ↓
        [Acceso al sistema]
              ↓
     [Seleccionar opción]
              ↓
   ┌───────────────┬───────────────┬───────────────┐
   │               │               │
[Ver materias] [Ver notas] [Estado académico]
   │               │               │
   ↓               ↓               ↓
[Mostrar datos] [Mostrar notas] [Evaluar promoción]
                                   ↓
                          ¿Cumple requisitos?
                           ├── Sí → [Promociona]
                           └── No → [No promociona]
                                   ↓
                              [Mostrar resultado]
                                   ↓
                               [Cerrar sesión]
                                   ↓
                                [Fin]

---

## users.json

|Campo|Tipo|
|---|---|
|id|string PK|
|github_id|string|
|username|string|
|avatar|string|
|email|string|
|nombre|string|
|apellido|string|
|dni|string|
|telefono|string|
|role|admin / director / subdirector / profesor / preceptor / alumno|
|status|pending_approval / approved / rejected|
|manual|boolean|

---

## courses.json

|Campo|Tipo|
|---|---|
|id|string PK|
|nombre|string|
|anio|int|
|division|string|
|turno|string|
|materias|array → { id, nombre, profesor_id FK→users }|
|alumnos|array de user_id FK→users|
|profesores|array de user_id FK→users|

---

## rooms.json

|Campo|Tipo|
|---|---|
|id|string PK|
|nombre|string|
|capacidad|int|
|ubicacion|string|
|curso_id|FK→courses|
|preceptor_id|FK→users|

---

## grades.json

|Campo|Tipo|
|---|---|
|id|string PK|
|alumno_id|FK→users|
|curso_id|FK→courses|
|materia_id|FK→courses.materias|
|cuatrimestre|1 / 2|
|nota|float (1–10)|
|concepto|string|
|asistencia|int|
|estado|aprobado / desaprobado / pendiente|
|fecha|date|
|registrado_por|FK→users|

---

## works.json

|Campo|Tipo|
|---|---|
|id|string PK|
|titulo|string|
|descripcion|string|
|curso_id|FK→courses|
|materia_id|FK→courses.materias|
|fecha_entrega|date|
|estado|activo / …|
|created_by|FK→users|
|created_at|datetime|
|submissions|array embebido ↓|

### works.submissions (embebido)

|Campo|Tipo|
|---|---|
|alumno_id|FK→users|
|entregado|boolean|
|contenido|string|
|fecha_entrega|datetime|
|nota_promedio|float|
|estado_calificacion|sin_calificar / aprobado / desaprobado|
|notas_profesores|array → { profesor_id, nota, devolucion, fecha }|

---

## Relaciones clave

```
users ──< courses.alumnos        (alumno pertenece a cursos)
users ──< courses.profesores     (profesor dicta cursos)
users ──< courses.materias.profesor_id (profesor de materia)
users ──< rooms.preceptor_id
courses ──< grades
courses ──< works
courses.materias ──< grades
courses.materias ──< works
works ──< works.submissions
works.submissions ──< notas_profesores
```
