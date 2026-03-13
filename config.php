<?php
// ═══════════════════════════════════════════════════════
//  AltaAgora — config.php
//  Configuração central · Pronto para Render + Env Vars
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
         . "style-src 'self' https://fonts.googleapis.com 'unsafe-inline'; "
         . "font-src https://fonts.gstatic.com; "
         . "script-src 'self' 'unsafe-inline';");
}

// ── API Key via variável de ambiente (Render Dashboard) ─
$apiKey = '';

// Tenta buscar de 3 formas diferentes (necessário para contornar bloqueios do Apache/Docker)
if (empty($apiKey)) $apiKey = getenv('HG_API_KEY') ?: '';
if (empty($apiKey) && isset($_ENV['HG_API_KEY'])) $apiKey = $_ENV['HG_API_KEY'];
if (empty($apiKey) && isset($_SERVER['HG_API_KEY'])) $apiKey = $_SERVER['HG_API_KEY'];

// Fallback para desenvolvimento local via .env.local (não sobe pro Git)
if (empty($apiKey) && file_exists(__DIR__ . '/.env.local')) {
    foreach (file(__DIR__ . '/.env.local', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2 && trim($parts[0]) === 'HG_API_KEY') {
            $apiKey = trim($parts[1]);
            break;
        }
    }
}

// Registra no log do Render se a chave foi encontrada (ajuda muito no debug!)
error_log("[AltaAgora] Status da API Key no servidor: " . (!empty($apiKey) ? "ENCONTRADA" : "AUSENTE"));

define('API_KEY',      $apiKey);
define('API_KEY_SET',  !empty($apiKey));
define('API_BASE_URL', 'https://api.hgbrasil.com/finance');

// ── Cache ───────────────────────────────────────────────
define('CACHE_DIR',  __DIR__ . '/cache/');
define('CACHE_TIME', 300); // 5 minutos

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

// ── Rate Limiting por IP (60 req/min) ───────────────────
function checkRateLimit(): void {
    $ip     = preg_replace('/[^a-fA-F0-9:.]/', '', $_SERVER['REMOTE_ADDR'] ?? '0');
    $file   = CACHE_DIR . 'rl_' . md5($ip) . '.json';
    $now    = time();
    $window = 60;
    $max    = 60; // Você pode aumentar para 120 depois, se o tráfego subir

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
