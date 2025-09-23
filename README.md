# Scraper de Pico y Placa (Santa Marta)

Este proyecto proporciona un pequeño _scraper_ en PHP que descarga la información de **pico y placa** publicada en la página oficial de tránsito de Santa Marta y la almacena en una base de datos MySQL. Posteriormente permite consultar las restricciones por día a través de la línea de comandos.

## Requisitos

- PHP 8.1 o superior con las extensiones `curl` y `mysqli` habilitadas.
- Servidor MySQL/MariaDB accesible desde la máquina donde se ejecutará el script.

## Instalación

1. Clonar el repositorio y acceder a la carpeta del proyecto.
2. Crear la base de datos (por ejemplo `pico_placa`) y ejecutar el script SQL que define la tabla:

   ```bash
   mysql -u <usuario> -p <basededatos> < database/schema.sql
   ```

3. Configurar las credenciales de conexión. Puede hacerlo de dos maneras:

   - Editar directamente `config.php` con sus valores.
   - O bien exportar variables de entorno (`DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE`, `DB_PORT`, `DB_CHARSET`).

   También se proporciona `config.example.php` como referencia.

4. Instalar las dependencias de PHP si fuese necesario (no se utiliza Composer en este ejemplo).

## Uso

El script principal es `scrape.php` y cuenta con dos subcomandos:

```bash
php scrape.php sync             # Descarga y guarda la información de pico y placa en la base de datos.
php scrape.php get lunes        # Consulta las restricciones almacenadas para el día indicado.
```

> **Nota:** el scrapper requiere acceso a `https://www.transitosantamarta.gov.co/pico-y-placa`. Verifique que su servidor tenga salida a Internet y que la página siga publicando la información en formato tabular. En caso de cambios en el HTML, puede ser necesario ajustar `SantaMartaPicoPlacaScraper`.

## Arquitectura

- `src/Database.php`: Pequeño _wrapper_ sobre MySQLi para operaciones básicas.
- `src/SantaMartaPicoPlacaScraper.php`: Contiene la lógica de descarga y parseo del HTML.
- `scrape.php`: Punto de entrada CLI para sincronizar o consultar la base de datos.
- `database/schema.sql`: Definición de la tabla `pico_placa_restrictions`.

## Extensiones futuras

- Añadir soporte para múltiples ciudades reutilizando la misma infraestructura.
- Programar tareas automáticas (cron) para actualizar la información cada día.
- Exponer la información mediante una API o interfaz web.
