# Actividad 4 · Pruebas del contrato

## Pruebas automatizadas

Ejecutar:

```powershell
php artisan test
```

Resultado obtenido:

```
PASS  Tests\Feature\SolicitudCitaTest
✓ p1 registra solicitud valida
✓ p2 rechaza solicitud sin campo obligatorio
✓ p3 rechaza tipo de dato incorrecto
✓ p4 rechaza fecha con formato incorrecto
✓ p5 rechaza hora fin anterior

Tests: 5 passed (34 assertions)
```

La salida completa está en `docs/evidencias/pruebas-automatizadas.txt`.

## Pruebas de la colección Postman (Newman)

También se ejecutó la colección con Newman contra el servidor local:

```
requests: 9
assertions: 17
failed: 0
```

El reporte está en `docs/evidencias/newman-report.json`.

## Casos de prueba

| Caso | Entrada o condición | Resultado esperado | Resultado obtenido |
|---|---|---|---|
| P1 | JSON válido con todos los campos obligatorios. | `201`, JSON con `data` y `mensaje`; registro en BD. | `201`, contrato correcto y registro creado. |
| P2 | Falta el campo `motivo`. | `422` con `errors.motivo`; sin registro. | `422`, error en `motivo`, BD sin cambios. |
| P3 | `paciente_id` con tipo incorrecto (arreglo en lugar de UUID). | `422` con `errors.paciente_id`; sin registro. | `422`, error en `paciente_id`, BD sin cambios. |
| P4 | `fecha` con formato `20/11/2026`. | `422` con `errors.fecha`; sin registro. | `422`, error en `fecha`. |
| P5 | `hora_fin` anterior a `hora_inicio`. | `422` con `errors.hora_fin`; sin registro. | `422`, error en `hora_fin`. |

## Pruebas manuales en Postman

1. Importar la colección y el environment de la carpeta `postman/`.
2. Ejecutar **00 - Listar pacientes** para obtener un `paciente_id` real del seeder.
3. Ejecutar **P1 - Solicitud válida** y verificar `201`.
4. Ejecutar **P2 - Falta motivo** y verificar `422`.
5. Ejecutar **P3 - Tipo incorrecto** y verificar `422`.

### Evidencias

Guardar las capturas en esta carpeta con los nombres:

- `docs/evidencias/p1.png`: body enviado, código `201` y respuesta JSON.
- `docs/evidencias/p2.png`: body sin `motivo`, código `422` y `errors.motivo`.
- `docs/evidencias/p3.png`: `paciente_id` con tipo incorrecto, código `422` y `errors.paciente_id`.

También se incluyen como respaldo:

- `docs/evidencias/pruebas-automatizadas.txt`: salida de `php artisan test`.
- `docs/evidencias/newman-report.json`: reporte de la colección ejecutada con Newman.

> Las capturas deben mostrar datos ficticios y no incluir tokens ni contraseñas.

## ¿Qué se interpretaría distinto sin documentación?

| Campo | Ambigüedad posible | Consecuencia |
|---|---|---|
| `paciente_id` | Un cliente podría asumir que es un entero y enviar `25`; otro, un UUID. | El backend rechaza el tipo o no encuentra al paciente. |
| `fecha` | `2026-11-20` vs `20/11/2026` vs un timestamp completo. | La cita se registra en otra fecha o se rechaza. |
| `hora_inicio` / `hora_fin` | `09:30`, `9:30`, `09:30:00` o `09:30 a. m.`. | Validación inconsistente entre web y móvil. |
| `tipo` | Texto libre: `consulta`, `Consulta`, `consulta general`. | Se guardan categorías distintas para el mismo concepto. |
| `canal` | `movil`, `móvil`, `mobile`, `app`. | Reportes inconsistentes por canal. |
| `observacion` | `null`, campo ausente o cadena vacía. | Un cliente muestra "sin observación" y otro "null". |
| `estado` | Un cliente intenta enviarlo; otro espera que lo calcule el servidor. | Se intenta modificar un campo que debe ser controlado por el backend. |
| `creado_en` | Fecha local sin zona horaria vs ISO 8601 con offset. | La app móvil muestra una hora distinta a la web. |
| Errores | Un cliente espera `error` y otro `errors`. | El frontend no puede mostrar los mensajes de validación. |

El contrato elimina estas ambigüedades porque fija nombres, tipos, formatos, valores permitidos y la forma exacta de los errores.
