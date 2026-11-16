# IS803 T2.2 · DTO y contratos de intercambio JSON

Práctica individual del tema **2.2 Capa de intercambio de información semántica**. Implementa una API Laravel 12 para registrar **solicitudes de cita** de un dominio clínico, con un contrato JSON explícito para clientes web y móvil.

- **Estudiante:** Luis M.
- **Curso / paralelo:** 8.º nivel / A
- **Fecha:** 15/11/2026
- **Repositorio:** https://github.com/Luis-M25/DTO-y-contratos-de-intercambio-JSON

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
- Composer es opcional: el repositorio incluye `composer.lock`, pero no `vendor/`. Si necesitas instalarlo, ejecuta `composer install`.

## Instalación

```powershell
cd C:\proyecto\DTO-y-contratos-de-intercambio-JSON
copy .env.example .env
php artisan key:generate
type nul > database\database.sqlite
php artisan migrate:fresh --seed
```

> El archivo `.env` no se versiona. Usa datos ficticios y no publiques tokens ni contraseñas.

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
| POST | `/api/v1/solicitudes-cita` | Registra una solicitud | 201 / 422 |
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

Resultado esperado: **5 pruebas aprobadas** (P1 a P5).

La colección de Postman también puede ejecutarse sin interfaz gráfica:

```powershell
npx newman run postman/IS803-T2.2-SolicitudCita.postman_collection.json -e postman/IS803-T2.2-SolicitudCita.postman_environment.json
```

## Postman

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
├─ Http/
│  ├─ Controllers/Api/V1/
│  ├─ Requests/StoreSolicitudCitaRequest.php
│  └─ Resources/
├─ Models/
└─ Services/SolicitudCitaService.php
database/
├─ factories/
├─ migrations/
└─ seeders/
docs/
├─ 01-actividad-1-proceso.md
├─ 02-actividad-2-contrato.md
├─ 03-actividad-3-implementacion.md
├─ 04-actividad-4-pruebas.md
├─ 05-reflexion.md
├─ 06-diagrama.md
└─ evidencias/
postman/
routes/api.php
tests/Feature/SolicitudCitaTest.php
```

## Documentación de la práctica

- [Actividad 1 · Proceso](docs/01-actividad-1-proceso.md)
- [Actividad 2 · Contrato JSON](docs/02-actividad-2-contrato.md)
- [Actividad 3 · Implementación](docs/03-actividad-3-implementacion.md)
- [Actividad 4 · Pruebas](docs/04-actividad-4-pruebas.md)
- [Reflexión individual](docs/05-reflexion.md)
- [Diagrama](docs/06-diagrama.md)

## Nota académica

El dominio y la arquitectura se basan en el proyecto de aula **proyect_clinico** (Laravel 12), en particular en la entidad `Agenda` y el proceso de registro de citas. La implementación de esta práctica es individual y vive en este repositorio para no mezclarse con el repositorio del equipo.
