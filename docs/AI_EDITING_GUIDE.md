# SAEP — Guía de Edición con IA

Esta guía explica cómo usar IA (como Claude) para editar este proyecto
de forma eficiente, clara y sin romper nada.

---

## Principios generales

1. **Siempre indicar el archivo exacto** que se quiere editar
2. **Mencionar la sección** (hay comentarios de sección en cada CSS)
3. **Describir el cambio** en términos visuales o funcionales
4. **Pedir solo lo necesario** — cambios pequeños y concretos son más seguros

---

## Estructura de un buen prompt para IA

```
Archivo: assets/css/[nombre].css
Sección: [nombre de la sección o componente]
Cambio: [descripción clara de lo que se quiere]
Restricción: [qué NO debe tocarse, si aplica]
```

---

## Ejemplos de prompts listos para usar

### Cambiar el color principal
```
Archivo: assets/css/global.css
Sección: Variables globales (:root)
Cambio: Cambiar --navy a #0d3b6e y --navy-dark a #092d54
```

### Cambiar el ancho del sidebar
```
Archivo: assets/css/dashboard.css
Sección: 1. LAYOUT BASE
Cambio: Aumentar el ancho del sidebar de 192px a 220px
        (también actualizar en .app grid-template-columns y .repo-sidebar)
```

### Agregar un nuevo color de badge
```
Archivo: assets/css/global.css
Sección: Badges
Cambio: Agregar .badge-purple con fondo rgba(124,58,237,.1) y color #7c3aed
```

### Modificar el estilo de una card de curso
```
Archivo: assets/css/dashboard.css
Sección: 5. STATS Y CARDS
Cambio: Hacer que .card tenga border-radius de 10px y un box-shadow suave
```

### Agregar un nuevo panel al dashboard
```
Archivo: dashboard.php
Cambio: Agregar un nuevo panel con id="panel-calendario" después del último panel.
        Debe tener un .ph con título "Calendario" y un .pb vacío.
        También agregar el botón en el sidebar con id="nav-calendario".
Archivo: assets/css/dashboard.css (si necesita estilos específicos)
Archivo: assets/js/dashboard.js (agregar 'calendario':loadCalendario en el objeto loaders)
```

### Agregar un endpoint de API
```
Crear archivo: api/[modulo]/[nombre].php
Estructura base:
<?php
session_start();
require_once '../../config.php';
require_once '../../lib/db.php';
require_once '../../lib/auth.php';
// Validar sesión
$user = require_auth(); // lanza 401 si no hay sesión
header('Content-Type: application/json');
// ... lógica del endpoint
```

---

## Estructura de dashboard.js

El archivo `assets/js/dashboard.js` está organizado en secciones con comentarios:

```
// ── Nav ──────────  función nav(id) — cambiar panel activo
// ── API ──────────  función api(url) — wrapper de fetch con auth
// ── INICIO ───────  loadInicio() — stats de bienvenida
// ── REPOS ────────  loadRepos(), selectRepo(), etc.
// ── USERS ────────  loadUsers(), filterUsers(), etc.
// ── COURSES ──────  loadCourses(), renderCourses(), etc.
// ── ROOMS ────────  loadRooms(), etc.
// ── GRADES ───────  initGrades(), loadMyGrades(), etc.
// ── WORKS ────────  loadMyWorks(), loadWorks(), etc.
// ── MAIL ─────────  initMail(), sendMail(), etc.
// ── SIDEBAR ──────  toggleSidebar() — mobile menu
```

Para editar una función específica, indicar la sección en el prompt:
```
Archivo: assets/js/dashboard.js
Sección: REPOS
Función: loadRepos
Cambio: Mostrar un skeleton loader mientras carga, en lugar de "Cargando..."
```

---

## Lo que NO se debe pedir a la IA que haga sola

- ❌ "Refactorizá todo el dashboard.js" → muy riesgoso, hacerlo en partes
- ❌ "Cambiar la base de datos a MySQL" → cambio arquitectural grande
- ❌ "Mejorar el diseño general" → demasiado amplio, especificar componente
- ❌ Modificar `config.php` con credenciales reales → hacerlo manualmente

---

## Variables PHP disponibles en dashboard.php

Estas variables están disponibles en el `<script>` al inicio de dashboard.php:

```javascript
window.ROLE   // rol del usuario: 'admin', 'profesor', 'alumno', etc.
window.MY_ID  // github_id del usuario actual (string)
```

Se usan en dashboard.js como:
```javascript
const ROLE = window.ROLE;
const MY_ID = window.MY_ID;
```

---

## Convenciones del código PHP (lib/ y api/)

### lib/db.php — funciones de base de datos
```php
db_read(USERS_FILE)              // Lee un JSON, devuelve array
db_write(USERS_FILE, $data)      // Escribe array como JSON
db_find($array, 'campo', $valor) // Busca un elemento por campo
```

### lib/auth.php — funciones de autenticación
```php
require_auth()   // Lanza 401 si no hay sesión, devuelve $user
check_role($user, ['admin', 'director'])  // Lanza 403 si no tiene rol
```

### Respuesta estándar de endpoints
```php
// Éxito
echo json_encode(['ok' => true, 'data' => $resultado]);

// Error
http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Mensaje de error']);
```

---

## Checklist antes de hacer un PR / commit

- [ ] Los CSS de páginas solo usan variables de `global.css` (no valores hardcoded)
- [ ] Los nuevos endpoints validan sesión con `require_auth()`
- [ ] Los nuevos campos de formulario tienen `<label>` accesible
- [ ] Las funciones JS nuevas están en la sección correcta de dashboard.js
- [ ] No se commitearon credenciales ni archivos de `data/*.json` sensibles
- [ ] Funciona en mobile (probar en 375px de ancho)
