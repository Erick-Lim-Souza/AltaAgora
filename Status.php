<?php
// ═══════════════════════════════════════════════════════
//  AltaAgora — Status.php (Detetive Multi-API)
//  Grupo Green Monster Project / by FinanIA
//  v1.2.0 - Debug & Connectivity Tool
// ═══════════════════════════════════════════════════════

$token = $_GET['token'] ?? '';
if ($token !== 'altaagora_debug') {
    http_response_code(403);
    exit('403 Forbidden - Acesso Restrito');
}

header('Content-Type: text/html; charset=UTF-8');

// Coleta as chaves configuradas no ambiente
$hgKey        = getenv('HG_API_KEY') ?: ($_ENV['HG_API_KEY'] ?? ($_SERVER['HG_API_KEY'] ?? ''));
$brapiKey     = getenv('BRAPI_KEY') ?: ($_ENV['BRAPI_KEY'] ?? ($_SERVER['BRAPI_KEY'] ?? ''));
$coingeckoKey = getenv('COINGECKO_KEY') ?: ($_ENV['COINGECKO_KEY'] ?? ($_SERVER['COINGECKO_KEY'] ?? ''));

/**
 * Função de Teste de Conexão
 */
function testApi($url, $headers = []) {
    $ch = curl_init();
    $opts = [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT      => 'AltaAgora-Debug/1.2.0'
    ];
    if (!empty($headers)) { $opts[CURLOPT_HTTPHEADER] = $headers; }
    
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return ['code' => $code, 'err' => $err, 'body' => $body];
}

$phpVersion = phpversion();
$isCurlLoaded = extension_loaded('curl');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>System Status · AltaAgora Terminal</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono&family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0A0F1C; --card: #111827; --border: #1F2937;
            --accent: #00E5FF; --green: #00ffa3; --red: #ff4a6b;
            --text: #94A3B8; --hi: #F3F4F6;
        }
        body { 
            background: var(--bg); color: var(--text); font-family: 'Inter', sans-serif; 
            margin: 0; padding: 40px; line-height: 1.6;
        }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: var(--hi); font-size: 1.5rem; margin-bottom: 30px; border-bottom: 1px solid var(--border); padding-bottom: 10px; }
        h2 { color: var(--accent); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; margin-top: 40px; }
        .card { background: var(--card); border: 1px solid var(--border); padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .status-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .label { font-family: 'Space Mono', monospace; font-size: 0.85rem; }
        .ok { color: var(--green); font-weight: bold; }
        .err { color: var(--red); font-weight: bold; }
        pre { 
            background: #000; color: #0f0; padding: 15px; border-radius: 4px; 
            font-size: 11px; overflow-x: auto; border: 1px solid var(--border);
            max-height: 200px;
        }
        .badge { font-size: 10px; padding: 2px 6px; border-radius: 3px; background: var(--border); color: var(--hi); }
        .footer { margin-top: 60px; text-align: center; font-size: 0.7rem; color: var(--border-bright); opacity: 0.5; }
    </style>
</head>
<body>

<div class="container">
    <h1><span style="color:var(--accent)">AltaAgora</span> System Diagnostic</h1>

    <div class="card">
        <div class="status-row">
            <span class="label">Ambiente PHP:</span>
            <span class="ok">v<?= $phpVersion ?></span>
        </div>
        <div class="status-row">
            <span class="label">Extensão cURL:</span>
            <span class="<?= $isCurlLoaded ? 'ok' : 'err' ?>"><?= $isCurlLoaded ? 'CARREGADA' : 'FALTANDO' ?></span>
        </div>
    </div>

    <h2>01. Mercado B3 (Brapi)</h2>
    <div class="card">
        <div class="status-row">
            <span class="label">Configuração da Chave:</span>
            <span class="<?= $brapiKey ? 'ok' : 'err' ?>"><?= $brapiKey ? 'PRESENTE' : 'AUSENTE' ?></span>
        </div>
        <?php if ($brapiKey): 
            $res = testApi("https://brapi.dev/api/quote/list?limit=1&token=" . $brapiKey);
        ?>
            <div class="status-row">
                <span class="label">Resposta do Servidor:</span>
                <span class="<?= $res['code'] == 200 ? 'ok' : 'err' ?>">HTTP <?= $res['code'] ?></span>
            </div>
            <pre><?= htmlspecialchars(substr($res['body'], 0, 300)) ?>...</pre>
        <?php endif; ?>
    </div>

    <h2>02. Índices & Câmbio (HG Brasil)</h2>
    <div class="card">
        <div class="status-row">
            <span class="label">Configuração da Chave:</span>
            <span class="<?= $hgKey ? 'ok' : 'err' ?>"><?= $hgKey ? 'PRESENTE' : 'AUSENTE' ?></span>
        </div>
        <?php if ($hgKey): 
            $res = testApi("https://api.hgbrasil.com/finance?key=" . $hgKey);
        ?>
            <div class="status-row">
                <span class="label">Resposta do Servidor:</span>
                <span class="<?= $res['code'] == 200 ? 'ok' : 'err' ?>">HTTP <?= $res['code'] ?></span>
            </div>
            <pre><?= htmlspecialchars(substr($res['body'], 0, 300)) ?>...</pre>
        <?php endif; ?>
    </div>

    <h2>03. Criptoativos (CoinGecko)</h2>
    <div class="card">
        <div class="status-row">
            <span class="label">Configuração da Chave:</span>
            <span class="<?= $coingeckoKey ? 'ok' : 'err' ?>"><?= $coingeckoKey ? 'PRESENTE' : 'AUSENTE' ?></span>
        </div>
        <?php if ($coingeckoKey): 
            $res = testApi("https://api.coingecko.com/api/v3/ping", ["x-cg-demo-api-key: $coingeckoKey"]);
        ?>
            <div class="status-row">
                <span class="label">Resposta do Servidor:</span>
                <span class="<?= $res['code'] == 200 ? 'ok' : 'err' ?>">HTTP <?= $res['code'] ?></span>
            </div>
            <pre><?= htmlspecialchars($res['body']) ?></pre>
        <?php endif; ?>
    </div>

    <div class="footer">
        by FinanIA & Grupo Green Monster Project
    </div>
</div>

</body>
</html>