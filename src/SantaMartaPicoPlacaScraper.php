<?php

declare(strict_types=1);

class SantaMartaPicoPlacaScraper
{
    private const CITY = 'Santa Marta';
    private const SOURCE_URL = 'https://www.transitosantamarta.gov.co/pico-y-placa';

    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Descarga el HTML de la página oficial y lo sincroniza con la base de datos.
     *
     * @return array<int, array{day_of_week:string, vehicle_type:string, restricted_digits:string, restriction_window:string}>
     */
    public function sync(): array
    {
        $html = $this->fetchHtml(self::SOURCE_URL);
        $restrictions = $this->parseRestrictions($html);

        foreach ($restrictions as $restriction) {
            $this->database->upsertRestriction(
                self::CITY,
                $restriction['day_of_week'],
                $restriction['vehicle_type'],
                $restriction['restricted_digits'],
                $restriction['restriction_window']
            );
        }

        return $restrictions;
    }

    /**
     * @param string $dayOfWeek
     *
     * @return array<int, array{day_of_week:string, vehicle_type:string, restricted_digits:string, restriction_window:string, last_updated:string}>
     */
    public function getRestrictionsByDay(string $dayOfWeek): array
    {
        $normalizedDay = $this->normalizeDay($dayOfWeek);
        return $this->database->getRestrictionsByDay(self::CITY, $normalizedDay);
    }

    private function fetchHtml(string $url): string
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new \RuntimeException('No fue posible inicializar cURL.');
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Analitic Pico y Placa Scraper/1.0 (+https://example.com)',
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('Error al descargar la página: ' . $error);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode >= 400) {
            throw new \RuntimeException(sprintf('La página devolvió un código HTTP no exitoso: %d', $statusCode));
        }

        return $response;
    }

    /**
     * @param string $html
     *
     * @return array<int, array{day_of_week:string, vehicle_type:string, restricted_digits:string, restriction_window:string}>
     */
    private function parseRestrictions(string $html): array
    {
        $cleanHtml = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        $internalErrors = libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($cleanHtml);
        libxml_use_internal_errors($internalErrors);

        $xpath = new \DOMXPath($dom);
        $tables = $xpath->query('//table');

        $restrictions = [];

        if ($tables !== false) {
            foreach ($tables as $table) {
                $headers = $xpath->query('.//th', $table);
                $headerTexts = [];
                foreach ($headers as $header) {
                    $headerTexts[] = $this->normalizeHeaderText($header->textContent);
                }

                $isPicoYPlacaTable = $this->looksLikeRestrictionTable($headerTexts);

                if (!$isPicoYPlacaTable) {
                    continue;
                }

                $rows = $xpath->query('.//tbody/tr|.//tr[not(ancestor::thead)]', $table);
                if ($rows === false) {
                    continue;
                }

                foreach ($rows as $row) {
                    $cells = $xpath->query('.//td', $row);
                    if ($cells === false || $cells->length < 2) {
                        continue;
                    }

                    $day = $this->normalizeDay($cells->item(0)?->textContent ?? '');
                    if ($day === '') {
                        continue;
                    }

                    $digits = trim((string) $cells->item(1)?->textContent);
                    $restrictionWindow = $cells->length >= 3
                        ? trim((string) $cells->item(2)?->textContent)
                        : '';

                    $restrictions[] = [
                        'day_of_week' => $day,
                        'vehicle_type' => 'General',
                        'restricted_digits' => $this->sanitizeWhitespace($digits),
                        'restriction_window' => $this->sanitizeWhitespace($restrictionWindow) ?: 'No especificado',
                    ];
                }
            }
        }

        if (empty($restrictions)) {
            throw new \RuntimeException('No fue posible extraer la información de pico y placa de la página fuente.');
        }

        return $restrictions;
    }

    private function normalizeDay(string $day): string
    {
        $normalized = $this->sanitizeWhitespace($day);
        $normalized = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], mb_strtolower($normalized, 'UTF-8'));

        $map = [
            'lunes' => 'Lunes',
            'martes' => 'Martes',
            'miercoles' => 'Miércoles',
            'jueves' => 'Jueves',
            'viernes' => 'Viernes',
            'sabado' => 'Sábado',
            'domingo' => 'Domingo',
        ];

        return $map[$normalized] ?? '';
    }

    /**
     * @param array<int, string> $headers
     */
    private function looksLikeRestrictionTable(array $headers): bool
    {
        if (empty($headers)) {
            return false;
        }

        $hayDia = false;
        $hayPlaca = false;

        foreach ($headers as $header) {
            if (str_contains($header, 'dia')) {
                $hayDia = true;
            }

            if (str_contains($header, 'placa') || str_contains($header, 'digito')) {
                $hayPlaca = true;
            }
        }

        return $hayDia && $hayPlaca;
    }

    private function normalizeHeaderText(string $text): string
    {
        $text = $this->sanitizeWhitespace($text);
        $text = mb_strtolower($text, 'UTF-8');
        $text = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $text);

        return $text;
    }

    private function sanitizeWhitespace(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }
}
