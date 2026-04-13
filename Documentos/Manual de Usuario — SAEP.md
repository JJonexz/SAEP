**Sistema de Administración Escolar con PHP**  
**Última actualización:** 31 de marzo de 2026  
**Versión:** 1.0

---

## Tabla de contenidos

1. [Introducción](#1-introducción)
2. [Requisitos para usar SAEP](#2-requisitos-para-usar-saep)
3. [Primeros pasos: registro y acceso](#3-primeros-pasos-registro-y-acceso)
4. [Interfaz general del sistema](#4-interfaz-general-del-sistema)
5. [Módulo: Inicio](#5-módulo-inicio)
6. [Módulo: Repositorios](#6-módulo-repositorios)
7. [Módulo: Trabajos y entregas (docentes y directivos)](#7-módulo-trabajos-y-entregas-docentes-y-directivos)
8. [Módulo: Mis trabajos (alumnos)](#8-módulo-mis-trabajos-alumnos)
9. [Módulo: Calificaciones (docentes y directivos)](#9-módulo-calificaciones-docentes-y-directivos)
10. [Módulo: Mis calificaciones (alumnos)](#10-módulo-mis-calificaciones-alumnos)
11. [Módulo: Usuarios (administración)](#11-módulo-usuarios-administración)
12. [Módulo: Cursos y materias (administración)](#12-módulo-cursos-y-materias-administración)
13. [Módulo: Aulas (administración)](#13-módulo-aulas-administración)
14. [Qué puede hacer cada rol](#14-qué-puede-hacer-cada-rol)
15. [Preguntas frecuentes](#15-preguntas-frecuentes)
16. [Solución de problemas comunes](#16-solución-de-problemas-comunes)

---

## 1. Introducción

SAEP (Sistema de Administración Escolar con PHP) es una plataforma web diseñada para facilitar la gestión académica y administrativa de instituciones educativas de nivel secundario. Desde SAEP se puede gestionar usuarios, cursos, materias, aulas, calificaciones, trabajos prácticos y repositorios de código, todo desde el navegador, sin necesidad de instalar ningún programa adicional.

Este manual está dirigido a todos los perfiles de usuario del sistema: alumnos, docentes, preceptores, directivos y administradores. Cada sección indica claramente a qué roles aplica, de modo que cada usuario pueda ir directamente a la información que le corresponde.

---

## 2. Requisitos para usar SAEP

Para acceder a SAEP solo se necesita:

- Un dispositivo con conexión a Internet (computadora, notebook o tablet).
- Un navegador web actualizado. Se recomiendan: Google Chrome, Mozilla Firefox, Microsoft Edge o Safari en sus versiones más recientes.
- Una cuenta activa en **GitHub** (plataforma gratuita disponible en [github.com](https://github.com)).
- Haber sido habilitado por la institución dentro del sistema.

No es necesario instalar ningún software adicional. SAEP funciona completamente desde el navegador.

---

## 3. Primeros pasos: registro y acceso

### 3.1 Crear una cuenta en GitHub

Si todavía no tenés cuenta en GitHub, seguí estos pasos:

1. Ingresá a [https://github.com](https://github.com).
2. Hacé clic en **Sign up**.
3. Completá el formulario con un correo electrónico, una contraseña y un nombre de usuario.
4. Verificá el correo electrónico siguiendo el enlace que GitHub te envía.

Una vez creada la cuenta de GitHub, ya podés acceder a SAEP.

### 3.2 Primer ingreso al sistema

1. Ingresá a la dirección web de SAEP proporcionada por tu institución (por ejemplo: `https://saep.miescuela.edu.ar`).
2. En la pantalla de inicio vas a ver el botón **"Iniciar sesión con GitHub"**. Hacé clic sobre él.
3. El navegador te redirige a GitHub, donde se te pedirá que autorices a SAEP a acceder a cierta información de tu cuenta. Revisá los permisos solicitados y hacé clic en **"Authorize"** (Autorizar).
4. GitHub te devuelve al sistema automáticamente.

> **Importante:** Al autorizar el acceso, SAEP obtiene tu nombre de usuario de GitHub, tu foto de perfil y tu correo electrónico verificado. No accede a tu contraseña en ningún momento.

### 3.3 Completar el perfil

Si es la primera vez que ingresás, el sistema te lleva automáticamente a la pantalla de **Completar perfil**. Allí debés ingresar:

- **Nombre** (obligatorio)
- **Apellido** (obligatorio)
- **DNI** (obligatorio, entre 7 y 8 dígitos numéricos)
- **Teléfono** (opcional)

Una vez completados los datos, hacé clic en **"Guardar perfil"**. El sistema envía tu solicitud de acceso a los administradores de la institución.

### 3.4 Esperar la aprobación

Luego de completar el perfil, el sistema te muestra la pantalla de **"Cuenta pendiente de aprobación"**. En este estado no podés acceder a ninguna funcionalidad hasta que un administrador, director o subdirector apruebe tu cuenta y te asigne un rol.

Una vez aprobada la cuenta, la próxima vez que ingreses al sistema con GitHub serás redirigido directamente al panel principal.

### 3.5 Ingresos posteriores

Para ingresos posteriores al sistema, el proceso es mucho más simple:

1. Ingresá a la dirección de SAEP.
2. Hacé clic en **"Iniciar sesión con GitHub"**.
3. Si ya autorizaste la aplicación anteriormente, GitHub te redirige al sistema de forma inmediata sin pedirte que vuelvas a autorizar.

### 3.6 Cerrar sesión

Para cerrar sesión, hacé clic en el botón **"Salir"** que está en el extremo superior derecho del encabezado. Es importante cerrar sesión siempre que uses el sistema desde una computadora compartida o pública.

---

## 4. Interfaz general del sistema

Una vez dentro del sistema, la pantalla principal se divide en tres áreas:

```
┌──────────────────────────────────────────────────────┐
│                    ENCABEZADO                        │
├────────────────┬─────────────────────────────────────┤
│                │                                     │
│   BARRA DE     │         ÁREA DE CONTENIDO           │
│  NAVEGACIÓN    │                                     │
│   (sidebar)    │                                     │
│                │                                     │
└────────────────┴─────────────────────────────────────┘
```

### 4.1 Encabezado

En la parte superior de la pantalla se encuentra el encabezado, que contiene:

- **Logo e ícono de SAEP** (a la izquierda)
- **Nombre de usuario y avatar** de GitHub (a la derecha)
- **Etiqueta de rol** con el rol asignado en la institución (Admin, Director, Profesor, Alumno, etc.), con un color diferente según el rol:
  - Violeta → Administrador
  - Azul → Director / Subdirector
  - Verde → Profesor
  - Ámbar → Preceptor
  - Gris → Alumno
- **Botón "Salir"** para cerrar sesión

### 4.2 Barra de navegación (sidebar)

A la izquierda de la pantalla se encuentra la barra de navegación lateral. Contiene los accesos a los distintos módulos del sistema. Los módulos visibles dependen del rol del usuario: un alumno ve menos opciones que un administrador.

Para navegar entre secciones, simplemente hacé clic en el nombre del módulo en la barra lateral. El módulo activo se resalta con una línea azul a la izquierda y el texto en color oscuro.

### 4.3 Área de contenido

El área principal, a la derecha de la barra de navegación, muestra el contenido del módulo seleccionado. Incluye un encabezado de sección con el título y, cuando corresponde, un botón de acción principal (como "Nuevo trabajo" o "+ Crear usuario"). Debajo se despliega la información y las herramientas del módulo.

### 4.4 Ventanas modales

Muchas acciones del sistema abren una **ventana modal**: un cuadro que aparece sobre el contenido principal y contiene un formulario o una confirmación. Para cerrar una ventana modal sin realizar ninguna acción, podés hacer clic en el botón **"Cancelar"** o hacer clic fuera del cuadro (sobre el fondo oscuro).

---

## 5. Módulo: Inicio

**Disponible para:** todos los roles

Al ingresar al sistema, el primer módulo que se muestra es **Inicio**. Esta pantalla ofrece un resumen general del estado del sistema, adaptado al rol del usuario.

Dependiendo del rol, en Inicio se pueden ver:

- **Totales rápidos** (cards con estadísticas): cantidad de usuarios, cursos, aulas, trabajos o calificaciones registradas.
- **Accesos directos** a los módulos más utilizados.
- **Información de bienvenida** con el nombre del usuario y su rol.

Esta sección es solo de lectura. Para realizar acciones, hay que ingresar al módulo correspondiente desde la barra lateral.

---

## 6. Módulo: Repositorios

**Disponible para:** todos los roles (requiere cuenta de GitHub con permisos de repositorio)

El módulo de **Repositorios** permite gestionar los repositorios de código de GitHub directamente desde SAEP, sin necesidad de entrar a la página de GitHub. Es especialmente útil en contextos educativos donde los alumnos trabajan con código.

### 6.1 Ver la lista de repositorios

Al ingresar al módulo, SAEP consulta la API de GitHub y muestra la lista de repositorios del usuario ordenados por fecha de última actividad. Para cada repositorio se muestra:

- **Nombre** del repositorio
- **Descripción** (si tiene)
- **Lenguaje principal** detectado por GitHub
- **Visibilidad**: público o privado
- **Fecha de última actualización**

Hacé clic sobre el nombre de un repositorio para ver su contenido.

### 6.2 Navegar por los archivos de un repositorio

Al seleccionar un repositorio, aparece el explorador de archivos con la lista de carpetas y archivos. Las carpetas aparecen primero, ordenadas alfabéticamente, seguidas de los archivos.

- Para **entrar en una carpeta**, hacé clic sobre su nombre.
- Para **volver al directorio anterior**, usá el botón con la flecha "←" o navegá por las migas de pan (breadcrumbs) en la parte superior del explorador.
- Para **abrir y editar un archivo**, hacé clic sobre su nombre.

### 6.3 Editar un archivo

Al hacer clic sobre un archivo, SAEP descarga su contenido desde GitHub y lo muestra en un **editor de texto** integrado. Podés modificar el contenido directamente en el editor.

Para guardar los cambios:

1. Editá el contenido del archivo en el editor.
2. Hacé clic en el botón **"Guardar"** en la barra superior del editor.
3. Si el guardado fue exitoso, el botón muestra brevemente **"Guardado ✓"** y los cambios quedan confirmados en GitHub.

> **Atención:** Los cambios guardados se aplican directamente sobre el repositorio en GitHub. No existe un botón de "deshacer" dentro de SAEP. Si borrás contenido por error, podés recuperarlo desde el historial de commits en GitHub.

Para volver a la lista de archivos sin guardar cambios, hacé clic en **"← Volver"**.

### 6.4 Crear un archivo nuevo

1. Dentro de un repositorio, hacé clic en el botón **"+ Archivo"**.
2. En la ventana que aparece, ingresá el **nombre del archivo** (por ejemplo: `README.md` o `ejercicio.py`).
3. Hacé clic en **"Crear"**.
4. El archivo se crea vacío en el repositorio y se abre automáticamente en el editor para que puedas agregar contenido.

### 6.5 Eliminar un archivo

1. Navegá hasta el archivo que querés eliminar.
2. Hacé clic en el ícono o botón de eliminar junto al nombre del archivo.
3. Confirmá la acción en la ventana de confirmación.

> **Atención:** La eliminación de archivos es **irreversible desde SAEP**. El archivo puede recuperarse desde el historial de commits de GitHub si es necesario.

### 6.6 Crear un repositorio nuevo

1. Hacé clic en el botón **"+ Nuevo repositorio"** en la parte superior del módulo.
2. Completá el formulario:
   - **Nombre:** nombre del repositorio (sin espacios; usá guiones en su lugar)
   - **Descripción:** descripción opcional del repositorio
   - **Visibilidad:** elegí entre **Público** (visible para cualquier persona en GitHub) o **Privado** (solo visible para vos y quienes invites)
3. Hacé clic en **"Crear"**.

El repositorio se crea automáticamente en tu cuenta de GitHub con un archivo inicial (`README.md` vacío) y aparece en la lista del módulo.

### 6.7 Eliminar un repositorio

1. Ingresá al repositorio que querés eliminar.
2. Hacé clic en el botón **"Eliminar"** (en rojo).
3. Se abre una ventana de confirmación donde debés **escribir el nombre exacto del repositorio** para confirmar la eliminación.
4. Hacé clic en **"Eliminar"**.

> **Atención:** La eliminación de un repositorio es **irreversible** y elimina todos los archivos, commits e historial del repositorio en GitHub. Asegurate de que realmente querés eliminarlo antes de confirmar.

---

## 7. Módulo: Trabajos y entregas (docentes y directivos)

**Disponible para:** Administrador, Director, Subdirector, Profesor

Este módulo permite crear trabajos prácticos, asignarlos a cursos y materias, ver las entregas de los alumnos y calificarlas.

### 7.1 Ver la lista de trabajos

Al ingresar al módulo, se muestra la lista de trabajos prácticos. Cada trabajo muestra:

- **Título** del trabajo
- **Curso y materia** a los que está asignado
- **Fecha de entrega** establecida
- **Estado** (activo)
- **Cantidad de entregas** recibidas
- Botones para **ver entregas** y **eliminar** el trabajo

Los profesores solo ven los trabajos de las materias que tienen asignadas. Los administradores y directivos ven todos los trabajos.

### 7.2 Crear un trabajo práctico

1. Hacé clic en el botón **"+ Nuevo trabajo"**.
2. En la ventana modal, completá:
   - **Título** del trabajo (obligatorio)
   - **Descripción** con las consignas o instrucciones para los alumnos (opcional)
   - **Curso** al que se asigna (seleccioná de la lista)
   - **Materia** dentro del curso seleccionado
   - **Fecha de entrega** (opcional)
3. Hacé clic en **"Crear"**.

Al crear el trabajo, el sistema genera automáticamente una entrada de entrega pendiente para cada alumno inscripto en el curso seleccionado. Los alumnos pueden ver el trabajo inmediatamente en su módulo **"Mis trabajos"**.

### 7.3 Ver las entregas de un trabajo

1. En la lista de trabajos, hacé clic en **"Ver entregas"** sobre el trabajo que querés revisar.
2. Se abre una ventana con la lista de todos los alumnos asignados al trabajo.
3. Para cada alumno se muestra:
   - **Nombre y apellido**
   - **Estado de entrega:** entregado o pendiente
   - **Fecha de entrega** (si entregó)
   - **Contenido de la entrega** (texto o código enviado por el alumno)
   - **Nota promedio** (si ya fue calificado)
   - Botón **"Calificar"**

### 7.4 Calificar una entrega

1. En la lista de entregas del trabajo, hacé clic en **"Calificar"** junto al alumno que querés evaluar.
2. En la ventana que se abre:
   - **Nota:** ingresá un valor entre 1 y 10 (podés dejarlo en blanco si solo querés dar una devolución sin nota)
   - **Devolución:** escribí los comentarios o correcciones para el alumno
3. Hacé clic en **"Guardar calificación"**.

Si varios docentes califican el mismo trabajo, el sistema calcula automáticamente la **nota promedio** entre todos los docentes evaluadores. El estado de la entrega se actualiza automáticamente:
- Nota promedio ≥ 6 → **Aprobado**
- Nota promedio < 6 → **Desaprobado**
- Sin nota → **Sin calificar**

### 7.5 Eliminar un trabajo

1. En la lista de trabajos, hacé clic en **"Eliminar"** sobre el trabajo que querés borrar.
2. Confirmá la acción.

> **Atención:** Al eliminar un trabajo se eliminan también todas las entregas de los alumnos asociadas a ese trabajo. Esta acción es irreversible.

---

## 8. Módulo: Mis trabajos (alumnos)

**Disponible para:** Alumno

Este módulo permite al alumno ver los trabajos prácticos asignados a su curso, consultar sus detalles y entregar las resoluciones.

### 8.1 Ver la lista de trabajos

Al ingresar al módulo, se muestra la lista de trabajos prácticos del curso al que pertenece el alumno. Para cada trabajo se muestra:

- **Título** y **descripción** del trabajo
- **Materia** correspondiente
- **Fecha de entrega**
- **Estado de la entrega propia:** pendiente o entregado
- **Nota y devolución** (si el docente ya calificó)

### 8.2 Entregar un trabajo

Si el trabajo todavía no fue entregado, aparece un área de texto debajo de los datos del trabajo:

1. Escribí o pegá tu resolución en el campo de texto (podés ingresar texto, código, links o cualquier contenido relevante).
2. Hacé clic en **"Entregar"**.

El sistema registra la entrega con la fecha y hora exacta del momento en que hacés clic en el botón. Una vez entregado, el trabajo queda marcado como entregado y no puede ser modificado desde el sistema.

> **Consejo:** Verificá que el contenido sea correcto antes de hacer clic en "Entregar". Una vez enviado, no podrás editar la entrega desde SAEP.

### 8.3 Ver la calificación de un trabajo entregado

Una vez que el docente califica la entrega, la nota y la devolución aparecen visibles en la tarjeta del trabajo dentro del módulo **"Mis trabajos"**. Si varios docentes evaluaron el trabajo, se muestra la nota promedio final.

---

## 9. Módulo: Calificaciones (docentes y directivos)

**Disponible para:** Administrador, Director, Subdirector, Profesor

Este módulo permite registrar, consultar y gestionar las calificaciones de los alumnos por materia y cuatrimestre.

### 9.1 Pestañas del módulo

El módulo de calificaciones tiene dos pestañas:

- **Registrar:** formulario para cargar una nueva calificación
- **Ver todas:** tabla con todas las calificaciones registradas (filtradas según el rol)

### 9.2 Registrar una calificación

1. Asegurate de estar en la pestaña **"Registrar"**.
2. Completá el formulario:
   - **Alumno:** seleccioná el alumno de la lista desplegable
   - **Curso:** seleccioná el curso correspondiente
   - **Materia:** seleccioná la materia dentro del curso
   - **Cuatrimestre:** seleccioná 1° o 2°
   - **Nota:** ingresá un valor numérico entre 1 y 10 (podés dejarlo vacío si todavía no hay nota)
   - **Concepto:** ingresá una calificación cualitativa (por ejemplo: "Muy bueno", "Regular")
   - **Asistencia:** ingresá el porcentaje de asistencia del alumno (entre 0 y 100)
   - **Estado:** seleccioná "Aprobado", "Desaprobado" o "Pendiente"
3. Hacé clic en **"Guardar calificación"**.

> **Importante:** El sistema no permite registrar dos calificaciones para el mismo alumno, la misma materia y el mismo cuatrimestre. Si ya existe una calificación para esa combinación, debés editarla desde la pestaña "Ver todas".

### 9.3 Ver y administrar calificaciones registradas

En la pestaña **"Ver todas"** se muestra una tabla con las calificaciones registradas. Las columnas son:

| Columna | Descripción |
|---|---|
| Alumno | Apellido y nombre del alumno |
| Curso | Año y división del curso |
| Materia | Nombre de la materia |
| Cuatrimestre | 1° o 2° cuatrimestre |
| Nota | Valor numérico (coloreado: verde si aprobó, rojo si no) |
| Concepto | Calificación cualitativa |
| Asistencia | Porcentaje de asistencia |
| Estado | Badge de estado: Aprobado / Desaprobado / Pendiente |
| Acciones | Botón para eliminar la calificación |

Los **profesores** solo ven las calificaciones de sus propias materias. Los **administradores y directivos** ven todas las calificaciones del sistema.

### 9.4 Eliminar una calificación

Para eliminar una calificación, hacé clic en el botón **"✕"** al final de la fila correspondiente. Se solicita confirmación antes de proceder. Una vez eliminada, la calificación puede volver a registrarse desde el formulario de la pestaña "Registrar".

---

## 10. Módulo: Mis calificaciones (alumnos)

**Disponible para:** Alumno

Este módulo muestra al alumno todas sus calificaciones registradas en el sistema, organizadas por materia y cuatrimestre.

Para cada calificación se muestra:

- **Materia** y **curso**
- **Cuatrimestre**
- **Nota** (resaltada en verde si está aprobada, en rojo si está desaprobada)
- **Concepto** cualitativo
- **Asistencia** en porcentaje
- **Estado** de aprobación

Este módulo es de **solo lectura**. Los alumnos no pueden modificar sus propias calificaciones. Si detectás un error en alguna calificación, debés comunicarlo directamente al docente responsable o a las autoridades de la institución.

---

## 11. Módulo: Usuarios (administración)

**Disponible para:** Administrador, Director, Subdirector

Este módulo permite gestionar las cuentas de todos los usuarios del sistema: aprobar solicitudes de acceso, asignar roles, editar datos personales, crear usuarios manuales y eliminar cuentas.

### 11.1 Ver la lista de usuarios

Al ingresar al módulo se muestra una tabla con todos los usuarios registrados en el sistema. Para cada usuario se ve:

- **Avatar** de GitHub
- **Nombre y apellido**
- **Usuario de GitHub** (username)
- **DNI**
- **Email**
- **Rol** asignado (con su etiqueta de color)
- **Estado** de la cuenta: Aprobado / Pendiente / Rechazado
- **Acciones disponibles** según el estado y rol del usuario

### 11.2 Aprobar o rechazar una solicitud de acceso

Cuando un usuario completa su perfil por primera vez, su cuenta queda en estado **"Pendiente de aprobación"**. Para gestionarla:

1. Buscá al usuario en la lista (los usuarios pendientes aparecen con botones de Aprobar y Rechazar).
2. Revisá los datos del usuario (nombre, DNI).
3. Hacé clic en **"Aprobar"** para habilitar el acceso o en **"Rechazar"** para denegarlo.

Al aprobar una cuenta, el sistema también debe asignarle un rol (ver sección siguiente).

### 11.3 Cambiar el rol de un usuario

Para modificar el rol de un usuario:

1. Ubicá al usuario en la lista.
2. Usá el menú desplegable de roles en la columna correspondiente para seleccionar el nuevo rol.
3. Confirmá el cambio.

**Roles disponibles:**

| Rol | Descripción |
|---|---|
| `admin` | Acceso total al sistema. Solo puede asignarlo otro administrador. |
| `director` | Gestión institucional completa, excepto asignar rol admin y eliminar usuarios. |
| `subdirector` | Igual que director. |
| `profesor` | Gestión de calificaciones y trabajos de sus materias. |
| `preceptor` | Acceso de lectura a cursos y aulas. |
| `alumno` | Acceso a sus propias calificaciones y trabajos. |

### 11.4 Crear un usuario manualmente

En casos donde un usuario no puede o no debe tener cuenta de GitHub (por ejemplo, alumnos menores o situaciones especiales), es posible crear una cuenta manual:

1. Hacé clic en **"+ Crear usuario"**.
2. Completá el formulario:
   - **Nombre** (obligatorio)
   - **Apellido** (obligatorio)
   - **DNI** (obligatorio)
   - **Email** (opcional)
   - **Teléfono** (opcional)
   - **Rol** que se le asignará
   - **Nombre de usuario** interno (opcional; si se deja vacío, el sistema genera uno automáticamente)
3. Hacé clic en **"Crear usuario"**.

Los usuarios manuales quedan automáticamente con estado **"Aprobado"** y pueden usar el sistema inmediatamente. No pueden iniciar sesión con GitHub ni usar el módulo de repositorios.

### 11.5 Editar los datos de un usuario

Para editar el nombre, apellido, DNI, email o teléfono de un usuario existente:

1. Hacé clic sobre el usuario en la lista para abrir sus datos.
2. Modificá los campos necesarios.
3. Guardá los cambios.

### 11.6 Eliminar un usuario

**Solo los administradores** pueden eliminar usuarios permanentemente.

1. En la fila del usuario que querés eliminar, hacé clic en **"Eliminar"**.
2. Confirmá la acción.

> **Importante:** No es posible eliminar la propia cuenta desde el panel de usuarios. Si necesitás desactivar tu cuenta de administrador, debés pedirle a otro administrador que lo haga.

Al eliminar un usuario, su cuenta desaparece del sistema pero los datos académicos (calificaciones, entregas) asociados a su ID pueden quedar registrados como referencias huérfanas. Se recomienda eliminar usuarios solo cuando sea estrictamente necesario.

---

## 12. Módulo: Cursos y materias (administración)

**Disponible para:** Administrador, Director, Subdirector

Este módulo permite crear y gestionar los cursos de la institución, asignarles materias, inscribir alumnos y asignar profesores.

### 12.1 Ver la lista de cursos

Al ingresar al módulo se muestran tarjetas con los cursos existentes. Para cada curso se ve:

- **Nombre** del curso
- **Año, división y turno**
- **Cantidad de materias, alumnos y profesores** asignados
- Botones para **"Gestionar"** y **"Eliminar"**

### 12.2 Crear un curso nuevo

1. Hacé clic en **"+ Nuevo curso"**.
2. Completá el formulario:
   - **Nombre:** nombre descriptivo del curso (por ejemplo: "3° Técnico Informática")
   - **Año:** número de año (1 a 7)
   - **División:** letra o número de división (por ejemplo: "A", "B", "1")
   - **Turno:** seleccioná Mañana, Tarde o Noche
3. Hacé clic en **"Crear"**.

El curso se crea vacío, sin alumnos, materias ni profesores asignados. Estos se pueden agregar inmediatamente desde el panel de gestión.

### 12.3 Gestionar un curso

Al hacer clic en **"Gestionar"** sobre un curso, se abre una ventana con tres pestañas:

#### Pestaña: Alumnos

Muestra la lista de alumnos inscriptos en el curso. Desde aquí podés:

- **Agregar un alumno:** seleccioná el alumno del menú desplegable y hacé clic en "Agregar". Solo aparecen en la lista los usuarios con rol `alumno`.
- **Quitar un alumno:** hacé clic en "Quitar" en la fila del alumno correspondiente.

#### Pestaña: Materias

Muestra las materias asignadas al curso con su docente responsable. Desde aquí podés:

- **Agregar una materia:** ingresá el nombre de la materia, seleccioná el profesor responsable del menú desplegable y hacé clic en "Agregar".
- **Quitar una materia:** hacé clic en "Quitar" en la fila de la materia correspondiente.

> **Importante:** Al quitar una materia de un curso, las calificaciones y trabajos vinculados a esa materia **no se eliminan automáticamente**, pero quedan sin una materia de referencia activa. Se recomienda revisar los datos académicos antes de eliminar una materia.

#### Pestaña: Profesores

Muestra los profesores asignados al curso (como referencia general, independientemente de las materias). Desde aquí podés:

- **Agregar un profesor al curso:** seleccioná el usuario del menú y hacé clic en "Agregar".
- **Quitar un profesor del curso:** hacé clic en "Quitar" en la fila correspondiente.

### 12.4 Eliminar un curso

1. En la tarjeta del curso, hacé clic en **"Eliminar"**.
2. Confirmá la acción.

> **Atención:** Al eliminar un curso se elimina toda su configuración (materias, inscripciones de alumnos y profesores). Las calificaciones y trabajos vinculados al curso no se eliminan automáticamente pero quedan sin referencia activa.

---

## 13. Módulo: Aulas (administración)

**Disponible para:** Administrador, Director, Subdirector

Este módulo permite registrar y gestionar las aulas físicas de la institución y asignarlas a cursos y preceptores.

### 13.1 Ver la lista de aulas

Al ingresar al módulo se muestra una tabla con todas las aulas registradas. Para cada aula se ve:

- **Nombre** del aula
- **Capacidad** (número de alumnos que puede contener)
- **Ubicación** dentro del edificio
- **Curso asignado** (si corresponde)
- **Preceptor asignado** (si corresponde)
- Botón para **eliminar** el aula

### 13.2 Crear un aula nueva

1. Hacé clic en **"+ Nueva aula"**.
2. Completá el formulario:
   - **Nombre:** identificación del aula (por ejemplo: "Aula 101", "Laboratorio de Informática")
   - **Capacidad:** número máximo de alumnos
   - **Ubicación:** descripción del lugar físico (por ejemplo: "Planta baja, ala norte")
3. Hacé clic en **"Crear"**.

El aula se crea sin curso ni preceptor asignado.

### 13.3 Asignar un curso o preceptor a un aula

Para asignar un curso o preceptor a un aula existente, hacé clic sobre el nombre del aula (o en el ícono de edición si está disponible) y actualizá los campos `curso_id` y `preceptor_id` desde el formulario de edición.

### 13.4 Eliminar un aula

1. En la fila del aula, hacé clic en **"Eliminar"**.
2. Confirmá la acción.

Al eliminar un aula no se eliminan los cursos o preceptores que tenían asignada esa aula; simplemente se desvinculan.

---

## 14. Qué puede hacer cada rol

La siguiente tabla resume las acciones disponibles según el rol del usuario:

| Acción | Admin | Director | Subdirector | Profesor | Preceptor | Alumno |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| Ver inicio (resumen) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Gestionar repositorios propios | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Ver todos los usuarios | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Aprobar / rechazar usuarios | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Cambiar rol de usuario | ✅ | ✅* | ✅* | ❌ | ❌ | ❌ |
| Asignar rol admin | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Crear usuario manual | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Eliminar usuario | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Crear / eliminar cursos | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Gestionar alumnos y materias del curso | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Ver cursos | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Crear / gestionar aulas | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Registrar calificaciones | ✅ | ✅ | ✅ | ✅** | ❌ | ❌ |
| Ver todas las calificaciones | ✅ | ✅ | ✅ | Solo sus materias | ❌ | ❌ |
| Ver calificaciones propias | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Crear trabajos prácticos | ✅ | ✅ | ✅ | ✅** | ❌ | ❌ |
| Calificar entregas | ✅ | ✅ | ✅ | ✅** | ❌ | ❌ |
| Ver todos los trabajos | ✅ | ✅ | ✅ | Solo sus materias | ❌ | ❌ |
| Entregar trabajos | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Ver propios trabajos y notas | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

*\* No puede asignar el rol `admin`.*  
*\*\* Solo en las materias asignadas a ese profesor.*

---

## 15. Preguntas frecuentes

**¿Qué hago si no puedo iniciar sesión con GitHub?**  
Verificá que tu cuenta de GitHub esté activa y que el correo electrónico esté verificado. Si ya iniciaste sesión antes y ahora no podés, intentá revocar el acceso de SAEP desde GitHub (Settings → Applications → Authorized OAuth Apps) y volvé a autorizar desde el sistema.

**¿Puedo cambiar mi nombre o DNI después de completar el perfil?**  
Sí, pero requiere la intervención de un administrador, director o subdirector. Contactá a las autoridades de tu institución para solicitar la corrección.

**¿Qué pasa si mi cuenta queda en estado "Pendiente"?**  
Debés esperar a que un administrador, director o subdirector la apruebe y le asigne un rol. Si pasaron varios días sin respuesta, contactá a la administración de la institución.

**¿Puedo usar SAEP desde el celular?**  
Sí, el sistema funciona desde navegadores móviles. Sin embargo, la interfaz está optimizada para pantallas de computadora, por lo que en pantallas pequeñas algunas secciones pueden verse menos cómodas. Se recomienda usar el sistema desde una computadora o notebook para una experiencia completa.

**¿Las calificaciones que aparecen en SAEP son las oficiales?**  
No necesariamente. SAEP es una herramienta de gestión interna. Las calificaciones oficiales son las que figuran en los registros formales de la institución (boletines, libretas, actas). Ante cualquier discrepancia, los registros oficiales prevalecen.

**¿Qué permisos le doy a SAEP cuando inicio sesión con GitHub?**  
Al autorizar SAEP, le otorgás permisos para leer tu perfil de GitHub (nombre, username, avatar), leer tu email verificado, y gestionar repositorios en tu nombre (`repo` y `delete_repo`). SAEP no accede a tu contraseña ni a ningún dato que no hayas autorizado explícitamente.

**¿Puedo tener más de una cuenta en SAEP?**  
No. Cada persona debe tener una única cuenta. Si detectás una duplicación, contactá al administrador del sistema.

**¿Puedo ver las calificaciones de mis compañeros?**  
No. El sistema está configurado para que cada alumno solo pueda ver sus propias calificaciones y entregas. Es imposible acceder a los datos de otro alumno desde la interfaz del sistema.

**¿Qué pasa si entrego un trabajo después de la fecha de vencimiento?**  
El sistema permite registrar la entrega igualmente, pero queda registrada la fecha y hora exacta. Es la institución o el docente quien evalúa si acepta o no entregas fuera de término, según su criterio pedagógico.

**¿Puedo editar una entrega de trabajo después de haberla enviado?**  
No. Una vez entregado un trabajo desde el módulo "Mis trabajos", no es posible modificar la entrega desde SAEP. Si necesitás hacer una corrección, contactá al docente responsable.

**¿Qué pasa si borro un repositorio por error?**  
La eliminación de repositorios desde SAEP es irreversible dentro del sistema. Sin embargo, si el repositorio no fue eliminado definitivamente de GitHub (esto ocurre solo si tenés configurada alguna protección o si usás el plan GitHub Enterprise), podés intentar recuperarlo directamente desde GitHub dentro de los 90 días posteriores a la eliminación. Contactá al soporte de GitHub para obtener asistencia.

---

## 16. Solución de problemas comunes

**El sistema no carga o muestra una pantalla en blanco**  
Intentá recargar la página con F5 o Ctrl+R. Si el problema persiste, verificá tu conexión a Internet y probá desde otro navegador. Si el problema continúa, contactá al administrador del sistema.

**Al hacer clic en "Iniciar sesión con GitHub" no pasa nada**  
Verificá que tu navegador no esté bloqueando ventanas emergentes (popups). SAEP puede necesitar abrir una ventana de GitHub para el proceso de autorización. Habilitá las ventanas emergentes para el dominio del sistema en la configuración de tu navegador.

**GitHub muestra un error de "redirect_uri mismatch"**  
Este es un error de configuración del sistema. Contactá al administrador del sistema para que revise la configuración de la OAuth App en GitHub.

**Completé mi perfil pero sigo sin poder acceder al sistema**  
Tu cuenta está en estado "Pendiente de aprobación". Debés esperar a que un administrador, director o subdirector la apruebe. Si la institución ya debería haberte habilitado el acceso, contactá a la administración.

**No veo el módulo de Repositorios**  
Verificá que tu cuenta de GitHub tenga los permisos de repositorio habilitados. Intentá revocar el acceso de SAEP desde GitHub y volver a autorizar iniciando sesión nuevamente.

**Al intentar guardar un archivo en el editor, aparece un error**  
Esto puede ocurrir si el token de GitHub de tu sesión expiró. Cerrá sesión en SAEP y volvé a iniciar sesión para renovar el token. Si el problema persiste, verificá que el repositorio todavía exista en tu cuenta de GitHub.

**No aparecen alumnos en el menú desplegable al cargar una calificación**  
Para que un alumno aparezca disponible al registrar calificaciones, debe estar inscripto en el curso correspondiente. Verificá desde el módulo "Cursos y materias" que el alumno esté asignado al curso correcto.

**Ingresé una calificación incorrecta y necesito corregirla**  
Las calificaciones no pueden editarse inline desde la tabla. Para corregir una calificación:
1. Eliminá la calificación incorrecta usando el botón "✕" en la tabla de calificaciones.
2. Registrá la calificación correcta nuevamente desde el formulario de la pestaña "Registrar".

**El sistema muestra mi rol incorrecto**  
El rol es asignado por el administrador de la institución. Si el rol que aparece no corresponde a tu función real, contactá al administrador para que realice la corrección.

---

*Para consultas que no estén resueltas en este manual, contactá al administrador del sistema o a las autoridades de tu institución.*
