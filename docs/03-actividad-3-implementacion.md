# Actividad 3 · Implementación en Laravel

## Flujo de intercambio

```
Cliente web / App móvil
        │  POST /api/v1/solicitudes-cita  (JSON)
        ▼
StoreSolicitudCitaRequest  →  valida campos y tipos
        ▼
SolicitudCitaData (DTO)    →  transporta datos ya validados
        ▼
SolicitudCitaService       →  regla de negocio y persistencia
        ▼
Modelo SolicitudCita       →  tabla solicitud_citas (SQLite)
        ▼
SolicitudCitaResource      →  serializa el modelo a JSON
        ▼
Respuesta 201 / 422        →  contrato uniforme para web y móvil
```

## Archivos principales

| Archivo | Responsabilidad |
|---|---|
| `routes/api.php` | Define las rutas versionadas `/api/v1`. |
| `app/Http/Requests/StoreSolicitudCitaRequest.php` | Valida el JSON de entrada y devuelve errores en español. |
| `app/DTO/SolicitudCitaData.php` | DTO inmutable con los datos validados. |
| `app/Services/SolicitudCitaService.php` | Registra, lista y busca solicitudes. |
| `app/Models/SolicitudCita.php` | Modelo Eloquent de la tabla `solicitud_citas`. |
| `app/Models/Paciente.php` | Modelo Eloquent de la tabla `pacientes`. |
| `app/Http/Resources/SolicitudCitaResource.php` | Define exactamente los campos de la respuesta. |
| `app/Http/Controllers/Api/V1/SolicitudCitaController.php` | Orquesta el flujo y define los códigos HTTP. |

## Reglas de validación

| Campo | Reglas |
|---|---|
| `paciente_id` | `required`, `uuid`, `exists:pacientes,id` |
| `fecha` | `required`, `date_format:Y-m-d`, `after_or_equal:today` |
| `hora_inicio` | `required`, `date_format:H:i` |
| `hora_fin` | `required`, `date_format:H:i`, `after:hora_inicio` |
| `tipo` | `required`, `string`, `in:consulta,tratamiento,control` |
| `motivo` | `required`, `string`, `max:255` |
| `canal` | `required`, `string`, `in:web,movil` |
| `observacion` | `nullable`, `string`, `max:500` |

## Serialización y deserialización

- **Deserialización:** Laravel recibe el cuerpo JSON y lo convierte en un arreglo PHP. El `FormRequest` valida ese arreglo antes de que llegue al controlador. Luego `SolicitudCitaData::fromValidated()` construye un DTO con nombres tipados.
- **Serialización:** el modelo se guarda en SQLite y `SolicitudCitaResource` transforma sus atributos al contrato de respuesta. Así el cliente nunca recibe nombres internos de la base de datos ni campos no documentados.
- **Fechas y horas:** la base de datos guarda `fecha` como `DATE` y las horas como `TIME`; el recurso las normaliza a `YYYY-MM-DD` y `HH:mm` para que coincidan exactamente con el contrato.
- **Errores:** `bootstrap/app.php` fuerza JSON en rutas `api/*`, de modo que un `422` siempre tenga `message` y `errors`.

## Códigos HTTP

| Código | Cuándo se usa |
|---|---|
| `200` | Consulta exitosa (`GET`). |
| `201` | Solicitud creada correctamente. |
| `404` | La solicitud consultada no existe. |
| `422` | Error de validación; no se persiste nada. |
| `500` | Error interno no controlado. |

## Ejemplo de controlador

```php
public function store(StoreSolicitudCitaRequest $request): JsonResponse
{
    $solicitud = $this->service->registrar(
        SolicitudCitaData::fromValidated($request->validated())
    );

    return response()->json([
        'data' => new SolicitudCitaResource($solicitud),
        'mensaje' => 'Solicitud de cita registrada correctamente.',
    ], 201);
}
```

## Comandos de verificación

```powershell
php artisan route:list
php artisan migrate:fresh --seed
php artisan test
php -S 127.0.0.1:8000 -t public router.php
```
