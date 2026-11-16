# Actividad 2 · Contrato JSON

## Contrato de solicitud

Endpoint: `POST /api/v1/solicitudes-cita`

| Campo | Tipo | ¿Obligatorio? | Significado |
|---|---|---|---|
| `paciente_id` | string (UUID) | Sí | Identificador único del paciente que solicita la cita. |
| `fecha` | string (`YYYY-MM-DD`) | Sí | Fecha de la cita. |
| `hora_inicio` | string (`HH:mm`) | Sí | Hora de inicio de la cita. |
| `hora_fin` | string (`HH:mm`) | Sí | Hora de fin; debe ser posterior a `hora_inicio`. |
| `tipo` | string (`consulta`, `tratamiento`, `control`) | Sí | Categoría de la cita. |
| `motivo` | string (máx. 255) | Sí | Razón de la solicitud. |
| `canal` | string (`web`, `movil`) | Sí | Cliente que origina la solicitud. |
| `observacion` | string / null (máx. 500) | No | Información adicional. |

### Ejemplo

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

## Contrato de respuesta `201`

| Campo | Tipo | ¿Siempre presente? | Significado |
|---|---|---|---|
| `data.solicitud_id` | string (UUID) | Sí | Identificador de la solicitud creada. |
| `data.paciente_id` | string (UUID) | Sí | Paciente asociado. |
| `data.fecha` | string (`YYYY-MM-DD`) | Sí | Fecha de la cita. |
| `data.hora_inicio` | string (`HH:mm`) | Sí | Hora de inicio. |
| `data.hora_fin` | string (`HH:mm`) | Sí | Hora de fin. |
| `data.tipo` | string | Sí | Tipo de cita. |
| `data.canal` | string | Sí | Canal de origen. |
| `data.estado` | string | Sí | Estado inicial: `pendiente`. |
| `data.creado_en` | string (ISO 8601) | Sí | Fecha y hora de creación con offset horario. |
| `mensaje` | string | Sí | Mensaje legible para el cliente. |

### Ejemplo

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

## Contrato de error `422`

| Campo | Tipo | Significado |
|---|---|---|
| `message` | string | Mensaje general del error. |
| `errors` | object | Arreglo de mensajes por cada campo inválido. |

### Ejemplo

```json
{
  "message": "El motivo es obligatorio. (and 1 more error)",
  "errors": {
    "motivo": ["El motivo es obligatorio."]
  }
}
```

## Reglas semánticas del contrato

1. **Identificadores:** `paciente_id` y `solicitud_id` son UUID y se transportan como **string**, no como número. Un cliente móvil no debe convertirlos a entero.
2. **Fechas:** se usan en formato ISO 8601 `YYYY-MM-DD`. No se aceptan formatos locales como `20/11/2026`.
3. **Horas:** se usan en formato `HH:mm` (24 horas). No se acepta `9:30` ni `09:30:00` en la entrada.
4. **Enumeraciones:** `tipo` y `canal` tienen valores cerrados. Esto evita errores por variantes como `móvil`, `MOVIL` o `presencial`.
5. **Nulos:** `observacion` puede ser `null` o estar ausente; ambos casos significan "sin observación". Los demás campos no admiten nulos.
6. **Estado:** `estado` es generado por el servidor (`pendiente`); el cliente no lo envía.
7. **Zona horaria:** `creado_en` se devuelve en ISO 8601 con offset (`-05:00`), definido por `APP_TIMEZONE=America/Guayaquil`.
8. **Nombres:** todos los campos usan `snake_case` para que web y móvil apliquen la misma convención.
9. **Errores:** todas las validaciones devuelven `message` + `errors`, nunca un formato distinto por endpoint.

## ¿Por qué este contrato es compartible?

Porque define nombres, tipos, obligatoriedad y significado sin depender del lenguaje del cliente. El frontend web (JavaScript/TypeScript) y la app móvil pueden generar y consumir el mismo JSON, y cualquier cambio futuro debe reflejarse primero en este documento y luego en ambos clientes.
