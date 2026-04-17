# SAEP — Guía de Estilos CSS

## Arquitectura CSS

El proyecto usa **un CSS universal + uno por página**:

```
assets/css/
├── global.css           ← Se carga en TODAS las páginas (siempre primero)
├── login.css            ← Solo en index.php
├── pending.css          ← Solo en pending.php
├── complete-profile.css ← Solo en complete-profile.php
└── dashboard.css        ← Solo en dashboard.php
```

Cada `.php` importa ambos archivos en el `<head>`:
```html
<link href="assets/css/global.css" rel="stylesheet">
<link href="assets/css/login.css" rel="stylesheet">
```

---

## Variables CSS (global.css)

Todas las variables están en `:root` dentro de `global.css`.
**Cambiar una variable acá afecta toda la app.**

### Colores principales
```css
--navy          /* Azul marino principal */
--navy-dark     /* Azul marino oscuro (hover) */
--navy-light    /* Azul marino claro */
--navy-faint    /* Azul marino muy suave (fondos de hover) */
--red           /* Rojo de acento y errores */
```

### Colores de estado
```css
--green         /* Éxito, aprobado */
--amber         /* Advertencia, pendiente */
--blue          /* Información */
```

### Colores de orientación (para cards de cursos)
```css
--o-prog        /* Programación — azul */
--o-mmo         /* MMO — rojo */
--o-cb          /* Ciclo Básico — ámbar */
--o-tur         /* Turismo — verde */
```

### Neutros y tipografía
```css
--bg            /* Fondo de página */
--white         /* Fondo de cards y panels */
--border        /* Color de bordes */
--text          /* Texto principal */
--text2         /* Texto secundario */
--muted         /* Texto tenue (labels, hints) */
--font          /* Familia tipográfica */
--radius        /* Radio de bordes */
```

---

## Qué va en cada archivo

### `global.css` — componentes reutilizables entre páginas
- Reset CSS
- Variables `:root`
- Animaciones globales (`fadeUp`, `fadeIn`)
- Botones (`.btn`, `.btn-navy`, `.btn-red`, etc.)
- Badges (`.badge-green`, `.badge-red`, etc.)
- Role pills (`.role-pill.admin`, etc.)
- Formularios (`.field`, `.fgrid`, `.ffull`)
- Modales (`.overlay`, `.modal`)
- Tablas (`.tbl-wrap`, `table`, `th`, `td`)
- Alertas (`.alert`, `.alert-amber`)
- Utilidades (`.empty`, `.mb1`, `.section-title`)

### `login.css` — solo para index.php
- `.top-bar` y sus hijos
- `.box` (caja de login)
- `.btn-gh` (botón de GitHub)

### `pending.css` — solo para pending.php
- Body centrado
- `.card` de espera
- `.info`, `.info-row`
- `.dot` (indicador amarillo)

### `complete-profile.css` — solo para complete-profile.php
- Body centrado
- `.card` de formulario
- `.card-header`, `.av`, `.uname`, `.uemail`
- `.grid`, `.full` (layout del form)
- `.btn` (submit)

### `dashboard.css` — solo para dashboard.php
- Layout `.app` (grid)
- Header (`.hdr-*`)
- Sidebar (`aside`, `.nav-item`, `.hamburger`)
- Paneles (`.panel`, `.ph`, `.pb`)
- Stats (`.stats`, `.stat`)
- Cards de cursos (`.cards`, `.card`, `.card-prog`, etc.)
- Filtros (`.courses-filter-row`, `.filter-btn`)
- Tabs (`.tabs`, `.tab`)
- Repos (`.repo-layout`, `.file-row`, `textarea.code`)
- Works (`.work-card`, `.sub-row`)
- Mail (`.ml-container`, `.ml-sidebar`)

---

## Guía de breakpoints

```css
@media (max-width: 1024px) { /* Tablet */ }
@media (max-width: 768px)  { /* Mobile */ }
@media (max-width: 380px)  { /* Mobile pequeño */ }
```

---

## Cómo agregar un nuevo componente

1. Si se usará en **varias páginas** → agregar en `global.css` al final
2. Si es **exclusivo de una página** → agregar en el css de esa página

---

## Convenciones de nomenclatura

- Clases en kebab-case: `.work-card`, `.nav-item`
- Modificadores con guion: `.btn-navy`, `.badge-green`
- Estados con sufijo: `.active`, `.visible`, `.open`
- Prefijos por módulo: `.repo-*`, `.ml-*`, `.hdr-*`
