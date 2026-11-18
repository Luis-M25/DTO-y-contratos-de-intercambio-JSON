# Evidencias · Tema 2.3

Capturas y resultados generados contra el servidor local `http://127.0.0.1:8000` con datos ficticios.

## Archivos

| Archivo | Contenido |
|---|---|
| `word/p1.png` | P1: formulario web válido → `201 Created` y contrato común. |
| `word/p2.png` | P2: servicio externo equivalente → mismo contrato clínico; `canal: movil`. |
| `word/p3.png` | P3: formulario sin `motivo` → `422` y error explícito. |
| `word/diagrama.png` | Diagrama de componentes: orígenes → traductor → contrato común. |
| `word/pruebas.png` | Resumen visual de la suite de pruebas. |
| `raw/` | Cuerpos JSON reales de entrada y salida de P1–P4. |
| `pruebas-automatizadas.txt` | Salida de `php artisan test`. |
| `newman-cli.txt` | Salida de Newman. |
| `newman-report.json` | Reporte JSON de Newman. |

## Resultados

```text
php artisan test
Tests: 13 passed (79 assertions)

npx newman run postman/IS803-T2.3-Normalizacion.postman_collection.json \
  -e postman/IS803-T2.3-Normalizacion.postman_environment.json
requests: 6
assertions: 13
failed: 0
```

## Reproducir

```powershell
php artisan migrate:fresh --seed
php -S 127.0.0.1:8000 -t public public/index.php
```

En otra terminal:

```powershell
php artisan test
npx newman run postman/IS803-T2.3-Normalizacion.postman_collection.json `
  -e postman/IS803-T2.3-Normalizacion.postman_environment.json
```

> Las capturas no incluyen tokens, contraseñas ni archivos `.env`. Los pacientes y las citas son datos ficticios del seeder.
