<?php
// ═══════════════════════════════════════════════════════
//  AltaAgora — config.php
//  Configuração central · Multi-API (HG Brasil + Brapi)
// ═══════════════════════════════════════════════════════

// Bloquear acesso direto a este arquivo
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    http_response_code(403); exit('Forbidden');
}

// ── Cabeçalhos de segurança HTTP ────────────────────────
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
    header("Content-Security-Policy: default-src 'self'; "
         . "img-src 'self' https: data:; "
         . "style-src 'self' https://fonts.googleapis.com https://use.typekit.net 'unsafe-inline'; "
         . "font-src 'self' https://fonts.gstatic.com https://use.typekit.net data:; "
         . "script-src 'self' 'unsafe-inline';");
}

// ── API Keys via variável de ambiente (Render Dashboard) ─
$hgApiKey    = '';
$brapiApiKey = '';

// Tenta buscar de 3 formas diferentes para bypass do Docker
if (empty($hgApiKey)) $hgApiKey = getenv('HG_API_KEY') ?: ($_ENV['HG_API_KEY'] ?? ($_SERVER['HG_API_KEY'] ?? ''));
if (empty($brapiApiKey)) $brapiApiKey = getenv('BRAPI_KEY') ?: ($_ENV['BRAPI_KEY'] ?? ($_SERVER['BRAPI_KEY'] ?? ''));

// Fallback para desenvolvimento local via .env.local
if ((empty($hgApiKey) || empty($brapiApiKey)) && file_exists(__DIR__ . '/.env.local')) {
    foreach (file(__DIR__ . '/.env.local', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            if ($key === 'HG_API_KEY') $hgApiKey = $val;
            if ($key === 'BRAPI_KEY')  $brapiApiKey = $val;
        }
    }
}

// Registra no log do Render se as chaves foram encontradas
error_log("[AltaAgora] HG Key: " . (!empty($hgApiKey) ? "OK" : "FALTA") . " | Brapi Key: " . (!empty($brapiApiKey) ? "OK" : "FALTA"));

// HG Brasil Auth (Índices)
define('API_KEY', $hgApiKey);
define('API_KEY_SET', !empty($hgApiKey));
define('API_BASE_URL', 'https://api.hgbrasil.com/finance');

// Brapi Auth (Ações da B3)
define('BRAPI_KEY', $brapiApiKey);
define('BRAPI_KEY_SET', !empty($brapiApiKey));
define('BRAPI_BASE_URL', 'https://brapi.dev/api');

// ── Cache Estratégico (Otimização de Limites) ───────────
define('CACHE_DIR',  __DIR__ . '/cache/');

// HG Brasil: 400 req/dia. 10 min = 144 req/dia (Seguro!)
define('CACHE_TIME_HG', 600); 

// Brapi: Limite mensal alto, mas só atualiza a cada 30 min. 30 min = 48 req/dia (Perfeito!)
define('CACHE_TIME_BRAPI', 1800); 

// Frontend recarrega a tela com base no timer mais rápido (10 min)
define('PAGE_REFRESH', 600); 

if (!is_dir(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}
$htCache = CACHE_DIR . '.htaccess';
if (!file_exists($htCache)) {
    file_put_contents($htCache, "Order Allow,Deny\nDeny from all\n");
}

// ── App Info ─────────────────────────────────────────────
define('APP_NAME',    'AltaAgora');
define('APP_VERSION', '1.0.0');
define('APP_ENV',     getenv('APP_ENV') ?: 'production');

// ── Timezone ─────────────────────────────────────────────
date_default_timezone_set('America/Sao_Paulo');

// ── Rate Limiting por IP ─────────────────────────────────
function checkRateLimit(): void {
    $ip     = preg_replace('/[^a-fA-F0-9:.]/', '', $_SERVER['REMOTE_ADDR'] ?? '0');
    $file   = CACHE_DIR . 'rl_' . md5($ip) . '.json';
    $now    = time();
    $window = 60;
    $max    = 60; 

    $data = ['count' => 0, 'start' => $now];
    if (file_exists($file)) {
        $stored = json_decode(file_get_contents($file), true) ?? $data;
        $data   = ($now - $stored['start']) < $window ? $stored : $data;
    }
    $data['count']++;
    file_put_contents($file, json_encode($data), LOCK_EX);

    if ($data['count'] > $max) {
        http_response_code(429);
        exit('Too Many Requests');
    }
}
checkRateLimit();
