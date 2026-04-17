// Gestión de Laboratorios EEST N°1 — app.js v4 (integrado con SAEP)
// Reemplaza el viejoapp.js: en vez de leer la variable `session` del login,
// hace un fetch a ../api/session.php (sesión real de GitHub/SAEP).

const HOY = new Date();
HOY.setHours(0,0,0,0);

let semanaOffset = 0;
let diaActual    = 0;
let filtroOrient  = 'all';
let filtroLab     = 'todos';
let modoUsuario   = 'prof';   // 'admin' | 'prof'
let editDocenteId = null;
let editLabId     = null;
let nextId        = 200;

const DIAS_SEMANA = ['LUN','MAR','MIÉ','JUE','VIE'];
const DIAS_LARGO  = ['Lunes','Martes','Miércoles','Jueves','Viernes'];

const MODULOS = [
  { id:0,  label:'1° Mañana',   inicio:'7:20',  fin:'8:20',  turno:'Mañana',    tipo:'clase',  icon:'🌅' },
  { id:1,  label:'2° Mañana',   inicio:'8:20',  fin:'9:20',  turno:'Mañana',    tipo:'clase',  icon:'🌅' },
  { id:2,  label:'Recreo M',    inicio:'9:20',  fin:'9:50',  turno:'Mañana',    tipo:'recreo', icon:'☕' },
  { id:3,  label:'3° Mañana',   inicio:'9:50',  fin:'10:50', turno:'Mañana',    tipo:'clase',  icon:'🌅' },
  { id:4,  label:'4° Mañana',   inicio:'10:50', fin:'11:50', turno:'Mañana',    tipo:'clase',  icon:'🌅' },
  { id:5,  label:'5° Mañana',   inicio:'11:50', fin:'12:50', turno:'Mañana',    tipo:'clase',  icon:'🌅' },
  { id:6,  label:'1° Tarde',    inicio:'13:00', fin:'14:00', turno:'Tarde',     tipo:'clase',  icon:'☀️' },
  { id:7,  label:'2° Tarde',    inicio:'14:00', fin:'15:00', turno:'Tarde',     tipo:'clase',  icon:'☀️' },
  { id:8,  label:'Recreo T',    inicio:'15:00', fin:'15:30', turno:'Tarde',     tipo:'recreo', icon:'🧃' },
  { id:9,  label:'3° Tarde',    inicio:'15:30', fin:'16:30', turno:'Tarde',     tipo:'clase',  icon:'☀️' },
  { id:10, label:'4° Tarde',    inicio:'16:30', fin:'17:30', turno:'Tarde',     tipo:'clase',  icon:'☀️' },
  { id:11, label:'1° Vespert.', inicio:'17:40', fin:'18:40', turno:'Vespertino',tipo:'clase',  icon:'🌆' },
  { id:12, label:'2° Vespert.', inicio:'18:40', fin:'19:40', turno:'Vespertino',tipo:'clase',  icon:'🌆' },
  { id:13, label:'Recreo V',    inicio:'19:40', fin:'20:00', turno:'Vespertino',tipo:'recreo', icon:'🌙' },
  { id:14, label:'3° Vespert.', inicio:'20:00', fin:'21:00', turno:'Vespertino',tipo:'clase',  icon:'🌆' },
  { id:15, label:'4° Vespert.', inicio:'21:00', fin:'22:00', turno:'Vespertino',tipo:'clase',  icon:'🌆' },
];
const MODULOS_CLASE = MODULOS.filter(function(m){ return m.tipo==='clase'; });

const TURNOS_CONFIG = [
  { label:'Mañana',     icon:'🌅', modulos:[0,1,3,4,5]   },
  { label:'Tarde',      icon:'☀️', modulos:[6,7,9,10]    },
  { label:'Vespertino', icon:'🌆', modulos:[11,12,14,15] },
];

const ORIENTACIONES = {
  info:  { nombre:'Informática',  ev:'ev-info',  emoji:'💻', ob:'ob-info'  },
  const: { nombre:'Construcción', ev:'ev-const', emoji:'🏗️', ob:'ob-const' },
  tur:   { nombre:'Turismo',      ev:'ev-tur',   emoji:'🌐', ob:'ob-tur'   },
  bas:   { nombre:'Básico',       ev:'ev-bas',   emoji:'📚', ob:'ob-bas'   },
};

let RECREOS = [
  { modulo:2,  evento:'Recreo de mañana',  notas:'30 min · patio' },
  { modulo:8,  evento:'Recreo de tarde',   notas:'30 min · patio' },
  { modulo:13, evento:'Recreo vespertino', notas:'20 min · patio' },
];

let LABS = [
  { id:'A', nombre:'Lab. Informático A', ocupado:false, capacidad:20, notas:'Windows 11, Packet Tracer' },
  { id:'B', nombre:'Lab. Informático B', ocupado:false, capacidad:24, notas:'Linux Mint, Arduino IDE' },
];

let PROFESORES = [
  { id:1, apellido:'Ces',        nombre:'Roberto', orientacion:'info',  materia:'Programación'   },
  { id:2, apellido:'Di Martino', nombre:'Claudia', orientacion:'const', materia:'Construcciones' },
  { id:3, apellido:'Pieroni',    nombre:'Marcelo', orientacion:'tur',   materia:'Turismo'        },
  { id:4, apellido:'Reichert',   nombre:'Sandra',  orientacion:'info',  materia:'Redes'          },
  { id:5, apellido:'Arnaiz',     nombre:'Gustavo', orientacion:'bas',   materia:'Matemática'     },
];

let RESERVAS = [
  { id:1,  semanaOffset:0, dia:0, modulo:0,  lab:'A', curso:'4°B', orient:'info',  profeId:1, secuencia:'Introducción a Python con Turtle',    cicloClases:1 },
  { id:2,  semanaOffset:0, dia:0, modulo:3,  lab:'A', curso:'6°A', orient:'const', profeId:2, secuencia:'Diseño asistido con AutoCAD',          cicloClases:2 },
  { id:3,  semanaOffset:0, dia:0, modulo:6,  lab:'A', curso:'3°A', orient:'const', profeId:2, secuencia:'Planos estructurales digitales',        cicloClases:1 },
  { id:4,  semanaOffset:0, dia:1, modulo:0,  lab:'B', curso:'2°C', orient:'bas',   profeId:5, secuencia:'Matemática con GeoGebra',               cicloClases:2 },
  { id:5,  semanaOffset:0, dia:1, modulo:1,  lab:'A', curso:'5°A', orient:'tur',   profeId:3, secuencia:'Diseño de página web turística',        cicloClases:1 },
  { id:6,  semanaOffset:0, dia:1, modulo:10, lab:'A', curso:'4°A', orient:'tur',   profeId:3, secuencia:'Reservas online: uso de sistemas',      cicloClases:3 },
  { id:7,  semanaOffset:0, dia:2, modulo:0,  lab:'A', curso:'3°B', orient:'const', profeId:2, secuencia:'Maquetas 3D con SketchUp',              cicloClases:1 },
  { id:8,  semanaOffset:0, dia:2, modulo:1,  lab:'B', curso:'6°B', orient:'info',  profeId:4, secuencia:'Configuración de routers Cisco',        cicloClases:2 },
  { id:9,  semanaOffset:0, dia:2, modulo:11, lab:'A', curso:'1°A', orient:'bas',   profeId:5, secuencia:'Introducción a la informática',         cicloClases:1 },
  { id:10, semanaOffset:0, dia:3, modulo:1,  lab:'A', curso:'2°A', orient:'const', profeId:2, secuencia:'Instalaciones eléctricas (simulación)', cicloClases:2 },
  { id:11, semanaOffset:0, dia:3, modulo:6,  lab:'B', curso:'2°B', orient:'bas',   profeId:5, secuencia:'Estadística con planilla de cálculo',   cicloClases:3 },
  { id:12, semanaOffset:0, dia:3, modulo:15, lab:'B', curso:'3°B', orient:'tur',   profeId:3, secuencia:'Geografía turística digital',           cicloClases:1 },
  { id:13, semanaOffset:0, dia:4, modulo:0,  lab:'B', curso:'5°A', orient:'info',  profeId:1, secuencia:'Proyecto final de programación',        cicloClases:2 },
  { id:14, semanaOffset:0, dia:4, modulo:1,  lab:'A', curso:'6°A', orient:'info',  profeId:4, secuencia:'Seguridad en redes — VPN',              cicloClases:1 },
  { id:15, semanaOffset:0, dia:4, modulo:11, lab:'B', curso:'4°C', orient:'info',  profeId:1, secuencia:'Algoritmia y estructuras de datos',     cicloClases:3 },
];

let SOLICITUDES = [
  { id:101, semanaOffset:0, dia:2, modulo:6, lab:'B', curso:'2°A', orient:'info', profeId:4, secuencia:'Laboratorio de subredes IPv4', cicloClases:1, estado:'pendiente' },
];

let LISTA_ESPERA = [
  { id:1, profeId:4, lab:'A', dia:3, modulo:1, semanaOffset:0 },
  { id:2, profeId:5, lab:'A', dia:3, modulo:1, semanaOffset:0 },
];

let PAUTAS = [
  'Dejar el aula limpia al salir',
  'Apagar equipos al finalizar',
  'Renovar turno cada 3 clases',
  'Registrar secuencia didáctica',
  'No consumir alimentos en el laboratorio',
];

// ─────────────────────────────────────────────
// INTEGRACIÓN CON SAEP
// ─────────────────────────────────────────────

/**
 * Carga la sesión desde session.php (SAEP).
 * Si no hay sesión o el rol no tiene acceso, redirige al dashboard de SAEP.
 * Devuelve una Promise que resuelve con el objeto de sesión.
 */
function cargarSesionSAEP() {
  return fetch("/saep/api/lab/session.php", { credentials: "same-origin" })
    .then(function(res) {

      if (res.status === 401) {
        // sin sesión
        window.location.href = "/saep/index.php";
        return Promise.reject("no_session");
      }

      if (res.status === 403) {
        return res.json().then(function(data) {

          if (data.status === "pending_profile") {
            window.location.href = "/saep/complete-profile.php";

          } else if (data.status === "pending_approval") {
            window.location.href = "/saep/pending.php";

          } else {
            window.location.href = "/saep/dashboard.php";
          }

          return Promise.reject("forbidden");
        });
      }

      if (!res.ok) {
        return Promise.reject("server_error");
      }

      return res.json();
    });
}

/**
 * Aplica los datos de sesión SAEP a la UI del gestor.
 * Recibe el objeto devuelto por session.php.
 */
function aplicarSesion(sess) {
  window.SESSION = sess;

  // Modo: admin/director/subdirector → modo admin; el resto → modo prof
  modoUsuario = sess.isAdmin ? 'admin' : 'prof';

  // Iniciales para el avatar
  var parts = sess.display.split(/[\s,]+/).filter(Boolean);
  var initials = parts.length >= 2
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : sess.display.substring(0, 2).toUpperCase();

  // Avatar: foto de GitHub si está disponible, si no iniciales
  var avEl    = document.getElementById('s-avatar');
  var nameEl  = document.getElementById('s-name');
  var roleEl  = document.getElementById('s-role');
  var ddName  = document.getElementById('sm-name');
  var ddRole  = document.getElementById('sm-role');

  if (avEl) {
    if (sess.avatar) {
      // Mostramos la foto del perfil de GitHub como fondo
      avEl.style.backgroundImage = 'url(' + sess.avatar + ')';
      avEl.style.backgroundSize  = 'cover';
      avEl.style.backgroundPosition = 'center';
      avEl.textContent = '';
    } else {
      avEl.textContent = initials;
    }
  }

  var rolLabel     = sess.isAdmin ? 'directivo' : 'docente';
  var rolLabelLong = sess.isAdmin ? 'Directivo / Administrador' : 'Docente';

  // Etiqueta del rol en el header: muestra el rol real de SAEP
  var rolDisplay = {
    admin:        'Admin',
    director:     'Director',
    subdirector:  'Subdirector',
    profesor:     'Docente',
    preceptor:    'Preceptor',
  }[sess.role] || sess.role;

  if (nameEl) nameEl.textContent = sess.display;
  if (roleEl) {
    roleEl.textContent = rolDisplay;
    if (sess.isAdmin) roleEl.classList.add('admin');
  }
  if (ddName) ddName.textContent = sess.display;
  if (ddRole) ddRole.textContent = rolLabelLong;

  // Mostrar/ocultar botones solo para admin
  document.querySelectorAll('.admin-only').forEach(function(el) {
    el.style.display = sess.isAdmin ? '' : 'none';
  });

  // Sincronizar el profeId del usuario actual en la lista PROFESORES.
  // Si el usuario logueado ya existe (por apellido) usamos su id.
  // Si no existe, lo insertamos como entrada de sesión (id = 'saep_me').
  var existente = PROFESORES.find(function(p) {
    return p.apellido.toLowerCase() === (sess.apellido || '').toLowerCase() &&
           p.nombre.toLowerCase()   === (sess.nombre   || '').toLowerCase();
  });
  if (existente) {
    window.SESSION.profeId = existente.id;
  } else {
    // Agrega al usuario SAEP dinámicamente para que pueda hacer reservas
    var nuevoProfe = {
      id:          'saep_' + sess.id,
      apellido:    sess.apellido  || sess.display,
      nombre:      sess.nombre    || '',
      orientacion: 'bas',   // sin orientación asignada por defecto
      materia:     '—',
    };
    PROFESORES.push(nuevoProfe);
    window.SESSION.profeId = nuevoProfe.id;
  }
}

/** Devuelve el profeId del usuario logueado */
function getCurrentProfId() {
  return (window.SESSION && window.SESSION.profeId) ? window.SESSION.profeId : 1;
}

/** Cierra sesión: redirige al logout de SAEP */
function cerrarSesion() {
  confirmar('¿Cerrar sesión y volver al inicio?', function() {
    window.location.href = "/saep/logout.php";
  });
}

// ─────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────

function getModulo(id) { return MODULOS.find(function(m){ return m.id===id; })||MODULOS[0]; }
function getProfe(id)  { return PROFESORES.find(function(p){ return p.id===id; })||{apellido:'—',nombre:'',orientacion:'bas',materia:'—'}; }
function getLab(id)    { return LABS.find(function(l){ return l.id===id; })||{nombre:'—',ocupado:false,capacidad:0,notas:''}; }
function getHora(m)    { var mod=getModulo(m); return mod?mod.inicio:''; }

function getSemanaStart(offset) {
  offset = offset||0;
  var d=new Date(HOY), dow=d.getDay(), lunesDiff=dow===0?-6:1-dow;
  d.setDate(d.getDate()+lunesDiff+(offset*7));
  return d;
}
function formatFecha(date) { return date.getDate()+'/'+(date.getMonth()+1); }
function getDiaDate(offset,dia) { var s=getSemanaStart(offset); s.setDate(s.getDate()+dia); return s; }
function esHoy(offset,dia) { var d=getDiaDate(offset,dia); return d.toDateString()===HOY.toDateString(); }

function toast(msg,tipo) {
  tipo=tipo||'ok';
  var c=document.getElementById('toast-container'); if(!c) return;
  var t=document.createElement('div');
  t.className='toast toast-'+tipo;
  t.setAttribute('role','status');
  var icons={ok:'✓',err:'✗',info:'ℹ',warn:'⚠'};
  t.innerHTML='<div class="toast-icon" aria-hidden="true">'+(icons[tipo]||'•')+'</div><span>'+msg+'</span>';
  c.appendChild(t);
  setTimeout(function(){ t.style.animation='toastOut .3s ease forwards'; setTimeout(function(){ if(t.parentNode)t.parentNode.removeChild(t); },300); },2800);
}

function confirmar(msg,callback) {
  var body=document.getElementById('confirm-body'), btn=document.getElementById('confirm-ok-btn');
  if(!body||!btn) return;
  body.innerHTML='<p>'+msg+'</p>';
  btn.onclick=function(){ cerrarModal('modal-confirm'); callback(); };
  abrirModal('modal-confirm');
}

function abrirModal(id) {
  var el=document.getElementById(id); if(!el) return;
  el.classList.add('open');
  setTimeout(function(){ var f=el.querySelector('button,input,select,textarea'); if(f)f.focus(); },100);
}
function cerrarModal(id) { var el=document.getElementById(id); if(el) el.classList.remove('open'); }

function navDia(dir) { diaActual=Math.max(0,Math.min(4,diaActual+dir)); renderCalendario(); }
function irDia(d)    { diaActual=d; renderCalendario(); }

function irA(pagina) {
  document.querySelectorAll('.page').forEach(function(p){ p.classList.remove('active'); });
  document.querySelectorAll('.nav-btn,.mobile-nav-btn').forEach(function(b){ b.classList.remove('active'); b.setAttribute('aria-current','false'); });
  var pg=document.getElementById('page-'+pagina);
  if(pg) pg.classList.add('active');
  document.querySelectorAll('[data-page="'+pagina+'"]').forEach(function(b){ b.classList.add('active'); b.setAttribute('aria-current','page'); });
  if(pagina==='admin') renderAdmin();
  if(pagina==='mis-reservas') renderMisReservas();
  closeMobileNav();
}

function toggleMobileNav() {
  var nav=document.getElementById('mobile-nav'), ham=document.getElementById('hamburger'); if(!nav||!ham) return;
  var isOpen=nav.classList.toggle('open');
  ham.classList.toggle('open',isOpen);
  ham.setAttribute('aria-expanded',String(isOpen));
}
function closeMobileNav() {
  var nav=document.getElementById('mobile-nav'), ham=document.getElementById('hamburger');
  if(nav) nav.classList.remove('open');
  if(ham){ ham.classList.remove('open'); ham.setAttribute('aria-expanded','false'); }
}
function toggleSessionMenu() {
  var m=document.getElementById('session-menu'), t=document.getElementById('session-trigger'); if(!m) return;
  var isOpen=m.classList.toggle('open');
  if(t) t.setAttribute('aria-expanded',String(isOpen));
}
function closeSessionMenu() {
  var m=document.getElementById('session-menu'), t=document.getElementById('session-trigger');
  if(m) m.classList.remove('open');
  if(t) t.setAttribute('aria-expanded','false');
}

function selOrient(el,orient) {
  document.querySelectorAll('.orient-tab').forEach(function(t){ t.classList.remove('sel'); t.setAttribute('aria-selected','false'); });
  el.classList.add('sel'); el.setAttribute('aria-selected','true');
  filtroOrient=orient; renderCalendario();
}
function setLabFilter(labId) {
  filtroLab=labId;
  document.querySelectorAll('.lab-card').forEach(function(c){ c.classList.toggle('sel',c.dataset.labId===labId); c.setAttribute('aria-pressed',String(c.dataset.labId===labId)); });
  document.querySelectorAll('.lab-filter-btn').forEach(function(b){ b.classList.remove('active'); b.setAttribute('aria-pressed','false'); });
  var todosBtn=document.getElementById('filt-todos');
  if(todosBtn){ todosBtn.classList.toggle('active',labId==='todos'); todosBtn.setAttribute('aria-pressed',String(labId==='todos')); }
  document.querySelectorAll('[data-lab-filter]').forEach(function(b){ var a=b.dataset.labFilter===labId; b.classList.toggle('active',a); b.setAttribute('aria-pressed',String(a)); });
  renderCalendario();
}

// ─────────────────────────────────────────────
// RENDER SIDEBAR
// ─────────────────────────────────────────────

function renderSidebar() {
  var sl=document.getElementById('sidebar-labs');
  if(sl){
    sl.innerHTML=LABS.map(function(l){
      return '<div class="lab-card '+(filtroLab===l.id?'sel':'')+'" data-lab-id="'+l.id+'" onclick="setLabFilter(\''+l.id+'\')" role="button" tabindex="0" aria-pressed="'+(filtroLab===l.id)+'" onkeydown="if(event.key===\'Enter\'||event.key===\' \')setLabFilter(\''+l.id+'\')"><div class="lab-card-name">'+l.nombre+'</div><div class="lab-card-status '+(l.ocupado?'status-ocup':'status-libre')+'"><span class="dot '+(l.ocupado?'dot-ocup':'dot-libre')+'" aria-hidden="true"></span>'+(l.ocupado?'En mantenimiento':'Disponible')+'</div></div>';
    }).join('');
  }
  var reservasSemana=RESERVAS.filter(function(r){ return r.semanaOffset===semanaOffset; });
  var totalEspera=LISTA_ESPERA.filter(function(e){ return e.semanaOffset===semanaOffset; }).length;
  var ms=document.getElementById('mini-stats');
  if(ms){
    ms.innerHTML='<div class="mini-stat az"><div class="mini-stat-n">'+reservasSemana.length+'</div><div class="mini-stat-l">Reservas</div></div><div class="mini-stat rj"><div class="mini-stat-n">'+totalEspera+'</div><div class="mini-stat-l">Espera</div></div><div class="mini-stat vd"><div class="mini-stat-n">'+(LABS.length*5*MODULOS_CLASE.length-reservasSemana.length)+'</div><div class="mini-stat-l">Libres</div></div>';
  }
  var pl=document.getElementById('pautas-list');
  if(pl){
    pl.innerHTML=PAUTAS.map(function(p){ return '<div class="pauta-item"><span class="chk" aria-hidden="true">✓</span>'+p+'</div>'; }).join('');
  }
  var lfb=document.getElementById('lab-filter-btns');
  if(lfb){
    lfb.innerHTML=LABS.map(function(l){ return '<button class="lab-filter-btn '+(filtroLab===l.id?'active':'')+'" data-lab-filter="'+l.id+'" aria-pressed="'+(filtroLab===l.id)+'" onclick="setLabFilter(\''+l.id+'\')">Lab. '+l.id+'</button>'; }).join('');
  }
}

// ─────────────────────────────────────────────
// RENDER CALENDÁRIO (copiado íntegramente del viejoapp.js)
// ─────────────────────────────────────────────

function renderDayNav() {
  var container=document.getElementById('day-nav-bar'); if(!container) return;
  var html='';
  for(var d=0;d<5;d++){
    var fecha=getDiaDate(semanaOffset,d), hoy=esHoy(semanaOffset,d), activo=d===diaActual;
    html+='<button class="day-nav-btn'+(activo?' active':'')+(hoy?' hoy':'')+'" onclick="irDia('+d+')" aria-pressed="'+activo+'"><span class="day-nav-nombre">'+DIAS_SEMANA[d]+'</span><span class="day-nav-fecha">'+formatFecha(fecha)+'</span>'+(hoy?'<span class="day-nav-hoy-dot"></span>':'')+'</button>';
  }
  container.innerHTML=html;
}

function navSemana(dir) { semanaOffset+=dir; renderCalendario(); }
function irHoy() { semanaOffset=0; var dow=new Date().getDay(); diaActual=dow===0?4:(dow===6?0:dow-1); diaActual=Math.max(0,Math.min(4,diaActual)); renderCalendario(); }

// ── Estilos de la tabla matriz (se inyectan una sola vez) ──────────────────
function inyectarEstilosMatriz() {
  if (document.getElementById('_cal-matrix-styles')) return;
  var s = document.createElement('style');
  s.id = '_cal-matrix-styles';
  s.textContent = [
    '.cm-wrap{overflow-x:auto;width:100%;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);}',
    '.cm{border-collapse:collapse;min-width:820px;width:100%;font-size:12px;background:#fff;}',
    '.cm th,.cm td{border:1px solid var(--gris-borde,#e2e8f0);}',
    '.cm-th-espacio{background:var(--azul-oscuro,#1e3a5f);color:#fff;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;padding:7px 10px;min-width:130px;text-align:left;vertical-align:middle;}',
    '.cm-th-turno{background:var(--azul,#2563eb);color:#fff;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;padding:5px 4px;text-align:center;}',
    '.cm-th-recreo-top{background:var(--gris-claro,#f1f5f9);width:28px;border-color:var(--gris-borde,#e2e8f0);}',
    '.cm-th-hora{background:var(--azul-faint,#eff6ff);color:var(--azul,#2563eb);font-size:10px;font-weight:700;padding:4px 5px;text-align:center;min-width:68px;line-height:1.4;white-space:nowrap;}',
    '.cm-th-hora span{display:block;font-size:9px;font-weight:600;opacity:.75;}',
    '.cm-th-recreo-bot{background:var(--gris-claro,#f1f5f9);width:28px;text-align:center;font-size:13px;cursor:pointer;}',
    '.cm-td-lab{padding:7px 10px;background:#fff;border-right:2px solid var(--azul,#2563eb);vertical-align:top;}',
    '.cm-td-lab strong{display:block;font-size:12px;color:var(--texto,#1e293b);font-weight:700;}',
    '.cm-td-lab .sl{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-top:2px;}',
    '.sl-ok{color:var(--verde,#16a34a);} .sl-err{color:var(--rojo,#ef4444);}',
    '.cm-td-libre{text-align:center;color:var(--gris,#94a3b8);font-size:18px;cursor:pointer;padding:10px 4px;transition:background .12s,color .12s;vertical-align:middle;}',
    '.cm-td-libre:hover{background:var(--azul-faint,#dbeafe);color:var(--azul,#2563eb);}',
    '.cm-td-res{padding:4px 5px;cursor:pointer;vertical-align:top;transition:background .12s;}',
    '.cm-td-res:hover{background:var(--azul-faint,#dbeafe);}',
    '.cm-td-mio{background:var(--azul-faint,#dbeafe)!important;}',
    '.cm-td-sol{padding:4px 5px;background:#fefce8;vertical-align:top;}',
    '.cm-td-mant{background:var(--gris-claro,#f1f5f9);text-align:center;color:#ccc;font-size:11px;vertical-align:middle;}',
    '.cm-td-rec{background:var(--gris-claro,#f1f5f9);width:28px;vertical-align:middle;text-align:center;}',
    '.cm-curso{font-weight:700;font-size:11px;color:var(--texto,#1e293b);line-height:1.3;white-space:nowrap;}',
    '.cm-profe{font-size:10px;color:var(--texto-muted,#64748b);line-height:1.3;white-space:nowrap;}',
    '.cm-pend{font-size:9px;background:#fef3c7;color:#92400e;border-radius:3px;padding:1px 4px;display:inline-block;margin-top:1px;}',
    '.cm-ciclo{display:block;width:100%;height:3px;background:var(--gris-borde,#e2e8f0);border-radius:2px;margin-top:3px;overflow:hidden;}',
    '.cm-ciclo-fill{height:100%;background:var(--azul,#2563eb);border-radius:2px;transition:width .3s;}',
    '.espera-item{display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--gris-borde,#e2e8f0);}',
    '.espera-num{width:22px;height:22px;border-radius:50%;background:var(--azul,#2563eb);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}',
    '.venc-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}',
    '.venc-item{display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--gris-borde,#e2e8f0);}',
  ].join('');
  document.head.appendChild(s);
}

function renderCalendario() {
  inyectarEstilosMatriz();
  var semStart = getSemanaStart(semanaOffset);
  var semEnd   = new Date(semStart); semEnd.setDate(semEnd.getDate()+4);
  var diaDate  = getDiaDate(semanaOffset, diaActual);
  var titleEl  = document.getElementById('cal-title-text');
  if (titleEl) titleEl.innerHTML =
    '<strong>'+DIAS_LARGO[diaActual]+' '+formatFecha(diaDate)+'</strong>'
    +' <span style="color:var(--texto-muted,#64748b);font-size:13px;">'+diaDate.getFullYear()+'</span>'
    +' <span style="color:var(--texto-muted,#64748b);font-size:12px;">· Semana '+formatFecha(semStart)+'–'+formatFecha(semEnd)+'</span>';

  renderDayNav();
  renderSidebar();

  var grid = document.getElementById('cal-grid');
  if (!grid) return;

  // Labs visibles según filtro de sidebar
  var labsVis = filtroLab==='todos' ? LABS : LABS.filter(function(l){ return l.id===filtroLab; });

  // ── TABLA MATRIZ ────────────────────────────────────────────────────────
  var h = '<div class="cm-wrap"><table class="cm" role="grid">';

  // Fila 1: ESPACIO + grupos de turno
  h += '<thead><tr>';
  h += '<th class="cm-th-espacio" rowspan="2" scope="col">ESPACIO</th>';
  TURNOS_CONFIG.forEach(function(turno) {
    h += '<th class="cm-th-turno" colspan="'+turno.modulos.length+'" scope="colgroup">'+turno.icon+' '+turno.label.toUpperCase()+'</th>';
    h += '<th class="cm-th-recreo-top" rowspan="2"></th>';
  });
  h += '</tr>';

  // Fila 2: horas individuales
  h += '<tr>';
  TURNOS_CONFIG.forEach(function(turno) {
    turno.modulos.forEach(function(mId) {
      var mod = getModulo(mId);
      var lbl = mod.label.replace(' Mañana','°M').replace(' Tarde','°T').replace('. Vespert.','°V').replace('° Vespert.','°V');
      h += '<th class="cm-th-hora" scope="col">'+mod.inicio+'<span>'+lbl+'</span></th>';
    });
    // columna recreo: ícono editable para admin
    var recMod = MODULOS.find(function(m){ return m.turno===turno.label && m.tipo==='recreo'; });
    // th recreo ya tiene rowspan=2 desde fila 1, no agregar aquí
  });
  h += '</tr></thead>';

  // Cuerpo: una fila por lab
  h += '<tbody>';
  labsVis.forEach(function(lab) {
    h += '<tr>';
    h += '<td class="cm-td-lab"><strong>'+lab.nombre+'</strong>'
       + '<span class="sl '+(lab.ocupado?'sl-err':'sl-ok')+'">'+( lab.ocupado?'En mantenimiento':'Disponible')+'</span></td>';

    TURNOS_CONFIG.forEach(function(turno) {
      turno.modulos.forEach(function(mId) {
        // Buscar si hay reserva o solicitud para este lab+día+módulo
        var res = null, sol = null;
        // Sólo mostrar si pasa el filtro de orientación (o es 'all')
        RESERVAS.forEach(function(r) {
          if (r.semanaOffset===semanaOffset && r.dia===diaActual && r.modulo===mId && r.lab===lab.id
              && (filtroOrient==='all'||r.orient===filtroOrient)) res = r;
        });
        if (!res) {
          SOLICITUDES.forEach(function(s) {
            if (s.semanaOffset===semanaOffset && s.dia===diaActual && s.modulo===mId && s.lab===lab.id
                && s.estado==='pendiente' && (filtroOrient==='all'||s.orient===filtroOrient)) sol = s;
          });
        }

        if (res) {
          var p   = getProfe(res.profeId);
          var ori = ORIENTACIONES[res.orient]||ORIENTACIONES.bas;
          var esMio = String(res.profeId)===String(getCurrentProfId());
          var pct = Math.round((res.cicloClases/3)*100);
          h += '<td class="cm-td-res'+(esMio?' cm-td-mio':'')+'" onclick="verDetalle('+res.id+')"'
             + ' title="'+p.apellido+' — '+res.secuencia+'">';
          h += '<div class="cm-curso">'+res.curso+' '+ori.emoji+'</div>';
          h += '<div class="cm-profe">'+p.apellido+'</div>';
          h += '<span class="cm-ciclo"><span class="cm-ciclo-fill" style="width:'+pct+'%"></span></span>';
          h += '</td>';
        } else if (sol) {
          var pS  = getProfe(sol.profeId);
          var oriS = ORIENTACIONES[sol.orient]||ORIENTACIONES.bas;
          h += '<td class="cm-td-sol" title="Pendiente de aprobación">';
          h += '<div class="cm-curso">'+sol.curso+' '+oriS.emoji+'</div>';
          h += '<div class="cm-profe">'+pS.apellido+'</div>';
          h += '<span class="cm-pend">⏳ Pendiente</span>';
          h += '</td>';
        } else if (!lab.ocupado) {
          h += '<td class="cm-td-libre" onclick="abrirModalReservaConModulo('+mId+')" title="Disponible · Solicitar turno" role="button" tabindex="0" '
             + 'onkeydown="if(event.key===\'Enter\'||event.key===\' \')abrirModalReservaConModulo('+mId+')">+</td>';
        } else {
          h += '<td class="cm-td-mant">—</td>';
        }
      });

      // Celda recreo entre turnos
      var recMod = MODULOS.find(function(m){ return m.turno===turno.label && m.tipo==='recreo'; });
      if (recMod) {
        var recObj = RECREOS.find(function(r){ return r.modulo===recMod.id; });
        var recClick = modoUsuario==='admin' ? ' onclick="abrirModalRecreo('+recMod.id+')" style="cursor:pointer;" title="Editar recreo"' : ' title="'+(recObj?recObj.evento:'Recreo')+'"';
        h += '<td class="cm-td-rec"'+recClick+'>'+recMod.icon+'</td>';
      } else {
        h += '<td class="cm-td-rec"></td>';
      }
    });

    h += '</tr>';
  });

  h += '</tbody></table></div>';
  grid.innerHTML = h;

  // Listas inferiores
  renderEsperaLista();
  renderVencimientos();

  // Badge admin
  var pendientes = SOLICITUDES.filter(function(s){ return s.estado==='pendiente'; }).length;
  var badge = document.getElementById('admin-badge');
  if (badge) { badge.textContent=pendientes||''; badge.style.display=pendientes&&modoUsuario==='admin'?'':'none'; }
}

function renderEsperaLista() {
  var el = document.getElementById('espera-lista'); if (!el) return;
  var lista = LISTA_ESPERA.filter(function(e){ return e.semanaOffset===semanaOffset; });
  if (!lista.length) { el.innerHTML='<div style="color:var(--texto-muted,#64748b);font-size:12px;padding:6px 0;">Sin personas en espera esta semana.</div>'; return; }
  el.innerHTML = lista.map(function(e,i) {
    var p=getProfe(e.profeId), mod=getModulo(e.modulo), fecha=getDiaDate(e.semanaOffset,e.dia);
    var del = modoUsuario==='admin' ? '<button class="tbl-btn danger" style="font-size:10px;padding:2px 7px;margin-left:auto;" onclick="quitarEspera('+e.id+')">✕</button>' : '';
    return '<div class="espera-item">'
      +'<div class="espera-num">'+(i+1)+'</div>'
      +'<div style="flex:1;"><div style="font-size:12px;font-weight:600;">Prof. '+p.apellido+'</div>'
      +'<div style="font-size:11px;color:var(--texto-muted,#64748b);">'+DIAS_SEMANA[e.dia]+' '+formatFecha(fecha)+' · '+mod.label+' · Lab.'+e.lab+'</div></div>'
      +del+'</div>';
  }).join('');
}

function quitarEspera(id) {
  LISTA_ESPERA = LISTA_ESPERA.filter(function(e){ return e.id!==id; });
  renderEsperaLista(); renderSidebar();
}

function renderVencimientos() {
  var el = document.getElementById('venc-lista'); if (!el) return;
  var prox = RESERVAS.filter(function(r){ return r.cicloClases>=2; }).slice(0,6);
  if (!prox.length) { el.innerHTML='<div style="color:var(--texto-muted,#64748b);font-size:12px;padding:6px 0;">Sin vencimientos próximos.</div>'; return; }
  el.innerHTML = prox.map(function(r) {
    var p=getProfe(r.profeId), ori=ORIENTACIONES[r.orient]||ORIENTACIONES.bas, lab=getLab(r.lab);
    var color = r.cicloClases>=3 ? 'var(--rojo,#ef4444)' : 'var(--amarillo,#f59e0b)';
    return '<div class="venc-item">'
      +'<div class="venc-dot" style="background:'+color+'"></div>'
      +'<div style="flex:1;"><div style="font-size:12px;font-weight:600;">'+r.curso+' '+ori.emoji+' '+ori.nombre+'</div>'
      +'<div style="font-size:11px;color:var(--texto-muted,#64748b);">Clase '+r.cicloClases+'/3 · '+lab.nombre+' · Prof. '+p.apellido+'</div></div>'
      +'<button class="tbl-btn" style="font-size:10px;padding:2px 7px;" onclick="renovarReserva('+r.id+')">🔄</button>'
      +'</div>';
  }).join('');
}

function verDetalle(id) {
  var r=RESERVAS.find(function(x){ return x.x===id; })||RESERVAS.find(function(x){ return x.id===id; });
  if(!r) return;
  var p=getProfe(r.profeId); var mod=getModulo(r.modulo); var ori=ORIENTACIONES[r.orient]||ORIENTACIONES.bas; var lab=getLab(r.lab);
  var fecha=getDiaDate(r.semanaOffset,r.dia);
  var esMio=r.profeId===getCurrentProfId();
  var body=document.getElementById('modal-detalle-body');
  var footer=document.getElementById('modal-detalle-footer');
  if(!body) return;
  body.innerHTML='<div class="detalle-grid"><div class="det-row"><span class="det-lbl">Laboratorio</span><span class="det-val">'+lab.nombre+'</span></div><div class="det-row"><span class="det-lbl">Fecha</span><span class="det-val">'+DIAS_LARGO[r.dia]+', '+formatFecha(fecha)+'</span></div><div class="det-row"><span class="det-lbl">Módulo</span><span class="det-val">'+mod.label+' ('+mod.inicio+'–'+mod.fin+')</span></div><div class="det-row"><span class="det-lbl">Docente</span><span class="det-val">Prof. '+p.apellido+(p.nombre?', '+p.nombre:'')+'</span></div><div class="det-row"><span class="det-lbl">Materia</span><span class="det-val">'+p.materia+'</span></div><div class="det-row"><span class="det-lbl">Curso</span><span class="det-val">'+r.curso+'</span></div><div class="det-row"><span class="det-lbl">Secuencia</span><span class="det-val">'+r.secuencia+'</span></div><div class="det-row"><span class="det-lbl">Orientación</span><span class="det-val"><span class="orient-badge '+ori.ob+'">'+ori.emoji+' '+ori.nombre+'</span></span></div><div class="det-row"><span class="det-lbl">Ciclo</span><span class="det-val">Clase '+r.cicloClases+' de 3</span></div></div>';
  if(footer){
    var btns='<button class="btn-cancel" onclick="cerrarModal(\'modal-detalle\')">Cerrar</button>';
    if(modoUsuario==='admin'||esMio) btns+='<button class="btn-ok" onclick="renovarReserva('+r.id+');cerrarModal(\'modal-detalle\')">🔄 Renovar</button><button class="btn-ok" style="background:var(--rojo,#ef4444)" onclick="cancelarReserva('+r.id+');cerrarModal(\'modal-detalle\')">✕ Cancelar</button>';
    footer.innerHTML=btns;
  }
  abrirModal('modal-detalle');
}

// ─────────────────────────────────────────────
// MODAL RESERVA
// ─────────────────────────────────────────────

function abrirModalReserva() { abrirModalReservaConModulo(null); }
function abrirModalReservaConModulo(moduloId) {
  var fLab=document.getElementById('f-lab'), fDia=document.getElementById('f-dia'), fMod=document.getElementById('f-modulo');
  var fCurso=document.getElementById('f-curso'), fOrient=document.getElementById('f-orient'), fSec=document.getElementById('f-secuencia');
  var hint=document.getElementById('reserva-hint');

  if(fLab) fLab.innerHTML=LABS.filter(function(l){ return !l.ocupado; }).map(function(l){ return '<option value="'+l.id+'">'+l.nombre+'</option>'; }).join('');
  if(fDia) fDia.innerHTML=DIAS_LARGO.map(function(d,i){ return '<option value="'+i+'"'+(i===diaActual?' selected':'')+'>'+d+'</option>'; }).join('');
  if(fMod){
    fMod.innerHTML=MODULOS_CLASE.map(function(m){ return '<option value="'+m.id+'"'+(moduloId===m.id?' selected':'')+'>'+m.label+' ('+m.inicio+')</option>'; }).join('');
    if(moduloId!==null) fMod.value=String(moduloId);
  }
  if(fOrient) fOrient.innerHTML=Object.entries(ORIENTACIONES).map(function(e){ return '<option value="'+e[0]+'">'+e[1].emoji+' '+e[1].nombre+'</option>'; }).join('');
  if(fCurso) fCurso.value='';
  if(fSec) fSec.value='';
  // El hint sólo se muestra para docentes
  if(hint) hint.style.display = modoUsuario==='admin' ? 'none' : '';

  checkConflict();
  abrirModal('modal-reserva');
}

function checkConflict() {
  var lab=document.getElementById('f-lab'), dia=document.getElementById('f-dia'), mod=document.getElementById('f-modulo');
  var msg=document.getElementById('conflict-msg'); if(!lab||!dia||!mod) return;
  var labV=lab.value, diaV=parseInt(dia.value), modV=parseInt(mod.value);
  var conflicto=RESERVAS.find(function(r){ return r.semanaOffset===semanaOffset&&r.lab===labV&&r.dia===diaV&&r.modulo===modV; });
  if(msg){ msg.style.display=conflicto?'':'none'; msg.textContent=conflicto?'⚠️ Ese horario ya está reservado para '+getLab(labV).nombre+'. Elegí otro.':''; }
}

function guardarReserva() {
  var elLab=document.getElementById('f-lab');
  var elDia=document.getElementById('f-dia');
  var elMod=document.getElementById('f-modulo');
  var elCurso=document.getElementById('f-curso');
  var elOrient=document.getElementById('f-orient');
  var elSec=document.getElementById('f-secuencia');
  if(!elLab||!elDia||!elMod||!elCurso||!elOrient||!elSec){
    toast('Error interno: faltan campos del formulario.','err'); return;
  }
  var labV   = elLab.value;
  var diaV   = parseInt(elDia.value);
  var modV   = parseInt(elMod.value);
  var cursoV = elCurso.value;           // es un <select>, .value es directo
  var orientV= elOrient.value;
  var secV   = elSec.value.trim();

  if(!cursoV||cursoV===''){ toast('Seleccioná un curso.','err'); return; }
  if(!secV){ toast('Ingresá la secuencia didáctica.','err'); return; }

  // Verificar conflicto en ese lab+día+módulo
  var conflicto=RESERVAS.find(function(r){ return r.semanaOffset===semanaOffset&&r.lab===labV&&r.dia===diaV&&r.modulo===modV; });
  if(conflicto){ toast('Ese horario ya está ocupado. Elegí otro laboratorio u horario.','err'); return; }

  // profeId viene del usuario logueado (no hay f-profe en el HTML)
  var profeId = getCurrentProfId();

  nextId++;
  if(modoUsuario==='admin'){
    RESERVAS.push({ id:nextId, semanaOffset:semanaOffset, dia:diaV, modulo:modV, lab:labV, curso:cursoV, orient:orientV, profeId:profeId, secuencia:secV, cicloClases:1 });
    toast('Reserva creada correctamente.','ok');
  } else {
    SOLICITUDES.push({ id:nextId, semanaOffset:semanaOffset, dia:diaV, modulo:modV, lab:labV, curso:cursoV, orient:orientV, profeId:profeId, secuencia:secV, cicloClases:1, estado:'pendiente' });
    toast('Solicitud enviada. El directivo deberá aprobarla.','info');
  }
  cerrarModal('modal-reserva');
  renderAll();
}

// ─────────────────────────────────────────────
// RECREO
// ─────────────────────────────────────────────

function abrirModalRecreo(moduloId) {
  var r=RECREOS.find(function(x){ return x.modulo===moduloId; })||{evento:'',notas:''};
  document.getElementById('recreo-modulo-id').value=moduloId;
  document.getElementById('recreo-evento').value=r.evento;
  document.getElementById('recreo-notas').value=r.notas;
  abrirModal('modal-recreo');
}
function guardarRecreo() {
  var mId=parseInt(document.getElementById('recreo-modulo-id').value);
  var ev=document.getElementById('recreo-evento').value.trim();
  var notas=document.getElementById('recreo-notas').value.trim();
  if(!ev){ toast('Ingresá un nombre para el evento.','err'); return; }
  var idx=RECREOS.findIndex(function(r){ return r.modulo===mId; });
  if(idx>=0){ RECREOS[idx].evento=ev; RECREOS[idx].notas=notas; } else { RECREOS.push({modulo:mId,evento:ev,notas:notas}); }
  cerrarModal('modal-recreo'); toast('Recreo actualizado.','ok'); renderCalendario();
}

// ─────────────────────────────────────────────
// ESPERA
// ─────────────────────────────────────────────

function abrirModalEspera(moduloId) {
  var el=document.getElementById('espera-lab'); if(el) el.innerHTML=LABS.map(function(l){ return '<option value="'+l.id+'">'+l.nombre+'</option>'; }).join('');
  var elD=document.getElementById('espera-dia'); if(elD) elD.innerHTML=DIAS_LARGO.map(function(d,i){ return '<option value="'+i+'"'+(i===diaActual?' selected':'')+'>'+d+'</option>'; }).join('');
  var elM=document.getElementById('espera-modulo'); if(elM){ elM.innerHTML=MODULOS_CLASE.map(function(m){ return '<option value="'+m.id+'"'+(moduloId===m.id?' selected':'')+'>'+m.label+'</option>'; }).join(''); if(moduloId!==undefined) elM.value=moduloId; }
  abrirModal('modal-espera');
}
function guardarEspera() {
  var lab=document.getElementById('espera-lab').value;
  var dia=parseInt(document.getElementById('espera-dia').value);
  var mod=parseInt(document.getElementById('espera-modulo').value);
  var pid=getCurrentProfId();
  var yaEsta=LISTA_ESPERA.find(function(e){ return e.profeId===pid&&e.lab===lab&&e.dia===dia&&e.modulo===mod&&e.semanaOffset===semanaOffset; });
  if(yaEsta){ toast('Ya estás en la lista de espera para ese turno.','warn'); cerrarModal('modal-espera'); return; }
  nextId++; LISTA_ESPERA.push({id:nextId,profeId:pid,lab:lab,dia:dia,modulo:mod,semanaOffset:semanaOffset});
  toast('Anotado en lista de espera.','ok'); cerrarModal('modal-espera'); renderSidebar();
}

// ─────────────────────────────────────────────
// MIS RESERVAS
// ─────────────────────────────────────────────

function inyectarEstilosMisReservas() {
  if (document.getElementById('_mr-styles')) return;
  var s = document.createElement('style');
  s.id = '_mr-styles';
  s.textContent = [
    // Grid de tarjetas
    '.reservas-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;margin-bottom:24px;}',
    // Tarjeta base
    '.reserva-card{background:#fff;border:1px solid var(--gris-borde,#e2e8f0);border-radius:12px;padding:14px 16px;display:flex;flex-direction:column;gap:7px;box-shadow:0 1px 4px rgba(0,0,0,.06);transition:box-shadow .15s,transform .15s;}',
    '.reserva-card:hover{box-shadow:0 4px 14px rgba(0,0,0,.1);transform:translateY(-1px);}',
    // Tarjeta de solicitud pendiente
    '.sol-card{border-left:3px solid var(--amarillo,#f59e0b);background:#fffbeb;}',
    // Fila superior: nombre lab + indicador
    '.rc-top{display:flex;align-items:center;justify-content:space-between;gap:8px;}',
    '.rc-lab{font-weight:700;font-size:13px;color:var(--azul-oscuro,#1e3a5f);background:var(--azul-faint,#eff6ff);padding:3px 8px;border-radius:6px;white-space:nowrap;}',
    // Fecha y módulo
    '.rc-fecha{font-size:12px;color:var(--texto,#1e293b);font-weight:600;}',
    '.rc-mod{font-size:11px;color:var(--texto-muted,#64748b);}',
    '.rc-curso{font-size:12px;font-weight:700;color:var(--texto,#1e293b);}',
    '.rc-sec{font-size:11px;color:var(--texto-muted,#64748b);font-style:italic;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}',
    // Acciones
    '.rc-actions{display:flex;gap:6px;margin-top:4px;flex-wrap:wrap;}',
    // Barra de ciclo
    '.ciclo-bar{height:5px;background:var(--gris-borde,#e2e8f0);border-radius:20px;overflow:hidden;flex-shrink:0;}',
    '.ciclo-fill{height:100%;background:var(--azul,#2563eb);border-radius:20px;transition:width .3s;}',
    // Chip estado pendiente
    '.ev-estado-chip.pendiente{font-size:10px;font-weight:700;background:#fef3c7;color:#92400e;border-radius:20px;padding:2px 8px;white-space:nowrap;}',
    // Stats strip de mis reservas
    '.mr-stats{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;}',
    '.mr-stat{flex:1;min-width:100px;background:#fff;border:1px solid var(--gris-borde,#e2e8f0);border-radius:10px;padding:12px 14px;text-align:center;}',
    '.mr-stat-n{font-size:22px;font-weight:800;color:var(--azul-oscuro,#1e3a5f);}',
    '.mr-stat-l{font-size:11px;color:var(--texto-muted,#64748b);margin-top:2px;}',
    // Empty state
    '.empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 20px;color:var(--texto-muted,#64748b);text-align:center;gap:10px;}',
    '.empty-state-icon{font-size:48px;}',
    '.empty-state-title{font-weight:800;font-size:16px;color:var(--texto,#1e293b);}',
    '.link-btn{background:none;border:none;color:var(--azul,#2563eb);font-weight:600;cursor:pointer;font-size:inherit;padding:0;text-decoration:underline;}',
    '.link-btn:hover{color:var(--azul-oscuro,#1e3a5f);}',
    // Sección label dentro de mis reservas
    '.mr-sec-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--texto-muted,#64748b);margin:16px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--gris-borde,#e2e8f0);}',
  ].join('');
  document.head.appendChild(s);
}

function renderMisReservas() {
  inyectarEstilosMisReservas();

  // ── Comparación segura: convertir ambos a String para evitar fallos
  //    cuando profeId es numérico en RESERVAS pero 'saep_xxx' en SESSION
  var pid = String(getCurrentProfId());

  var list   = document.getElementById('mis-reservas-list');
  var empty  = document.getElementById('mis-reservas-empty');
  var stats  = document.getElementById('mis-stats-strip');
  var titulo = document.getElementById('mis-reservas-sub');
  if (!list) return;

  var misRes = RESERVAS.filter(function(r){ return String(r.profeId) === pid; })
               .sort(function(a,b){ return a.semanaOffset-b.semanaOffset || a.dia-b.dia || a.modulo-b.modulo; });
  var misSol = SOLICITUDES.filter(function(s){ return String(s.profeId) === pid && s.estado === 'pendiente'; });

  // ── Subtítulo dinámico
  if (titulo) {
    var profe = PROFESORES.find(function(p){ return String(p.id) === pid; });
    titulo.textContent = profe ? profe.materia || '' : '';
  }

  // ── Stats strip
  if (stats) {
    var activas   = misRes.length;
    var pendientes= misSol.length;
    var proxVenc  = misRes.filter(function(r){ return r.cicloClases >= 3; }).length;
    stats.className = 'mr-stats';
    stats.innerHTML =
      '<div class="mr-stat"><div class="mr-stat-n">'+activas+'</div><div class="mr-stat-l">Reservas activas</div></div>' +
      '<div class="mr-stat"><div class="mr-stat-n">'+pendientes+'</div><div class="mr-stat-l">Pendientes</div></div>' +
      '<div class="mr-stat"><div class="mr-stat-n">'+proxVenc+'</div><div class="mr-stat-l">Por vencer</div></div>';
  }

  // ── Sin datos
  if (!misRes.length && !misSol.length) {
    list.innerHTML = '';
    if (empty) empty.style.display = '';
    return;
  }
  if (empty) empty.style.display = 'none';

  var html = '';

  // ── Solicitudes pendientes
  if (misSol.length) {
    html += '<div class="mr-sec-label">⏳ Solicitudes pendientes ('+misSol.length+')</div>';
    html += '<div class="reservas-grid">';
    misSol.forEach(function(s) {
      var mod  = getModulo(s.modulo);
      var fecha= getDiaDate(s.semanaOffset, s.dia);
      var lab  = getLab(s.lab);
      var ori  = ORIENTACIONES[s.orient] || ORIENTACIONES.bas;
      html += '<div class="reserva-card sol-card">'
        + '<div class="rc-top"><span class="rc-lab">'+lab.nombre+'</span><span class="ev-estado-chip pendiente">⏳ Pendiente</span></div>'
        + '<div class="rc-fecha">'+DIAS_LARGO[s.dia]+', '+formatFecha(fecha)+'</div>'
        + '<div class="rc-mod">'+mod.label+' · '+mod.inicio+'–'+mod.fin+'</div>'
        + '<div class="rc-curso">'+s.curso+'</div>'
        + '<div class="rc-sec">'+s.secuencia+'</div>'
        + '<span class="orient-badge '+ori.ob+'">'+ori.emoji+' '+ori.nombre+'</span>'
        + '<div class="rc-actions">'
        +   '<button class="tbl-btn danger" onclick="cancelarSolicitud('+s.id+')">✕ Cancelar</button>'
        + '</div>'
        + '</div>';
    });
    html += '</div>';
  }

  // ── Reservas activas
  if (misRes.length) {
    html += '<div class="mr-sec-label">✅ Reservas activas ('+misRes.length+')</div>';
    html += '<div class="reservas-grid">';
    misRes.forEach(function(r) {
      var mod  = getModulo(r.modulo);
      var fecha= getDiaDate(r.semanaOffset, r.dia);
      var lab  = getLab(r.lab);
      var ori  = ORIENTACIONES[r.orient] || ORIENTACIONES.bas;
      var pct  = Math.round((r.cicloClases / 3) * 100);
      var vencColor = r.cicloClases >= 3 ? 'var(--rojo,#ef4444)' : r.cicloClases === 2 ? 'var(--amarillo,#f59e0b)' : 'var(--azul,#2563eb)';
      html += '<div class="reserva-card">'
        + '<div class="rc-top">'
        +   '<span class="rc-lab">'+lab.nombre+'</span>'
        +   '<div class="ciclo-bar" style="width:52px" title="Ciclo '+r.cicloClases+' de 3">'
        +     '<div class="ciclo-fill" style="width:'+pct+'%;background:'+vencColor+'"></div>'
        +   '</div>'
        + '</div>'
        + '<div class="rc-fecha">'+DIAS_LARGO[r.dia]+', '+formatFecha(fecha)+'</div>'
        + '<div class="rc-mod">'+mod.label+' · '+mod.inicio+'–'+mod.fin+'</div>'
        + '<div class="rc-curso">'+r.curso+'</div>'
        + '<div class="rc-sec">'+r.secuencia+'</div>'
        + '<span class="orient-badge '+ori.ob+'">'+ori.emoji+' '+ori.nombre+'</span>'
        + (r.cicloClases >= 3 ? '<div style="font-size:10px;color:var(--rojo,#ef4444);font-weight:700;">⚠ Ciclo completo — renovar</div>' : '')
        + '<div class="rc-actions">'
        +   '<button class="tbl-btn" onclick="verDetalle('+r.id+')">👁 Ver</button>'
        +   '<button class="tbl-btn" onclick="renovarReserva('+r.id+')">🔄 Renovar</button>'
        +   '<button class="tbl-btn danger" onclick="cancelarReserva('+r.id+')">✕ Cancelar</button>'
        + '</div>'
        + '</div>';
    });
    html += '</div>';
  }

  list.innerHTML = html;
}

function cancelarSolicitud(solId) {
  var s=SOLICITUDES.find(function(x){ return x.id===solId; }); if(!s) return;
  confirmar('¿Cancelar esta solicitud pendiente?',function(){
    SOLICITUDES=SOLICITUDES.filter(function(x){ return x.id!==solId; }); toast('Solicitud cancelada.','info'); renderAll();
  });
}

function renovarReserva(id) {
  var r=RESERVAS.find(function(x){ return x.id===id; }); if(!r) return;
  function puedeNuevo(r){ return (r.renovaciones||0)>=2; }
  if(modoUsuario==='admin'){
    if(puedeNuevo(r)){
      confirmar('Han pasado 2 semanas de renovaciones. ¿Iniciar nuevo ciclo completo de 3 clases?',function(){ r.cicloClases=1; r.renovaciones=0; r.semanaRenovacion=null; toast('Nuevo ciclo completo iniciado.','ok'); renderAll(); });
    } else {
      confirmar('¿Aprobar renovación por 1 día para '+getLab(r.lab).nombre+' — '+r.curso+'?',function(){ r.cicloClases=1; r.renovaciones=(r.renovaciones||0)+1; toast('Renovación aprobada (semana '+(r.renovaciones)+'/2).','ok'); renderAll(); });
    }
    return;
  }
  if(puedeNuevo(r)){ toast('Ya cumpliste 2 semanas de renovación. Podés hacer una nueva reserva de 3 días normalmente.','info'); return; }
  var labNombre=getLab(r.lab).nombre, semLabel=(r.renovaciones||0)+1;
  confirmar('¿Solicitar renovación semanal '+semLabel+'/2 para <strong>'+labNombre+' — '+r.curso+'</strong>? El directivo deberá aprobarla.',function(){
    nextId++;
    SOLICITUDES.push({ id:nextId, semanaOffset:semanaOffset, dia:r.dia, modulo:r.modulo, lab:r.lab, curso:r.curso, orient:r.orient, profeId:r.profeId, secuencia:r.secuencia, cicloClases:1, estado:'pendiente', esRenovacion:true, reservaOriginalId:r.id, renovacionNum:semLabel });
    toast('Solicitud de renovación semana '+semLabel+'/2 enviada al directivo.','info'); renderAll();
  });
}

function cancelarReserva(id) {
  var r=RESERVAS.find(function(x){ return x.id===id; }); if(!r) return;
  var p=getProfe(r.profeId);
  confirmar('¿Cancelar la reserva de <strong>Prof. '+p.apellido+'</strong> — '+r.curso+' el '+DIAS_LARGO[r.dia]+'?',function(){
    RESERVAS=RESERVAS.filter(function(x){ return x.id!==id; }); toast('Reserva cancelada.','info');
    var waiting=LISTA_ESPERA.filter(function(e){ return e.lab===r.lab&&e.dia===r.dia&&e.modulo===r.modulo; });
    if(waiting.length) setTimeout(function(){ toast('Hay '+waiting.length+' docente(s) en espera para ese turno.','warn'); },400);
    renderAll();
  });
}

// ─────────────────────────────────────────────
// ADMIN
// ─────────────────────────────────────────────

function renderAdmin() {
  var total=RESERVAS.length, pendientes=SOLICITUDES.filter(function(s){ return s.estado==='pendiente'; }).length;
  var docActivos=new Set(RESERVAS.map(function(r){ return r.profeId; })).size;
  ['s-semana','s-pendientes','s-docs','s-labs'].forEach(function(id,i){
    var el=document.getElementById(id); if(el) el.textContent=[total,pendientes,docActivos,LABS.length][i];
  });
  renderSolicitudesAdmin(); renderProfesores(); renderLabsConfig(); renderAdminReservas(); renderPautasAdmin();
}

function renderSolicitudesAdmin() {
  var el=document.getElementById('solicitudes-tbody'); if(!el) return;
  var solic=SOLICITUDES.filter(function(s){ return s.estado==='pendiente'; });
  var count=document.getElementById('solicitudes-count'); if(count) count.textContent=solic.length?'('+solic.length+')':'';
  if(!solic.length){ el.innerHTML='<tr><td colspan="7" style="text-align:center;color:var(--texto-muted);padding:20px;">No hay solicitudes pendientes.</td></tr>'; return; }
  el.innerHTML=solic.map(function(s){
    var p=getProfe(s.profeId); var ori=ORIENTACIONES[s.orient]; var fecha=getDiaDate(s.semanaOffset,s.dia); var mod=getModulo(s.modulo);
    return '<tr'+(s.esRenovacion?' style="background:#eff6ff"':'')+'><td>Prof. '+p.apellido+'</td><td>Lab.'+s.lab+(s.esRenovacion?'&nbsp;<span style="font-size:9px;font-weight:800;background:var(--azul);color:#fff;padding:1px 5px;border-radius:4px;">RENOV '+s.renovacionNum+'/2</span>':'')+'</td><td>'+DIAS_SEMANA[s.dia]+' '+formatFecha(fecha)+'</td><td>'+mod.label+' ('+mod.inicio+')</td><td>'+s.curso+'</td><td><span class="orient-badge '+ori.ob+'">'+ori.emoji+' '+ori.nombre+'</span></td><td><div class="table-actions"><button class="tbl-btn ok" onclick="aceptarSolicitud('+s.id+')" aria-label="Aprobar">✓ Aprobar</button><button class="tbl-btn danger" onclick="rechazarSolicitud('+s.id+')" aria-label="Rechazar">✕ Rechazar</button></div></td></tr>';
  }).join('');
}

function aceptarSolicitud(id) {
  var s=SOLICITUDES.find(function(x){ return x.id===id; }); if(!s) return;
  confirmar('¿Aprobar la solicitud de Prof. '+getProfe(s.profeId).apellido+' para Lab.'+s.lab+'?',function(){
    if(s.esRenovacion){
      var orig=RESERVAS.find(function(r){ return r.id===s.reservaOriginalId; });
      if(orig){ orig.cicloClases=1; orig.renovaciones=(orig.renovaciones||0)+1; }
    } else {
      nextId++;
      RESERVAS.push({ id:nextId, semanaOffset:s.semanaOffset, dia:s.dia, modulo:s.modulo, lab:s.lab, curso:s.curso, orient:s.orient, profeId:s.profeId, secuencia:s.secuencia, cicloClases:s.cicloClases||1 });
    }
    SOLICITUDES=SOLICITUDES.filter(function(x){ return x.id!==id; });
    toast('Solicitud aprobada.','ok'); renderAll();
  });
}

function rechazarSolicitud(id) {
  confirmar('¿Rechazar esta solicitud?',function(){
    SOLICITUDES=SOLICITUDES.filter(function(x){ return x.id!==id; }); toast('Solicitud rechazada.','info'); renderAll();
  });
}

function renderProfesores() {
  var qEl=document.getElementById('search-prof'); var q=qEl?qEl.value.toLowerCase():'';
  var tbody=document.getElementById('prof-tbody'); if(!tbody) return;
  var filtered=PROFESORES.filter(function(p){ return (p.apellido+' '+p.nombre+' '+p.materia).toLowerCase().indexOf(q)>=0; });
  tbody.innerHTML=filtered.map(function(p){
    var ori=ORIENTACIONES[p.orientacion]||ORIENTACIONES.bas;
    var reservas=RESERVAS.filter(function(r){ return r.profeId===p.id; }).length;
    return '<tr><td><strong>'+p.apellido+'</strong>, '+p.nombre+'</td><td>'+p.materia+'</td><td><span class="orient-badge '+ori.ob+'">'+ori.emoji+' '+ori.nombre+'</span></td><td><strong>'+reservas+'</strong></td><td><div class="table-actions"><button class="tbl-btn" onclick="editarDocente(\''+p.id+'\')">✏️ Editar</button><button class="tbl-btn danger" onclick="eliminarDocente(\''+p.id+'\')">🗑 Eliminar</button></div></td></tr>';
  }).join('');
  if(!filtered.length) tbody.innerHTML='<tr><td colspan="5" style="text-align:center;color:var(--texto-muted);padding:20px;">No se encontraron docentes.</td></tr>';
}

function renderLabsConfig() {
  var el=document.getElementById('labs-config-list'); if(!el) return;
  if(!LABS.length){ el.innerHTML='<div style="padding:16px 18px;color:var(--texto-muted);font-size:13px;">No hay espacios configurados.</div>'; return; }
  el.innerHTML=LABS.map(function(l){ return '<div class="lab-config-card"><div class="lab-config-icon" aria-hidden="true">🖥️</div><div class="lab-config-info"><div class="lab-config-name">'+l.nombre+'</div><div class="lab-config-sub">'+l.capacidad+' equipos · '+(l.notas||'Sin notas')+'</div></div><span class="orient-badge '+(l.ocupado?'ob-err':'ob-ok')+'" style="margin-right:8px;">'+(l.ocupado?'Mantenimiento':'Disponible')+'</span><div class="lab-config-actions"><button class="tbl-btn" onclick="editarLab(\''+l.id+'\')">✏️ Editar</button><button class="tbl-btn" onclick="toggleEstadoLab(\''+l.id+'\')">'+(l.ocupado?'🟢 Liberar':'🔴 Ocupar')+'</button><button class="tbl-btn danger" onclick="eliminarLab(\''+l.id+'\')">🗑</button></div></div>'; }).join('');
}

function renderAdminReservas() {
  var tbody=document.getElementById('admin-reservas-tbody'); if(!tbody) return;
  var filterEl=document.getElementById('admin-filter-orient'); var filterO=filterEl?filterEl.value:'all';
  var filtered=RESERVAS.filter(function(r){ return filterO==='all'||r.orient===filterO; });
  tbody.innerHTML=filtered.map(function(r){
    var p=getProfe(r.profeId); var ori=ORIENTACIONES[r.orient]; var fecha=getDiaDate(r.semanaOffset,r.dia); var pct=(r.cicloClases/3)*100; var mod=getModulo(r.modulo);
    return '<tr><td>Prof.'+p.apellido+'</td><td>Lab.'+r.lab+'</td><td>'+DIAS_SEMANA[r.dia]+' '+formatFecha(fecha)+'</td><td>'+mod.label+' ('+mod.inicio+')</td><td>'+r.curso+'</td><td><span class="orient-badge '+ori.ob+'">'+ori.emoji+' '+ori.nombre+'</span></td><td><div style="display:flex;align-items:center;gap:6px;"><div style="width:44px;background:var(--gris-borde);border-radius:20px;height:6px;overflow:hidden;"><div style="width:'+pct+'%;height:100%;background:var(--azul);border-radius:20px;"></div></div><span style="font-size:11px;color:var(--texto-muted);">'+r.cicloClases+'/3</span></div></td><td><div class="table-actions"><button class="tbl-btn" onclick="verDetalle('+r.id+')">👁 Ver</button><button class="tbl-btn danger" onclick="cancelarReserva('+r.id+')">🗑</button></div></td></tr>';
  }).join('');
  if(!filtered.length) tbody.innerHTML='<tr><td colspan="8" style="text-align:center;color:var(--texto-muted);padding:20px;">No hay reservas.</td></tr>';
}

function renderPautasAdmin() {
  var el=document.getElementById('pautas-admin-list'); if(!el) return;
  if(!PAUTAS.length){ el.innerHTML='<div style="padding:16px 18px;color:var(--texto-muted);font-size:13px;">No hay pautas configuradas.</div>'; return; }
  el.innerHTML=PAUTAS.map(function(p,i){ return '<div class="list-item" style="padding:10px 18px;"><span class="chk" aria-hidden="true">✓</span><span style="flex:1;font-size:13px;">'+p+'</span><button class="tbl-btn danger" onclick="eliminarPauta('+i+')" style="padding:3px 8px;font-size:11px;">✕</button></div>'; }).join('');
}

function abrirModalDocente() {
  editDocenteId=null;
  document.getElementById('modal-docente-title').textContent='+ Agregar docente';
  ['doc-apellido','doc-nombre','doc-materia'].forEach(function(id){ var el=document.getElementById(id); if(el) el.value=''; });
  var orient=document.getElementById('doc-orient'); if(orient) orient.value='info';
  abrirModal('modal-docente');
}
function editarDocente(id) {
  var p=getProfe(id); editDocenteId=id;
  document.getElementById('modal-docente-title').textContent='✏️ Editar docente';
  document.getElementById('doc-apellido').value=p.apellido;
  document.getElementById('doc-nombre').value=p.nombre;
  document.getElementById('doc-materia').value=p.materia;
  document.getElementById('doc-orient').value=p.orientacion;
  abrirModal('modal-docente');
}
function guardarDocente() {
  var apellido=document.getElementById('doc-apellido').value.trim();
  var nombre=document.getElementById('doc-nombre').value.trim();
  var materia=document.getElementById('doc-materia').value.trim();
  var orient=document.getElementById('doc-orient').value;
  if(!apellido||!nombre||!materia){ toast('Completá todos los campos.','err'); return; }
  if(editDocenteId){ var p=PROFESORES.find(function(x){ return x.id===editDocenteId; }); if(p){ p.apellido=apellido; p.nombre=nombre; p.materia=materia; p.orientacion=orient; } toast('Docente actualizado.','ok'); }
  else { nextId++; PROFESORES.push({id:nextId,apellido:apellido,nombre:nombre,materia:materia,orientacion:orient}); toast('Docente agregado.','ok'); }
  cerrarModal('modal-docente'); renderAdmin();
}
function eliminarDocente(id) {
  var p=getProfe(id);
  confirmar('¿Eliminar a <strong>'+p.apellido+', '+p.nombre+'</strong>? Se eliminarán sus reservas.',function(){
    PROFESORES=PROFESORES.filter(function(x){ return x.id!==id; }); RESERVAS=RESERVAS.filter(function(r){ return r.profeId!==id; }); SOLICITUDES=SOLICITUDES.filter(function(s){ return s.profeId!==id; });
    toast('Docente eliminado.','info'); renderAdmin(); renderCalendario();
  });
}

function abrirModalLab() {
  editLabId=null;
  document.getElementById('modal-lab-title').textContent='+ Agregar espacio';
  ['lab-nombre','lab-capacidad','lab-notas'].forEach(function(id){ var el=document.getElementById(id); if(el) el.value=''; });
  var estado=document.getElementById('lab-estado'); if(estado) estado.value='libre';
  abrirModal('modal-lab');
}
function editarLab(id) {
  var l=getLab(id); editLabId=id;
  document.getElementById('modal-lab-title').textContent='✏️ Editar espacio';
  document.getElementById('lab-nombre').value=l.nombre;
  document.getElementById('lab-capacidad').value=l.capacidad||'';
  document.getElementById('lab-estado').value=l.ocupado?'ocupado':'libre';
  document.getElementById('lab-notas').value=l.notas||'';
  abrirModal('modal-lab');
}
function guardarLab() {
  var nombre=document.getElementById('lab-nombre').value.trim();
  var capacidad=parseInt(document.getElementById('lab-capacidad').value)||0;
  var estado=document.getElementById('lab-estado').value;
  var notas=document.getElementById('lab-notas').value.trim();
  if(!nombre){ toast('Ingresá un nombre para el espacio.','err'); return; }
  if(editLabId){ var l=LABS.find(function(x){ return x.id===editLabId; }); if(l){ l.nombre=nombre; l.capacidad=capacidad; l.ocupado=estado==='ocupado'; l.notas=notas; } toast('Espacio actualizado.','ok'); }
  else { var newId=String.fromCharCode(65+LABS.length); LABS.push({id:newId,nombre:nombre,capacidad:capacidad,ocupado:estado==='ocupado',notas:notas}); toast('Espacio "'+nombre+'" agregado.','ok'); }
  cerrarModal('modal-lab'); renderAdmin(); renderCalendario();
}
function toggleEstadoLab(id) {
  var l=LABS.find(function(x){ return x.id===id; }); if(!l) return;
  l.ocupado=!l.ocupado; toast('Lab.'+l.id+': '+(l.ocupado?'En mantenimiento':'Disponible')+'.','info');
  renderAdmin(); renderSidebar();
}
function eliminarLab(id) {
  var l=getLab(id);
  confirmar('¿Eliminar el espacio <strong>'+l.nombre+'</strong>? Se eliminarán sus reservas.',function(){
    LABS=LABS.filter(function(x){ return x.id!==id; }); RESERVAS=RESERVAS.filter(function(r){ return r.lab!==id; }); SOLICITUDES=SOLICITUDES.filter(function(s){ return s.lab!==id; });
    toast('Espacio eliminado.','info'); renderAdmin(); renderCalendario();
  });
}

function abrirModalPauta() { var el=document.getElementById('pauta-texto'); if(el) el.value=''; abrirModal('modal-pauta'); }
function guardarPauta() {
  var txt=document.getElementById('pauta-texto').value.trim();
  if(!txt){ toast('Ingresá el texto de la pauta.','err'); return; }
  PAUTAS.push(txt); cerrarModal('modal-pauta'); toast('Pauta agregada.','ok'); renderAdmin(); renderSidebar();
}
function eliminarPauta(i) {
  confirmar('¿Eliminar la pauta "<strong>'+PAUTAS[i]+'</strong>"?',function(){
    PAUTAS.splice(i,1); toast('Pauta eliminada.','info'); renderAdmin(); renderSidebar();
  });
}

function renderAll(){

  console.log("renderAll ejecutado");

  renderCalendario();

  var page = document.querySelector('.page.active');

  if(!page){
    console.warn("No hay .page.active, usando calendario por defecto");
    page = document.getElementById('page-calendario');
  }

  console.log("pagina activa:", page.id);

  if(page.id === 'page-mis-reservas'){

      console.log("Entrando a renderMisReservas()");

      var before = document.getElementById("mis-reservas-list").innerHTML;
      console.log("HTML antes:", before);

      renderMisReservas();

      var after = document.getElementById("mis-reservas-list").innerHTML;
      console.log("HTML despues:", after);

      if(after === ""){
        console.error("renderMisReservas no insertó contenido");
      } else {
        console.log("renderMisReservas insertó contenido correctamente");
      }
  }

}

// ─────────────────────────────────────────────
// INIT — reemplaza el antiguo DOMContentLoaded con sesión hardcodeada
// ─────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function() {
  // Día actual
  var dow = new Date().getDay();
  diaActual = dow===0 ? 4 : (dow===6 ? 0 : dow-1);
  diaActual = Math.max(0, Math.min(4, diaActual));

  // Eventos globales
  document.addEventListener('click', function(e) {
    if(!e.target.closest('.session-widget')) closeSessionMenu();
    if(e.target.classList.contains('modal-overlay')) e.target.classList.remove('open');
  });
  document.addEventListener('keydown', function(e) {
    if(e.key==='Escape') document.querySelectorAll('.modal-overlay.open').forEach(function(m){ m.classList.remove('open'); });
  });
  ['f-lab','f-dia','f-modulo'].forEach(function(id){ var el=document.getElementById(id); if(el) el.addEventListener('change', checkConflict); });

  // Cargamos la sesión desde SAEP y arrancamos el gestor
  cargarSesionSAEP()
    .then(function(sess) {
      aplicarSesion(sess);
      renderCalendario();
    })
    .catch(function(err) {
      // Los redirects ya están manejados dentro de cargarSesionSAEP().
      // Si llega aquí es un error de red.
      if(err !== 'no_session' && err !== 'forbidden') {
        console.error('Error al cargar sesión SAEP:', err);
        // Mostramos pantalla de error en lugar de la app rota
        document.body.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;font-family:sans-serif;gap:16px;"><p style="font-size:1.1rem;color:#555;">No se pudo conectar con SAEP.</p><a href="/saep/dashboard.php"> style="padding:10px 24px;background:#1e3a5f;color:#fff;border-radius:8px;text-decoration:none;">Volver al dashboard</a></div>';
      }
    });
});