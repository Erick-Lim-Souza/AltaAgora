<?php
// ═══════════════════════════════════════════════════════
//  AltaAgora — functions.php
//  Multi-API: Brapi (Ações) + HG Brasil (Índices & Moedas)
// ═══════════════════════════════════════════════════════
require_once 'config.php';

function fetchApi(string $url, string $cacheSufix, int $cacheTime): array {
    $cacheKey  = md5($url . $cacheSufix);
    $cacheFile = CACHE_DIR . $cacheKey . '.json';

    if (file_exists($cacheFile)) {
        $age = time() - filemtime($cacheFile);
        if ($age < $cacheTime) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return array_merge($cached, ['_cached' => true, '_cache_age' => $age]);
            }
        }
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT      => APP_NAME . '/' . APP_VERSION,
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErrno = curl_errno($ch);
    $errorMsg  = curl_error($ch);
    curl_close($ch);

    if ($curlErrno || $httpCode !== 200) {
        error_log("[AltaAgora] API error — HTTP $httpCode | $errorMsg | URL: " . preg_replace('/(token|key)=[^&]+/', '$1=***', $url));
        return ['error' => true, 'code' => $httpCode];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE || isset($data['error'])) {
        error_log("[AltaAgora] API Data/JSON error — URL: " . preg_replace('/(token|key)=[^&]+/', '$1=***', $url));
        return ['error' => true, 'msg' => 'Invalid data format'];
    }

    file_put_contents($cacheFile, json_encode($data), LOCK_EX);
    return array_merge($data, ['_cached' => false, '_cache_age' => 0]);
}

/**
 * Retorna ações da BRAPI baseadas na ordenação (desc = Altas, asc = Baixas)
 */
function getBrapiStocks(string $order = 'desc', int $limit = 15): array {
    if (!BRAPI_KEY_SET) return [];

    $url = BRAPI_BASE_URL . '/quote/list?sortBy=change&sortOrder=' . $order . '&limit=' . $limit . '&token=' . BRAPI_KEY;
    $data = fetchApi($url, 'brapi_stocks_' . $order, CACHE_TIME_BRAPI);

    if (isset($data['error']) || empty($data['stocks'])) return [];

    $stocks = [];
    foreach ($data['stocks'] as $stock) {
        $price = (float)($stock['close'] ?? 0);
        $changePct = (float)($stock['change'] ?? 0);
        
        $oldPrice = $changePct != 0 ? ($price / (1 + ($changePct / 100))) : $price;
        $changePrice = $price - $oldPrice;

        $stocks[] = [
            'symbol'         => h($stock['stock']),
            'name'           => h($stock['name'] ?? $stock['stock']),
            'price'          => $price,
            'change_percent' => $changePct,
            'change_price'   => $changePrice,
            'volume'         => (int)($stock['volume'] ?? 0),
            'logo'           => !empty($stock['logo']) ? filter_var($stock['logo'], FILTER_VALIDATE_URL) : null,
        ];
    }
    return $stocks;
}

/**
 * Índices e Moedas do mercado via HG BRASIL
 */
function getHgData(): array {
    if (!API_KEY_SET) return [];
    
    // Removido o filtro "fields" para ele trazer Moedas e Índices na mesma chamada
    $url = API_BASE_URL . '?key=' . API_KEY;
    $data = fetchApi($url, 'hg_all_data', CACHE_TIME_HG);
    
    return $data['results'] ?? [];
}

function getSecondsUntilRefresh(): int {
    if (!API_KEY_SET) return PAGE_REFRESH;
    $cacheFile = CACHE_DIR . md5(API_BASE_URL . '?key=' . API_KEY . 'hg_all_data') . '.json';
    
    if (!file_exists($cacheFile)) return PAGE_REFRESH;
    
    $age = time() - filemtime($cacheFile);
    return max(0, PAGE_REFRESH - $age);
}

// ── Helpers ──────────────────────────────────────────────
function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES | ENT_HTML5, 'UTF-8'); }

function formatMoney(float $v): string {
    $neg = $v < 0;
    return ($neg ? '-' : '') . 'R$&nbsp;' . number_format(abs($v), 2, ',', '.');
}

function formatVariation(float $v): array {
    $pos       = $v >= 0;
    $class     = $pos ? 'positive' : 'negative';
    $symbol    = $pos ? '▲' : '▼';
    $formatted = number_format(abs($v), 2, ',', '.');
    return [
        'class'  => $class, 'symbol' => $symbol, 'value'  => $formatted, 'raw'    => $v,
        'html'   => "<span class='{$class}'>{$symbol} {$formatted}%</span>",
    ];
}

function formatVolume(int $v): string {
    if ($v >= 1_000_000_000) return number_format($v / 1_000_000_000, 1, ',', '.') . 'B';
    if ($v >= 1_000_000)     return number_format($v / 1_000_000, 1, ',', '.') . 'M';
    if ($v >= 1_000)         return number_format($v / 1_000, 1, ',', '.') . 'K';
    return (string)$v;
}
