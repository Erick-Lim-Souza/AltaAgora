<?php
// ═══════════════════════════════════════════════════════
//  AltaAgora — Status.php (Detetive Multi-API)
// ═══════════════════════════════════════════════════════

$token = $_GET['token'] ?? '';
if ($token !== 'altaagora_debug') {
    http_response_code(403);
    exit('403 Forbidden');
}

header('Content-Type: text/html; charset=UTF-8');

$hgKey    = getenv('HG_API_KEY') ?: ($_ENV['HG_API_KEY'] ?? ($_SERVER['HG_API_KEY'] ?? ''));
$brapiKey = getenv('BRAPI_KEY') ?: ($_ENV['BRAPI_KEY'] ?? ($_SERVER['BRAPI_KEY'] ?? ''));

function testApi($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return ['code' => $code, 'err' => $err, 'body' => $body];
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico Híbrido</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #050810; color: #e8edf8; padding: 32px; }
        h1 { color: #00ffa3; } h2 { color: #fbbf24; margin-top: 30px; border-bottom: 1px solid #161e33; padding-bottom: 8px;}
        .ok { color: #00ffa3; } .err { color: #ff4a6b; }
        pre { background: #0c1020; border: 1px solid #161e33; padding: 14px; border-radius: 6px; white-space: pre-wrap; word-wrap: break-word;}
    </style>
</head>
<body>

<h1>⚙ Diagnóstico de APIs (AltaAgora)</h1>

<h2>1. BRAPI (Responsável pelas Ações)</h2>
<p>Chave configurada no Render? 
    <?php echo $brapiKey ? "<strong class='ok'>✓ SIM</strong>" : "<strong class='err'>✗ NÃO ENCONTRADA</strong>"; ?>
</p>

<?php if ($brapiKey): ?>
    <?php 
        $brapiUrl = "https://brapi.dev/api/quote/list?sortBy=volume&sortOrder=desc&limit=5&token=" . urlencode($brapiKey);
        $brapiRes = testApi($brapiUrl);
    ?>
    <p>HTTP Code: <strong class="<?= $brapiRes['code'] == 200 ? 'ok' : 'err' ?>"><?= $brapiRes['code'] ?></strong></p>
    <?php if ($brapiRes['err']): ?><p class="err">Erro de Conexão: <?= $brapiRes['err'] ?></p><?php endif; ?>
    <p>Resposta da Brapi (JSON):</p>
    <pre><?= htmlspecialchars(substr($brapiRes['body'], 0, 1000)) ?></pre>
<?php endif; ?>

<h2>2. HG BRASIL (Responsável pelos Índices)</h2>
<p>Chave configurada no Render? 
    <?php echo $hgKey ? "<strong class='ok'>✓ SIM</strong>" : "<strong class='err'>✗ NÃO ENCONTRADA</strong>"; ?>
</p>

<?php if ($hgKey): ?>
    <?php 
        $hgUrl = "https://api.hgbrasil.com/finance?fields=stocks&key=" . urlencode($hgKey);
        $hgRes = testApi($hgUrl);
    ?>
    <p>HTTP Code: <strong class="<?= $hgRes['code'] == 200 ? 'ok' : 'err' ?>"><?= $hgRes['code'] ?></strong></p>
    <?php if ($hgRes['err']): ?><p class="err">Erro de Conexão: <?= $hgRes['err'] ?></p><?php endif; ?>
    <p>Resposta da HG Brasil (JSON):</p>
    <pre><?= htmlspecialchars(substr($hgRes['body'], 0, 1000)) ?></pre>
<?php endif; ?>

</body>
</html>
