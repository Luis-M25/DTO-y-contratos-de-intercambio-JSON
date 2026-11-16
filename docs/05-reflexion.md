# Reflexión individual

## ¿Qué problema de integración resolviste?

Resolví el problema de que el cliente web y la aplicación móvil interpretaran de forma distinta los datos de una cita. Antes de definir el contrato, un cliente podía enviar la fecha como `20/11/2026` y otro como `2026-11-20`; uno podía tratar el identificador del paciente como número y otro como UUID; y los errores podían llegar en formatos diferentes. Con el contrato JSON y el endpoint `POST /api/v1/solicitudes-cita`, ambos clientes conocen los mismos nombres de campos, tipos, formatos, valores permitidos y códigos HTTP. Esto reduce errores de integración y facilita que web y móvil avancen en paralelo.

## ¿Qué decisión técnica tomaste y por qué?

Decidí separar la validación, el transporte y la respuesta en tres piezas: `StoreSolicitudCitaRequest` valida la entrada, `SolicitudCitaData` transporta los datos ya validados como un DTO inmutable y `SolicitudCitaResource` define la respuesta. Además, usé UUID como string para los identificadores, fechas ISO 8601 y horas `HH:mm`, y cerré los valores de `tipo` y `canal` con listas permitidas. Elegí SQLite porque no requiere instalar un servidor de base de datos y permite que cualquier compañero ejecute la práctica con pocos pasos. También forcé respuestas JSON en las rutas `api/*` para que los errores `422` siempre tengan `message` y `errors`.

## ¿Cómo verificaste que la solución funciona en tu proyecto?

La verifiqué de dos formas. Primero, con pruebas automatizadas en Laravel: P1 comprueba el `201` y el registro en la base de datos; P2 comprueba el `422` cuando falta `motivo`; P3 comprueba el `422` cuando `paciente_id` tiene un tipo incorrecto; P4 y P5 cubren fecha mal formateada y hora de fin anterior. Todas las pruebas pasaron con `php artisan test` (5 pruebas, 34 aserciones). Segundo, con la colección de Postman: envié las mismas solicitudes desde un cliente externo, contrasté el JSON con el contrato y guardé capturas de P1, P2 y P3. Con eso comprobé que el backend deserializa el JSON, valida, persiste datos correctos y devuelve una respuesta que cualquier cliente puede consumir.
