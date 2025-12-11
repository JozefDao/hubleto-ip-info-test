<?php

namespace Hubleto\App\Custom\IpInfoTest\Models;

class FavoriteIp
{
    private static ?\PDO $pdo = null;
    private static bool $initialized = false;

    /**
     * Získa PDO pripojenie na databázu podľa ConfigEnv.php
     */
    private static function getPdo(): \PDO
    {
        if (self::$pdo === null) {
            // __DIR__ = .../src/apps/IpInfoTest/Models
            // 4x hore: Models -> IpInfoTest -> apps -> src -> projekt root
            $config = require dirname(__DIR__, 4) . '/ConfigEnv.php';

            $host    = $config['dbHost'] ?? '127.0.0.1';
            $db      = $config['dbName'] ?? 'hubleto_dev';
            $charset = $config['dbCodepage'] ?? 'utf8mb4';

            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $db, $charset);
            self::$pdo = new \PDO($dsn, $config['dbUser'] ?? 'root', $config['dbPassword'] ?? '');
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        if (!self::$initialized) {
            self::ensureTable();
            self::$initialized = true;
        }

        return self::$pdo;
    }

    /**
     * Vytvorí tabuľku ipinfo_favorites, ak ešte neexistuje.
     */
    private static function ensureTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS ipinfo_favorites (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              ip VARCHAR(45) NOT NULL,
              country VARCHAR(100) NULL,
              city VARCHAR(100) NULL,
              timezone VARCHAR(64) NULL,
              isp VARCHAR(191) NULL,
              lat DECIMAL(10,6) NULL,
              lon DECIMAL(10,6) NULL,
              created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
              DEFAULT CHARSET=utf8mb4
              COLLATE=utf8mb4_unicode_ci;
        ";

        self::$pdo->exec($sql);
    }

    /**
     * Uloží IP adresu a jej údaje do tabuľky.
     */
    public static function add(array $data): void
    {
        $pdo = self::getPdo();

        $stmt = $pdo->prepare("
            INSERT INTO ipinfo_favorites (ip, country, city, timezone, isp, lat, lon)
            VALUES (:ip, :country, :city, :timezone, :isp, :lat, :lon)
        ");

        $stmt->execute([
            ':ip'       => $data['ip']       ?? '',
            ':country'  => $data['country']  ?? null,
            ':city'     => $data['city']     ?? null,
            ':timezone' => $data['timezone'] ?? null,
            ':isp'      => $data['isp']      ?? null,
            ':lat'      => $data['lat']      ?? null,
            ':lon'      => $data['lon']      ?? null,
        ]);
    }

    /**
     * Vráti všetky obľúbené IP adresy.
     */
    public static function all(): array
    {
        $pdo = self::getPdo();

        $stmt = $pdo->query("
            SELECT id, ip, country, city, timezone, isp, lat, lon, created_at
            FROM ipinfo_favorites
            ORDER BY created_at DESC
        ");

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Štatistika obľúbených IP podľa timezone.
     */
    public static function statsByTimezone(): array
    {
        $pdo = self::getPdo();

        $stmt = $pdo->query("
            SELECT 
              COALESCE(NULLIF(timezone, ''), 'Unknown') AS tz,
              COUNT(*) AS total
            FROM ipinfo_favorites
            GROUP BY tz
            ORDER BY total DESC, tz ASC
        ");

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}