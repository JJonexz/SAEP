# SAEP — Sistema de Administración Educativa y Proyectos

## Descripción
Sistema web para gestión educativa con autenticación OAuth vía GitHub.
Permite administrar usuarios, cursos, aulas, notas, trabajos y repositorios.

---

## Estructura del proyecto

```
saep/
│
├── 📄 index.php              ← Página de inicio / login con GitHub
├── 📄 login.php              ← Inicia el flujo OAuth de GitHub
├── 📄 callback.php           ← Recibe el token de GitHub post-auth
├── 📄 logout.php             ← Cierra sesión
├── 📄 pending.php            ← Espera de aprobación de cuenta
├── 📄 complete-profile.php   ← Formulario de perfil inicial
├── 📄 dashboard.php          ← Panel principal (toda la app)
├── 📄 config.php             ← Configuración global (credenciales, rutas)
│
├── assets/
│   ├── css/
│   │   ├── global.css        ← ⭐ CSS UNIVERSAL (variables, reset, componentes)
│   │   ├── login.css         ← Estilos de index.php
│   │   ├── pending.css       ← Estilos de pending.php
│   │   ├── complete-profile.css ← Estilos de complete-profile.php
│   │   └── dashboard.css     ← Estilos del panel principal
│   └── js/
│       ├── dashboard.js      ← Toda la lógica del panel (~700 líneas)
│       ├── complete-profile.js ← Validación del formulario de perfil
│       └── pending.js        ← Lógica de la página de espera
│
├── api/                      ← Endpoints PHP (llamados via fetch desde JS)
│   ├── admin/
│   │   └── users.php         ← CRUD de usuarios (solo admin/director)
│   ├── auth/
│   │   ├── me.php            ← Datos del usuario actual
│   │   └── save-profile.php  ← Guardar perfil inicial
│   ├── courses/
│   │   └── courses.php       ← Listado y CRUD de cursos
│   ├── grades/
│   │   └── grades.php        ← Notas
│   ├── mail/
│   │   └── send.php          ← Envío de correos
│   ├── repos/
│   │   ├── createRepo.php    ← Crear repositorio en GitHub
│   │   ├── deleteFile.php
│   │   ├── deleteRepo.php
│   │   ├── getFileContent.php
│   │   ├── getFiles.php
│   │   ├── getRepos.php
│   │   └── saveFile.php
│   ├── rooms/
│   │   └── rooms.php         ← Aulas
│   └── works/
│       └── works.php         ← Trabajos y entregas
│
├── lib/
│   ├── auth.php              ← Helpers de autenticación y sesión
│   └── db.php                ← Helpers de base de datos (JSON files)
│
├── data/                     ← Base de datos JSON (archivos planos)
│   ├── users.json
│   ├── courses.json
│   ├── subjects.json
│   ├── rooms.json
│   ├── grades.json
│   └── works.json
│
└── docs/
    ├── README.md             ← Este archivo
    ├── CSS_GUIDE.md          ← Guía de edición de estilos
    └── AI_EDITING_GUIDE.md   ← Guía para edición con IA
```

---

## Roles del sistema

| Rol          | Acceso                                           |
|--------------|--------------------------------------------------|
| `admin`      | Todo, incluyendo gestión de usuarios             |
| `director`   | Todo excepto funciones exclusivas de admin       |
| `subdirector`| Igual que director                               |
| `profesor`   | Cursos, repos, notas, trabajos                   |
| `preceptor`  | Cursos, aulas, notas                             |
| `alumno`     | Mis notas, mis trabajos, mis repos               |

---

## Flujo de autenticación

```
Usuario → index.php → login.php → GitHub OAuth → callback.php
    ↓
  ¿Perfil completo?
  No  → complete-profile.php → pending.php (espera aprobación)
  Sí  → ¿Aprobado?
        No  → pending.php
        Sí  → dashboard.php
```

---

## Configuración inicial

1. Copiar el proyecto al servidor PHP (Apache/Nginx)
2. Editar `config.php` con las credenciales de GitHub OAuth App:
   ```php
   define('GITHUB_CLIENT_ID',     'tu_client_id');
   define('GITHUB_CLIENT_SECRET', 'tu_client_secret');
   ```
3. Asegurarse de que la carpeta `data/` tenga permisos de escritura
4. El archivo `api/mail/api_key.env` debe crearse manualmente (está en .gitignore)

---

## Stack tecnológico

- **Backend**: PHP 8+ (sin framework, funciones propias en lib/)
- **Frontend**: HTML5 + CSS3 + JavaScript vanilla (sin framework)
- **Auth**: GitHub OAuth 2.0
- **DB**: Archivos JSON (sin base de datos relacional)
- **Repos**: GitHub API v3

---

## Para el equipo

- Leer `docs/CSS_GUIDE.md` antes de tocar estilos
- Leer `docs/AI_EDITING_GUIDE.md` antes de usar IA para editar
- Cada endpoint en `api/` valida sesión y rol internamente
- Los datos en `data/*.json` NO se commitean en producción (sensibles)
