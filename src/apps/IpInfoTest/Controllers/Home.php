<?php

namespace Hubleto\App\Custom\IpInfoTest\Controllers;

use Hubleto\App\Custom\IpInfoTest\Controllers\Api\IpInfo as IpInfoApi;
use Hubleto\App\Custom\IpInfoTest\Models\FavoriteIp;

class Home
{
    public static function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $ip        = isset($_GET['ip']) ? trim($_GET['ip']) : '';
        $result    = null;
        $error     = null;
        $flashInfo = null;

        // 1) Uloženie medzi obľúbené (POST)
        if ($method === 'POST' && isset($_POST['save_favorite'])) {
            $postIp = isset($_POST['ip']) ? trim($_POST['ip']) : '';

            if (!filter_var($postIp, FILTER_VALIDATE_IP)) {
                $flashInfo = 'IP sa nepodarilo uložiť – neplatná IP adresa.';
            } else {
                $data = IpInfoApi::fetchIpInfo($postIp);
                if ($data === null) {
                    $flashInfo = 'IP sa nepodarilo uložiť – externé API neodpovedalo.';
                } else {
                    try {
                        FavoriteIp::add($data);
                        $flashInfo = 'IP adresa bola uložená medzi obľúbené.';
                    } catch (\Throwable $e) {
                        $flashInfo = 'Chyba pri ukladaní do databázy.';
                    }
                }
            }

            // Redirect na GET, aby sme predišli opakovanému POST-u
            header('Location: /ipinfotest?ip=' . urlencode($postIp) . '&msg=' . urlencode($flashInfo));
            exit;
        }

        // 2) Ak je v URL msg, zobraz ho
        if (isset($_GET['msg']) && is_string($_GET['msg'])) {
            $flashInfo = $_GET['msg'];
        }

        // 3) Načítanie info o IP (GET)
        if ($ip !== '') {
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $error = 'Neplatná IP adresa';
            } else {
                $result = IpInfoApi::fetchIpInfo($ip);

                if ($result === null) {
                    $error = 'Chyba pri volaní externého API';
                }
            }
        }

        // 4) Načítanie obľúbených IP + štatistík z DB
        try {
            $favorites     = FavoriteIp::all();
            $timezoneStats = FavoriteIp::statsByTimezone();
        } catch (\Throwable $e) {
            $favorites     = [];
            $timezoneStats = [];
            if ($error === null) {
                $error = 'Chyba pri načítaní obľúbených IP z databázy.';
            }
        }

        // HTML výstup
        header('Content-Type: text/html; charset=utf-8');

        ?>
        <!DOCTYPE html>
        <html lang="sk">
        <head>
            <meta charset="utf-8">
            <title>IpInfoTest</title>
            <style>
                body {
                    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                    margin: 0;
                    padding: 24px;
                    background: #f4f5f7;
                }
                .card {
                    background: #fff;
                    border-radius: 8px;
                    padding: 16px 20px;
                    max-width: 950px;
                    margin: 0 auto;
                    box-shadow: 0 1px 3px rgba(15,23,42,0.1);
                }
                h1 {
                    font-size: 20px;
                    margin-top: 0;
                    margin-bottom: 12px;
                }
                label {
                    font-weight: 600;
                    display: block;
                    margin-bottom: 4px;
                }
                input[type="text"] {
                    padding: 6px 10px;
                    border-radius: 4px;
                    border: 1px solid #cbd5e1;
                    width: 100%;
                    box-sizing: border-box;
                }
                button {
                    padding: 6px 12px;
                    border-radius: 4px;
                    border: none;
                    background: #2563eb;
                    color: white;
                    font-weight: 500;
                    cursor: pointer;
                    margin-left: 8px;
                }
                button:disabled {
                    background: #94a3b8;
                    cursor: default;
                }
                .row {
                    display: flex;
                    gap: 8px;
                    align-items: center;
                    margin-bottom: 4px;
                }
                .help {
                    font-size: 12px;
                    color: #64748b;
                    margin-top: 4px;
                }
                .alert {
                    margin-top: 12px;
                    padding: 8px 10px;
                    border-radius: 4px;
                    font-size: 14px;
                }
                .alert-error {
                    background: #fee2e2;
                    color: #b91c1c;
                }
                .alert-info {
                    background: #e0f2fe;
                    color: #0369a1;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 16px;
                    font-size: 14px;
                }
                th, td {
                    text-align: left;
                    padding: 6px 4px;
                    border-bottom: 1px solid #e2e8f0;
                }
                th {
                    font-weight: 600;
                    color: #475569;
                }
                .section-title {
                    margin-top: 20px;
                    margin-bottom: 6px;
                    font-size: 16px;
                    font-weight: 600;
                }
                .muted {
                    font-size: 13px;
                    color: #64748b;
                }
                .two-cols {
                    display: grid;
                    grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr);
                    gap: 24px;
                    margin-top: 16px;
                }
                .badge {
                    display: inline-block;
                    padding: 2px 6px;
                    border-radius: 999px;
                    background: #e2e8f0;
                    font-size: 11px;
                    color: #475569;
                }
            </style>
        </head>
        <body>
        <div class="card">
            <h1>IpInfoTest – informácie o IP adrese</h1>

            <form method="GET">
                <label for="ip">IP adresa</label>
                <div class="row">
                    <input
                        type="text"
                        id="ip"
                        name="ip"
                        value="<?php echo htmlspecialchars($ip, ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="napr. 8.8.8.8"
                    />
                    <button type="submit">Načítať informácie</button>
                </div>
                <div class="help">
                    Zadaj IP adresu, ktorú chceš analyzovať. Aplikácia zavolá externé API a zobrazí dostupné informácie.
                </div>
            </form>

            <?php if ($flashInfo !== null): ?>
                <div class="alert alert-info">
                    <?php echo htmlspecialchars($flashInfo, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if ($error !== null): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <div class="two-cols">
                <div>
                    <?php if ($result !== null): ?>
                        <div class="section-title">Výsledok</div>
                        <table>
                            <tbody>
                            <tr>
                                <th>IP adresa</th>
                                <td><?php echo htmlspecialchars($result['ip'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th>Krajina</th>
                                <td><?php echo htmlspecialchars($result['country'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th>Mesto</th>
                                <td><?php echo htmlspecialchars($result['city'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th>Timezone</th>
                                <td><?php echo htmlspecialchars($result['timezone'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th>ISP / ASN</th>
                                <td><?php echo htmlspecialchars($result['isp'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th>GPS súradnice</th>
                                <td>
                                    <?php
                                    if (!empty($result['lat']) && !empty($result['lon'])) {
                                        echo htmlspecialchars($result['lat'] . ', ' . $result['lon'], ENT_QUOTES, 'UTF-8');
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <form method="POST" style="margin-top: 12px;">
                            <input type="hidden" name="ip"
                                   value="<?php echo htmlspecialchars($result['ip'] ?? $ip, ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" name="save_favorite" value="1">
                                Uložiť medzi obľúbené
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="section-title">Výsledok</div>
                        <p class="muted">
                            Zatiaľ nebola vyhľadaná žiadna IP. Zadaj IP adresu vyššie a potvrď.
                        </p>
                    <?php endif; ?>
                </div>

                <div>
                    <div class="section-title">Obľúbené IP adresy</div>
                    <div
                        id="ipinfotest-react-root"
                        data-favorites="<?php echo htmlspecialchars(json_encode($favorites, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                        data-base-url="/ipinfotest"
                    ></div>
                </div>
            </div>

            <div class="section-title" style="margin-top: 24px;">Štatistiky obľúbených IP podľa timezone</div>
            <?php if (!empty($timezoneStats)): ?>
                <table>
                    <thead>
                    <tr>
                        <th>Timezone</th>
                        <th>Počet IP adries</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($timezoneStats as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['tz'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo (int)$row['total']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="muted">
                    Zatiaľ nemáme žiadne obľúbené IP adresy, takže nie je čo štatisticky zobraziť.
                </p>
            <?php endif; ?>
        </div>

        <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
        <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
        <script>
        (function () {
          var e = React.createElement;

          // --- React komponent: Form na rýchle vyhľadanie IP ---
          function IpInfoForm(props) {
            var useState = React.useState;
            var state = useState('');
            var ip = state[0];
            var setIp = state[1];

            function handleSubmit(ev) {
              ev.preventDefault();
              var trimmed = (ip || '').trim();
              if (!trimmed) return;
              window.location.href = props.baseUrl + '?ip=' + encodeURIComponent(trimmed);
            }

            return e('form', { onSubmit: handleSubmit, style: { marginBottom: '12px' } },
              e('label', { style: { fontWeight: 600, display: 'block', marginBottom: 4 } }, 'Rýchle vyhľadanie IP'),
              e('div', { style: { display: 'flex', gap: 8, alignItems: 'center' } },
                e('input', {
                  type: 'text',
                  value: ip,
                  onChange: function (ev) { setIp(ev.target.value); },
                  placeholder: 'napr. 1.1.1.1',
                  style: {
                    padding: '6px 10px',
                    borderRadius: 4,
                    border: '1px solid #cbd5e1',
                    flex: 1
                  }
                }),
                e('button', {
                  type: 'submit',
                  style: {
                    padding: '6px 12px',
                    borderRadius: 4,
                    border: 'none',
                    background: '#2563eb',
                    color: 'white',
                    fontWeight: 500,
                    cursor: 'pointer'
                  }
                }, 'Otvoriť')
              )
            );
          }

          // --- React komponent: Tabuľka obľúbených IP ---
          function FavoritesTable(props) {
            var favorites = props.favorites || [];
            var baseUrl = props.baseUrl || '/ipinfotest';

            if (!favorites.length) {
              return e('p', { className: 'muted' },
                'Zatiaľ nemáš uložené žiadne obľúbené IP adresy. Vyhľadaj IP vo formulári vľavo a klikni na „Uložiť medzi obľúbené“.'
              );
            }

            return e('table', null,
              e('thead', null,
                e('tr', null,
                  e('th', null, 'IP'),
                  e('th', null, 'Krajina / mesto'),
                  e('th', null, 'Timezone')
                )
              ),
              e('tbody', null,
                favorites.map(function (fav) {
                  var parts = [];
                  if (fav.country) parts.push(fav.country);
                  if (fav.city) parts.push(fav.city);

                  return e('tr', { key: fav.id },
                    e('td', null,
                      e('a', { href: baseUrl + '?ip=' + encodeURIComponent(fav.ip) }, fav.ip)
                    ),
                    e('td', null, parts.length ? parts.join(' / ') : '—'),
                    e('td', null,
                      fav.timezone
                        ? e('span', { className: 'badge' }, fav.timezone)
                        : '—'
                    )
                  );
                })
              )
            );
          }

          // --- wrapper komponent pre panel ---
          function IpInfoSidePanel(props) {
            return e('div', null,
              e(IpInfoForm, { baseUrl: props.baseUrl }),
              e(FavoritesTable, { favorites: props.favorites, baseUrl: props.baseUrl })
            );
          }

          // --- Mount Reactu po načítaní stránky ---
          document.addEventListener('DOMContentLoaded', function () {
            var rootEl = document.getElementById('ipinfotest-react-root');
            if (!rootEl) return;

            var favoritesJson = rootEl.getAttribute('data-favorites') || '[]';
            var favorites;
            try {
              favorites = JSON.parse(favoritesJson);
            } catch (err) {
              console.error('Cannot parse favorites JSON', err);
              favorites = [];
            }

            var baseUrl = rootEl.getAttribute('data-base-url') || '/ipinfotest';
            var root = ReactDOM.createRoot(rootEl);
            root.render(e(IpInfoSidePanel, { favorites: favorites, baseUrl: baseUrl }));
          });
        })();
        </script>
        </body>
        </html>
        <?php
        exit;
    }
}