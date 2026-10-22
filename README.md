# Digiturno (PHP + MySQL + XAMPP)

Aplicación web de turnos con roles (administrador, asesor y pantalla), kiosco de emisión, panel de asesor, pantalla TV con actualización automática (AJAX polling cada 3s) y reportes con Chart.js.

## Estructura

- `index.php`: enrutador principal y acciones.
- `config/database.php`: conexión.
- `app/helpers`: utilidades (DB, auth, render).
- `app/views`: vistas del sistema.
- `public/assets`: CSS y JS.
- `sql/digiturno.sql`: script completo de base de datos + datos de prueba.

## Instalación en XAMPP

1. Copia esta carpeta como `C:/xampp/htdocs/digiturno`.
2. Inicia **Apache** y **MySQL** en XAMPP Control Panel.
3. Abre `http://localhost/phpmyadmin`.
4. Importa `sql/digiturno.sql`.
5. Verifica `config/database.php` (por defecto root sin contraseña).
6. Abre `http://localhost/digiturno/`.

## Usuario inicial

- **Administrador**: `admin`
- **Asesor**: `asesor1`
- **Pantalla**: `tv1`
- **Contraseña para todos**: `Admin123*`

## Uso rápido

1. Inicia sesión como `admin`.
2. Crea/edita servicios en **Servicios** (prefijos configurables).
3. Abre **Kiosco** y genera turnos.
4. Entra con asesor en **Panel Asesor** y usa: llamar, repetir, finalizar, transferir, ausente.
5. Inicia sesión como `tv1` (rol pantalla) o administrador y abre **Pantalla TV** en otro monitor/pestaña.
6. Revisa métricas en **Reportes** con filtros por fecha, servicio, asesor y estado.

## Notas técnicas

- PHP con **PDO**.
- Actualización en tiempo real: **AJAX polling** (`tv-data`, cada 3 segundos).
- Métricas calculadas: tiempo de espera (`created_at -> called_at`) y atención (`called_at -> finished_at`).


## Seguridad y pruebas de regresión

- `tv` y `tv-data` requieren sesión con rol `pantalla` o `administrador`.
- Repetir, finalizar, marcar ausente y transferir solo modifican turnos del usuario
  actual que estén en estado `llamado`, incluso al enviar un POST manual.
- La TV inserta los valores de la base como texto, sin interpretarlos como HTML.

Desde la raíz del proyecto, con Python 3.9+ y Node.js 18+:

```sh
python3 -m unittest discover -s tests -p '*_test.py' -v
node --test tests/tv.test.js
node --check public/assets/js/app.js
php -l index.php
php -l app/views/dashboard.php
```

La prueba de matriz de acceso ejecuta el router y el helper de autenticación con
PHP CLI; si `php` no está en PATH, se informa como omitida. En XAMPP, agrega
`C:/xampp/php` al PATH antes de ejecutarla. Las consultas del asesor se prueban
con SQLite en memoria, y el renderizado con un doble de DOM en Node. Las pruebas
no usan ni modifican la base configurada de producción. No sustituyen una prueba
integral en XAMPP/MySQL.
