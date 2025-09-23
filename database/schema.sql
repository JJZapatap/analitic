CREATE TABLE IF NOT EXISTS pico_placa_restrictions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100) NOT NULL,
    day_of_week VARCHAR(20) NOT NULL,
    vehicle_type VARCHAR(100) NOT NULL,
    restricted_digits VARCHAR(255) NOT NULL,
    restriction_window VARCHAR(255) NOT NULL,
    last_updated DATETIME NOT NULL,
    UNIQUE KEY unique_restriction (city, day_of_week, vehicle_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
