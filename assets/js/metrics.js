/**
 * SAEP — Módulo de Métricas
 * Frontend dinámico con Chart.js. Se integra en dashboard.php.
 * Todas las secciones cargan datos desde /metrics/*.php (JSON).
 */

// ── Instancias de charts para destruirlas antes de recrear ────────
const MetricCharts = {};

// ── Colores coherentes con el sistema ─────────────────────────────
const MC = {
    navy:    '#1e3a5f',
    blue:    '#2563eb',
    green:   '#16a34a',
    red:     '#dc2626',
    amber:   '#d97706',
    teal:    '#0d9488',
    purple:  '#7c3aed',
    muted:   '#9ca3af',
    border:  '#e5e7eb',
    // Paleta para múltiples series
    palette: ['#1e3a5f','#2563eb','#0d9488','#16a34a','#d97706','#dc2626','#7c3aed','#db2777','#ea580c','#65a30d'],
};

// ─────────────────────────────────────────────────────────────────
// UTILIDADES
// ─────────────────────────────────────────────────────────────────

function mc_destroyChart(key) {
    if (MetricCharts[key]) { MetricCharts[key].destroy(); delete MetricCharts[key]; }
}

function mc_nota_color(n) {
    if (n === null) return '';
    if (n >= 7) return 'alta';
    if (n >= 5) return 'media';
    return 'baja';
}

function mc_pct_color(p) {
    if (p >= 70) return 'green';
    if (p >= 40) return 'amber';
    return 'red';
}

function mc_rank_class(i) {
    if (i === 0) return 'gold';
    if (i === 1) return 'silver';
    if (i === 2) return 'bronze';
    return '';
}

/** Muestra loading en un contenedor */
function mc_loading(id) {
    const el = document.getElementById(id);
    if (el) el.innerHTML = `
        <div class="metrics-loading">
            <div class="metrics-spinner"></div>
            <div style="font-size:.78rem;">Cargando datos…</div>
        </div>`;
}

/** Muestra error en un contenedor */
function mc_error(id, msg) {
    const el = document.getElementById(id);
    if (el) el.innerHTML = `<div class="metrics-error">⚠ ${msg}</div>`;
}

/** Fetch con manejo de errores */
async function mc_fetch(url) {
    const r = await fetch(url);
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    return r.json();
}

// ─────────────────────────────────────────────────────────────────
// NAVEGACIÓN ENTRE TABS
// ─────────────────────────────────────────────────────────────────

function metricsNav(tab) {
    // Tabs
    document.querySelectorAll('.metrics-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.metrics-section').forEach(s => s.classList.remove('active'));

    const tabEl = document.querySelector(`.metrics-tab[data-tab="${tab}"]`);
    const secEl = document.getElementById(`metrics-sec-${tab}`);
    if (tabEl) tabEl.classList.add('active');
    if (secEl) secEl.classList.add('active');

    // Cargar datos según tab
    if (tab === 'general')  mc_loadGeneral();
    if (tab === 'alumnos')  mc_loadAlumnos();
    if (tab === 'cursos')   mc_loadCursos();
    if (tab === 'aulas')    mc_loadAulas();
    if (tab === 'notas')    mc_loadNotas();
}

// ─────────────────────────────────────────────────────────────────
// SECCIÓN: GENERAL
// ─────────────────────────────────────────────────────────────────

async function mc_loadGeneral() {
    mc_loading('mc-general-kpis');
    mc_loading('mc-general-charts');
    try {
        const d = await mc_fetch('metrics/general.php');
        mc_renderGeneralKpis(d);
        mc_renderGeneralCharts(d);
    } catch(e) {
        mc_error('mc-general-kpis', 'No se pudieron cargar los datos generales.');
        document.getElementById('mc-general-charts').innerHTML = '';
    }
}

function mc_renderGeneralKpis(d) {
    document.getElementById('mc-general-kpis').innerHTML = `
    <div class="kpi-grid">
        <div class="kpi-card blue">
            <div class="kpi-label">Alumnos activos</div>
            <div class="kpi-value">${d.total_alumnos ?? '—'}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Cursos</div>
            <div class="kpi-value">${d.total_cursos ?? '—'}</div>
        </div>
        <div class="kpi-card ${mc_nota_color(d.promedio_general)}">
            <div class="kpi-label">Promedio institucional</div>
            <div class="kpi-value">${d.promedio_general ?? '—'}</div>
            <div class="kpi-sub">sobre 10</div>
        </div>
        <div class="kpi-card ${mc_pct_color(d.porcentaje_aprobacion)}">
            <div class="kpi-label">% Aprobación</div>
            <div class="kpi-value">${d.porcentaje_aprobacion ?? '—'}<span style="font-size:.9rem">%</span></div>
            <div class="kpi-sub">${d.aprobados} aprob. / ${d.desaprobados} desaprob.</div>
        </div>
        <div class="kpi-card teal">
            <div class="kpi-label">Total entregas</div>
            <div class="kpi-value">${d.total_entregas ?? '—'}</div>
            <div class="kpi-sub">${d.entregas_realizadas} realizadas</div>
        </div>
        <div class="kpi-card ${d.promedio_asistencia !== null ? mc_pct_color(d.promedio_asistencia) : ''}">
            <div class="kpi-label">Promedio asistencia</div>
            <div class="kpi-value">${d.promedio_asistencia !== null ? d.promedio_asistencia+'%' : '—'}</div>
        </div>
        <div class="kpi-card amber">
            <div class="kpi-label">Capacidad total</div>
            <div class="kpi-value">${d.capacidad_total ?? '—'}</div>
            <div class="kpi-sub">vacantes en aulas</div>
        </div>
        <div class="kpi-card purple">
            <div class="kpi-label">% Ocupación</div>
            <div class="kpi-value">${d.porcentaje_ocupacion ?? '—'}<span style="font-size:.9rem">%</span></div>
            <div class="kpi-sub">${d.alumnos_en_aulas} de ${d.capacidad_total}</div>
        </div>
    </div>`;
}

function mc_renderGeneralCharts(d) {
    document.getElementById('mc-general-charts').innerHTML = `
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-title">Aprobados vs Desaprobados</div>
            <div class="chart-wrap short"><canvas id="chart-gen-pie"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-title">Promedio por curso (Top 15)</div>
            <div class="chart-wrap tall"><canvas id="chart-gen-bars"></canvas></div>
        </div>
    </div>`;

    // Torta
    mc_destroyChart('gen-pie');
    MetricCharts['gen-pie'] = new Chart(
        document.getElementById('chart-gen-pie').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Aprobados','Desaprobados'],
            datasets: [{ data: [d.aprobados, d.desaprobados], backgroundColor: [MC.green, MC.red], borderWidth: 0 }]
        },
        options: { plugins: { legend: { position:'bottom', labels: { font:{size:11}, padding:10 } } }, cutout:'65%' }
    });

    // Barras por curso (top 15)
    const top = (d.cursos_stats || []).filter(c => c.promedio !== null).slice(0,15);
    mc_destroyChart('gen-bars');
    MetricCharts['gen-bars'] = new Chart(
        document.getElementById('chart-gen-bars').getContext('2d'), {
        type: 'bar',
        data: {
            labels: top.map(c => c.nombre),
            datasets: [{ label:'Promedio', data: top.map(c => c.promedio),
                backgroundColor: top.map(c => c.promedio >= 7 ? MC.green+'cc' : c.promedio >= 5 ? MC.amber+'cc' : MC.red+'cc'),
                borderRadius: 4, borderSkipped: false }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { min:0, max:10, grid: { color: '#f3f4f6' }, ticks:{ font:{size:10} } },
                y: { ticks:{ font:{size:10} } }
            }
        }
    });
}

// ─────────────────────────────────────────────────────────────────
// SECCIÓN: ALUMNOS
// ─────────────────────────────────────────────────────────────────

async function mc_loadAlumnos() {
    mc_loading('mc-alumnos-content');
    try {
        const d = await mc_fetch('metrics/alumnos.php');
        mc_renderAlumnos(d);
    } catch(e) {
        mc_error('mc-alumnos-content', 'No se pudieron cargar los datos de alumnos.');
    }
}

function mc_renderAlumnos(d) {
    document.getElementById('mc-alumnos-content').innerHTML = `
    <div class="kpi-grid">
        <div class="kpi-card blue">
            <div class="kpi-label">Total alumnos</div>
            <div class="kpi-value">${d.total}</div>
        </div>
        <div class="kpi-card green">
            <div class="kpi-label">Activos</div>
            <div class="kpi-value">${d.activos}</div>
        </div>
        <div class="kpi-card red">
            <div class="kpi-label">Inactivos</div>
            <div class="kpi-value">${d.inactivos}</div>
        </div>
        <div class="kpi-card ${mc_nota_color(d.promedio_general)}">
            <div class="kpi-label">Promedio general</div>
            <div class="kpi-value">${d.promedio_general ?? '—'}</div>
        </div>
        <div class="kpi-card ${d.promedio_asistencia !== null ? mc_pct_color(d.promedio_asistencia) : ''}">
            <div class="kpi-label">Prom. asistencia</div>
            <div class="kpi-value">${d.promedio_asistencia !== null ? d.promedio_asistencia+'%' : '—'}</div>
        </div>
        <div class="kpi-card ${mc_pct_color(d.pct_aprobados)}">
            <div class="kpi-label">% Aprobados</div>
            <div class="kpi-value">${d.pct_aprobados}<span style="font-size:.9rem">%</span></div>
            <div class="kpi-sub">${d.aprobados} de ${d.aprobados+d.desaprobados}</div>
        </div>
        <div class="kpi-card purple">
            <div class="kpi-label">Ocupación</div>
            <div class="kpi-value">${d.pct_ocupacion !== null ? d.pct_ocupacion+'%' : '—'}</div>
            <div class="kpi-sub">vs cap. ${d.capacidad_total}</div>
        </div>
    </div>
    <div class="charts-grid thirds">
        <div class="chart-card">
            <div class="chart-title">Activos vs Inactivos</div>
            <div class="chart-wrap short"><canvas id="chart-al-activos"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-title">Aprobados vs Desaprobados</div>
            <div class="chart-wrap short"><canvas id="chart-al-estados"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-title">Distribución de rendimiento</div>
            <div class="chart-wrap short"><canvas id="chart-al-dist"></canvas></div>
        </div>
    </div>`;

    mc_destroyChart('al-activos');
    MetricCharts['al-activos'] = new Chart(
        document.getElementById('chart-al-activos').getContext('2d'), {
        type: 'doughnut',
        data: { labels:['Activos','Inactivos'], datasets:[{ data:[d.activos,d.inactivos], backgroundColor:[MC.green, MC.muted], borderWidth:0 }] },
        options: { plugins:{legend:{position:'bottom',labels:{font:{size:11},padding:8}}}, cutout:'60%' }
    });

    mc_destroyChart('al-estados');
    MetricCharts['al-estados'] = new Chart(
        document.getElementById('chart-al-estados').getContext('2d'), {
        type: 'doughnut',
        data: { labels:['Aprobados','Desaprobados'], datasets:[{ data:[d.aprobados,d.desaprobados], backgroundColor:[MC.green, MC.red], borderWidth:0 }] },
        options: { plugins:{legend:{position:'bottom',labels:{font:{size:11},padding:8}}}, cutout:'60%' }
    });

    const dist = d.distribucion_rendimiento || {};
    mc_destroyChart('al-dist');
    MetricCharts['al-dist'] = new Chart(
        document.getElementById('chart-al-dist').getContext('2d'), {
        type: 'bar',
        data: {
            labels: Object.keys(dist),
            datasets:[{ label:'Alumnos', data:Object.values(dist),
                backgroundColor: [MC.red+'cc',MC.amber+'cc',MC.amber+'99',MC.green+'cc',MC.teal+'cc'],
                borderRadius:4, borderSkipped:false }]
        },
        options: { plugins:{legend:{display:false}}, scales:{ x:{grid:{display:false},ticks:{font:{size:10}}}, y:{grid:{color:'#f3f4f6'},ticks:{font:{size:10}}} } }
    });
}

// ─────────────────────────────────────────────────────────────────
// SECCIÓN: CURSOS
// ─────────────────────────────────────────────────────────────────

async function mc_loadCursos() {
    mc_loading('mc-cursos-content');
    try {
        const d = await mc_fetch('metrics/cursos.php');
        mc_renderCursos(d);
    } catch(e) {
        mc_error('mc-cursos-content', 'No se pudieron cargar los datos de cursos.');
    }
}

function mc_renderCursos(d) {
    const cursos  = d.cursos || [];
    const hl      = d.highlights || {};
    const top15   = cursos.filter(c => c.promedio !== null).slice(0,15);
    const porAlum = [...cursos].sort((a,b)=>b.cant_alumnos-a.cant_alumnos).slice(0,15);

    // Chips de highlights
    const hlHtml = hl ? `
    <div class="highlights-row">
        ${hl.mejor_curso ? `<div class="highlight-chip">🏆 <span class="hl-label">Mejor promedio:</span> ${hl.mejor_curso}</div>` : ''}
        ${hl.peor_curso  ? `<div class="highlight-chip">📉 <span class="hl-label">Menor promedio:</span> ${hl.peor_curso}</div>` : ''}
        ${hl.mayor_asistencia ? `<div class="highlight-chip">✅ <span class="hl-label">Mayor asistencia:</span> ${hl.mayor_asistencia}</div>` : ''}
        ${hl.menor_asistencia ? `<div class="highlight-chip">⚠️ <span class="hl-label">Menor asistencia:</span> ${hl.menor_asistencia}</div>` : ''}
        ${hl.mas_alumnos ? `<div class="highlight-chip">👥 <span class="hl-label">Más alumnos:</span> ${hl.mas_alumnos}</div>` : ''}
    </div>` : '';

    // Tabla de ranking
    const tablaRows = cursos.map((c,i) => `
        <tr>
            <td><span class="rank-badge ${mc_rank_class(i)}">${i+1}</span></td>
            <td style="font-weight:600">${c.nombre}</td>
            <td>${c.turno ?? '—'}</td>
            <td>${c.cant_alumnos}</td>
            <td>
                ${c.promedio !== null
                    ? `<span class="nota-pill ${mc_nota_color(c.promedio)}">${c.promedio}</span>`
                    : '<span style="color:var(--muted)">—</span>'}
            </td>
            <td>
                ${c.prom_asistencia !== null ? `
                <div class="prog-bar-wrap">
                    <div class="prog-bar"><div class="prog-bar-fill ${mc_pct_color(c.prom_asistencia)}" style="width:${c.prom_asistencia}%"></div></div>
                    <span style="font-size:.72rem;font-weight:600;color:var(--text2);min-width:32px">${c.prom_asistencia}%</span>
                </div>` : '<span style="color:var(--muted)">—</span>'}
            </td>
            <td>
                ${c.pct_aprobacion !== null
                    ? `<span class="estado-badge ${c.pct_aprobacion>=60?'aprobado':'desaprobado'}">${c.pct_aprobacion}%</span>`
                    : '<span style="color:var(--muted)">—</span>'}
            </td>
            <td style="color:var(--muted)">${c.entregas_total}</td>
        </tr>`).join('');

    document.getElementById('mc-cursos-content').innerHTML = `
    ${hlHtml}
    <div class="charts-grid" style="margin-bottom:1.25rem">
        <div class="chart-card">
            <div class="chart-title">Promedio por curso (top 15)</div>
            <div class="chart-wrap tall"><canvas id="chart-cur-prom"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-title">Alumnos por curso (top 15)</div>
            <div class="chart-wrap tall"><canvas id="chart-cur-alum"></canvas></div>
        </div>
    </div>
    <div class="metrics-sec-header">
        <div class="metrics-sec-title">Ranking completo de cursos</div>
    </div>
    <div class="metrics-table-wrap">
        <table class="metrics-table">
            <thead>
                <tr>
                    <th>#</th><th>Curso</th><th>Turno</th><th>Alumnos</th>
                    <th>Promedio</th><th>Asistencia</th><th>% Aprobación</th><th>Entregas</th>
                </tr>
            </thead>
            <tbody>${tablaRows}</tbody>
        </table>
    </div>`;

    // Gráfico promedios
    mc_destroyChart('cur-prom');
    MetricCharts['cur-prom'] = new Chart(
        document.getElementById('chart-cur-prom').getContext('2d'), {
        type: 'bar',
        data: {
            labels: top15.map(c=>c.nombre),
            datasets:[{ label:'Promedio', data:top15.map(c=>c.promedio),
                backgroundColor: top15.map(c=>c.promedio>=7?MC.green+'cc':c.promedio>=5?MC.amber+'cc':MC.red+'cc'),
                borderRadius:4, borderSkipped:false }]
        },
        options: {
            indexAxis:'y',
            plugins:{legend:{display:false}},
            scales:{ x:{min:0,max:10,grid:{color:'#f3f4f6'},ticks:{font:{size:10}}}, y:{ticks:{font:{size:10}}} }
        }
    });

    // Gráfico alumnos
    mc_destroyChart('cur-alum');
    MetricCharts['cur-alum'] = new Chart(
        document.getElementById('chart-cur-alum').getContext('2d'), {
        type: 'bar',
        data: {
            labels: porAlum.map(c=>c.nombre),
            datasets:[{ label:'Alumnos', data:porAlum.map(c=>c.cant_alumnos),
                backgroundColor: MC.navy+'cc', borderRadius:4, borderSkipped:false }]
        },
        options: {
            indexAxis:'y',
            plugins:{legend:{display:false}},
            scales:{ x:{grid:{color:'#f3f4f6'},ticks:{font:{size:10}}}, y:{ticks:{font:{size:10}}} }
        }
    });
}

// ─────────────────────────────────────────────────────────────────
// SECCIÓN: AULAS
// ─────────────────────────────────────────────────────────────────

async function mc_loadAulas() {
    mc_loading('mc-aulas-content');
    try {
        const d = await mc_fetch('metrics/aulas.php');
        mc_renderAulas(d);
    } catch(e) {
        mc_error('mc-aulas-content', 'No se pudieron cargar los datos de aulas.');
    }
}

function mc_renderAulas(d) {
    const aulas = d.aulas || [];

    const filas = aulas.map((a,i) => `
        <tr>
            <td style="font-weight:600">${a.nombre}</td>
            <td>${a.ubicacion ?? '—'}</td>
            <td>${a.capacidad}</td>
            <td>${a.cant_alumnos}</td>
            <td>
                <div class="prog-bar-wrap">
                    <div class="prog-bar"><div class="prog-bar-fill ${mc_pct_color(a.pct_ocupacion)}" style="width:${Math.min(a.pct_ocupacion,100)}%"></div></div>
                    <span style="font-size:.72rem;font-weight:600;color:var(--text2);min-width:36px">${a.pct_ocupacion}%</span>
                </div>
            </td>
            <td>${a.curso_nombre ?? '<span style="color:var(--muted)">Sin asignar</span>'}</td>
        </tr>`).join('');

    document.getElementById('mc-aulas-content').innerHTML = `
    <div class="kpi-grid" style="margin-bottom:1.25rem">
        <div class="kpi-card blue">
            <div class="kpi-label">Total aulas</div>
            <div class="kpi-value">${d.total_aulas}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Capacidad total</div>
            <div class="kpi-value">${d.capacidad_total}</div>
            <div class="kpi-sub">vacantes</div>
        </div>
        <div class="kpi-card ${mc_pct_color(d.pct_ocupacion_gral)}">
            <div class="kpi-label">% Ocupación gral.</div>
            <div class="kpi-value">${d.pct_ocupacion_gral}<span style="font-size:.9rem">%</span></div>
            <div class="kpi-sub">${d.alumnos_total} alumnos asignados</div>
        </div>
        <div class="kpi-card amber">
            <div class="kpi-label">Sin asignar</div>
            <div class="kpi-value">${d.aulas_sin_asignar}</div>
            <div class="kpi-sub">aulas libres</div>
        </div>
        ${d.aula_mas_usada ? `<div class="kpi-card green"><div class="kpi-label">Más ocupada</div><div class="kpi-value small">${d.aula_mas_usada}</div></div>` : ''}
        ${d.aula_menos_usada ? `<div class="kpi-card red"><div class="kpi-label">Menos ocupada</div><div class="kpi-value small">${d.aula_menos_usada}</div></div>` : ''}
    </div>
    <div class="charts-grid single" style="margin-bottom:1.25rem">
        <div class="chart-card">
            <div class="chart-title">% Ocupación por aula</div>
            <div class="chart-wrap" style="height:${Math.max(180,aulas.length*28)}px"><canvas id="chart-aulas-ocu"></canvas></div>
        </div>
    </div>
    <div class="metrics-sec-header"><div class="metrics-sec-title">Detalle de aulas</div></div>
    <div class="metrics-table-wrap">
        <table class="metrics-table">
            <thead><tr><th>Aula</th><th>Ubicación</th><th>Capacidad</th><th>Alumnos</th><th>Ocupación</th><th>Curso asignado</th></tr></thead>
            <tbody>${filas}</tbody>
        </table>
    </div>`;

    mc_destroyChart('aulas-ocu');
    MetricCharts['aulas-ocu'] = new Chart(
        document.getElementById('chart-aulas-ocu').getContext('2d'), {
        type: 'bar',
        data: {
            labels: aulas.map(a=>a.nombre),
            datasets:[{
                label:'% Ocupación',
                data: aulas.map(a=>a.pct_ocupacion),
                backgroundColor: aulas.map(a=>a.pct_ocupacion>=70?MC.green+'cc':a.pct_ocupacion>=40?MC.amber+'cc':MC.red+'cc'),
                borderRadius:4, borderSkipped:false
            }]
        },
        options: {
            indexAxis:'y',
            plugins:{legend:{display:false}},
            scales:{ x:{min:0,max:100,grid:{color:'#f3f4f6'},ticks:{font:{size:10},callback:v=>v+'%'}}, y:{ticks:{font:{size:10}}} }
        }
    });
}

// ─────────────────────────────────────────────────────────────────
// SECCIÓN: NOTAS / MATERIAS
// ─────────────────────────────────────────────────────────────────

// Opciones para filtros (se cargan al iniciar)
window._mc_cursos_opts = [];

async function mc_loadNotasOpts() {
    try {
        const d = await mc_fetch('metrics/cursos.php');
        window._mc_cursos_opts = d.cursos || [];
        const sel = document.getElementById('mc-notas-f-curso');
        if (!sel) return;
        sel.innerHTML = '<option value="">Todos los cursos</option>' +
            (d.cursos||[]).map(c=>`<option value="${c.id}">${c.nombre}</option>`).join('');
    } catch(e) {}
}

async function mc_loadNotas() {
    mc_loading('mc-notas-content');
    await mc_loadNotasOpts();
    await mc_fetchNotas();
}

async function mc_fetchNotas() {
    const curso   = document.getElementById('mc-notas-f-curso')?.value || '';
    const materia = document.getElementById('mc-notas-f-materia')?.value || '';
    const params  = new URLSearchParams();
    if (curso)   params.set('curso_id', curso);
    if (materia) params.set('materia_id', materia);

    try {
        const d = await mc_fetch('metrics/notas.php?' + params.toString());
        mc_renderNotas(d, curso);
    } catch(e) {
        mc_error('mc-notas-content', 'No se pudieron cargar los datos de notas.');
    }
}

function mc_renderNotas(d, cursoFiltrado) {
    const mats  = d.materias || [];
    const epcs  = d.entregas_por_curso || [];

    const filasMat = mats.map((m,i) => `
        <tr>
            <td><span class="rank-badge ${mc_rank_class(i)}">${i+1}</span></td>
            <td style="font-weight:600">${m.nombre}</td>
            <td>${m.cant_notas}</td>
            <td>${m.promedio !== null
                ? `<span class="nota-pill ${mc_nota_color(m.promedio)}">${m.promedio}</span>`
                : '<span style="color:var(--muted)">—</span>'}</td>
            <td>${m.prom_asistencia !== null ? m.prom_asistencia+'%' : '—'}</td>
            <td><span class="estado-badge aprobado">${m.aprobados}</span></td>
            <td><span class="estado-badge desaprobado">${m.desaprobados}</span></td>
            <td>${m.pct_aprobacion !== null
                ? `<span class="estado-badge ${m.pct_aprobacion>=60?'aprobado':'desaprobado'}">${m.pct_aprobacion}%</span>`
                : '—'}</td>
        </tr>`).join('');

    const filasEpc = epcs.map(e => `
        <tr>
            <td style="font-weight:600">${e.curso_nombre}</td>
            <td>${e.total}</td>
            <td>${e.realizadas}</td>
            <td>${e.total>0 ? Math.round(e.realizadas/e.total*100)+'%' : '—'}</td>
        </tr>`).join('');

    document.getElementById('mc-notas-content').innerHTML = `
    <div class="metrics-filters">
        <label>Filtrar:</label>
        <select id="mc-notas-f-curso" onchange="mc_fetchNotas()">
            <option value="">Todos los cursos</option>
            ${window._mc_cursos_opts.map(c=>`<option value="${c.id}" ${c.id===cursoFiltrado?'selected':''}>${c.nombre}</option>`).join('')}
        </select>
    </div>
    <div class="kpi-grid" style="margin-bottom:1.25rem">
        <div class="kpi-card ${mc_pct_color(d.pct_aprobacion??0)}">
            <div class="kpi-label">% Aprobación gral.</div>
            <div class="kpi-value">${d.pct_aprobacion ?? '—'}<span style="font-size:.9rem">%</span></div>
        </div>
        <div class="kpi-card green">
            <div class="kpi-label">Total aprobados</div>
            <div class="kpi-value">${d.total_aprobados}</div>
        </div>
        <div class="kpi-card red">
            <div class="kpi-label">Total desaprobados</div>
            <div class="kpi-value">${d.total_desaprobados}</div>
        </div>
        <div class="kpi-card teal">
            <div class="kpi-label">Materias con datos</div>
            <div class="kpi-value">${mats.length}</div>
        </div>
    </div>
    <div class="charts-grid" style="margin-bottom:1.25rem">
        <div class="chart-card">
            <div class="chart-title">Promedio por materia</div>
            <div class="chart-wrap" style="height:${Math.max(180,mats.length*22)}px"><canvas id="chart-notas-mat"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-title">Aprobados vs Desaprobados (total)</div>
            <div class="chart-wrap short"><canvas id="chart-notas-pie"></canvas></div>
        </div>
    </div>
    <div class="charts-grid single" style="margin-bottom:1.25rem">
        <div class="chart-card">
            <div class="chart-title">Entregas por curso</div>
            <div class="chart-wrap short"><canvas id="chart-notas-epc"></canvas></div>
        </div>
    </div>
    <div class="metrics-sec-header"><div class="metrics-sec-title">Detalle por materia</div></div>
    <div class="metrics-table-wrap">
        <table class="metrics-table">
            <thead><tr><th>#</th><th>Materia</th><th>Calificaciones</th><th>Promedio</th><th>Asistencia</th><th>Aprobados</th><th>Desaprobados</th><th>% Aprob.</th></tr></thead>
            <tbody>${filasMat || '<tr><td colspan="8" style="text-align:center;color:var(--muted);padding:2rem">Sin datos</td></tr>'}</tbody>
        </table>
    </div>
    ${epcs.length ? `
    <div class="metrics-sec-header" style="margin-top:1.25rem"><div class="metrics-sec-title">Entregas por curso</div></div>
    <div class="metrics-table-wrap">
        <table class="metrics-table">
            <thead><tr><th>Curso</th><th>Total entregas</th><th>Realizadas</th><th>% Entregado</th></tr></thead>
            <tbody>${filasEpc}</tbody>
        </table>
    </div>` : ''}`;

    // Chart: promedios por materia
    mc_destroyChart('notas-mat');
    if (mats.length) {
        MetricCharts['notas-mat'] = new Chart(
            document.getElementById('chart-notas-mat').getContext('2d'), {
            type:'bar',
            data:{
                labels: mats.map(m=>m.nombre.length>30?m.nombre.slice(0,28)+'…':m.nombre),
                datasets:[{ label:'Promedio', data:mats.map(m=>m.promedio),
                    backgroundColor: mats.map(m=>m.promedio>=7?MC.green+'cc':m.promedio>=5?MC.amber+'cc':MC.red+'cc'),
                    borderRadius:4, borderSkipped:false }]
            },
            options:{
                indexAxis:'y',
                plugins:{legend:{display:false}},
                scales:{x:{min:0,max:10,grid:{color:'#f3f4f6'},ticks:{font:{size:10}}},y:{ticks:{font:{size:9}}}}
            }
        });
    }

    // Chart: torta aprobación
    mc_destroyChart('notas-pie');
    MetricCharts['notas-pie'] = new Chart(
        document.getElementById('chart-notas-pie').getContext('2d'), {
        type:'doughnut',
        data:{ labels:['Aprobados','Desaprobados'], datasets:[{ data:[d.total_aprobados,d.total_desaprobados], backgroundColor:[MC.green,MC.red], borderWidth:0 }] },
        options:{ plugins:{legend:{position:'bottom',labels:{font:{size:11},padding:10}}}, cutout:'60%' }
    });

    // Chart: entregas por curso
    mc_destroyChart('notas-epc');
    if (epcs.length) {
        MetricCharts['notas-epc'] = new Chart(
            document.getElementById('chart-notas-epc').getContext('2d'), {
            type:'bar',
            data:{
                labels: epcs.map(e=>e.curso_nombre),
                datasets:[
                    { label:'Total', data:epcs.map(e=>e.total), backgroundColor:MC.navy+'88', borderRadius:3, borderSkipped:false },
                    { label:'Realizadas', data:epcs.map(e=>e.realizadas), backgroundColor:MC.green+'cc', borderRadius:3, borderSkipped:false },
                ]
            },
            options:{
                plugins:{legend:{position:'top',labels:{font:{size:10}}}},
                scales:{x:{grid:{display:false},ticks:{font:{size:9}}},y:{grid:{color:'#f3f4f6'},ticks:{font:{size:10}}}}
            }
        });
    }
}

// ─────────────────────────────────────────────────────────────────
// INICIALIZACIÓN — se llama cuando se navega a 'metricas'
// ─────────────────────────────────────────────────────────────────
function initMetrics() {
    metricsNav('general');
}
