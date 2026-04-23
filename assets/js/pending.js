async function check(){
    const r=await fetch('api/auth/me.php');
    if(r.status===401){location.href='index.php';return;}
    const u=await r.json();
    if(u.status==='approved')location.href='dashboard.php';
    else alert('Tu cuenta aún está pendiente de aprobación.');
}