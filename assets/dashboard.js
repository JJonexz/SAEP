const ROLE = window.ROLE, MY_ID = window.MY_ID;
const S={repos:[],activeRepo:null,repoPath:[],editorFile:null,editorSha:null,users:[],courses:[],rooms:[],isPrivate:false};

// ── Nav ────────────────────────────────────────────────────────────────────
function nav(id){
    document.querySelectorAll('.panel').forEach(p=>p.classList.remove('visible'));
    document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
    document.getElementById('panel-'+id)?.classList.add('visible');
    document.getElementById('nav-'+id)?.classList.add('active');
    const loaders={repos:loadRepos,users:loadUsers,courses:loadCourses,rooms:loadRooms,grades:initGrades,'my-grades':loadMyGrades,'my-works':loadMyWorks,inicio:loadInicio,mail:initMail};
    loaders[id]?.();
}

// ── API ────────────────────────────────────────────────────────────────────
async function api(url,opts={}){
    const r=await fetch(url,opts);
    if(r.status===401){location.href='index.php';throw new Error('unauth');}
    return r;
}

// ── INICIO ─────────────────────────────────────────────────────────────────
async function loadInicio(){
    const [cRes,rRes]=await Promise.all([api('api/courses/courses.php'),api('api/rooms/rooms.php')]);
    S.courses=await cRes.json(); S.rooms=await rRes.json();
    let usersData=[];
    if(['admin','director','subdirector'].includes(ROLE)){const uRes=await api('api/admin/users.php');usersData=await uRes.json();S.users=usersData;}
    const pending=usersData.filter(u=>u.status==='pending_approval');
    const approved=usersData.filter(u=>u.status==='approved');
    document.getElementById('stats-row').innerHTML=`
        <div class="stat"><div class="stat-val">${approved.length||'—'}</div><div class="stat-lbl">Usuarios activos</div></div>
        <div class="stat"><div class="stat-val" style="color:${pending.length>0?'var(--amber)':'var(--navy)'}">${pending.length}</div><div class="stat-lbl">Pendientes aprobación</div></div>
        <div class="stat"><div class="stat-val">${S.courses.length}</div><div class="stat-lbl">Cursos</div></div>
        <div class="stat"><div class="stat-val">${S.rooms.length}</div><div class="stat-lbl">Aulas</div></div>`;
    const alertsEl=document.getElementById('pending-alerts');
    if(pending.length>0&&['admin','director','subdirector'].includes(ROLE)){
        alertsEl.innerHTML=`<div class="alert alert-amber">Hay <strong>${pending.length}</strong> usuario(s) pendientes de aprobación. <a href="#" onclick="nav('users');document.getElementById('uf-status').value='pending_approval';filterUsers()" style="color:var(--amber);font-weight:600">Revisar →</a></div>`;
    } else alertsEl.innerHTML='';
}

// ── REPOS ──────────────────────────────────────────────────────────────────
async function loadRepos(){
    document.getElementById('repo-sidebar').innerHTML='<div class="empty">Cargando...</div>';
    const r=await api('api/repos/getRepos.php');
    S.repos=await r.json(); renderRepoSidebar();
}
const LC={JavaScript:'js-c',Python:'py-c',PHP:'php-c',HTML:'html-c',CSS:'css-c'};
function renderRepoSidebar(){
    const el=document.getElementById('repo-sidebar');
    if(!S.repos.length){el.innerHTML='<div class="empty">Sin repositorios</div>';return;}
    el.innerHTML=S.repos.map(r=>`<div class="repo-item${S.activeRepo?.full_name===r.full_name?' active':''}" onclick='selectRepo(${JSON.stringify(r)})'>
        <div class="repo-item-name">${r.name}</div>
        <div style="display:flex;gap:.35rem;margin-top:.3rem;align-items:center">
            <span class="tag ${r.private?'tag-priv':'tag-pub'}">${r.private?'privado':'público'}</span>
            ${r.language?`<span class="lang-dot ${LC[r.language]||''}"></span><span style="font-size:.68rem;color:var(--muted)">${r.language}</span>`:''}
        </div></div>`).join('');
}
function selectRepo(repo){S.activeRepo=repo;S.repoPath=[];S.editorFile=null;renderRepoSidebar();renderRepoHeader();loadFiles();}
function renderRepoHeader(){
    const r=S.activeRepo;
    document.getElementById('repo-main-area').innerHTML=`
        <div style="padding:1rem 1.25rem;background:var(--white);border-bottom:1px solid var(--border);display:flex;align-items:flex-start;justify-content:space-between;gap:1rem">
            <div><div style="font-weight:700;font-size:.95rem;color:var(--text)">${r.name}</div><div style="font-size:.75rem;color:var(--muted);margin-top:.2rem">${r.description||'Sin descripción'}</div></div>
            <div class="btn-group"><button class="btn btn-outline" onclick="openNewFileModal()">+ Archivo</button><button class="btn btn-red" onclick="openDeleteRepoModal()">Eliminar</button></div>
        </div>
        <div id="repo-bc" class="breadcrumb"></div>
        <div id="repo-files"></div>`;
}
async function loadFiles(){
    const path=S.repoPath.join('/'); renderBreadcrumb();
    document.getElementById('repo-files').innerHTML='<div class="empty">Cargando...</div>';
    const r=await api(`api/repos/getFiles.php?repo=${encodeURIComponent(S.activeRepo.full_name)}&path=${encodeURIComponent(path)}`);
    renderFiles(await r.json());
}
function renderBreadcrumb(){
    const parts=[{label:S.activeRepo.name,i:-1},...S.repoPath.map((p,i)=>({label:p,i}))];
    document.getElementById('repo-bc').innerHTML=parts.map((p,idx)=>idx<parts.length-1?`<span onclick="navPath(${p.i})">${p.label}</span><span class="sep">/</span>`:`<span style="color:var(--text)">${p.label}</span>`).join('');
}
function navPath(i){S.repoPath=i===-1?[]:S.repoPath.slice(0,i+1);S.editorFile=null;loadFiles();}
function renderFiles(files){
    const el=document.getElementById('repo-files');
    if(!files.length){el.innerHTML='<div class="empty">Carpeta vacía</div>';return;}
    el.innerHTML=files.map(f=>{
        const icon=f.type==='dir'?`<svg width="15" height="15" fill="var(--navy)" viewBox="0 0 16 16"><path d="M1.75 1A1.75 1.75 0 000 2.75v10.5C0 14.216.784 15 1.75 15h12.5A1.75 1.75 0 0016 13.25v-8.5A1.75 1.75 0 0014.25 3H7.5L6.562 1.562A1.75 1.75 0 005.25 1H1.75z"/></svg>`:`<svg width="15" height="15" fill="var(--muted)" viewBox="0 0 16 16"><path d="M3.75 1.5a.25.25 0 00-.25.25v11.5c0 .138.112.25.25.25h8.5a.25.25 0 00.25-.25V6H9.75A1.75 1.75 0 018 4.25V1.5H3.75zm5.75.56v2.19c0 .138.112.25.25.25h2.19L9.5 2.06zM2 1.75C2 .784 2.784 0 3.75 0h5.086c.464 0 .909.184 1.237.513l3.414 3.414c.329.328.513.773.513 1.237v8.086A1.75 1.75 0 0112.25 15h-8.5A1.75 1.75 0 012 13.25V1.75z"/></svg>`;
        const sz=f.type==='file'&&f.size?(f.size<1024?f.size+'B':(f.size/1024).toFixed(1)+'KB'):'';
        return `<div class="file-row" onclick='handleFile(${JSON.stringify(f)})'>${icon}<span class="fr-name">${f.name}</span><span class="fr-size">${sz}</span>${f.type==='file'?`<div class="fr-acts"><button class="fbt" onclick='event.stopPropagation();confirmDelFile(${JSON.stringify(f)})'>eliminar</button></div>`:''}
        </div>`;
    }).join('');
}
function handleFile(f){if(f.type==='dir'){S.repoPath.push(f.name);loadFiles();}else openEditor(f);}
async function openEditor(f){
    S.editorFile=f;
    document.getElementById('repo-files').innerHTML='<div class="empty">Cargando...</div>';
    const r=await api(`api/repos/getFileContent.php?repo=${encodeURIComponent(S.activeRepo.full_name)}&path=${encodeURIComponent(f.path)}`);
    const d=await r.json(); S.editorSha=d.sha;
    const ma=document.getElementById('repo-main-area');
    const top=ma.children[0].outerHTML;
    ma.innerHTML=top+`<div class="editor-wrap"><div class="editor-bar"><div><button style="background:none;border:none;color:var(--muted);font-family:var(--font);font-size:.75rem;cursor:pointer;font-weight:600" onclick="closeEditor()">← Volver</button>&nbsp;<span class="editor-filename">${f.path}</span></div><button class="btn btn-navy" onclick="saveFile()" id="save-btn">Guardar</button></div><textarea class="code" id="ed-area" spellcheck="false">${esc(d.content)}</textarea></div>`;
}
function closeEditor(){S.editorFile=null;S.editorSha=null;renderRepoHeader();loadFiles();}
async function saveFile(){
    const content=document.getElementById('ed-area').value;
    const r=await api('api/repos/saveFile.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({repo:S.activeRepo.full_name,path:S.editorFile.path,content,sha:S.editorSha,message:`Actualizado desde SAEP: ${S.editorFile.name}`})});
    const d=await r.json();
    if(d.success){S.editorSha=d.sha;const b=document.getElementById('save-btn');b.textContent='Guardado ✓';setTimeout(()=>b.textContent='Guardar',2000);}
    else alert('Error: '+d.error);
}
function openNewFileModal(){modal(`<h3>Nuevo archivo</h3><div class="field"><label>Nombre del archivo</label><input id="nf-n" placeholder="archivo.txt"></div><div class="modal-footer"><button class="btn btn-outline" onclick="closeModal()">Cancelar</button><button class="btn btn-navy" onclick="createFile()">Crear</button></div>`);setTimeout(()=>document.getElementById('nf-n')?.focus(),50);}
async function createFile(){const n=document.getElementById('nf-n').value.trim();if(!n)return;const path=[...S.repoPath,n].join('/');const r=await api('api/repos/saveFile.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({repo:S.activeRepo.full_name,path,content:'',message:`Creado desde SAEP: ${n}`})});const d=await r.json();closeModal();if(d.success)loadFiles();else alert('Error: '+d.error);}
function confirmDelFile(f){modal(`<h3>Eliminar "${f.name}"</h3><p style="font-size:.82rem;color:var(--muted);margin-bottom:1rem">Esta acción es irreversible.</p><div class="modal-footer"><button class="btn btn-outline" onclick="closeModal()">Cancelar</button><button class="btn btn-red" onclick='doDelFile(${JSON.stringify(f)})'>Eliminar</button></div>`);}
async function doDelFile(f){const r=await api('api/repos/deleteFile.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({repo:S.activeRepo.full_name,path:f.path,sha:f.sha})});const d=await r.json();closeModal();if(d.success)loadFiles();else alert('Error: '+d.error);}
function openCreateRepoModal(){S.isPrivate=false;modal(`<h3>Nuevo repositorio</h3><div class="field" style="margin-bottom:.75rem"><label>Nombre</label><input id="nr-n" placeholder="mi-proyecto"></div><div class="field" style="margin-bottom:.75rem"><label>Descripción (opcional)</label><input id="nr-d"></div><div class="field"><label>Visibilidad</label><div style="display:flex;gap:.5rem;margin-top:.35rem"><button class="btn btn-navy" id="vp" onclick="setVis(false)">Público</button><button class="btn btn-outline" id="vpr" onclick="setVis(true)">Privado</button></div></div><div class="modal-footer"><button class="btn btn-outline" onclick="closeModal()">Cancelar</button><button class="btn btn-navy" onclick="createRepo()">Crear</button></div>`);setTimeout(()=>document.getElementById('nr-n')?.focus(),50);}
function setVis(p){S.isPrivate=p;document.getElementById('vp').className=p?'btn btn-outline':'btn btn-navy';document.getElementById('vpr').className=p?'btn btn-navy':'btn btn-outline';}
async function createRepo(){const n=document.getElementById('nr-n').value.trim(),d=document.getElementById('nr-d').value.trim();if(!n)return;const r=await api('api/repos/createRepo.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({name:n,description:d,private:S.isPrivate})});const data=await r.json();closeModal();if(data.success)loadRepos();else alert('Error: '+data.error);}
function openDeleteRepoModal(){modal(`<h3>Eliminar repositorio</h3><p style="font-size:.82rem;color:var(--muted);margin-bottom:1rem">Escribí el nombre del repo para confirmar.</p><div class="field"><label>Nombre del repositorio</label><input id="drc" placeholder="${S.activeRepo.name}"></div><div class="modal-footer"><button class="btn btn-outline" onclick="closeModal()">Cancelar</button><button class="btn btn-red" onclick="doDelRepo()">Eliminar</button></div>`);}
async function doDelRepo(){if(document.getElementById('drc').value.trim()!==S.activeRepo.name){alert('El nombre no coincide.');return;}const r=await api('api/repos/deleteRepo.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({full_name:S.activeRepo.full_name})});const d=await r.json();closeModal();if(d.success){S.activeRepo=null;document.getElementById('repo-main-area').innerHTML='<div class="empty">Repositorio eliminado.</div>';loadRepos();}else alert('Error: '+d.error);}

// ── WORKS (staff) ──────────────────────────────────────────────────────────
async function initWorks(){
    if(!S.courses.length){const r=await api('api/courses/courses.php');S.courses=await r.json();}
    if(!S.users.length&&['admin','director','subdirector'].includes(ROLE)){const r=await api('api/admin/users.php');S.users=await r.json();}
    const sel=document.getElementById('wf-curso');
    if(sel){sel.innerHTML='<option value="">Todos</option>'+S.courses.map(c=>`<option value="${c.id}">${c.anio}° ${c.division} — ${c.nombre}</option>`).join('');}
    loadWorks();
}
async function loadWorks(){
    const cid=document.getElementById('wf-curso')?.value||'';
    const mid=document.getElementById('wf-materia')?.value||'';
    let url='api/works/works.php?';
    if(cid)url+=`curso_id=${cid}&`;
    if(mid)url+=`materia_id=${mid}&`;
    const r=await api(url); const works=await r.json();
    renderWorksList(works);
}
function renderWorksList(works){
    const el=document.getElementById('works-list');
    if(!works.length){el.innerHTML='<div class="empty">Sin trabajos creados.</div>';return;}
    el.innerHTML=works.map(w=>{
        const curso=S.courses.find(c=>c.id===w.curso_id);
        const mat=curso?.materias?.find(m=>m.id===w.materia_id);
        const stBadge=w.estado==='activo'?'badge-green':'badge-gray';
        return `<div class="work-card">
            <div class="work-card-header">
                <div><div class="work-title">${w.titulo}</div><div class="work-desc">${w.descripcion||'Sin descripción'}</div></div>
                <div class="btn-group">
                    <button class="btn btn-outline" onclick="openWorkDetail('${w.id}')">Ver entregas</button>
                    <button class="btn btn-red" onclick="deleteWork('${w.id}')">Eliminar</button>
                </div>
            </div>
            <div class="work-meta">
                <span class="badge ${stBadge}">${w.estado}</span>
                ${curso?`<span class="badge badge-blue">${curso.anio}° ${curso.division}</span>`:''}
                ${mat?`<span class="badge badge-gray">${mat.nombre}</span>`:''}
                ${w.fecha_entrega?`<span class="badge badge-amber">Entrega: ${w.fecha_entrega}</span>`:''}
                <span class="badge badge-gray">${w.submissions_count} alumnos</span>
            </div>
        </div>`;
    }).join('');
}
function openCreateWorkModal(){
    if(!S.courses.length){alert('Primero creá al menos un curso.');return;}
    modal(`<h3>Nuevo trabajo</h3>
        <div class="fgrid">
            <div class="field ffull"><label>Título</label><input id="nw-titulo" placeholder="Trabajo Práctico N°1"></div>
            <div class="field ffull"><label>Descripción</label><textarea id="nw-desc" rows="2" placeholder="Consigna del trabajo..."></textarea></div>
            <div class="field"><label>Curso</label><select id="nw-curso" onchange="nwLoadMaterias()"><option value="">Seleccionar...</option>${S.courses.map(c=>`<option value="${c.id}">${c.anio}° ${c.division} — ${c.nombre}</option>`).join('')}</select></div>
            <div class="field"><label>Materia</label><select id="nw-materia"><option value="">Seleccionar...</option></select></div>
            <div class="field"><label>Fecha de entrega (opcional)</label><input type="date" id="nw-fecha"></div>
        </div>
        <div class="err-msg" id="nw-err"></div>
        <div class="modal-footer"><button class="btn btn-outline" onclick="closeModal()">Cancelar</button><button class="btn btn-navy" onclick="createWork()">Crear</button></div>`);
}
function nwLoadMaterias(){const cid=document.getElementById('nw-curso').value;const c=S.courses.find(x=>x.id===cid);const sel=document.getElementById('nw-materia');sel.innerHTML='<option value="">Seleccionar...</option>'+(c?.materias||[]).map(m=>`<option value="${m.id}">${m.nombre}</option>`).join('');}
async function createWork(){
    const titulo=document.getElementById('nw-titulo').value.trim();
    const desc=document.getElementById('nw-desc').value.trim();
    const cid=document.getElementById('nw-curso').value;
    const mid=document.getElementById('nw-materia').value;
    const fecha=document.getElementById('nw-fecha').value;
    const err=document.getElementById('nw-err');
    if(!titulo||!cid||!mid){err.textContent='Título, curso y materia son obligatorios.';err.style.display='block';return;}
    const r=await api('api/works/works.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({titulo,descripcion:desc,curso_id:cid,materia_id:mid,fecha_entrega:fecha||null})});
    const d=await r.json();
    closeModal();if(d.success)loadWorks();else alert('Error: '+d.error);
}
async function openWorkDetail(workId){
    const r=await api(`api/works/works.php?id=${workId}`);
    const work=await r.json();
    const curso=S.courses.find(c=>c.id===work.curso_id);
    const submissions=work.submissions||[];
    const rows=submissions.map(s=>{
        const alumno=S.users.find(u=>u.id===s.alumno_id);
        const aName=alumno?`${alumno.apellido} ${alumno.nombre}`:s.alumno_id;
        const avg=s.nota_promedio;
        const avgColor=avg===null?'nota-pending':avg>=6?'nota-ok':'nota-fail';
        const estBadge=s.estado_calificacion==='aprobado'?'badge-green':s.estado_calificacion==='desaprobado'?'badge-red':'badge-amber';
        const profGrades=(s.notas_profesores||[]).map(g=>{const p=S.users.find(u=>u.id===g.profesor_id);return `<div style="font-size:.75rem;margin-top:.3rem;padding:.4rem .6rem;background:#f8f9fc;border-radius:4px"><strong>${p?p.apellido+' '+p.nombre:'Profesor'}</strong>: <span class="nota-val ${g.nota>=6?'nota-ok':'nota-fail'}">${g.nota??'—'}</span>${g.devolucion?`<div class="devolucion-box">${esc(g.devolucion)}</div>`:''}</div>`}).join('');
        return `<div class="sub-row" style="flex-direction:column;align-items:flex-start;gap:.35rem;padding:.75rem 0">
            <div style="display:flex;align-items:center;gap:.75rem;width:100%">
                <span class="sub-name">${aName}</span>
                ${s.entregado?'<span class="badge badge-green">Entregado</span>':'<span class="badge badge-gray">Sin entregar</span>'}
                ${avg!==null?`<span class="nota-val ${avgColor}">Promedio: ${avg}</span>`:''}
                <span class="badge ${estBadge}">${s.estado_calificacion.replace('_',' ')}</span>
                <button class="btn btn-outline" style="margin-left:auto;font-size:.7rem;padding:.25rem .6rem" onclick="openGradeForm('${workId}','${s.alumno_id}','${aName.replace(/'/g,"\\'")}')">Calificar</button>
            </div>
            ${s.entregado&&s.contenido?`<div style="font-size:.75rem;color:var(--muted)">Entregado: ${s.fecha_entrega||''}</div><div class="devolucion-box" style="width:100%">${esc(s.contenido)}</div>`:''}
            ${profGrades}
        </div>`;
    }).join('');
    modal(`<h3>${work.titulo}</h3><p style="font-size:.8rem;color:var(--muted);margin-bottom:1rem">${work.descripcion||''}</p><div class="submissions-list">${rows||'<div class="empty">Sin alumnos asignados</div>'}</div><div class="modal-footer"><button class="btn btn-outline" onclick="closeModal()">Cerrar</button></div>`,'640px');
}
function openGradeForm(workId,alumnoId,alumnoName){
    modal(`<h3>Calificar entrega</h3><p style="font-size:.82rem;color:var(--muted);margin-bottom:1rem">Alumno: <strong>${alumnoName}</strong></p>
        <div class="fgrid">
            <div class="field"><label>Nota (1–10)</label><input type="number" id="gf-nota" min="1" max="10" step="0.1" placeholder="7"></div>
            <div class="field ffull"><label>Devolución / comentario</label><textarea id="gf-dev" rows="3" placeholder="Escribí tu devolución al alumno..."></textarea></div>
        </div>
        <div class="err-msg" id="gf-err"></div>
        <div class="modal-footer"><button class="btn btn-outline" onclick="closeModal()">Cancelar</button><button class="btn btn-navy" onclick="submitGrade('${workId}','${alumnoId}')">Guardar calificación</button></div>`);
}
async function submitGrade(workId,alumnoId){
    const nota=document.getElementById('gf-nota').value;
    const dev=document.getElementById('gf-dev').value.trim();
    const err=document.getElementById('gf-err');
    if(!nota&&!dev){err.textContent='Ingresá al menos una nota o devolución.';err.style.display='block';return;}
    const r=await api('api/works/works.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'grade_submission',work_id:workId,alumno_id:alumnoId,nota:nota||null,devolucion:dev})});
    const d=await r.json();
    closeModal();
    if(d.success){alert(`Calificación guardada. Promedio actual: ${d.nota_promedio??'—'}`);loadWorks();}
    else alert('Error: '+d.error);
}
async function deleteWork(id){if(!confirm('¿Eliminar este trabajo?'))return;await api('api/works/works.php',{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});loadWorks();}

// ── MIS TRABAJOS (alumno) ──────────────────────────────────────────────────
async function loadMyWorks(){
    if(!S.courses.length){const r=await api('api/courses/courses.php');S.courses=await r.json();}
    const r=await api('api/works/works.php');
    const works=await r.json();
    const el=document.getElementById('my-works-list');
    if(!works.length){el.innerHTML='<div class="empty">No tenés trabajos asignados.</div>';return;}
    const rows=await Promise.all(works.map(async w=>{
        const wr=await api(`api/works/works.php?id=${w.id}`);
        const full=await wr.json();
        const sub=(full.submissions||[]).find(s=>s.alumno_id===MY_ID);
        const curso=S.courses.find(c=>c.id===w.curso_id);
        const mat=curso?.materias?.find(m=>m.id===w.materia_id);
        const avg=sub?.nota_promedio;
        const avgColor=avg===null?'nota-pending':avg>=6?'nota-ok':'nota-fail';
        const profDevs=(sub?.notas_profesores||[]).filter(g=>g.devolucion).map(g=>`<div style="font-size:.78rem;margin-top:.5rem"><strong>Devolución del profesor:</strong><div class="devolucion-box">${esc(g.devolucion)}</div></div>`).join('');
        return `<div class="work-card">
            <div class="work-card-header">
                <div><div class="work-title">${w.titulo}</div><div class="work-desc">${w.descripcion||''}</div></div>
                <div>${avg!==null?`<span class="nota-val ${avgColor}" style="font-size:1.3rem">${avg}</span><br><span style="font-size:.7rem;color:var(--muted)">promedio</span>`:''}</div>
            </div>
            <div class="work-meta">
                ${curso?`<span class="badge badge-blue">${curso.anio}° ${curso.division}</span>`:''}
                ${mat?`<span class="badge badge-gray">${mat.nombre}</span>`:''}
                ${w.fecha_entrega?`<span class="badge badge-amber">Entrega: ${w.fecha_entrega}</span>`:''}
                ${sub?.entregado?'<span class="badge badge-green">Entregado</span>':'<span class="badge badge-gray">Sin entregar</span>'}
                ${sub?.estado_calificacion?`<span class="badge ${sub.estado_calificacion==='aprobado'?'badge-green':sub.estado_calificacion==='desaprobado'?'badge-red':'badge-amber'}">${sub.estado_calificacion.replace('_',' ')}</span>`:''}
            </div>
            ${profDevs}
            ${!sub?.entregado?`<div style="margin-top:.75rem"><div class="field"><label>Tu entrega</label><textarea id="sub-${w.id}" rows="3" placeholder="Pegá tu código, respuesta o link..."></textarea></div><button class="btn btn-navy" style="margin-top:.5rem" onclick="submitWork('${w.id}')">Entregar</button></div>`:''}
        </div>`;
    }));
    el.innerHTML=rows.join('');
}
async function submitWork(workId){
    const content=document.getElementById('sub-'+workId)?.value.trim();
    if(!content){alert('Escribí tu entrega antes de enviar.');return;}
    const r=await api('api/works/works.php',{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({work_id:workId,contenido:content})});
    const d=await r.json();
    if(d.success)loadMyWorks();else alert('Error: '+d.error);
}

// ── GRADES ─────────────────────────────────────────────────────────────────
const CUATRI_LABEL={'1':'1° Informe','2':'1°','3':'2° Informe','4':'2°'};
function cuatriLabel(v){return CUATRI_LABEL[String(v)]||v+'°';}
function cuatriBase(v){return v?v.replace('_informe',''):'';}
async function initGrades(){
    if(!S.courses.length){const r=await api('api/courses/courses.php');S.courses=await r.json();}
    if(!S.users.length&&['admin','director','subdirector'].includes(ROLE)){const r=await api('api/admin/users.php');S.users=await r.json();}
    const gvC=document.getElementById('gv-curso'),gcC=document.getElementById('gc-curso');
    const opts='<option value="">Seleccionar curso...</option>'+S.courses.map(c=>`<option value="${c.id}">${c.anio}° ${c.division} — ${c.nombre}</option>`).join('');
    if(gvC)gvC.innerHTML=opts;
    if(gcC)gcC.innerHTML='<option value="">Seleccionar...</option>'+S.courses.map(c=>`<option value="${c.id}">${c.anio}° ${c.division} — ${c.nombre}</option>`).join('');
    // No cargar tabla hasta que se elija un curso
    const el=document.getElementById('grades-tbl');
    if(el)el.innerHTML='<div class="empty" style="padding:1.5rem">Seleccioná un curso para ver las calificaciones.</div>';
}
function gvOnCursoChange(){
    const cid=document.getElementById('gv-curso')?.value||'';
    const cuatriField=document.getElementById('gv-cuatri-field');
    const cuatriSel=document.getElementById('gv-cuatri');
    if(cid){
        if(cuatriField)cuatriField.style.display='';
    } else {
        if(cuatriField)cuatriField.style.display='none';
        if(cuatriSel)cuatriSel.value='';
        const el=document.getElementById('grades-tbl');
        if(el)el.innerHTML='<div class="empty" style="padding:1.5rem">Seleccioná un curso para ver las calificaciones.</div>';
        return;
    }
    loadGradesTable();
}
async function loadGradesTable(){
    const cid=document.getElementById('gv-curso')?.value||'';
    const qua=document.getElementById('gv-cuatri')?.value||'';
    const quaBase=cuatriBase(qua); // '1' o '2' o '' para la API
    let url='api/grades/grades.php?'; if(cid)url+=`curso_id=${cid}&`; if(quaBase)url+=`cuatrimestre=${quaBase}&`;
    const r=await api(url); let grades=await r.json();
    // Filtro fino en cliente: si se eligió un valor específico, filtrar por cuatrimestre exacto
    if(qua) grades=grades.filter(g=>String(g.cuatrimestre)===qua);
    const el=document.getElementById('grades-tbl');
    if(!grades.length){el.innerHTML='<div class="empty" style="padding:1.5rem">Sin calificaciones.</div>';return;}
    el.innerHTML=`<table><thead><tr><th>Alumno</th><th>Curso</th><th>Materia</th><th>Cuatri</th><th>Nota</th><th>Concepto</th><th>Asistencia</th><th>Estado</th><th></th></tr></thead><tbody>${grades.map(g=>{
        const al=S.users.find(u=>u.id===g.alumno_id);
        const co=S.courses.find(c=>c.id===g.curso_id);
        const ma=co?.materias?.find(m=>m.id===g.materia_id);
        const nc=g.nota!==null?(g.nota>=6?'nota-ok':'nota-fail'):'nota-pending';
        const estB=g.estado==='aprobado'?'badge-green':g.estado==='desaprobado'?'badge-red':'badge-amber';
        return `<tr><td>${al?al.apellido+' '+al.nombre:'—'}</td><td>${co?co.anio+'° '+co.division:'—'}</td><td>${ma?ma.nombre:'—'}</td><td>${cuatriLabel(g.cuatrimestre)}</td><td><span class="nota-val ${nc}">${g.nota??'—'}</span></td><td>${g.concepto||'—'}</td><td>${g.asistencia!=null?g.asistencia+'%':'—'}</td><td><span class="badge ${estB}">${g.estado}</span></td><td><button class="btn btn-red" style="font-size:.7rem;padding:.2rem .5rem" onclick="delGrade('${g.id}')">✕</button></td></tr>`;
    }).join('')}</tbody></table>`;
}
function gcLoadStudents(){
    const cid=document.getElementById('gc-curso').value;
    const c=S.courses.find(x=>x.id===cid);
    const asel=document.getElementById('gc-alumno'),msel=document.getElementById('gc-materia');
    if(!c){asel.innerHTML='<option>—</option>';msel.innerHTML='<option>—</option>';return;}
    const alumnos=S.users.filter(u=>c.alumnos?.includes(u.id));
    asel.innerHTML='<option value="">Seleccionar...</option>'+alumnos.map(a=>`<option value="${a.id}">${a.apellido} ${a.nombre}</option>`).join('');
    msel.innerHTML='<option value="">Seleccionar...</option>'+(c.materias||[]).map(m=>`<option value="${m.id}">${m.nombre}</option>`).join('');
}
async function saveGrade(){
    const body={alumno_id:document.getElementById('gc-alumno').value,curso_id:document.getElementById('gc-curso').value,materia_id:document.getElementById('gc-materia').value,cuatrimestre:document.getElementById('gc-cuatri').value,nota:document.getElementById('gc-nota').value||null,concepto:document.getElementById('gc-concepto').value||null,asistencia:document.getElementById('gc-asist').value||null,estado:document.getElementById('gc-estado').value};
    const err=document.getElementById('gc-err'); err.style.display='none';
    if(!body.alumno_id||!body.curso_id||!body.materia_id){err.textContent='Seleccioná curso, materia y alumno.';err.style.display='block';return;}
    const r=await api('api/grades/grades.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    const d=await r.json();
    if(d.success){loadGradesTable();document.getElementById('gc-nota').value='';document.getElementById('gc-asist').value='';}
    else{err.textContent=d.error||'Error';err.style.display='block';}
}
async function delGrade(id){if(!confirm('¿Eliminar esta calificación?'))return;await api('api/grades/grades.php',{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});loadGradesTable();}
function gTab(name){document.querySelectorAll('#panel-grades .tab').forEach(t=>t.classList.remove('active'));document.querySelectorAll('#panel-grades .tab-content').forEach(t=>t.classList.remove('visible'));event.target.classList.add('active');document.getElementById('gtab-'+name)?.classList.add('visible');}

// ── MIS NOTAS ──────────────────────────────────────────────────────────────
async function loadMyGrades(){
    if(!S.courses.length){const r=await api('api/courses/courses.php');S.courses=await r.json();}
    const qua=document.getElementById('mg-cuatri')?.value||'';
    const quaBase=cuatriBase(qua);
    let url=`api/grades/grades.php?alumno_id=${MY_ID}`; if(quaBase)url+=`&cuatrimestre=${quaBase}`;
    const r=await api(url); let grades=await r.json();
    if(qua) grades=grades.filter(g=>String(g.cuatrimestre)===qua);
    const el=document.getElementById('my-grades-tbl');
    if(!grades.length){el.innerHTML='<div class="empty" style="padding:1.5rem">Sin calificaciones registradas.</div>';return;}
    el.innerHTML=`<table><thead><tr><th>Materia</th><th>Curso</th><th>Cuatri</th><th>Nota</th><th>Concepto</th><th>Asistencia</th><th>Estado</th></tr></thead><tbody>${grades.map(g=>{
        const co=S.courses.find(c=>c.id===g.curso_id);
        const ma=co?.materias?.find(m=>m.id===g.materia_id);
        const nc=g.nota!==null?(g.nota>=6?'nota-ok':'nota-fail'):'nota-pending';
        const estB=g.estado==='aprobado'?'badge-green':g.estado==='desaprobado'?'badge-red':'badge-amber';
        return `<tr><td>${ma?ma.nombre:'—'}</td><td>${co?co.anio+'° '+co.division:'—'}</td><td>${cuatriLabel(g.cuatrimestre)}</td><td><span class="nota-val ${nc}">${g.nota??'—'}</span></td><td>${g.concepto||'—'}</td><td>${g.asistencia!=null?g.asistencia+'%':'—'}</td><td><span class="badge ${estB}">${g.estado}</span></td></tr>`;
    }).join('')}</tbody></table>`;
}

// ── USERS ──────────────────────────────────────────────────────────────────
async function loadUsers(){const r=await api('api/admin/users.php');S.users=await r.json();renderUsersTable();}
function filterUsers(){renderUsersTable();}
function renderUsersTable(){
    const f=document.getElementById('uf-status')?.value||'';
    const list=f?S.users.filter(u=>u.status===f):S.users;
    const stB={pending_profile:'badge-gray',pending_approval:'badge-amber',approved:'badge-green',rejected:'badge-red'};
    const stL={pending_profile:'Sin perfil',pending_approval:'Pendiente',approved:'Aprobado',rejected:'Rechazado'};
    document.getElementById('users-tbl').innerHTML=`<table><thead><tr><th>Nombre</th><th>Username</th><th>DNI</th><th>Email</th><th>Tel.</th><th>Tipo</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>${list.map(u=>`<tr>
        <td style="font-weight:500">${u.apellido||''} ${u.nombre||''}</td>
        <td style="color:var(--muted)">${u.username}</td>
        <td>${u.dni||'—'}</td><td>${u.email||'—'}</td><td>${u.telefono||'—'}</td>
        <td><span class="badge ${u.manual?'badge-amber':'badge-blue'}">${u.manual?'Manual':'GitHub'}</span></td>
        <td><select onchange="changeRole('${u.id}',this.value)" ${u.id===MY_ID?'disabled':''} style="font-family:var(--font);font-size:.75rem;padding:.25rem .4rem;border:1px solid var(--border);border-radius:4px">
            ${['admin','director','subdirector','profesor','preceptor','alumno'].map(r=>`<option value="${r}"${u.role===r?' selected':''}>${r}</option>`).join('')}
            ${!u.role?'<option value="" selected>Sin rol</option>':''}
        </select></td>
        <td><span class="badge ${stB[u.status]||'badge-gray'}">${stL[u.status]||u.status}</span></td>
        <td><div class="td-act">
            ${u.status==='pending_approval'?`<button class="btn btn-green" style="font-size:.7rem;padding:.25rem .55rem" onclick="approveUser('${u.id}')">Aprobar</button><button class="btn btn-red" style="font-size:.7rem;padding:.25rem .55rem" onclick="rejectUser('${u.id}')">Rechazar</button>`:''}
            ${u.id!==MY_ID?`<button class="btn btn-red" style="font-size:.7rem;padding:.25rem .55rem" onclick="deleteUser('${u.id}')">Eliminar</button>`:''}
        </div></td>
    </tr>`).join('')}</tbody></table>`;
}
async function approveUser(id){await api('api/admin/users.php',{method:'PATCH',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,status:'approved'})});loadUsers();loadInicio();}
async function rejectUser(id){await api('api/admin/users.php',{method:'PATCH',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,status:'rejected'})});loadUsers();loadInicio();}
async function changeRole(id,role){await api('api/admin/users.php',{method:'PATCH',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,role})});}
async function deleteUser(id){if(!confirm('¿Eliminar este usuario?'))return;await api('api/admin/users.php',{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});loadUsers();}
function openManualUserModal(){
    modal(`<h3>Crear usuario manualmente</h3>
        <div class="fgrid">
            <div class="field"><label>Nombre</label><input id="mu-n" placeholder="Juan"></div>
            <div class="field"><label>Apellido</label><input id="mu-a" placeholder="Pérez"></div>
            <div class="field"><label>DNI</label><input id="mu-dni" placeholder="12345678" maxlength="8"></div>
            <div class="field"><label>Email</label><input id="mu-email" type="email" placeholder="juan@escuela.edu.ar"></div>
            <div class="field"><label>Teléfono (opcional)</label><input id="mu-tel" placeholder="2241xxxxxx"></div>
            <div class="field"><label>Username (opcional)</label><input id="mu-user" placeholder="juan.perez"></div>
            <div class="field ffull"><label>Rol</label><select id="mu-role">${['alumno','profesor','preceptor','subdirector','director','admin'].map(r=>`<option value="${r}">${r}</option>`).join('')}</select></div>
        </div>
        <div class="err-msg" id="mu-err"></div>
        <div class="modal-footer"><button class="btn btn-outline" onclick="closeModal()">Cancelar</button><button class="btn btn-navy" onclick="createManualUser()">Crear usuario</button></div>`);
}
async function createManualUser(){
    const body={nombre:document.getElementById('mu-n').value.trim(),apellido:document.getElementById('mu-a').value.trim(),dni:document.getElementById('mu-dni').value.trim(),email:document.getElementById('mu-email').value.trim(),telefono:document.getElementById('mu-tel').value.trim(),username:document.getElementById('mu-user').value.trim(),role:document.getElementById('mu-role').value};
    const err=document.getElementById('mu-err'); err.style.display='none';
    if(!body.nombre||!body.apellido||!body.dni){err.textContent='Nombre, apellido y DNI son obligatorios.';err.style.display='block';return;}
    if(!/^\d{7,8}$/.test(body.dni)){err.textContent='DNI inválido (7 u 8 dígitos).';err.style.display='block';return;}
    const r=await api('api/admin/users.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    const d=await r.json();
    if(d.success){closeModal();loadUsers();}else{err.textContent=d.error||'Error';err.style.display='block';}
}

// ── COURSES ────────────────────────────────────────────────────────────────
async function loadCourses(){
    const r=await api('api/courses/courses.php');S.courses=await r.json();
    if(!S.users.length){const ur=await api('api/admin/users.php');S.users=await ur.json();}
    renderCoursesCards();
}
function renderCoursesCards(){
    const el=document.getElementById('courses-cards');
    if(!S.courses.length){el.innerHTML='<div class="empty" style="grid-column:1/-1">Sin cursos creados.</div>';return;}
    el.innerHTML=S.courses.map(c=>`<div class="card">
        <h3>${c.anio}° ${c.division}</h3>
        <p>${c.nombre}<br><span style="color:var(--muted)">Turno: ${c.turno}</span></p>
        <div class="card-meta"><span class="badge badge-blue">${c.alumnos?.length||0} alumnos</span><span class="badge badge-gray">${c.materias?.length||0} materias</span></div>
        <div class="card-actions"><button class="btn btn-outline" onclick='openCourseDetail(${JSON.stringify(c)})'>Gestionar</button><button class="btn btn-red" onclick="deleteCourse('${c.id}')">Eliminar</button></div>
    </div>`).join('');
}
function openCourseModal(){modal(`<h3>Nuevo curso</h3><div class="fgrid"><div class="field ffull"><label>Nombre</label><input id="cc-n" placeholder="3° Técnico Informática"></div><div class="field"><label>Año</label><input id="cc-a" type="number" min="1" max="7" placeholder="3"></div><div class="field"><label>División</label><input id="cc-d" placeholder="A"></div><div class="field ffull"><label>Turno</label><select id="cc-t"><option>Mañana</option><option>Tarde</option><option>Noche</option></select></div><div class="field ffull"><label>Orientación</label><select id="cc-o"><option>Ciclo Básico</option><option>MMO</option><option>Programación</option><option>Turismo</option></select></div></div><div class="modal-footer"><button class="btn btn-outline" onclick="closeModal()">Cancelar</button><button class="btn btn-navy" onclick="saveCourse()">Crear</button></div>`);}
async function saveCourse(){const b={nombre:document.getElementById('cc-n').value.trim(),anio:document.getElementById('cc-a').value,division:document.getElementById('cc-d').value.trim(),turno:document.getElementById('cc-t').value,orientacion:document.getElementById('cc-o').value};if(!b.nombre||!b.anio||!b.division)return;const r=await api('api/courses/courses.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(b)});const d=await r.json();closeModal();if(d.success)loadCourses();else alert('Error: '+d.error);}
async function deleteCourse(id){if(!confirm('¿Eliminar este curso?'))return;await api('api/courses/courses.php',{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});loadCourses();}
function openCourseDetail(course){
    const alumnos=S.users.filter(u=>course.alumnos?.includes(u.id));
    const profes=S.users.filter(u=>course.profesores?.includes(u.id));
    const allAlum=S.users.filter(u=>u.role==='alumno'&&u.status==='approved'&&!course.alumnos?.includes(u.id));
    const allProf=S.users.filter(u=>u.role==='profesor'&&u.status==='approved'&&!course.profesores?.includes(u.id));
    modal(`<h3>${course.anio}° ${course.division} — ${course.nombre}</h3>
        <div class="tabs"><div class="tab active" onclick="cdTab('alumnos')">Alumnos</div><div class="tab" onclick="cdTab('materias')">Materias</div><div class="tab" onclick="cdTab('profesores')">Profesores</div></div>
        <div class="tab-content visible" id="cd-alumnos">
            <div class="inline-form mb1"><div class="field"><label>Agregar alumno</label><select id="cd-as"><option value="">Seleccionar...</option>${allAlum.map(a=>`<option value="${a.id}">${a.apellido} ${a.nombre}</option>`).join('')}</select></div><button class="btn btn-navy" onclick="addToGroup('${course.id}','add_alumno','cd-as','user_id')">Agregar</button></div>
            <table><thead><tr><th>Alumno</th><th>DNI</th><th></th></tr></thead><tbody>${alumnos.map(a=>`<tr><td>${a.apellido} ${a.nombre}</td><td>${a.dni||'—'}</td><td><button class="btn btn-red" style="font-size:.7rem;padding:.2rem .5rem" onclick="removeFromGroup('${course.id}','remove_alumno','${a.id}')">Quitar</button></td></tr>`).join('')}</tbody></table>
        </div>
        <div class="tab-content" id="cd-materias">
            <div class="inline-form mb1">
                <div class="field"><label>Materia</label><input id="cd-mn" placeholder="Matemática"></div>
                <div class="field"><label>Profesor</label><select id="cd-mp"><option value="">Sin asignar</option>${S.users.filter(u=>u.role==='profesor'&&u.status==='approved').map(p=>`<option value="${p.id}">${p.apellido} ${p.nombre}</option>`).join('')}</select></div>
                <button class="btn btn-navy" onclick="addMateria('${course.id}')">Agregar</button>
            </div>
            <table><thead><tr><th>Materia</th><th>Profesor</th><th></th></tr></thead><tbody>${(course.materias||[]).map(m=>{const p=S.users.find(u=>u.id===m.profesor_id);return `<tr><td>${m.nombre}</td><td>${p?p.apellido+' '+p.nombre:'—'}</td><td><button class="btn btn-red" style="font-size:.7rem;padding:.2rem .5rem" onclick="removeMateria('${course.id}','${m.id}')">Quitar</button></td></tr>`}).join('')}</tbody></table>
        </div>
        <div class="tab-content" id="cd-profesores">
            <div class="inline-form mb1"><div class="field"><label>Agregar profesor</label><select id="cd-ps"><option value="">Seleccionar...</option>${allProf.map(p=>`<option value="${p.id}">${p.apellido} ${p.nombre}</option>`).join('')}</select></div><button class="btn btn-navy" onclick="addToGroup('${course.id}','add_profesor','cd-ps','user_id')">Agregar</button></div>
            <table><thead><tr><th>Profesor</th><th>Email</th><th></th></tr></thead><tbody>${profes.map(p=>`<tr><td>${p.apellido} ${p.nombre}</td><td>${p.email||'—'}</td><td><button class="btn btn-red" style="font-size:.7rem;padding:.2rem .5rem" onclick="removeFromGroup('${course.id}','remove_profesor','${p.id}')">Quitar</button></td></tr>`).join('')}</tbody></table>
        </div>
        <div class="modal-footer"><button class="btn btn-outline" onclick="closeModal()">Cerrar</button></div>`,'640px');
}
function cdTab(n){document.querySelectorAll('#modal-root .tab').forEach(t=>t.classList.remove('active'));document.querySelectorAll('#modal-root .tab-content').forEach(t=>t.classList.remove('visible'));event.target.classList.add('active');document.getElementById('cd-'+n)?.classList.add('visible');}
async function addToGroup(cid,action,selId,field){const val=document.getElementById(selId)?.value;if(!val)return;await api('api/courses/courses.php',{method:'PATCH',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:cid,action,[field]:val})});await loadCourses();const upd=S.courses.find(c=>c.id===cid);if(upd)openCourseDetail(upd);}
async function removeFromGroup(cid,action,uid){await api('api/courses/courses.php',{method:'PATCH',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:cid,action,user_id:uid})});await loadCourses();const upd=S.courses.find(c=>c.id===cid);if(upd)openCourseDetail(upd);}
async function addMateria(cid){const n=document.getElementById('cd-mn').value.trim(),pid=document.getElementById('cd-mp').value||null;if(!n)return;await api('api/courses/courses.php',{method:'PATCH',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:cid,action:'add_materia',materia:n,profesor_id:pid})});await loadCourses();const upd=S.courses.find(c=>c.id===cid);if(upd)openCourseDetail(upd);}
async function removeMateria(cid,mid){await api('api/courses/courses.php',{method:'PATCH',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:cid,action:'remove_materia',materia_id:mid})});await loadCourses();const upd=S.courses.find(c=>c.id===cid);if(upd)openCourseDetail(upd);}

// ── ROOMS ──────────────────────────────────────────────────────────────────
async function loadRooms(){
    const[rRes,cRes]=await Promise.all([api('api/rooms/rooms.php'),api('api/courses/courses.php')]);
    S.rooms=await rRes.json();S.courses=await cRes.json();
    if(!S.users.length){const ur=await api('api/admin/users.php');S.users=await ur.json();}
    const precs=S.users.filter(u=>u.role==='preceptor'&&u.status==='approved');
    document.getElementById('rooms-tbl').innerHTML=`<table><thead><tr><th>Aula</th><th>Ubicación</th><th>Cap.</th><th>Curso asignado</th><th>Preceptor</th><th></th></tr></thead><tbody>${S.rooms.map(r=>{
        return `<tr><td style="font-weight:600">${r.nombre}</td><td>${r.ubicacion||'—'}</td><td>${r.capacidad||'—'}</td>
            <td><select onchange="assignRC('${r.id}',this.value)" style="font-family:var(--font);font-size:.75rem;padding:.25rem .4rem;border:1px solid var(--border);border-radius:4px"><option value="">Sin asignar</option>${S.courses.map(c=>`<option value="${c.id}"${r.curso_id===c.id?' selected':''}>${c.anio}° ${c.division} — ${c.nombre}</option>`).join('')}</select></td>
            <td><select onchange="assignRP('${r.id}',this.value)" style="font-family:var(--font);font-size:.75rem;padding:.25rem .4rem;border:1px solid var(--border);border-radius:4px"><option value="">Sin asignar</option>${precs.map(p=>`<option value="${p.id}"${r.preceptor_id===p.id?' selected':''}>${p.apellido} ${p.nombre}</option>`).join('')}</select></td>
            <td><button class="btn btn-red" style="font-size:.7rem;padding:.25rem .5rem" onclick="deleteRoom('${r.id}')">Eliminar</button></td></tr>`;
    }).join('')}</tbody></table>`;
}
function openRoomModal(){modal(`<h3>Nueva aula</h3><div class="fgrid"><div class="field"><label>Nombre</label><input id="rm-n" placeholder="Aula 101"></div><div class="field"><label>Capacidad</label><input id="rm-c" type="number" placeholder="30"></div><div class="field ffull"><label>Ubicación</label><input id="rm-u" placeholder="Planta baja, ala norte"></div></div><div class="modal-footer"><button class="btn btn-outline" onclick="closeModal()">Cancelar</button><button class="btn btn-navy" onclick="saveRoom()">Crear</button></div>`);}
async function saveRoom(){const b={nombre:document.getElementById('rm-n').value.trim(),capacidad:document.getElementById('rm-c').value,ubicacion:document.getElementById('rm-u').value.trim()};if(!b.nombre)return;const r=await api('api/rooms/rooms.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(b)});const d=await r.json();closeModal();if(d.success)loadRooms();else alert('Error: '+d.error);}
async function assignRC(id,cid){await api('api/rooms/rooms.php',{method:'PATCH',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,curso_id:cid||null})});}
async function assignRP(id,pid){await api('api/rooms/rooms.php',{method:'PATCH',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,preceptor_id:pid||null})});}
async function deleteRoom(id){if(!confirm('¿Eliminar esta aula?'))return;await api('api/rooms/rooms.php',{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});loadRooms();}

// ── Modal helpers ──────────────────────────────────────────────────────────
function modal(html,maxW='540px'){document.getElementById('modal-root').innerHTML=`<div class="overlay" onclick="if(event.target===this)closeModal()"><div class="modal" style="max-width:${maxW}">${html}</div></div>`;}
function closeModal(){document.getElementById('modal-root').innerHTML='';}

// ── Utils ──────────────────────────────────────────────────────────────────
function esc(s){return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
document.addEventListener('keydown',e=>{
    if(e.key==='Escape')closeModal();
    if((e.ctrlKey||e.metaKey)&&e.key==='s'&&S.editorFile){e.preventDefault();saveFile();}
});

// ── CORREOS ────────────────────────────────────────────────────────────────
// Pegá este bloque completo al final de assets/dashboard.js

const ML = { selected: new Set(), filtered: [] };

function initMail() {
    if (!S.users.length) {
        api('api/admin/users.php').then(r => r.json()).then(u => { S.users = u; mlBuildList(); });
    } else {
        mlBuildList();
    }
}

function mlBuildList() {
    ML.filtered = [...S.users];
    mlRender();
}

function mlFilter() {
    const q     = (document.getElementById('ml-search')?.value || '').toLowerCase().replace(/,/g, '').replace(/\s+/g, ' ').trim();
    const rol   = document.getElementById('ml-f-rol')?.value   || '';
    const estado= document.getElementById('ml-f-estado')?.value || '';

    ML.filtered = S.users.filter(u => {
        if (rol    && u.role   !== rol)    return false;
        if (estado && u.status !== estado) return false;
        if (q) {
            const name  = ((u.apellido||'') + ' ' + (u.nombre||'')).toLowerCase();
            const email = (u.email||'').toLowerCase();
            const dni   = String(u.dni||'');
            const user  = (u.username||'').toLowerCase();
            if (!name.includes(q) && !email.includes(q) && !dni.includes(q) && !user.includes(q)) return false;
        }
        return true;
    });

    mlRender();
}

const ML_STATUS_LABEL = { approved:'Aprobado', pending_approval:'Pendiente', rejected:'Rechazado', pending_profile:'Sin perfil' };
const ML_STATUS_BADGE = { approved:'badge-green', pending_approval:'badge-amber', rejected:'badge-red', pending_profile:'badge-gray' };
const ML_ROLE_BADGE   = { admin:'badge-blue', director:'badge-blue', subdirector:'badge-blue', profesor:'badge-green', preceptor:'badge-amber', alumno:'badge-gray' };

function mlRender() {
    const el = document.getElementById('ml-user-list');
    if (!ML.filtered.length) {
        el.innerHTML = '<div class="empty" style="padding:1.5rem">Sin usuarios para mostrar.</div>';
        document.getElementById('ml-visible-count').textContent = '0 usuarios';
        document.getElementById('ml-chk-all').checked = false;
        mlUpdateCounter();
        return;
    }

    el.innerHTML = ML.filtered.map(u => {
        const name    = (u.apellido && u.nombre) ? `${u.apellido}, ${u.nombre}` : u.username;
        const checked = ML.selected.has(u.id) ? 'checked' : '';
        const noEmail = !u.email;
        const stBadge = ML_STATUS_BADGE[u.status] || 'badge-gray';
        const stLabel = ML_STATUS_LABEL[u.status] || u.status;
        const rlBadge = ML_ROLE_BADGE[u.role]   || 'badge-gray';

        return `<label style="display:flex;align-items:flex-start;gap:.6rem;padding:.55rem .75rem;border-bottom:1px solid var(--border);cursor:${noEmail?'default':'pointer'};${noEmail?'opacity:.45':''}">
            <input type="checkbox" ${checked} ${noEmail?'disabled':''} style="margin-top:.15rem;cursor:pointer" onchange="mlToggleUser('${u.id}',this.checked)">
            <div style="flex:1;min-width:0">
                <div style="font-size:.78rem;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(name)}</div>
                <div style="font-size:.7rem;color:var(--muted);margin-top:.1rem">${u.email ? esc(u.email) : 'Sin email'}</div>
                <div style="display:flex;gap:.3rem;margin-top:.3rem;flex-wrap:wrap">
                    <span class="badge ${rlBadge}" style="font-size:.6rem">${u.role||'—'}</span>
                    <span class="badge ${stBadge}" style="font-size:.6rem">${stLabel}</span>
                    ${u.dni ? `<span class="badge badge-gray" style="font-size:.6rem">DNI ${u.dni}</span>` : ''}
                </div>
            </div>
        </label>`;
    }).join('');

    document.getElementById('ml-visible-count').textContent = ML.filtered.length + ' usuario' + (ML.filtered.length !== 1 ? 's' : '');

    const allChecked = ML.filtered.filter(u => u.email).every(u => ML.selected.has(u.id));
    document.getElementById('ml-chk-all').checked = allChecked && ML.filtered.some(u => u.email);

    mlUpdateCounter();
    mlUpdateChips();
}

function mlToggleUser(id, checked) {
    if (checked) ML.selected.add(id);
    else ML.selected.delete(id);
    mlUpdateCounter();
    mlUpdateChips();
    // sync "select all" checkbox
    const allChecked = ML.filtered.filter(u => u.email).every(u => ML.selected.has(u.id));
    document.getElementById('ml-chk-all').checked = allChecked && ML.filtered.some(u => u.email);
}

function mlToggleAll(checked) {
    ML.filtered.filter(u => u.email).forEach(u => {
        if (checked) ML.selected.add(u.id);
        else ML.selected.delete(u.id);
    });
    mlRender();
}

function mlUpdateCounter() {
    const cnt = ML.selected.size;
    const el  = document.getElementById('ml-counter');
    if (!el) return;
    if (cnt === 0) { el.style.display = 'none'; return; }
    el.style.display = 'block';
    el.textContent   = cnt + ' seleccionado' + (cnt !== 1 ? 's' : '');
}

function mlUpdateChips() {
    const wrap  = document.getElementById('ml-chips');
    const inner = document.getElementById('ml-chips-inner');
    if (!wrap || !inner) return;
    if (ML.selected.size === 0) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';
    inner.innerHTML = [...ML.selected].map(id => {
        const u    = S.users.find(x => x.id === id);
        if (!u) return '';
        const name = (u.apellido && u.nombre) ? `${u.apellido} ${u.nombre}` : u.username;
        return `<span style="display:inline-flex;align-items:center;gap:.3rem;background:var(--navy-faint);color:var(--navy);font-size:.7rem;font-weight:600;padding:.2rem .5rem;border-radius:20px;border:1px solid #b8c8e8">
            ${esc(name)}
            <span onclick="mlRemoveChip('${id}')" style="cursor:pointer;font-size:.8rem;line-height:1;color:var(--muted)">×</span>
        </span>`;
    }).join('');
}

function mlRemoveChip(id) {
    ML.selected.delete(id);
    // uncheck in list
    const el = document.querySelector(`#ml-user-list input[onchange*="${id}"]`);
    if (el) el.checked = false;
    const allChecked = ML.filtered.filter(u => u.email).every(u => ML.selected.has(u.id));
    document.getElementById('ml-chk-all').checked = allChecked && ML.filtered.some(u => u.email);
    mlUpdateCounter();
    mlUpdateChips();
}

async function sendMail() {
    const subject = document.getElementById('ml-subject')?.value.trim();
    const message = document.getElementById('ml-body')?.value.trim();
    const err     = document.getElementById('ml-err');
    const ok      = document.getElementById('ml-ok');
    const btn     = document.getElementById('ml-btn');

    err.style.display = 'none';
    ok.style.display  = 'none';

    if (!ML.selected.size) { err.textContent = 'Seleccioná al menos un destinatario.'; err.style.display = 'block'; return; }
    if (!subject)          { err.textContent = 'El asunto es obligatorio.';            err.style.display = 'block'; return; }
    if (!message)          { err.textContent = 'El mensaje no puede estar vacío.';     err.style.display = 'block'; return; }

    btn.disabled    = true;
    btn.textContent = 'Enviando...';

    const r = await api('api/mail/send.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ ids: [...ML.selected], subject, message }),
    });
    const d = await r.json();

    btn.disabled  = false;
    btn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Enviar correo';

    if (d.success) {
        ok.textContent  = `✓ Enviado a ${d.sent} de ${d.total} destinatario(s).${d.failed.length ? ' Sin enviar: ' + d.failed.join(', ') : ''}`;
        ok.style.display = 'block';
        document.getElementById('ml-subject').value = '';
        document.getElementById('ml-body').value    = '';
        ML.selected.clear();
        mlRender();
    } else {
        err.textContent   = d.error || 'Error al enviar.';
        err.style.display = 'block';
    }
}

// En la función nav(), agregá 'mail' al objeto loaders:
// mail: () => { if(!S.users.length && ['admin','director','subdirector'].includes(ROLE)){api('api/admin/users.php').then(r=>r.json()).then(u=>S.users=u);} }
// O simplemente no hace falta porque los users ya se cargan al init.

// ── Init ───────────────────────────────────────────────────────────────────
loadInicio();
if(['admin','director','subdirector'].includes(ROLE)){api('api/admin/users.php').then(r=>r.json()).then(u=>S.users=u);}
api('api/courses/courses.php').then(r=>r.json()).then(c=>S.courses=c);