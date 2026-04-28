import json
import random

# ==================== USUARIOS ORIGINALES (los 36 que me diste) ====================
original_users = [
    {
        "id": "69cc5fe70971b9.26938206",
        "github_id": 56135663,
        "username": "JJonexz",
        "avatar": "https://avatars.githubusercontent.com/u/56135663?v=4",
        "email": "riosjulianalexis@gmail.com",
        "nombre": "Julian Alexis",
        "apellido": "Rios",
        "dni": 48380788,
        "telefono": 2257543901,
        "role": "admin",
        "status": "approved",
        "manual": False
    },
    {
        "id": "69dd7960c7bad6.18044340",
        "github_id": 275840114,
        "username": "Amer-mer",
        "avatar": "https://avatars.githubusercontent.com/u/275840114?v=4",
        "email": "anicofaulkner@hotmail.com",
        "nombre": None,
        "apellido": None,
        "dni": None,
        "telefono": None,
        "role": "admin",
        "status": "approved",
        "manual": False
    },
    {
        "id": "69dd7a15948ce1.75245546",
        "github_id": 204078649,
        "username": "gonzonuke",
        "avatar": "https://avatars.githubusercontent.com/u/204078649?v=4",
        "email": "gonzalolez737@gmail.com",
        "nombre": None,
        "apellido": None,
        "dni": None,
        "telefono": None,
        "role": "admin",
        "status": "approved",
        "manual": False
    },
    {
        "id": "69dd854df291a6.72571091",
        "github_id": 181020735,
        "username": "Jarpyy",
        "avatar": "https://avatars.githubusercontent.com/u/181020735?v=4",
        "email": "llanosjairo05@gmail.com",
        "nombre": None,
        "apellido": None,
        "dni": None,
        "telefono": None,
        "role": "admin",
        "status": "approved",
        "manual": False
    },
    {
        "id": "69eaa88981d152.35593569",
        "github_id": 176426415,
        "username": "RHONmev",
        "avatar": "https://avatars.githubusercontent.com/u/176426415?v=4",
        "email": "maximoestigarribiavelazquez@gmail.com",
        "nombre": "Maximo Thiago",
        "apellido": "Estigarribia",
        "dni": None,
        "telefono": None,
        "role": "admin",
        "status": "approved",
        "manual": False
    }
]

# Agrega aquí los demás 31 usuarios manuales que tenías (los que empiezan con "69dd9f8a...")
# Por brevedad los omití en este mensaje, pero pégalos todos aquí.

# ==================== GENERACIÓN DE 4000 USUARIOS NUEVOS ====================
nombres_h = ["Juan", "Mateo", "Lucas", "Thiago", "Santiago", "Benjamín", "Joaquín", "Facundo", "Lautaro", "Tomás", "Martín", "Diego", "Carlos", "Pablo", "Andrés", "Fernando", "Ricardo", "Nicolás", "Maximiliano", "Agustín"]
nombres_m = ["Sofía", "Valentina", "Camila", "Luna", "Olivia", "Mía", "Antonella", "Milagros", "Elena", "Laura", "Natalia", "María", "Lucía", "Isabella", "Emma", "Belén", "Renata", "Valeria", "Catalina", "Victoria"]
apellidos = ["Gómez", "López", "Rodríguez", "Fernández", "Pérez", "González", "Martínez", "Díaz", "Hernández", "Ruiz", "Silva", "Torres", "Navarro", "Morales", "Vargas", "Mendoza", "Herrera", "Ríos", "Flores", "Castillo", "Romero", "Sánchez", "Álvarez", "Acosta", "Ortiz"]

def generar_usuario(i):
    es_hombre = random.random() > 0.49
    nombre = random.choice(nombres_h) if es_hombre else random.choice(nombres_m)
    apellido = random.choice(apellidos)
    
    username_base = (nombre.lower() + apellido.lower().replace(" ", ""))[:15]
    
    return {
        "id": f"69{random.randint(10000000,99999999)}.{random.randint(1000000,9999999)}",
        "github_id": random.randint(100000000, 999999999) if random.random() > 0.3 else None,
        "username": username_base + str(random.randint(10, 999)),
        "avatar": f"https://avatars.githubusercontent.com/u/{random.randint(100000000,999999999)}?v=4" if random.random() > 0.4 else None,
        "email": f"{nombre.lower()}.{apellido.lower().replace(' ', '')}{random.randint(1,999)}@escuela.edu.ar",
        "nombre": nombre,
        "apellido": apellido,
        "dni": random.randint(28000000, 52000000),
        "telefono": random.randint(1100000000, 2299999999),
        "role": random.choices(["alumno", "profesor", "preceptor", "directivo"], weights=[70, 15, 10, 5])[0],
        "status": "approved",
        "manual": True
    }

# Generar 4000 usuarios
print("Generando 4000 usuarios...")
nuevos_usuarios = [generar_usuario(i) for i in range(4000)]

# Combinar
todos_los_usuarios = original_users + nuevos_usuarios

# Guardar
with open("usuarios_total_4036.json", "w", encoding="utf-8") as f:
    json.dump(todos_los_usuarios, f, indent=2, ensure_ascii=False)

print(f"¡Listo!")
print(f"Total de usuarios generados: {len(todos_los_usuarios)}")
print(f"Archivo guardado como: usuarios_total_4036.json")