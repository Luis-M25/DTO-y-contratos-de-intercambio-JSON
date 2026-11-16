# Actividad 1 · Selección del proceso

## Proceso elegido

**Registro de una solicitud de cita odontológica.**

El proceso consiste en que un paciente, desde el cliente web o la aplicación móvil, solicita una cita indicando la fecha, el horario y el motivo. El backend valida los datos, registra la solicitud con estado `pendiente` y devuelve un JSON con la información creada.

## Actores

| Actor | Rol en el proceso |
|---|---|
| Paciente | Envía la solicitud desde el cliente web o móvil. |
| Recepcionista | Puede consultar las solicitudes desde el cliente web. |
| API Laravel | Valida, registra y serializa la respuesta. |
| Base de datos SQLite | Persiste pacientes y solicitudes de cita. |

## Endpoint

| Método | Ruta | Descripción |
|---|---|---|
| POST | `/api/v1/solicitudes-cita` | Registra una nueva solicitud de cita. |
| GET | `/api/v1/solicitudes-cita` | Lista las solicitudes registradas. |
| GET | `/api/v1/solicitudes-cita/{id}` | Consulta una solicitud por su UUID. |
| GET | `/api/v1/pacientes` | Lista los pacientes ficticios de prueba. |

## Datos que envía el cliente

- Identificador del paciente (`paciente_id`, UUID).
- Fecha de la cita (`fecha`, `YYYY-MM-DD`).
- Hora de inicio y hora de fin (`HH:mm`).
- Tipo de cita (`consulta`, `tratamiento`, `control`).
- Motivo de la solicitud.
- Canal de origen (`web` o `movil`).
- Observación opcional.

## Datos que devuelve el backend

- Identificador de la solicitud creada.
- Datos normalizados de la cita.
- Estado inicial `pendiente`.
- Fecha y hora de creación en formato ISO 8601.
- Mensaje legible para el cliente.

## Justificación

Se eligió este proceso porque ya existe en el proyecto de aula clínico y porque intervienen dos clientes distintos (web y móvil) que deben interpretar exactamente los mismos campos. Además, permite demostrar validación de tipos, obligatoriedad, formatos de fecha y hora, y un contrato de error uniforme.
