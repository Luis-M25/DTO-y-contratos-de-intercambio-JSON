# Diagrama de intercambio

```mermaid
flowchart LR
    W[Cliente web] -->|POST /api/v1/solicitudes-cita<br/>JSON| API[API Laravel 12]
    M[App movil] -->|POST /api/v1/solicitudes-cita<br/>JSON| API

    API --> R[StoreSolicitudCitaRequest<br/>validacion]
    R -->|datos validos| D[SolicitudCitaData<br/>DTO]
    R -->|datos invalidos| E[422 Unprocessable Entity<br/>message + errors]
    D --> S[SolicitudCitaService]
    S --> DB[(SQLite<br/>pacientes / solicitud_citas)]
    DB --> RES[SolicitudCitaResource<br/>serializacion]
    RES -->|201 Created<br/>JSON| W
    RES -->|201 Created<br/>JSON| M
    E --> W
    E --> M
```

## Explicación

1. El cliente web o móvil envía un JSON al endpoint.
2. Laravel deserializa el cuerpo y el `FormRequest` valida campos, tipos y reglas de negocio.
3. Si hay errores, responde `422` con `message` y `errors`, sin persistir nada.
4. Si los datos son válidos, el DTO transporta la información al servicio.
5. El servicio registra la solicitud en SQLite con estado `pendiente`.
6. El recurso serializa el modelo y devuelve `201` con el contrato de respuesta.
