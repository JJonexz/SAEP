async function submit(){
    const n=document.getElementById('nombre').value.trim(),a=document.getElementById('apellido').value.trim(),d=document.getElementById('dni').value.trim(),t=document.getElementById('telefono').value.trim();
    const e=document.getElementById('err'),b=document.getElementById('btn');
    e.style.display='none';
    if(!n||!a||!d){e.textContent='Nombre, apellido y DNI son obligatorios.';e.style.display='block';return;}
    if(!/^\d{7,8}$/.test(d)){e.textContent='El DNI debe tener 7 u 8 dígitos.';e.style.display='block';return;}
    b.disabled=true;b.textContent='Enviando...';
    const r=await fetch('api/auth/save-profile.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({nombre:n,apellido:a,dni:d,telefono:t})});
    const data=await r.json();
    if(data.success)window.location.href='pending.php';
    else{e.textContent=data.error||'Error.';e.style.display='block';b.disabled=false;b.textContent='Enviar solicitud';}
}
document.addEventListener('keydown',e=>{if(e.key==='Enter')submit();});