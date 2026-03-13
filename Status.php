<?php
// ═══════════════════════════════════════════════════════
//  AltaAgora — status.php
//  Página de diagnóstico — REMOVA após confirmar que funciona
// ═══════════════════════════════════════════════════════

// Proteção mínima: só acessível com token na URL
// Acesse: /status.php?token=altaagora_debug
$token = $_GET['token'] ?? '';
if ($token !== 'altaagora_debug') {
    http_response_code(403);
    exit('403 Forbidden');
}

header('Content-Type: text/html; charset=UTF-8');

// Leitura direta da env var — sem passar pelo config.php
$apiKey      = getenv('HG_API_KEY');
$apiKeySet   = !empty($apiKey);
$apiKeyMask  = $apiKeySet
    ? substr($apiKey, 0, 4) . str_repeat('*', max(0, strlen($apiKey) - 8)) . substr($apiKey, -4)
    : '— NÃO DEFINIDA —';

// Testar chamada real à API (se a chave existir)
$apiStatus   = '–';
$apiResponse = '';
if ($apiKeySet) {
    $url = 'https://api.hgbrasil.com/finance/stock_price?symbol=PETR4&key=' . urlencode($apiKey);
    $ch  = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    $apiStatus   = $httpCode;
    $apiResponse = $curlErr ?: substr($body, 0, 300);
}

// Info do ambiente
$phpVersion  = PHP_VERSION;
$serverSoft  = $_SERVER['SERVER_SOFTWARE'] ?? 'N/A';
$cacheDir    = __DIR__ . '/cache/';
$cacheExists = is_dir($cacheDir);
$cacheWrite  = $cacheExists && is_writable($cacheDir);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>AltaAgora · Status</title>
<style>
  body { font-family: 'Courier New', monospace; background: #050810; color: #e8edf8; padding: 32px; }
  h1   { color: #00ffa3; font-size: 1.4rem; margin-bottom: 24px; }
  h2   { color: #8899bb; font-size: .85rem; text-transform: uppercase; letter-spacing: .1em; margin: 24px 0 8px; }
  .row { display: flex; gap: 16px; align-items: center; padding: 10px 0; border-bottom: 1px solid #161e33; }
  .label  { color: #4a5a7a; width: 220px; flex-shrink: 0; font-size: .82rem; }
  .value  { font-size: .85rem; }
  .ok     { color: #00ffa3; }
  .warn   { color: #fbbf24; }
  .err    { color: #ff4a6b; }
  pre { background: #0c1020; border: 1px solid #161e33; padding: 14px; border-radius: 6px;
        font-size: .75rem; color: #8899bb; overflow-x: auto; white-space: pre-wrap; }
  .badge { padding: 2px 8px; border-radius: 4px; font-size: .72rem; font-weight: 700; }
  .badge-ok  { background: #00ffa322; color: #00ffa3; border: 1px solid #00ffa340; }
  .badge-err { background: #ff4a6b22; color: #ff4a6b; border: 1px solid #ff4a6b40; }
  .badge-warn{ background: #fbbf2422; color: #fbbf24; border: 1px solid #fbbf2440; }
</style>
</head>
<body>

<h1>⚙ AltaAgora · Diagnóstico</h1>

<h2>Variável de Ambiente</h2>
<div class="row">
    <span class="label">HG_API_KEY definida?</span>
    <span class="value">
        <?php if ($apiKeySet): ?>
            <span class="badge badge-ok">✓ SIM</span>
        <?php else: ?>
            <span class="badge badge-err">✗ NÃO — Configure no Render: Settings → Environment Variables → HG_API_KEY</span>
        <?php endif; ?>
    </span>
</div>
<div class="row">
    <span class="label">Valor (mascarado)</span>
    <span class="value <?= $apiKeySet ? 'ok' : 'err' ?>"><?= htmlspecialchars($apiKeyMask) ?></span>
</div>

<h2>Teste de Conexão HG Brasil</h2>
<div class="row">
    <span class="label">HTTP Status</span>
    <span class="value">
        <?php if (!$apiKeySet): ?>
            <span class="badge badge-warn">PULADO — sem chave</span>
        <?php elseif ($apiStatus === 200): ?>
            <span class="badge badge-ok">200 OK</span>
        <?php else: ?>
            <span class="badge badge-err"><?= htmlspecialchars((string)$apiStatus) ?></span>
        <?php endif; ?>
    </span>
</div>
<?php if ($apiKeySet): ?>
<div class="row">
    <span class="label">Resposta (primeiros 300 chars)</span>
</div>
<pre><?= htmlspecialchars($apiResponse) ?></pre>
<?php endif; ?>

<h2>Ambiente PHP / Servidor</h2>
<div class="row">
    <span class="label">PHP Version</span>
    <span class="value ok"><?= $phpVersion ?></span>
</div>
<div class="row">
    <span class="label">Servidor</span>
    <span class="value"><?= htmlspecialchars($serverSoft) ?></span>
</div>
<div class="row">
    <span class="label">Extensão cURL</span>
    <span class="value">
        <?= function_exists('curl_init')
            ? '<span class="badge badge-ok">✓ Disponível</span>'
            : '<span class="badge badge-err">✗ Ausente</span>' ?>
    </span>
</div>
<div class="row">
    <span class="label">Diretório cache/ existe</span>
    <span class="value">
        <?= $cacheExists
            ? '<span class="badge badge-ok">✓ Sim</span>'
            : '<span class="badge badge-err">✗ Não existe</span>' ?>
    </span>
</div>
<div class="row">
    <span class="label">cache/ tem permissão de escrita</span>
    <span class="value">
        <?= $cacheWrite
            ? '<span class="badge badge-ok">✓ Gravável</span>'
            : '<span class="badge badge-err">✗ Sem permissão</span>' ?>
    </span>
</div>

<h2>Variáveis de Ambiente Disponíveis (filtradas)</h2>
<pre><?php
$safe = [];
foreach ($_ENV + getenv() as $k => $v) {
    if (str_contains(strtolower($k), 'key')
     || str_contains(strtolower($k), 'secret')
     || str_contains(strtolower($k), 'pass')
     || str_contains(strtolower($k), 'token')) {
        $safe[$k] = str_repeat('*', 8) . ' (oculto)';
    } else {
        $safe[$k] = $v;
    }
}
// Mostrar apenas HG_ e APP_ para não poluir
$filtered = array_filter($safe, fn($k) =>
    str_starts_with($k, 'HG_')
 || str_starts_with($k, 'APP_')
 || str_starts_with($k, 'RENDER')
 || str_starts_with($k, 'PHP'),
    ARRAY_FILTER_USE_KEY
);
echo htmlspecialchars(empty($filtered) ? '— nenhuma variável HG_/APP_/RENDER encontrada —' : print_r($filtered, true));
?></pre>

<p style="margin-top:32px; color:#2e3d5e; font-size:.72rem;">
    ⚠ Remova ou proteja este arquivo após o diagnóstico.<br>
    Acesso via: <code>/status.php?token=altaagora_debug</code>
</p>
</body>
</html>