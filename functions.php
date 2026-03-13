<?php
// ═══════════════════════════════════════════════════════
//  AltaAgora — functions.php
// ═══════════════════════════════════════════════════════
require_once 'config.php';

/**
 * Faz requisição à API HG Brasil com cache em disco
 */
function getApiData(string $endpoint, array $params = []): array {
    if (!API_KEY_SET) {
        return ['error' => true, 'msg' => 'API key não configurada'];
    }

    $params['key'] = API_KEY;
    $cacheKey      = md5($endpoint . serialize($params));
    $cacheFile     = CACHE_DIR . $cacheKey . '.json';

    // Cache válido
    if (file_exists($cacheFile)) {
        $age = time() - filemtime($cacheFile);
        if ($age < CACHE_TIME) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return array_merge($cached, ['_cached' => true, '_cache_age' => $age]);
            }
        }
    }

    $url = rtrim(API_BASE_URL, '/') . '/' . ltrim($endpoint, '/');
    if (!empty($params)) $url .= '?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT      => APP_NAME . '/' . APP_VERSION,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_MAXREDIRS      => 0,
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErrno = curl_errno($ch);
    curl_close($ch);

    if ($curlErrno || $httpCode !== 200) {
        error_log("[AltaAgora] API error — HTTP $httpCode | cURL $curlErrno | $url");
        return ['error' => true, 'code' => $httpCode];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("[AltaAgora] JSON parse error — $url");
        return ['error' => true, 'msg' => 'JSON inválido'];
    }

    file_put_contents($cacheFile, json_encode($data), LOCK_EX);
    return array_merge($data, ['_cached' => false, '_cache_age' => 0]);
}

/**
 * Retorna o timestamp da última atualização do cache
 */
function getCacheTimestamp(): int {
    $files = glob(CACHE_DIR . '*.json');
    if (empty($files)) return 0;
    $files = array_filter($files, fn($f) => basename($f) !== '.htaccess');
    if (empty($files)) return 0;
    return (int)max(array_map('filemtime', $files));
}

/**
 * Segundos restantes até o próximo refresh
 */
function getSecondsUntilRefresh(): int {
    $ts = getCacheTimestamp();
    if ($ts === 0) return 0;
    $elapsed   = time() - $ts;
    $remaining = CACHE_TIME - $elapsed;
    return max(0, $remaining);
}

/**
 * Top ações em alta, ordenadas por variação %
 */
function getTopGainers(int $limit = 15): array {
    $data = getApiData('stock_price', ['symbol' => 'get-high']);

    if (!isset($data['results']) || !is_array($data['results'])) {
        return [];
    }

    $stocks = [];
    foreach ($data['results'] as $symbol => $stock) {
        if (!isset($stock['price'])) continue;
        $stocks[] = [
            'symbol'         => h($symbol),
            'name'           => h($stock['name']           ?? $symbol),
            'price'          => (float)($stock['price']          ?? 0),
            'change_percent' => (float)($stock['change_percent'] ?? 0),
            'change_price'   => (float)($stock['change_price']   ?? 0),
            'volume'         => (int)($stock['volume']           ?? 0),
            'logo'           => isset($stock['logo']['small'])
                                    ? filter_var($stock['logo']['small'], FILTER_VALIDATE_URL) ?: null
                                    : null,
        ];
    }

    usort($stocks, fn($a, $b) => $b['change_percent'] <=> $a['change_percent']);
    return array_slice($stocks, 0, $limit);
}

/**
 * Índices do mercado
 */
function getMarketIndices(): array {
    $data = getApiData('', ['fields' => 'stocks']);
    return $data['results']['stocks'] ?? [];
}

// ── Helpers ──────────────────────────────────────────────

function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

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
        'class'  => $class,
        'symbol' => $symbol,
        'value'  => $formatted,
        'raw'    => $v,
        'html'   => "<span class='{$class}'>{$symbol} {$formatted}%</span>",
    ];
}

function formatVolume(int $v): string {
    if ($v >= 1_000_000_000) return number_format($v / 1_000_000_000, 1, ',', '.') . 'B';
    if ($v >= 1_000_000)     return number_format($v / 1_000_000, 1, ',', '.') . 'M';
    if ($v >= 1_000)         return number_format($v / 1_000, 1, ',', '.') . 'K';
    return (string)$v;
}
