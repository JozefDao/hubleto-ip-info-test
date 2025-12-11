<?php

namespace Hubleto\App\Custom\IpInfoTest;

use Hubleto\App\Custom\IpInfoTest\Controllers\Home as HomePage;
use Hubleto\App\Custom\IpInfoTest\Controllers\Api\IpInfo as IpInfoApi;

class Loader extends \Hubleto\Framework\App
{
    // init
    public function init(): void
    {
        // Vlastný jednoduchý routing pre /ipinfotest
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '/';

        if (strpos($path, '/ipinfotest') === 0) {
            // API endpoint: /ipinfotest/api/ipinfo?ip=...
            if ($path === '/ipinfotest/api/ipinfo' || $path === '/ipinfotest/api/ipinfo/') {
                IpInfoApi::handle();
                return;
            }

            // Hlavná stránka aplikácie
            HomePage::handle();
            return;
        }

        // Pre všetky ostatné URL nech sa správa ako štandardný Hubleto app
        parent::init();

    }

    // installTables
    public function installTables(int $round): void
    {
        if ($round == 1) {
            // DO NOT DELETE FOLLOWING LINE, OR `php hubleto` WILL NOT GENERATE CODE HERE
            //@hubleto-cli:install-tables
        }
        if ($round == 2) {
            // do something in the 2nd round, if required
        }
        if ($round == 3) {
            // do something in the 3rd round, if required
        }
    }

    // generateDemoData
    public function generateDemoData(): void
    {
        // Create any demo data to promote your app.
    }
}