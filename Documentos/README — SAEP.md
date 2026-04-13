# SAEP — Sistema de Administración Escolar

Sistema web de gestión escolar que integra autenticación con GitHub OAuth. Permite administrar usuarios, cursos, materias, aulas, calificaciones y trabajos prácticos, con control de acceso basado en roles.

---

## Tabla de contenidos

1. [Descripción general](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#descripci%C3%B3n-general)
2. [Tecnologías](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#tecnolog%C3%ADas)
3. [Estructura del proyecto](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#estructura-del-proyecto)
4. [Instalación y configuración](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#instalaci%C3%B3n-y-configuraci%C3%B3n)
5. [Autenticación y flujo de onboarding](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#autenticaci%C3%B3n-y-flujo-de-onboarding)
6. [Roles y permisos](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#roles-y-permisos)
7. [Base de datos (JSON)](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#base-de-datos-json)
8. [API — Endpoints](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#api--endpoints)
9. [Módulos del sistema](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#m%C3%B3dulos-del-sistema)
10. [Integración con GitHub](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#integraci%C3%B3n-con-github)
11. [Interfaz de usuario](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#interfaz-de-usuario)

---

## Descripción general

SAEP es una aplicación PHP orientada a instituciones educativas de nivel secundario. En lugar de una base de datos relacional tradicional, utiliza archivos JSON como capa de persistencia, lo que simplifica el despliegue a un servidor con PHP sin necesidad de configurar MySQL o PostgreSQL.

El sistema diferencia seis tipos de usuario con distintos niveles de acceso, desde el administrador con control total hasta el alumno que solo puede ver sus propios datos. La autenticación se delega completamente a GitHub OAuth 2.0.

---

## Tecnologías

|Capa|Tecnología|
|---|---|
|Backend|PHP 8+ (sin framework)|
|Persistencia|Archivos JSON en `data/`|
|Autenticación|GitHub OAuth 2.0|
|Integración externa|GitHub REST API v3|
|Frontend|HTML + CSS + JavaScript vanilla|
|Fuentes|Inter (Google Fonts)|
|Servidor recomendado|Apache / Nginx + PHP-FPM|

---

## Estructura del proyecto

```
saep/
├── index.php               # Landing / login
├── login.php               # Inicia el flujo OAuth
├── callback.php            # Callback OAuth de GitHub
├── complete-profile.php    # Completar datos personales (primer login)
├── pending.php             # Pantalla de espera de aprobación
├── logout.php              # Destruye sesión
├── dashboard.php           # SPA principal (panel según rol)
├── config.php              # Constantes globales
│
├── lib/
│   ├── auth.php            # Helpers de autenticación y roles
│   └── db.php              # CRUD sobre archivos JSON
│
├── api/
│   ├── admin/
│   │   └── users.php       # Gestión de usuarios (admin/director)
│   ├── auth/
│   │   ├── me.php          # Devuelve el usuario actual
│   │   └── save-profile.php
│   ├── courses/
│   │   └── courses.php     # CRUD de cursos, materias y asignaciones
│   ├── grades/
│   │   └── grades.php      # CRUD de calificaciones
│   ├── rooms/
│   │   └── rooms.php       # CRUD de aulas
│   ├── works/
│   │   └── works.php       # Trabajos prácticos y entregas
│   └── repos/
│       ├── createRepo.php
│       ├── deleteRepo.php
│       ├── getRepos.php
│       ├── getFiles.php
│       ├── getFileContent.php
│       ├── saveFile.php
│       └── deleteFile.php
│
└── data/
    ├── users.json
    ├── courses.json
    ├── rooms.json
    ├── grades.json
    ├── works.json
    └── subjects.json
```

---

## Instalación y configuración

### Requisitos

- PHP 8.0 o superior
- Extensión `json` habilitada (incluida por defecto)
- Servidor web con soporte a PHP (Apache, Nginx, o `php -S` para desarrollo)
- Cuenta de GitHub para registrar la OAuth App

### Pasos

**1. Clonar o copiar el proyecto**

```bash
git clone <repo> /var/www/html/saep
```

**2. Crear la OAuth App en GitHub**

Ir a `GitHub → Settings → Developer settings → OAuth Apps → New OAuth App` y completar:

- **Homepage URL:** `http://localhost/saep` (o tu dominio)
- **Authorization callback URL:** `http://localhost/saep/callback.php`

Copiar el **Client ID** y generar un **Client Secret**.

**3. Configurar `config.php`**

```php
define('GITHUB_CLIENT_ID',     'TU_CLIENT_ID');
define('GITHUB_CLIENT_SECRET', 'TU_CLIENT_SECRET');
define('GITHUB_REDIRECT_URI',  'http://localhost/saep/callback.php');
```

**4. Crear la carpeta de datos**

```bash
mkdir saep/data
touch saep/data/users.json saep/data/courses.json
touch saep/data/rooms.json saep/data/grades.json
touch saep/data/works.json saep/data/subjects.json

# Inicializar cada archivo como array vacío
for f in saep/data/*.json; do echo "[]" > "$f"; done

# Permisos de escritura para el servidor web
chmod 664 saep/data/*.json
chown www-data:www-data saep/data/*.json
```

**5. Primer acceso**

El primer usuario que inicie sesión con GitHub es asignado automáticamente como `admin` con estado `approved`. Los siguientes deberán ser aprobados manualmente.

---

## Autenticación y flujo de onboarding

El sistema no tiene contraseñas propias. Toda la autenticación pasa por GitHub OAuth 2.0 con los scopes `read:user user:email repo delete_repo`.

```
[Usuario] → login.php
              └─ genera state aleatorio + redirige a GitHub

[GitHub]  → callback.php (con code + state)
              ├─ intercambia code por access_token
              ├─ obtiene perfil GitHub (/user) y email verificado (/user/emails)
              └─ según estado del usuario:
                    pending_profile   → complete-profile.php  (cargar DNI, nombre, apellido)
                    pending_approval  → pending.php            (esperar aprobación de admin)
                    approved          → dashboard.php
```

El `access_token` de GitHub se guarda en sesión PHP para las llamadas a la API de repositorios.

---

## Roles y permisos

|Rol|Gestión usuarios|Cursos / Aulas|Calificaciones|Trabajos|Repos|
|---|:-:|:-:|:-:|:-:|:-:|
|admin|✅ total|✅|✅|✅|✅|
|director|✅ (no puede asignar admin)|✅|✅|✅|✅|
|subdirector|✅ (no puede asignar admin)|✅|✅|✅|✅|
|profesor|❌|❌|✅ (sus materias)|✅ (sus materias)|✅|
|preceptor|❌|❌|❌|❌|✅|
|alumno|❌|❌|👁 solo los propios|👁 / ✏ solo los propios|✅|

### Funciones helper en `lib/auth.php`

```php
session_user()       // Devuelve usuario activo o null
require_auth()       // 401 si no hay sesión
require_approved()   // 403 si cuenta no aprobada
require_role([...])  // 403 si el rol no está en la lista
is_admin($user)
can_manage_users($user)
can_manage_courses($user)
can_grade($user)
```

---

## Base de datos (JSON)

Toda la persistencia se maneja con tres funciones en `lib/db.php`:

```php
db_read(string $file): array         // Lee y parsea JSON
db_write(string $file, array $data)  // Serializa y guarda
db_find(array $records, $field, $value): ?array   // Búsqueda lineal
db_find_index(array $records, $field, $value): int
generate_id(): string                // uniqid() como PK
```

### Esquema de cada archivo

#### `users.json`

```json
{
  "id": "...",
  "github_id": 12345678,
  "username": "usuario_github",
  "avatar": "https://avatars.githubusercontent.com/...",
  "email": "...",
  "nombre": "Juan",
  "apellido": "Pérez",
  "dni": "12345678",
  "telefono": "...",
  "role": "alumno",
  "status": "approved",
  "manual": false
}
```

#### `courses.json`

```json
{
  "id": "...",
  "nombre": "5to A",
  "anio": 5,
  "division": "A",
  "turno": "mañana",
  "materias": [
    { "id": "...", "nombre": "Matemática", "profesor_id": "..." }
  ],
  "alumnos": ["user_id_1", "user_id_2"],
  "profesores": ["user_id_3"]
}
```

#### `grades.json`

```json
{
  "id": "...",
  "alumno_id": "...",
  "curso_id": "...",
  "materia_id": "...",
  "cuatrimestre": 1,
  "nota": 8.5,
  "concepto": "Muy bueno",
  "asistencia": 90,
  "estado": "aprobado",
  "fecha": "2025-06-15",
  "registrado_por": "..."
}
```

#### `works.json` (con submissions embebidas)

```json
{
  "id": "...",
  "titulo": "TP Nro 1",
  "descripcion": "...",
  "curso_id": "...",
  "materia_id": "...",
  "fecha_entrega": "2025-07-01",
  "estado": "activo",
  "created_by": "...",
  "created_at": "2025-06-01 10:00:00",
  "submissions": [
    {
      "alumno_id": "...",
      "entregado": true,
      "contenido": "Mi resolución...",
      "fecha_entrega": "2025-06-28 14:30:00",
      "nota_promedio": 7.5,
      "estado_calificacion": "aprobado",
      "notas_profesores": [
        { "profesor_id": "...", "nota": 7.5, "devolucion": "Bien.", "fecha": "2025-06-30 09:00:00" }
      ]
    }
  ]
}
```

---

## API — Endpoints

Todos los endpoints devuelven `Content-Type: application/json`. La autenticación es por sesión PHP (cookie).

### Usuarios — `api/admin/users.php`

|Método|Acción|Roles permitidos|
|---|---|---|
|GET|Listar todos los usuarios|admin, director, subdirector|
|POST|Crear usuario manual|admin|
|PATCH|Editar rol, estado o datos personales|admin, director, subdirector|
|DELETE|Eliminar usuario|admin|

**Campos editables vía PATCH:** `role`, `status`, `nombre`, `apellido`, `dni`, `email`, `telefono`

**Estados de usuario:** `pending_profile` → `pending_approval` → `approved` / `rejected`

---

### Cursos — `api/courses/courses.php`

|Método|Acción|Roles permitidos|
|---|---|---|
|GET|Listar cursos|todos los aprobados|
|POST|Crear curso|admin, director, subdirector|
|PATCH|Modificar curso (acciones)|admin, director, subdirector|
|DELETE|Eliminar curso|admin, director, subdirector|

**Acciones disponibles en PATCH (`action`):**

|Acción|Descripción|
|---|---|
|`add_materia`|Agrega una materia al curso (con `nombre` y `profesor_id`)|
|`remove_materia`|Elimina una materia por `materia_id`|
|`add_alumno`|Inscribe un alumno al curso por `user_id`|
|`remove_alumno`|Desinscribe un alumno|
|`add_profesor`|Asigna un profesor al curso|
|`remove_profesor`|Desasigna un profesor|

---

### Calificaciones — `api/grades/grades.php`

|Método|Acción|Restricción|
|---|---|---|
|GET|Listar calificaciones|Alumnos: solo las propias. Profesores: solo sus materias.|
|POST|Crear calificación|admin, director, subdirector, profesor|
|PATCH|Editar nota/concepto/asistencia/estado|admin, director, subdirector, profesor|
|DELETE|Eliminar calificación|admin, director, subdirector, profesor|

**Filtros GET:** `?curso_id=`, `?alumno_id=`, `?materia_id=`

**Nota:** Se valida que no exista ya una calificación para el mismo `alumno_id + materia_id + cuatrimestre`.

---

### Trabajos prácticos — `api/works/works.php`

|Método|Acción|
|---|---|
|GET|Listar trabajos (filtros: `curso_id`, `materia_id`) o detalle por `?id=`|
|POST|Crear trabajo o calificar entrega (`action: grade_submission`)|
|PUT|Alumno entrega su trabajo|
|DELETE|Eliminar trabajo|

Al crear un trabajo, el sistema genera automáticamente una `submission` vacía por cada alumno inscripto en el curso.

La calificación promedia las notas de todos los profesores que evaluaron (`notas_profesores`).

---

### Aulas — `api/rooms/rooms.php`

|Método|Acción|
|---|---|
|GET|Listar aulas|
|POST|Crear aula (`nombre`, `capacidad`, `ubicacion`)|
|PATCH|Editar aula (incluyendo `curso_id` y `preceptor_id`)|
|DELETE|Eliminar aula|

---

### Repositorios GitHub — `api/repos/`

Todos los endpoints usan el `access_token` guardado en sesión para actuar en nombre del usuario autenticado sobre la API de GitHub.

|Archivo|Método|Descripción|
|---|---|---|
|`getRepos.php`|GET|Lista repositorios del usuario (máx. 100, ordenados por última actividad)|
|`createRepo.php`|POST|Crea un repositorio nuevo (`name`, `description`, `private`)|
|`deleteRepo.php`|DELETE|Elimina un repositorio por `full_name`|
|`getFiles.php`|GET|Lista archivos de un repo/path|
|`getFileContent.php`|GET|Obtiene contenido y SHA de un archivo|
|`saveFile.php`|PUT|Crea o actualiza un archivo (requiere `sha` si ya existe)|
|`deleteFile.php`|DELETE|Elimina un archivo por `repo`, `path` y `sha`|

---

## Módulos del sistema

### Dashboard

SPA de una sola página cargada en `dashboard.php`. El sidebar y los paneles visibles se renderizan condicionalmente según el rol del usuario. No hay recarga de página entre secciones.

### Gestión de usuarios (admin/director/subdirector)

- Listado con filtros por rol y estado
- Cambio de rol y aprobación/rechazo de cuentas
- Alta manual de usuarios (sin GitHub)
- Eliminación de usuarios (solo admin)

### Gestión de cursos

- Creación de cursos con año, división y turno
- Asignación de materias con profesor responsable
- Inscripción y desinscripción de alumnos y profesores
- Cada curso puede tener un aula asignada

### Calificaciones

- Registro por cuatrimestre (1 o 2)
- Nota numérica (1–10), concepto y porcentaje de asistencia
- Estado: `aprobado`, `desaprobado`, `pendiente`
- El alumno solo ve sus propias calificaciones
- El profesor solo ve las de sus materias

### Trabajos prácticos

- El docente crea el trabajo asignado a un curso/materia
- Se generan automáticamente las entregas pendientes para cada alumno del curso
- El alumno entrega contenido textual
- Múltiples profesores pueden calificar; la nota final es el promedio
- Estados de entrega: `sin_calificar`, `aprobado`, `desaprobado`

### Editor de repositorios GitHub

- Navegación por árbol de archivos del repositorio
- Editor de código integrado con guardado directo vía API de GitHub
- Creación y eliminación de repositorios y archivos

---

## Integración con GitHub

El sistema usa GitHub en dos capas:

**Autenticación (OAuth 2.0)**

- Scope: `read:user user:email`
- Identifica al usuario por `github_id` (número entero estable)
- El email se obtiene de `/user/emails` buscando el primario y verificado

**API de repositorios**

- Scope adicional: `repo delete_repo`
- Las llamadas se hacen con `file_get_contents` + `stream_context_create`
- El token se almacena en `$_SESSION['access_token']` durante la sesión

---

## Interfaz de usuario

La UI está construida directamente en `dashboard.php` con HTML y CSS vanilla. Usa una grilla de dos columnas: sidebar de navegación fijo (230px) y área de contenido principal.

**Paleta de colores:**

|Variable|Color|
|---|---|
|`--navy`|`#1a3a6b` (color principal de marca)|
|`--red`|`#e63946`|
|`--green`|`#16a34a`|
|`--amber`|`#d97706`|
|`--bg`|`#f0f2f5`|

**Pills de roles:**

|Rol|Color|
|---|---|
|admin|Violeta|
|director / subdirector|Azul|
|profesor|Verde|
|preceptor|Ámbar|
|alumno|Gris|

La navegación del sidebar varía según el rol: un alumno solo ve sus calificaciones y trabajos; un admin accede a todos los módulos.
