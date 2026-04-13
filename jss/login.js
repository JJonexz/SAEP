const usuarios = [
    {usuario:'maestro', pass:'maestro123', rol:'maestro', nombre:'Profesor José'},
    {usuario:'alumno1', pass:'alumno123', rol:'alumno', nombre:'Juan Perez', id:0},
    {usuario:'alumno2', pass:'alumno123', rol:'alumno', nombre:'Maria Lopez', id:1}
];

function accesRapido(u, p) {
    document.getElementById('usuario').value = u;
    document.getElementById('contrasena').value = p;
}

function iniciarSesion() {
    let u = document.getElementById('usuario').value.trim();
    let p = document.getElementById('contrasena').value.trim();
    let user = usuarios.find(x => x.usuario === u && x.pass === p);
    if (!user) {
        document.getElementById('errorMsg').textContent = 'Usuario o contraseña incorrectos';
        return;
    }
    localStorage.setItem('sesion', JSON.stringify(user));
    window.location.href = 'app.html';
}
