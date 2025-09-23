#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/src/Database.php';
require __DIR__ . '/src/SantaMartaPicoPlacaScraper.php';

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    fwrite(STDERR, "No se encontró config.php. Copie config.example.php y ajuste sus credenciales.\n");
    exit(1);
}

$config = require $configPath;

if (!isset($config['db']) || !is_array($config['db'])) {
    fwrite(STDERR, "La configuración de la base de datos es inválida.\n");
    exit(1);
}

try {
    $database = new Database($config['db']);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Error al conectar con la base de datos: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

$scraper = new SantaMartaPicoPlacaScraper($database);

$usage = <<<TXT
Uso:
  php scrape.php sync             Sincroniza la información desde la página oficial.
  php scrape.php get <día>        Muestra la restricción almacenada para el día indicado (ej. lunes, martes...).

TXT;

if ($argc < 2) {
    fwrite(STDOUT, $usage);
    exit(0);
}

$command = strtolower($argv[1]);

switch ($command) {
    case 'sync':
        try {
            $restrictions = $scraper->sync();
            fwrite(STDOUT, "Se sincronizaron " . count($restrictions) . " registros de pico y placa para Santa Marta.\n");
        } catch (Throwable $exception) {
            fwrite(STDERR, 'Ocurrió un error durante la sincronización: ' . $exception->getMessage() . PHP_EOL);
            exit(1);
        }
        break;

    case 'get':
        if ($argc < 3) {
            fwrite(STDERR, "Debe especificar el día que desea consultar.\n");
            exit(1);
        }

        $day = $argv[2];

        try {
            $results = $scraper->getRestrictionsByDay($day);
        } catch (Throwable $exception) {
            fwrite(STDERR, 'Error al consultar la base de datos: ' . $exception->getMessage() . PHP_EOL);
            exit(1);
        }

        if (empty($results)) {
            fwrite(STDOUT, "No se encontraron restricciones almacenadas para el día indicado. Ejecute 'php scrape.php sync'.\n");
            exit(0);
        }

        fwrite(STDOUT, "Restricciones para {$results[0]['day_of_week']} en Santa Marta:" . PHP_EOL);
        foreach ($results as $result) {
            fwrite(
                STDOUT,
                sprintf(
                    "  • Tipo de vehículo: %s | Dígitos restringidos: %s | Horario: %s (actualizado: %s)\n",
                    $result['vehicle_type'],
                    $result['restricted_digits'],
                    $result['restriction_window'],
                    $result['last_updated']
                )
            );
        }
        break;

    default:
        fwrite(STDOUT, $usage);
        exit(1);
}
