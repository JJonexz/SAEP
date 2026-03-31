**Sistema de Administración Escolar con PHP**  
**Última actualización:** 31 de marzo de 2026  
**Versión:** 1.0

---

## Tabla de contenidos

1. [Introducción](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#1-introducci%C3%B3n)
2. [Responsable del tratamiento](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#2-responsable-del-tratamiento)
3. [Datos que recopilamos](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#3-datos-que-recopilamos)
4. [Finalidad del tratamiento](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#4-finalidad-del-tratamiento)
5. [Base legal del tratamiento](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#5-base-legal-del-tratamiento)
6. [Autenticación con GitHub](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#6-autenticaci%C3%B3n-con-github)
7. [Almacenamiento y seguridad](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#7-almacenamiento-y-seguridad)
8. [Acceso a los datos según rol](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#8-acceso-a-los-datos-seg%C3%BAn-rol)
9. [Datos de menores de edad](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#9-datos-de-menores-de-edad)
10. [Compartición de datos con terceros](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#10-compartici%C3%B3n-de-datos-con-terceros)
11. [Transferencias internacionales de datos](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#11-transferencias-internacionales-de-datos)
12. [Conservación de los datos](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#12-conservaci%C3%B3n-de-los-datos)
13. [Derechos de los titulares](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#13-derechos-de-los-titulares)
14. [Cookies y sesiones](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#14-cookies-y-sesiones)
15. [Cambios en esta política](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#15-cambios-en-esta-pol%C3%ADtica)
16. [Contacto](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#16-contacto)

---

## 1. Introducción

El presente documento describe la Política de Privacidad del **Sistema de Administración Escolar con PHP (SAEP)**, en adelante "el Sistema" o "SAEP". Esta política regula la forma en que el Sistema recopila, utiliza, almacena, protege y eventualmente elimina los datos personales de todos los usuarios que interactúan con él, incluyendo alumnos, docentes, preceptores, directivos y personal administrativo de la institución educativa.

El uso de SAEP implica la aceptación plena de los términos aquí descritos. Si el usuario no está de acuerdo con alguna parte de esta política, deberá abstenerse de utilizar el Sistema y comunicarlo a la institución responsable para que se adopten las medidas correspondientes.

Esta política ha sido redactada en cumplimiento de la **Ley Nacional N.º 25.326 de Protección de los Datos Personales de la República Argentina** (y sus decretos reglamentarios), así como de los principios generales de privacidad by design, minimización de datos y transparencia informativa.

---

## 2. Responsable del tratamiento

El responsable del tratamiento de los datos personales gestionados a través de SAEP es la **institución educativa** que despliega y opera el Sistema. Dicha institución actúa como titular de la base de datos ante la **Agencia de Acceso a la Información Pública (AAIP)** de Argentina, organismo de control competente en materia de protección de datos personales.

El desarrollador o proveedor del software SAEP no tiene acceso a los datos personales de los usuarios finales una vez que el sistema está desplegado en la infraestructura de la institución, por lo que no reviste el carácter de responsable ni de encargado del tratamiento en el sentido de la Ley 25.326.

La institución responsable deberá:

- Inscribir la base de datos ante el Registro Nacional de Bases de Datos de la AAIP cuando así lo exija la normativa vigente.
- Designar a un referente interno de privacidad accesible para los titulares de los datos.
- Asegurar que el entorno de despliegue de SAEP cumpla con los estándares mínimos de seguridad informática.
- Informar a los usuarios sobre la existencia y el contenido de esta política antes de la primera utilización del Sistema.

---

## 3. Datos que recopilamos

SAEP recopila únicamente los datos necesarios para cumplir con sus funciones de gestión escolar. A continuación se detalla cada categoría.

### 3.1 Datos de identificación personal

Recopilados durante el proceso de registro y completado de perfil:

|Dato|Descripción|Obligatorio|
|---|---|---|
|Nombre|Nombre de pila del usuario|Sí|
|Apellido|Apellido del usuario|Sí|
|DNI|Documento Nacional de Identidad (7 u 8 dígitos)|Sí|
|Correo electrónico|Email verificado asociado a la cuenta de GitHub|No (se obtiene automáticamente si está disponible)|
|Teléfono|Número de contacto|No|

### 3.2 Datos de identidad digital (GitHub)

Obtenidos automáticamente mediante el proceso de autenticación OAuth 2.0 con GitHub:

|Dato|Descripción|
|---|---|
|`github_id`|Identificador numérico único e inmutable de la cuenta de GitHub|
|`username`|Nombre de usuario público de GitHub|
|`avatar_url`|URL de la foto de perfil de GitHub|
|`access_token`|Token de acceso OAuth (se almacena en sesión PHP, no en disco)|

### 3.3 Datos académicos

Generados y gestionados dentro del sistema por el personal autorizado:

- **Calificaciones:** nota numérica (escala 1–10), concepto cualitativo, porcentaje de asistencia y estado (aprobado / desaprobado / pendiente) por materia y cuatrimestre.
- **Trabajos prácticos:** contenido de la entrega, fecha de entrega, notas y devoluciones de cada docente evaluador, nota promedio y estado de calificación.
- **Inscripciones:** cursos y materias a los que pertenece cada alumno.
- **Asignaciones:** materias a cargo de cada docente y aulas a cargo de cada preceptor.

### 3.4 Datos de uso y sesión

- Dirección IP del cliente (gestionada por el servidor web, no almacenada por SAEP directamente).
- Identificador de sesión PHP almacenado en cookie temporal del navegador.
- Marca de tiempo de operaciones críticas (creación de calificaciones, entrega de trabajos).

### 3.5 Datos que NO recopilamos

SAEP no recopila ni procesa, bajo ninguna circunstancia:

- Datos biométricos (huella dactilar, reconocimiento facial, voz).
- Datos de salud o condición médica.
- Datos de origen racial o étnico.
- Datos de afiliación política, sindical o religiosa.
- Datos de geolocalización en tiempo real.
- Historial de navegación externo al Sistema.
- Datos de tarjetas de crédito o información financiera.

---

## 4. Finalidad del tratamiento

Los datos personales recopilados por SAEP son tratados exclusivamente para los siguientes fines:

**a) Autenticación e identificación de usuarios** Verificar la identidad de cada persona que accede al Sistema y garantizar que solo los usuarios autorizados puedan ingresar.

**b) Gestión académica institucional** Registrar, consultar y actualizar la información necesaria para el funcionamiento administrativo de la institución: cursos, materias, inscripciones, asistencia y calificaciones.

**c) Comunicación interna** Facilitar la identificación de usuarios entre sí dentro del Sistema (nombre, apellido, avatar) para la asignación de tareas, calificaciones y trabajos.

**d) Gestión de repositorios de código** Permitir a los usuarios gestionar sus repositorios de GitHub directamente desde el Sistema, actuando en su nombre a través del token OAuth con los permisos explícitamente otorgados.

**e) Control de acceso basado en roles** Determinar qué funcionalidades del Sistema puede utilizar cada usuario según su rol institucional asignado.

**f) Auditoría y trazabilidad** Registrar qué usuario realizó una calificación o acción relevante dentro del sistema, con fines de control institucional y corrección de errores.

Los datos **no serán utilizados** para:

- Elaboración de perfiles psicológicos o conductuales.
- Publicidad o marketing de ningún tipo.
- Venta, cesión o intercambio comercial con terceros.
- Toma de decisiones automatizadas que produzcan efectos jurídicos sobre los titulares sin intervención humana.

---

## 5. Base legal del tratamiento

El tratamiento de datos personales en SAEP se sustenta en las siguientes bases legales conforme a la Ley 25.326:

**Consentimiento del titular (Art. 5)** Los usuarios manifiestan su consentimiento al iniciar sesión con GitHub y completar su perfil. El consentimiento es libre, expreso e informado mediante la lectura de esta política.

**Ejecución de una relación contractual o institucional** El tratamiento de datos académicos (calificaciones, inscripciones, asistencia) es necesario para el cumplimiento de la relación educativa que vincula al alumno con la institución.

**Obligación legal** Las instituciones educativas argentinas tienen obligaciones legales de registro académico, asistencia y calificaciones establecidas por la normativa educativa nacional y provincial.

**Interés legítimo de la institución** La gestión interna de usuarios, roles y permisos responde al interés legítimo de la institución en garantizar el correcto funcionamiento administrativo, siempre que no prevalezcan los intereses o derechos fundamentales de los titulares.

---

## 6. Autenticación con GitHub

SAEP utiliza **GitHub OAuth 2.0** como único mecanismo de autenticación. Este proceso implica:

### 6.1 Alcance de los permisos solicitados

Al iniciar sesión, el usuario es redirigido a GitHub y se le solicita autorización para los siguientes scopes:

|Scope|Propósito|
|---|---|
|`read:user`|Obtener nombre de usuario, ID y avatar del perfil público|
|`user:email`|Obtener el email primario verificado de la cuenta|
|`repo`|Crear, leer y modificar repositorios y archivos del usuario|
|`delete_repo`|Eliminar repositorios del usuario|

### 6.2 Qué información se almacena de GitHub

- El `github_id` (número entero) se almacena de forma permanente como identificador único del usuario en `users.json`.
- El `username` y `avatar_url` se actualizan en cada inicio de sesión para mantenerlos sincronizados con el perfil de GitHub.
- El `access_token` OAuth **no se escribe en disco**. Se almacena únicamente en la sesión PHP del servidor y se destruye al cerrar sesión o al expirar la sesión.

### 6.3 Revocación del acceso a GitHub

El usuario puede revocar en cualquier momento el acceso de SAEP a su cuenta de GitHub desde:  
`GitHub → Settings → Applications → Authorized OAuth Apps → SAEP → Revoke`

La revocación impide futuros inicios de sesión a través de GitHub, pero no elimina los datos académicos ya registrados en el Sistema. Para solicitar la eliminación de dichos datos, el usuario debe seguir el procedimiento indicado en la sección [13. Derechos de los titulares](https://claude.ai/chat/279cc381-485b-4d29-8100-1cec8a3d531e#13-derechos-de-los-titulares).

### 6.4 Política de privacidad de GitHub

El tratamiento de datos realizado por GitHub en el marco del proceso OAuth está regido por la [Política de Privacidad de GitHub](https://docs.github.com/en/site-policy/privacy-policies/github-general-privacy-statement), que es independiente de la presente política.

---

## 7. Almacenamiento y seguridad

### 7.1 Ubicación de los datos

Todos los datos personales gestionados por SAEP se almacenan en archivos JSON dentro del directorio `data/` en el servidor donde está desplegado el Sistema. La institución educativa es responsable de la seguridad física y lógica de dicho servidor.

### 7.2 Medidas de seguridad implementadas en el Sistema

- **Control de acceso por sesión:** Todas las rutas de la API verifican la existencia de una sesión válida antes de procesar cualquier solicitud.
- **Control de roles:** Cada endpoint valida que el rol del usuario autenticado tenga permiso para la operación solicitada.
- **Validación de datos de entrada:** Los datos recibidos son validados antes de ser procesados (formato de DNI, rango de notas, roles permitidos, estados válidos).
- **Protección CSRF en OAuth:** El flujo OAuth utiliza un parámetro `state` aleatorio para prevenir ataques de falsificación de solicitudes entre sitios.
- **No exposición de datos sensibles:** El campo `github_id` es excluido de las respuestas de la API que devuelven datos de usuarios a otros usuarios.
- **Token OAuth en memoria:** El `access_token` de GitHub nunca se escribe en disco ni en los archivos JSON.

### 7.3 Medidas de seguridad recomendadas para la institución

La institución responsable del despliegue de SAEP debe implementar, como mínimo:

- Acceso al servidor únicamente a través de conexiones cifradas (HTTPS con certificado TLS válido).
- Restricción del acceso al directorio `data/` desde el exterior mediante configuración del servidor web (directiva `deny from all` en Apache o bloque `location` en Nginx).
- Copias de seguridad periódicas de los archivos JSON en almacenamiento separado.
- Políticas de contraseña y autenticación robusta para el acceso administrativo al servidor.
- Actualizaciones regulares del entorno PHP y del sistema operativo del servidor.
- Registro de accesos al servidor (logs de Apache/Nginx) con retención mínima de 90 días.

### 7.4 Limitaciones de seguridad inherentes

SAEP utiliza archivos JSON planos como capa de persistencia. Esta arquitectura, si bien simplifica el despliegue, presenta limitaciones frente a motores de base de datos relacionales con cifrado en reposo. La institución debe valorar si este nivel de seguridad es adecuado para el volumen y sensibilidad de los datos que gestiona, y considerar una migración a motor de base de datos si el contexto lo requiere.

---

## 8. Acceso a los datos según rol

El acceso a los datos personales dentro del Sistema está estrictamente segmentado por rol:

|Dato|admin|director / subdirector|profesor|preceptor|alumno|
|---|:-:|:-:|:-:|:-:|:-:|
|Lista completa de usuarios|✅|✅|❌|❌|❌|
|Datos personales de otros usuarios|✅|✅|❌|❌|❌|
|Calificaciones de todos los alumnos|✅|✅|Solo sus materias|❌|❌|
|Calificaciones propias|✅|✅|✅|✅|✅|
|Entregas de trabajos de todos|✅|✅|Solo sus materias|❌|❌|
|Entrega propia|✅|✅|✅|✅|✅|
|Datos de cursos y aulas|✅|✅|Solo lectura|Solo lectura|Solo lectura|

Ningún usuario puede acceder a los datos de otro usuario que esté fuera del alcance definido por su rol, independientemente de la URL o el método HTTP utilizado.

---

## 9. Datos de menores de edad

Los alumnos de nivel secundario pueden ser menores de 18 años. En ese caso, aplican las siguientes consideraciones:

**Consentimiento:** El consentimiento para el tratamiento de datos de menores de edad debe ser otorgado por sus padres, madres o tutores legales. La institución educativa es responsable de obtener y documentar dicho consentimiento antes de registrar al alumno en el Sistema.

**Cuenta de GitHub:** Para poder utilizar SAEP, el alumno debe tener una cuenta de GitHub. Conforme a los [Términos de Servicio de GitHub](https://docs.github.com/en/site-policy/github-terms/github-terms-of-service), los menores de 13 años no pueden crear cuentas en GitHub. La institución deberá evaluar si habilitar la creación manual de usuarios (opción disponible en SAEP para el rol admin) como alternativa para alumnos que no puedan o no deban tener cuenta en GitHub.

**Datos académicos:** Las calificaciones y trabajos de alumnos menores son datos especialmente sensibles. Solo el personal docente y directivo con acceso al Sistema y con relación directa al alumno puede visualizarlos.

**Derecho de acceso de los tutores:** Los padres, madres o tutores legales tienen derecho a solicitar a la institución la información académica de los menores a su cargo registrada en el Sistema.

---

## 10. Compartición de datos con terceros

SAEP **no comparte datos personales con terceros** con fines comerciales, publicitarios ni de ninguna otra naturaleza.

Los únicos casos en que datos del Sistema pueden llegar a terceros son:

**GitHub Inc.** (Estados Unidos): Exclusivamente en el marco del proceso de autenticación OAuth y de las operaciones sobre repositorios realizadas por el propio usuario. En ningún caso SAEP envía a GitHub datos académicos (calificaciones, notas, asistencia).

**Autoridades competentes:** Si la institución recibe una orden judicial o requerimiento legal válido que obligue a la entrega de datos, deberá cumplirlo conforme a la normativa aplicable, notificando al titular cuando sea legalmente posible.

**Proveedores de infraestructura:** El proveedor de hosting o servidor donde esté desplegado SAEP puede tener acceso técnico a los archivos del Sistema. La institución debe asegurarse de contratar proveedores que ofrezcan garantías de confidencialidad y que cumplan con la normativa de protección de datos.

---

## 11. Transferencias internacionales de datos

La autenticación mediante GitHub implica una transferencia de datos a GitHub Inc., empresa con sede en los Estados Unidos. Esta transferencia está limitada a los datos de perfil de GitHub (ID, username, avatar, email) y se realiza bajo los mecanismos de garantía previstos en los Términos de Servicio y la Política de Privacidad de GitHub.

El resto de los datos del Sistema (datos académicos, calificaciones, trabajos) residen exclusivamente en el servidor donde la institución ha desplegado SAEP y no son transferidos internacionalmente a través del Sistema.

Si la institución opta por alojar el servidor en un proveedor de infraestructura en la nube con centros de datos fuera de Argentina, deberá asegurarse de que dicha transferencia cumpla con lo dispuesto en el Artículo 12 de la Ley 25.326, que prohíbe la transferencia de datos a países u organismos internacionales que no proporcionen niveles de protección adecuados, salvo las excepciones allí previstas.

---

## 12. Conservación de los datos

Los datos personales almacenados en SAEP se conservan durante los siguientes plazos:

|Tipo de dato|Plazo de conservación|
|---|---|
|Datos de perfil del usuario activo|Mientras el usuario tenga una cuenta activa en el Sistema|
|Datos de usuario eliminado manualmente por admin|Eliminados inmediatamente del archivo JSON|
|Calificaciones y asistencia|Según los plazos de conservación de registros académicos establecidos por la normativa educativa vigente (mínimo 10 años en Argentina para registros escolares oficiales)|
|Trabajos prácticos y entregas|A criterio de la institución; se recomienda mantenerlos al menos durante el ciclo lectivo en curso más un año adicional|
|Logs de sesión y acceso|Según la política de logs del servidor web de la institución|

Al finalizar el ciclo lectivo, la institución debe revisar qué datos pueden ser anonimizados o eliminados conforme al principio de minimización y al plazo de conservación aplicable.

---

## 13. Derechos de los titulares

Todo usuario cuyos datos personales sean tratados por SAEP tiene los siguientes derechos reconocidos por la Ley 25.326:

### Derecho de acceso

El titular puede solicitar información sobre qué datos suyos están almacenados en el Sistema, con qué finalidad y quiénes tienen acceso a ellos. En SAEP, los usuarios pueden consultar sus propios datos en el panel de perfil. Para datos que no estén disponibles en la interfaz, la solicitud debe dirigirse a la institución.

### Derecho de rectificación

El titular puede solicitar la corrección de datos inexactos, incompletos o desactualizados. Los usuarios pueden actualizar nombre, apellido, DNI y teléfono a través de la función de edición de perfil, previa validación por parte del personal autorizado.

### Derecho de supresión ("derecho al olvido")

El titular puede solicitar la eliminación de sus datos cuando ya no sean necesarios para la finalidad para la que fueron recopilados, cuando retire el consentimiento, o cuando el tratamiento sea ilícito. La eliminación de registros académicos puede estar sujeta a restricciones legales si los datos forman parte del legajo escolar oficial del alumno.

### Derecho de oposición

El titular puede oponerse al tratamiento de sus datos cuando este se base en el interés legítimo de la institución, salvo que existan motivos legítimos imperiosos que prevalezcan.

### Derecho a la portabilidad

El titular puede solicitar que sus datos personales le sean entregados en un formato estructurado y de uso común. La institución deberá proveer un extracto de los datos en formato JSON o CSV según corresponda.

### Cómo ejercer estos derechos

Las solicitudes deben presentarse ante la institución educativa responsable del Sistema, por escrito (físico o electrónico), indicando:

1. Nombre completo y DNI del titular.
2. Descripción clara del derecho que desea ejercer.
3. Datos de contacto para la respuesta.

La institución tiene un plazo de **5 días hábiles** para acusar recibo y de **30 días corridos** para dar respuesta sustantiva, conforme al Artículo 14 de la Ley 25.326.

Si el titular considera que su solicitud no fue atendida adecuadamente, puede presentar una denuncia ante la **Agencia de Acceso a la Información Pública (AAIP)**:  
Sitio web: [https://www.argentina.gob.ar/aaip](https://www.argentina.gob.ar/aaip)

---

## 14. Cookies y sesiones

SAEP utiliza **exclusivamente cookies de sesión** necesarias para el funcionamiento del Sistema. No utiliza cookies de rastreo, publicidad ni analítica de terceros.

|Cookie|Tipo|Descripción|Duración|
|---|---|---|---|
|`PHPSESSID`|Sesión (técnica)|Identifica la sesión del usuario en el servidor PHP|Se elimina al cerrar el navegador o al hacer logout|

La cookie de sesión no contiene datos personales directamente legibles; almacena únicamente el identificador de sesión, que en el servidor se asocia al `github_id` del usuario autenticado.

El usuario puede eliminar las cookies desde la configuración de su navegador, lo que resultará en el cierre automático de la sesión en SAEP.

---

## 15. Cambios en esta política

La institución responsable del despliegue de SAEP puede actualizar esta Política de Privacidad cuando sea necesario, por ejemplo ante cambios normativos, modificaciones en el Sistema o variaciones en las prácticas de tratamiento de datos.

Cuando se produzcan cambios sustanciales, la institución deberá:

- Actualizar la fecha de "Última actualización" indicada en el encabezado del documento.
- Notificar a los usuarios mediante un aviso visible en el Sistema o por correo electrónico con una antelación mínima de 15 días corridos antes de que los cambios entren en vigencia.
- Conservar las versiones anteriores de la política a disposición de quien las solicite.

El uso continuado del Sistema tras la entrada en vigencia de los cambios implica la aceptación de la política actualizada.

---

## 16. Contacto

Para cualquier consulta, solicitud de ejercicio de derechos o reporte de incidente de seguridad relacionado con el tratamiento de datos personales en SAEP, los usuarios deben dirigirse a la **institución educativa responsable** del despliegue del Sistema.

La institución debe designar y publicar los datos de contacto de su referente de privacidad o área administrativa responsable. A modo de referencia, el canal de contacto debería incluir:

- **Correo electrónico institucional** destinado a consultas de privacidad
- **Dirección postal** de la institución
- **Horario de atención** para consultas presenciales

---

_Esta política de privacidad ha sido redactada conforme a la Ley Nacional N.º 25.326 de Protección de los Datos Personales de la República Argentina y sus normas complementarias. Su contenido debe ser revisado por asesoramiento legal especializado antes de su implementación formal en una institución educativa._