const usuarios = [
    {usuario:'maestro', pass:'maestro123', rol:'maestro', nombre:'Profesor José'},
    {usuario:'alumno1', pass:'alumno123', rol:'alumno', nombre:'Juan Perez', id:0},
    {usuario:'alumno2', pass:'alumno123', rol:'alumno', nombre:'Maria Lopez', id:1}
];
let sesion = null;
let estudiantes = [];
let materias = [];
let notas = [];

function verificarSesion() {
    let sesionJSON = localStorage.getItem('sesion');
    if (!sesionJSON) {
        window.location.href = 'index.html';
        return false;
    }
    sesion = JSON.parse(sesionJSON);
    cargarDatos();
    return true;
}

function cargarDatos() {
    let datos = localStorage.getItem('datos');
    if (datos) {
        let d = JSON.parse(datos);
        estudiantes = d.estudiantes || [];
        materias = d.materias || [];
        notas = d.notas || [];
    } else {
        estudiantes.push({nombre:'Juan', apellido:'Perez', curso:'1A'});
        estudiantes.push({nombre:'Maria', apellido:'Lopez', curso:'1B'});
        materias.push('Matemáticas');
        materias.push('Lengua');
        notas.push({estudiante:0, materia:0, nota:85});
        notas.push({estudiante:1, materia:1, nota:92});
        guardarDatos();
    }
}

function guardarDatos() {
    localStorage.setItem('datos', JSON.stringify({estudiantes, materias, notas}));
}

function mostrar(id) {
    document.querySelectorAll('.seccion').forEach(s => s.classList.remove('activa'));
    document.getElementById(id).classList.add('activa');
    if (id === 'notas') actualizar();
}

function agregarEstudiante() {
    if (sesion?.rol !== 'maestro') return;
    let n = document.getElementById('nombre').value;
    let a = document.getElementById('apellido').value;
    let c = document.getElementById('curso').value;
    estudiantes.push({ nombre: n, apellido: a, curso: c });
    document.getElementById('nombre').value = '';
    document.getElementById('apellido').value = '';
    document.getElementById('curso').value = '';
    guardarDatos();
    actualizar();
}

function agregarMateria() {
    if (sesion?.rol !== 'maestro') return;
    let m = document.getElementById('nombreMateria').value;
    if (m.trim()) {
        materias.push(m);
        document.getElementById('nombreMateria').value = '';
        guardarDatos();
        actualizar();
    }
}

function agregarNota() {
    if (sesion?.rol !== 'maestro') return;
    let e = document.getElementById('selEstudiante').value;
    let m = document.getElementById('selMateria').value;
    let n = parseInt(document.getElementById('nota').value);
    if (e !== '' && m !== '' && !isNaN(n)) {
        notas.push({ estudiante: e, materia: m, nota: n });
        document.getElementById('nota').value = '';
        guardarDatos();
        actualizar();
    }
}

function actualizar() {
    let le = document.getElementById('listaEstudiantes');
    le.innerHTML = '';
    estudiantes.forEach(e => {
        let li = document.createElement('li');
        let texto = e.nombre + ' ' + e.apellido;
        if (e.curso) texto += ' (' + e.curso + ')';
        li.textContent = texto;
        le.appendChild(li);
    });
    
    let lm = document.getElementById('listaMaterias');
    lm.innerHTML = '';
    materias.forEach(m => {
        let li = document.createElement('li');
        li.textContent = m;
        lm.appendChild(li);
    });
    
    let se = document.getElementById('selEstudiante');
    se.innerHTML = '';
    
    estudiantes.forEach((e, i) => {
        let o = document.createElement('option');
        o.value = i;
        o.textContent = e.nombre;
        se.appendChild(o);
    });
    
    usuarios.filter(u => u.rol === 'alumno').forEach((u) => {
        let o = document.createElement('option');
        o.value = 'alumno_' + u.id;
        o.textContent = u.nombre + ' (Usuario)';
        se.appendChild(o);
    });
    
    let sm = document.getElementById('selMateria');
    sm.innerHTML = '';
    materias.forEach((m, i) => {
        let o = document.createElement('option');
        o.value = i;
        o.textContent = m;
        sm.appendChild(o);
    });
    
    let tn = document.getElementById('tablaNotas');
    tn.innerHTML = '';
    
    let notasAMostrar = notas;
    if (sesion?.rol === 'alumno') {
        notasAMostrar = notas.filter(n => {
            return (typeof n.estudiante === 'string' && n.estudiante === 'alumno_' + sesion.id) || 
                   (typeof n.estudiante === 'number' && n.estudiante == sesion.id);
        });
    }
    
    notasAMostrar.forEach(n => {
        let tr = document.createElement('tr');
        let nombreEstudiante = '';
        
        if (typeof n.estudiante === 'string' && n.estudiante.startsWith('alumno_')) {
            let alumnoId = parseInt(n.estudiante.split('_')[1]);
            let alumno = usuarios.find(u => u.rol === 'alumno' && u.id === alumnoId);
            nombreEstudiante = alumno ? alumno.nombre : '';
        } else {
            nombreEstudiante = estudiantes[n.estudiante]?.nombre || '';
        }
        
        tr.innerHTML = '<td>' + nombreEstudiante + '</td><td>' + (materias[n.materia] || '') + '</td><td>' + n.nota + '</td>';
        tn.appendChild(tr);
    });
}

function salir() {
    localStorage.removeItem('sesion');
    window.location.href = 'index.html';
}

window.onload = function() {
    if (!verificarSesion()) return;
    
    document.getElementById('userInfo').textContent = sesion.nombre + ' (' + sesion.rol + ')';
    
    if (sesion.rol === 'maestro') {
        document.getElementById('linkEstudiantes').style.display = 'block';
        document.getElementById('linkMaterias').style.display = 'block';
        document.getElementById('linkNotas').style.display = 'block';
        document.getElementById('formEstudiantes').style.display = 'block';
        document.getElementById('formMaterias').style.display = 'block';
        document.getElementById('formNotas').style.display = 'block';
    } else {
        document.getElementById('linkEstudiantes').style.display = 'none';
        document.getElementById('linkMaterias').style.display = 'none';
        document.getElementById('linkNotas').style.display = 'block';
        document.getElementById('formEstudiantes').style.display = 'none';
        document.getElementById('formMaterias').style.display = 'none';
        document.getElementById('formNotas').style.display = 'none';
    }
    
    actualizar();
}
