<?php

declare(strict_types=1);

/**
 * Simple MySQLi wrapper to manage database connections and queries.
 */
class Database
{
    private \mysqli $connection;

    /**
     * @param array{host:string,username:string,password:string,database:string,port?:int,charset?:string} $config
     */
    public function __construct(array $config)
    {
        $host = $config['host'] ?? 'localhost';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';
        $database = $config['database'] ?? '';
        $port = $config['port'] ?? 3306;
        $charset = $config['charset'] ?? 'utf8mb4';

        $this->connection = new \mysqli($host, $username, $password, $database, $port);

        if ($this->connection->connect_errno) {
            throw new \RuntimeException('Database connection failed: ' . $this->connection->connect_error);
        }

        if (!$this->connection->set_charset($charset)) {
            throw new \RuntimeException('Failed to set database charset: ' . $this->connection->error);
        }
    }

    public function getConnection(): \mysqli
    {
        return $this->connection;
    }

    /**
     * @param string $city
     * @param string $dayOfWeek
     * @param string $vehicleType
     * @param string $restrictedDigits
     * @param string $restrictionWindow
     */
    public function upsertRestriction(
        string $city,
        string $dayOfWeek,
        string $vehicleType,
        string $restrictedDigits,
        string $restrictionWindow
    ): void {
        $query = <<<SQL
            INSERT INTO pico_placa_restrictions (city, day_of_week, vehicle_type, restricted_digits, restriction_window, last_updated)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                restricted_digits = VALUES(restricted_digits),
                restriction_window = VALUES(restriction_window),
                last_updated = VALUES(last_updated)
        SQL;

        $statement = $this->connection->prepare($query);
        if ($statement === false) {
            throw new \RuntimeException('Failed to prepare statement: ' . $this->connection->error);
        }

        $statement->bind_param('sssss', $city, $dayOfWeek, $vehicleType, $restrictedDigits, $restrictionWindow);

        if (!$statement->execute()) {
            throw new \RuntimeException('Failed to execute statement: ' . $statement->error);
        }

        $statement->close();
    }

    /**
     * @return array<int, array{day_of_week:string, vehicle_type:string, restricted_digits:string, restriction_window:string, last_updated:string}>
     */
    public function getRestrictionsByDay(string $city, string $dayOfWeek): array
    {
        $query = <<<SQL
            SELECT day_of_week, vehicle_type, restricted_digits, restriction_window, last_updated
            FROM pico_placa_restrictions
            WHERE city = ? AND day_of_week = ?
            ORDER BY vehicle_type
        SQL;

        $statement = $this->connection->prepare($query);
        if ($statement === false) {
            throw new \RuntimeException('Failed to prepare statement: ' . $this->connection->error);
        }

        $statement->bind_param('ss', $city, $dayOfWeek);

        if (!$statement->execute()) {
            throw new \RuntimeException('Failed to execute statement: ' . $statement->error);
        }

        $result = $statement->get_result();
        $restrictions = $result->fetch_all(MYSQLI_ASSOC) ?: [];

        $statement->close();

        return $restrictions;
    }
}
