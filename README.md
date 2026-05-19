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
5. Abre **Pantalla TV** en otro monitor/pestaña para visualización en tiempo real.
6. Revisa métricas en **Reportes** con filtros por fecha, servicio, asesor y estado.

## Notas técnicas

- PHP con **PDO**.
- Actualización en tiempo real: **AJAX polling** (`tv-data`, cada 3 segundos).
- Métricas calculadas: tiempo de espera (`created_at -> called_at`) y atención (`called_at -> finished_at`).
