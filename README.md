# IS803 · Prácticas 2.2 y 2.3

Repositorio individual de las prácticas del sílabo **IS-803**:

- **Tema 2.2 · Capa de intercambio de información semántica:** DTO, serialización y contrato JSON para registrar solicitudes de cita.
- **Tema 2.3 · Patrones de traducción y normalización:** traducción de dos estructuras heterogéneas al mismo contrato, con validación y errores explícitos. Implementado en la rama `practica-2.3-lmendoza`.

- **Estudiante:** Luis Andrés Mendoza Zambrano
- **Curso / paralelo:** 8.º nivel / A
- **Fecha T2.3:** 17/11/2026
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
npx newman run postman/IS803-T2.3-Normalizacion.postman_collection.json `
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
postman/
routes/api.php
tests/Feature/
├─ NormalizacionSolicitudCitaTest.php
└─ SolicitudCitaTest.php
```

## Notas de entorno

- Se habilitaron `pdo_sqlite` y `sqlite3` en `php.ini`; el respaldo quedó en `php.ini.bak-is803`.
- El servidor local se inicia con `start-server.bat`, con `php -S 127.0.0.1:8000 -t public router.php` o con `php -S 127.0.0.1:8000 -t public public/index.php`.
- La base SQLite local (`database/database.sqlite`) y el archivo `.env` no se versionan.

## Nota académica

El dominio y la arquitectura se basan en el proyecto de aula **proyect_clinico** (Laravel 12), en particular en la entidad `Agenda` y el proceso de registro de citas. La implementación de esta práctica es individual y vive en este repositorio para no mezclarse con el repositorio del equipo.
