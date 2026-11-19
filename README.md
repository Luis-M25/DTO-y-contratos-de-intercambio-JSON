# IS803 · Prácticas 2.2 y 2.3

Repositorio individual de las prácticas del sílabo **IS-803**:

- **Tema 2.2 · Capa de intercambio de información semántica:** DTO, serialización y contrato JSON para registrar solicitudes de cita.
- **Tema 2.3 · Patrones de traducción y normalización:** traducción de dos estructuras heterogéneas al mismo contrato, con validación y errores explícitos. Implementado en la rama `practica-2.3-lmendoza`.

- **Tema 2.4 · Seguridad en la integración de sistemas:** autenticación Sanctum, roles y policies, protección de endpoints. Implementado en la rama `practica-2.4-lmendoza`.

- **Estudiante:** Luis Andrés Mendoza Zambrano
- **Curso / paralelo:** 8.º nivel / A
- **Fecha T2.3:** 17/11/2026
- **Fecha T2.4:** 19/11/2026
- **Repositorio:** https://github.com/Luis-M25/DTO-y-contratos-de-intercambio-JSON

## Tema 2.3 · Normalización de solicitudes de cita

La práctica 2.3 agrega un traductor independiente del controlador:

| Origen | Estructura | Ejemplo |
|---|---|---|
| A · formulario web | JSON plano en español | `fechaCita: "20/11/2026"`, `horaInicio: "3:30 PM"`, `tipoCita: "Consulta"` |
| B · servicio externo | JSON anidado en inglés | `appointment.date: "2026-11-20"`, `start_time: "15:30"`, `type: "consultation"` |

Ambos se transforman en el mismo contrato de la práctica 2.2:

```json
{
  "paciente_id": "11111111-1111-4111-8111-111111111111",
  "fecha": "2026-11-20",
  "hora_inicio": "15:30",
  "hora_fin": "16:00",
  "tipo": "consulta",
  "motivo": "Dolor dental",
  "canal": "web",
  "observacion": null
}
```

### Endpoint de importación

```http
POST /api/v1/solicitudes-cita/importar
Content-Type: application/json
```

```json
{
  "origen": "formulario_web",
  "datos": {
    "cedula": "0102030405",
    "fechaCita": "20/11/2026",
    "horaInicio": "3:30 PM",
    "horaFin": "4:00 PM",
    "tipoCita": "Consulta",
    "motivo": "Dolor dental",
    "observacion": ""
  }
}
```

Respuesta `201`:

```json
{
  "data": {
    "solicitud_id": "9b2f0c1e-6a4d-4f0e-9b2a-1c3d5e7f8a90",
    "paciente_id": "11111111-1111-4111-8111-111111111111",
    "fecha": "2026-11-20",
    "hora_inicio": "15:30",
    "hora_fin": "16:00",
    "tipo": "consulta",
    "canal": "web",
    "estado": "pendiente",
    "creado_en": "2026-11-17T20:47:38-05:00"
  },
  "mensaje": "Solicitud de cita importada y normalizada correctamente."
}
```

Si la fecha no existe, el tipo no pertenece al catálogo, la cédula no está registrada o falta un campo obligatorio, responde `422` con `message`, `errors` y `origen`, sin persistir nada.

### Archivos nuevos

```text
app/Exceptions/NormalizacionException.php
app/Support/ReglasSolicitudCita.php
app/Services/NormalizadorSolicitudCita.php
app/Http/Requests/ImportSolicitudCitaRequest.php
app/Http/Controllers/Api/V1/SolicitudCitaImportController.php
tests/Feature/NormalizacionSolicitudCitaTest.php
postman/IS803-T2.3-Normalizacion.postman_collection.json
docs/T2.3-normalizacion.md
```

### Pruebas de la práctica 2.3

```powershell
php artisan test
```

Resultado: **13 pruebas aprobadas (79 aserciones)**, incluidas las 5 pruebas de la práctica 2.2.

```powershell
npx newman run postman/IS803-T2.3-Normalizacion.postman_collection.json \
  -e postman/IS803-T2.3-Normalizacion.postman_environment.json
```

Resultado: **6 solicitudes, 13 aserciones, 0 fallos**.

Las capturas de P1, P2 y P3, el diagrama y la documentación completa están en `docs/T2.3-normalizacion.md` y `docs/evidencias/t2.3/`.

## Tema 2.2 · DTO y contratos de intercambio JSON

Práctica individual del tema **2.2 Capa de intercambio de información semántica**. Implementa una API Laravel 12 para registrar **solicitudes de cita** de un dominio clínico, con un contrato JSON explícito para clientes web y móvil.

## Stack

- PHP 8.3
- Laravel 12
- SQLite
- Postman
- PHPUnit

## Requisitos

- PHP 8.3 con las extensiones `pdo_sqlite` y `sqlite3` habilitadas.
- Git.
- Postman (para las evidencias).
- Composer (solo si clonas el repositorio desde cero; en este equipo ya existe `vendor/`).

## Instalación

```powershell
cd C:\proyecto\DTO-y-contratos-de-intercambio-JSON
composer install          # solo si no existe la carpeta vendor/
copy .env.example .env
php artisan key:generate
type nul > database\database.sqlite
php artisan migrate:fresh --seed
```

## Ejecución

```powershell
php -S 127.0.0.1:8000 -t public router.php
```

También puedes hacer doble clic en `start-server.bat`.

> Alternativa: `php artisan serve --host=127.0.0.1 --port=8000` si tu instalación de Laravel incluye el archivo `resources/server.php`.

La API queda disponible en `http://127.0.0.1:8000/api/v1`.

## Endpoints

| Método | Ruta | Descripción | Código esperado |
|---|---|---|---|
| GET | `/api/v1/pacientes` | Lista pacientes ficticios | 200 |
| GET | `/api/v1/solicitudes-cita` | Lista solicitudes | 200 |
| POST | `/api/v1/solicitudes-cita` | Registra una solicitud con el contrato común | 201 / 422 |
| POST | `/api/v1/solicitudes-cita/importar` | Normaliza y registra desde el origen A o B | 201 / 422 |
| GET | `/api/v1/solicitudes-cita/{id}` | Consulta una solicitud | 200 / 404 |

### Ejemplo de solicitud

```json
{
  "paciente_id": "11111111-1111-4111-8111-111111111111",
  "fecha": "2026-11-20",
  "hora_inicio": "09:30",
  "hora_fin": "10:00",
  "tipo": "consulta",
  "motivo": "Dolor dental",
  "canal": "web",
  "observacion": "Primera consulta"
}
```

### Ejemplo de respuesta `201`

```json
{
  "data": {
    "solicitud_id": "9b2f0c1e-6a4d-4f0e-9b2a-1c3d5e7f8a90",
    "paciente_id": "11111111-1111-4111-8111-111111111111",
    "fecha": "2026-11-20",
    "hora_inicio": "09:30",
    "hora_fin": "10:00",
    "tipo": "consulta",
    "canal": "web",
    "estado": "pendiente",
    "creado_en": "2026-11-15T22:15:00-05:00"
  },
  "mensaje": "Solicitud de cita registrada correctamente."
}
```

### Ejemplo de error `422`

```json
{
  "message": "El motivo es obligatorio. (and 1 more error)",
  "errors": {
    "motivo": ["El motivo es obligatorio."]
  }
}
```

## Pruebas

```powershell
php artisan test
```

Resultado esperado: **13 pruebas aprobadas** (5 de la práctica 2.2 + 8 de la práctica 2.3).

Las colecciones de Postman también pueden ejecutarse sin interfaz gráfica:

```powershell
# Práctica 2.3
npx newman run postman/IS803-T2.3-Normalizacion.postman_collection.json -e postman/IS803-T2.3-Normalizacion.postman_environment.json

# Práctica 2.2
npx newman run postman/IS803-T2.2-SolicitudCita.postman_collection.json -e postman/IS803-T2.2-SolicitudCita.postman_environment.json
```

## Postman

### Práctica 2.3

1. Importa `postman/IS803-T2.3-Normalizacion.postman_collection.json`.
2. Importa `postman/IS803-T2.3-Normalizacion.postman_environment.json`.
3. Selecciona el environment **IS803 T2.3 - Local**.
4. Ejecuta **P1** (formulario web) y **P2** (servicio externo). P2 compara sus campos con los de P1.
5. Ejecuta **P3–P6** para verificar los errores explícitos.

### Práctica 2.2

1. Importa `postman/IS803-T2.2-SolicitudCita.postman_collection.json`.
2. Importa `postman/IS803-T2.2-SolicitudCita.postman_environment.json`.
3. Selecciona el environment **IS803 T2.2 - Local**.
4. Ejecuta primero **00 - Listar pacientes** para obtener un `paciente_id`.
5. Ejecuta las carpetas P1, P2 y P3.
6. Toma las capturas solicitadas en la guía.

## Estructura

```
app/
├─ DTO/SolicitudCitaData.php
├─ Exceptions/NormalizacionException.php
├─ Http/
│  ├─ Controllers/Api/V1/
│  ├─ Requests/ImportSolicitudCitaRequest.php
│  ├─ Requests/StoreSolicitudCitaRequest.php
│  └─ Resources/
├─ Models/
├─ Services/
│  ├─ NormalizadorSolicitudCita.php
│  └─ SolicitudCitaService.php
└─ Support/ReglasSolicitudCita.php
database/
├─ factories/
├─ migrations/
└─ seeders/
docs/
├─ T2.3-normalizacion.md
├─ 01-actividad-1-proceso.md
├─ 02-actividad-2-contrato.md
├─ 03-actividad-3-implementacion.md
├─ 04-actividad-4-pruebas.md
├─ 05-reflexion.md
├─ 06-diagrama.md
└─ evidencias/
   └─ t2.3/
postman/
routes/api.php
tests/Feature/
├─ NormalizacionSolicitudCitaTest.php
└─ SolicitudCitaTest.php
```

## Documentación de la práctica

### Tema 2.3

- [Documento completo de normalización](docs/T2.3-normalizacion.md)
- [Evidencias P1, P2, P3 y diagrama](docs/evidencias/t2.3/)

### Tema 2.2

- [Actividad 1 · Proceso](docs/01-actividad-1-proceso.md)
- [Actividad 2 · Contrato JSON](docs/02-actividad-2-contrato.md)
- [Actividad 3 · Implementación](docs/03-actividad-3-implementacion.md)
- [Actividad 4 · Pruebas](docs/04-actividad-4-pruebas.md)
- [Reflexión individual](docs/05-reflexion.md)
- [Diagrama](docs/06-diagrama.md)

## Checklist de entrega

### Tema 2.3

- [x] Actividad 1: dos orígenes, cinco diferencias y contrato común.
- [x] Actividad 2: tabla de mapeo A / B / contrato común.
- [x] Actividad 3: `NormalizadorSolicitudCita` con un método por origen.
- [x] Actividad 3: validación estricta y errores explícitos.
- [x] Actividad 4: P1 y P2 generan el mismo contrato clínico.
- [x] Pruebas P1–P8 (13 pruebas, 79 aserciones).
- [x] Colección Postman y ejecución Newman (6 solicitudes, 13 aserciones, 0 fallos).
- [x] Capturas P1, P2, P3 y diagrama en `docs/evidencias/t2.3/`.
- [x] Documentación `docs/T2.3-normalizacion.md`.

### Tema 2.2

- [x] Actividad 1: proceso, actores y endpoint documentados.
- [x] Actividad 2: contrato de solicitud, respuesta y error.
- [x] Actividad 3: endpoint Laravel con validación y serialización.
- [x] Actividad 4: pruebas P1 a P5 y colección Postman.
- [x] Diagrama de intercambio.
- [x] Reflexión individual.
- [x] README con instrucciones.
- [ ] Capturas `p1.png`, `p2.png` y `p3.png` en `docs/evidencias/`.
- [ ] Subir las capturas al repositorio.
- [ ] Verificar que el repositorio sea accesible para el docente.

### Tema 2.4

- [ ] Autenticación Sanctum operativa con tokens Bearer.
- [ ] Roles `paciente`, `asistente`, `doctora` diferenciados y operativos.
- [ ] Matriz de acceso documentada en `docs/T2.4-seguridad.md`.
- [ ] Endpoints protegidos con middleware `auth:sanctum` y `role`.
- [ ] Policies de Laravel implementadas para `SolicitudCita` y `Paciente`.
- [ ] Paciente solo puede acceder a sus propias solicitudes.
- [ ] Asistente y doctora tienen permisos extendidos (eliminación solo doctora).
- [ ] `.env` excluido del repositorio (`gitignore`), sin secretos en capturas.
- [ ] Pruebas P1–P4 (positivas y negativas) documentadas y passing.
- [ ] Colección Postman `IS803-T2.4-Seguridad` ejecutable con Newman (0 fallos).
- [ ] Evidencias de pruebas (capturas P1–P4) en `docs/evidencias/t2.4/`.
- [ ] Reflexión individual en `docs/T2.4-seguridad.md`.
- [ ] `README.md` actualizado con nuevos endpoints y requisitos.

## Checklist de entrega (continuación)

### Notas de entorno

- Se habilitaron `pdo_sqlite` y `sqlite3` en `php.ini`; el respaldo quedó en `php.ini.bak-is803`.
- El servidor local se inicia con `start-server.bat`, con `php -S 127.0.0.1:8000 -t public router.php` o con `php -S 127.0.0.1:8000 -t public public/index.php`.
- La base SQLite local (`database/database.sqlite`) y el archivo `.env` no se versionan.

## Nota académica

El dominio y la arquitectura se basan en el proyecto de aula **proyect_clinico** (Laravel 12), en particular en la entidad `Agenda` y el proceso de registro de citas. La implementación de esta práctica es individual y vive en este repositorio para no mezclarse con el repositorio del equipo.