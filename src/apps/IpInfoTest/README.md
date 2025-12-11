# IpInfoTest (Hubleto custom app)

Custom aplikácia pre Hubleto ERP, ktorá umožňuje zadať IP adresu, získať o nej informácie z externého API a pracovať s obľúbenými IP adresami.

---

## Funkcionalita

### Základ

- Formulár pre zadanie **IP adresy**.
- Volanie externého API `ipapi.co` a zobrazenie údajov:
  - IP adresa
  - krajina
  - mesto
  - timezone
  - ISP / ASN
  - GPS súradnice (lat, lon)
- Výsledok je zobrazený v prehľadnej tabuľke.

### Práca s obľúbenými IP

- Tlačidlo **„Uložiť medzi obľúbené“** uloží aktuálne zobrazenú IP do databázy.
- Vlastný model `FavoriteIp` vytvára tabuľku `ipinfo_favorites` (MySQL) a pracuje s dátami.
- Vpravo je panel **„Obľúbené IP adresy“**, kde vidno:
  - IP adresu (klikateľný link – po kliknutí sa načíta detail danej IP),
  - kombináciu krajina / mesto,
  - timezone (badge).

### Bonus #1 – React komponenty (Form + Table)

Panel **„Obľúbené IP adresy“** je renderovaný pomocou Reactu:

- **`IpInfoForm`** – malý formulár „Rýchle vyhľadanie IP“ v Reacte
  - umožňuje rýchlo zadať IP a presmeruje na `/ipinfotest?ip=…`.
- **`FavoritesTable`** – React tabuľka zobrazujúca obľúbené IP adresy
  - dáta dostáva z backendu cez `data-favorites` (JSON),
  - používa rovnaké informácie ako PHP časť (IP, country/city, timezone).

React je použitý v „plain JS“ podobe cez CDN (`react` + `react-dom`), bez build toolov – z dôvodu jednoduchšej integrácie do existujúceho Hubleto projektu.

### Bonus #2 – Štatistiky podľa timezone

Pod hlavným panelom sa nachádza sekcia:

> **Štatistiky obľúbených IP podľa timezone**

- Dáta pochádzajú z metódy `FavoriteIp::statsByTimezone()`.
- V tabuľke sú zobrazené dvojice:
  - `Timezone`
  - `Počet IP adries`

---

## Použité externé API

Pôvodné zadanie uvádzalo ako príklad API:

> `https://api.techniknews.net/ipgeo`

Pri implementácii však toto API neodpovedalo konzistentne, preto je použitá služba:

- **`https://ipapi.co/{IP}/json/`**

Dôvody:

- stabilná a dobre zdokumentovaná IP geolokačná služba,
- podobná štruktúra údajov (country, city, timezone, org/ISP, GPS),
- jednoduchá integrácia v PHP (`file_get_contents` + `json_decode`).

Mapovanie polí prebieha v triede:

- `src/apps/IpInfoTest/Controllers/Api/IpInfo.php`  
  - metóda `fetchIpInfo(string $ip): ?array`

---

## Technologický stack

- **PHP 8.x**
- **MySQL / MariaDB**
- **Hubleto ERP** (balík `hubleto/erp-project`)
- **React 18** (UMD build cez CDN)
- čistý JS bez bundlerov (React komponenty sú definované priamo v `Home` controllery)

---

## Štruktúra kódu

Kľúčové súbory custom appky:

src/
  apps/
    IpInfoTest/
      Loader.php                       # registrácia appky, routing /ipinfotest a /ipinfotest/api/ipinfo
      Controllers/
        Home.php                       # hlavná stránka, HTML, React mount, logika formulárov
        Api/
          IpInfo.php                   # volanie externého API ipapi.co
      Models/
        FavoriteIp.php                 # model + DB tabuľka ipinfo_favorites (CRUD + štatistiky)

## Rýchly návod na spustenie

1. **Predpoklady**
   - PHP 8.x, Composer  
   - MySQL/MariaDB (napr. XAMPP)  
   - zapnuté `allow_url_fopen` v `php.ini` (kvôli volaniu API `ipapi.co`)

2. **Databáza**
   - vytvoríme DB (alebo ju upravíme podľa `ConfigEnv.php`):

   ```sql
   CREATE DATABASE hubleto_dev
     CHARACTER SET utf8mb4
     COLLATE utf8mb4_unicode_ci;

3. **Inštalácia závislostí**
    - composer install

4. **Inicializácia Hubleto**
    - ak ConfigEnv.php ešte neexistuje:
        - php hubleto init
        - pri otázkach nastavíme: projectUrl napr. http://localhost:8000/
        - dbHost 127.0.0.1, dbUser root, dbName hubleto_dev (alebo podľa seba)

5. **Spustenie servera**
    - php -S localhost:8000

6. **Používanie**
    - hlavné Hubleto UI: http://localhost:8000/
    - custom appka IpInfoTest: http://localhost:8000/ipinfotest

    Na /ipinfotest:
        - zadáme IP → Načítať informácie
        - Uložiť medzi obľúbené pridá IP do DB
        - vpravo uvidíme React panel s obľúbenými IP a dole štatistiku podľa timezone.