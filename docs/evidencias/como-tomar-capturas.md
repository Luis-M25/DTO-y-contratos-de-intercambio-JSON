# Cómo tomar las capturas de Postman

1. Abre una terminal en `C:\proyecto\DTO-y-contratos-de-intercambio-JSON`.
2. Ejecuta `start-server.bat` o `php -S 127.0.0.1:8000 -t public router.php`.
3. Abre Postman e importa:
   - `postman/IS803-T2.2-SolicitudCita.postman_collection.json`
   - `postman/IS803-T2.2-SolicitudCita.postman_environment.json`
4. Selecciona el environment **IS803 T2.2 - Local**.
5. Ejecuta **00 - Listar pacientes**.
6. Ejecuta **P1 - Solicitud valida** y captura:
   - URL y método.
   - Body JSON enviado.
   - Código `201 Created`.
   - Respuesta JSON con `data` y `mensaje`.
   Guarda como `p1.png`.
7. Ejecuta **P2 - Falta motivo** y captura:
   - Body sin el campo `motivo`.
   - Código `422 Unprocessable Content`.
   - Respuesta con `errors.motivo`.
   Guarda como `p2.png`.
8. Ejecuta **P3 - Tipo incorrecto** y captura:
   - Body con `paciente_id` como arreglo.
   - Código `422`.
   - Respuesta con `errors.paciente_id`.
   Guarda como `p3.png`.
9. Copia las tres imágenes en esta carpeta (`docs/evidencias/`).

> Verifica que las capturas no muestren tokens, contraseñas ni archivos `.env`.
