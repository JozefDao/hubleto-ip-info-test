<?php

namespace Hubleto\App\Custom\IpInfoTest\Controllers\Api;

class IpInfo
{
    /**
     * Hlavný vstup pre API endpoint.
     * Zavolá sa z Loader::init(), vypíše JSON a skončí.
     */
    public static function handle(): void
    {
        $ip = $_GET['ip'] ?? null;

        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) {
            self::respondJson(['error' => 'Neplatná IP adresa'], 400);
            return;
        }

        $data = self::fetchIpInfo($ip);

        if ($data === null) {
            self::respondJson(['error' => 'Chyba pri volaní externého API'], 502);
            return;
        }

        self::respondJson($data);
    }

    /**
     * Načíta informácie o IP adrese z externého API (ipapi.co)
     * a vráti ich v zjednodušenom tvare.
     */
    public static function fetchIpInfo(string $ip): ?array
    {
        $url = 'https://ipapi.co/' . urlencode($ip) . '/json/';

        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'header'  => "User-Agent: IpInfoTest/1.0\r\nAccept: application/json\r\n",
            ],
        ]);

        $json = @file_get_contents($url, false, $context);
        if ($json === false) {
            return null;
        }

        $raw = json_decode($json, true);
        if (!is_array($raw)) {
            return null;
        }

        return [
            'ip'       => $raw['ip']           ?? $ip,
            'country'  => $raw['country_name'] ?? null,
            'city'     => $raw['city']         ?? null,
            'timezone' => $raw['timezone']     ?? null,
            'isp'      => $raw['org']          ?? null,
            'lat'      => $raw['latitude']     ?? null,
            'lon'      => $raw['longitude']    ?? null,
        ];
    }

    private static function respondJson(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}