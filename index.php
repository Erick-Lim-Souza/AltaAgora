<?php
// ═══════════════════════════════════════════════════════
//  AltaAgora Terminal — index.php
//  Grupo Green Monster Project / by FinanIA
//  v1.2.0 - Terminal Híbrido Completo (B3, Global & Cripto)
// ═══════════════════════════════════════════════════════
require_once 'functions.php';

// ── 1. Coleta de Dados Multi-API ────────────────────────
$gainers = getBrapiStocks('desc', 15);
$losers  = getBrapiStocks('asc', 15);
$cryptos = getCryptoData();
$hgData  = getHgData();

$indices    = $hgData['stocks'] ?? [];
$currencies = $hgData['currencies'] ?? [];
// Adicionando Minérios se disponíveis na API
$minerals   = $hgData['minerals'] ?? []; 

$lastUpdate  = date('H:i:s');
$secondsLeft = getSecondsUntilRefresh();

// ── 2. Processamento de Resumo (Hero) ────────────────────
$topGainer = !empty($gainers) ? $gainers[0] : ['symbol' => '-', 'price' => 0];
$topLoser  = !empty($losers)  ? $losers[0]  : ['symbol' => '-', 'price' => 0];
$totalVol  = !empty($gainers) ? array_sum(array_column($gainers, 'volume')) : 0;

/**
 * Gerador de minigráfico (Sparkline) procedural
 */
function generateSparkline($seed, $change) {
    $isPositive = $change >= 0;
    srand(crc32($seed) + date('z'));
    $points = []; $y = 12;
    for ($x = 0; $x <= 60; $x += 10) {
        $points[] = "$x,$y"; 
        $y += rand(-6, 6); 
        $y = max(2, min(22, $y));
    }
    $yLast = $isPositive ? rand(2, 8) : rand(16, 22);
    $points[count($points)-1] = "60,$yLast";
    return implode(" ", $points);
}

/**
 * Renderizador de Linhas da Tabela
 */
function renderTableRows($stocks) {
    foreach ($stocks as $i => $stock):
        $var = formatVariation($stock['change_percent']);
        $trend = generateSparkline($stock['symbol'], $stock['change_percent']);
?>
        <tr class="table-row" data-symbol="<?= strtolower($stock['symbol']) ?>">
            <td><span class="rank accent"><?= $i + 1 ?></span></td>
            <td>
                <div class="asset-cell">
                    <?php if (!empty($stock['logo'])): ?>
                        <img src="<?= h($stock['logo']) ?>" alt="" class="stock-logo" loading="lazy">
                    <?php else: ?>
                        <div style="width:32px; height:32px; background:var(--bg-card-2); border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:bold;"><?= substr($stock['symbol'], 0, 2) ?></div>
                    <?php endif; ?>
                    <strong class="asset-ticker"><?= $stock['symbol'] ?></strong>
                </div>
            </td>
            <td class="col-company"><?= $stock['name'] ?></td>
            <td class="col-chart">
                <svg class="sparkline-svg" viewBox="0 0 60 24">
                    <polyline class="sparkline-line <?= $var['class'] ?>" points="<?= $trend ?>" />
                </svg>
            </td>
            <td class="mono"><?= formatMoney($stock['price']) ?></td>
            <td class="mono <?= $var['class'] ?>"><?= $var['html'] ?></td>
            <td class="mono col-vol"><?= formatVolume($stock['volume']) ?></td>
        </tr>
<?php 
    endforeach;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AltaAgora Terminal · Grupo Green Monster Project</title>
    
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0A0F1C">
    <link rel="apple-touch-icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='6' fill='%230A0F1C'/%3E%3Ctext x='4' y='23' font-family='monospace' font-size='18' font-weight='700' fill='%2300E5FF'%3EA%3C/text%3E%3C/svg%3E">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='6' fill='%230A0F1C'/%3E%3Ctext x='4' y='23' font-family='monospace' font-size='18' font-weight='700' fill='%2300E5FF'%3EA%3C/text%3E%3C/svg%3E">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="scanlines" aria-hidden="true"></div>

<div class="layout">
    <div class="orb-1" aria-hidden="true"></div>

    <header class="topbar">
        <div class="topbar-left">
            <a href="index.php" class="logo">Alta<span class="logo-accent">Agora</span><span class="logo-dot">.</span></a>
        </div>
        <div class="topbar-right">
            <div class="countdown-wrap" title="Próxima atualização">
                <span class="countdown-timer mono" id="countdown"><?= sprintf('%02d:%02d', intdiv($secondsLeft, 60), $secondsLeft % 60) ?></span>
            </div>
            <span class="live-dot"></span>
            <span class="update-time mono"><?= $lastUpdate ?></span>
            <a href="sobre.php" style="color:var(--text-lo); text-decoration:none; font-size:11px; font-weight:800; letter-spacing:1px;">MANIFESTO</a>
        </div>
    </header>

    <main class="main-content">
        
        <div class="stat-grid">
            <div class="stat-card" style="border-color: rgba(0,255,163,0.15);">
                <div class="stat-label">▲ MAIOR ALTA B3</div>
                <div class="stat-symbol"><?= $topGainer['symbol'] ?></div>
                <div class="stat-price mono"><?= formatMoney($topGainer['price']) ?></div>
            </div>
            <div class="stat-card" style="border-color: rgba(255,74,107,0.15);">
                <div class="stat-label" style="color:var(--red)">▼ MAIOR QUEDA B3</div>
                <div class="stat-symbol"><?= $topLoser['symbol'] ?></div>
                <div class="stat-price mono"><?= formatMoney($topLoser['price']) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label"># VOLUME TOP GAINERS</div>
                <div class="stat-big mono"><?= formatVolume($totalVol) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">↻ AUTO-REFRESH</div>
                <div class="stat-big mono" id="countdown-card"><?= sprintf('%02d:%02d', intdiv($secondsLeft, 60), $secondsLeft % 60) ?></div>
            </div>
        </div>

        <div class="asset-selector-wrap">
            <div class="segmented-control">
                <button class="segment-btn active" data-target="section-stocks">Ações</button>
                <button class="segment-btn" data-target="section-indices">Índices & Moedas</button>
                <button class="segment-btn" data-target="section-crypto">Criptoativos</button>
            </div>
        </div>

        <div id="section-stocks" class="asset-section">
            <h3 class="section-label">Top Performers B3</h3>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Ativo</th><th class="col-company">Empresa</th><th class="col-chart">Trend</th><th>Preço</th><th>Variação</th><th class="col-vol">Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php renderTableRows($gainers); ?>
                        <?php renderTableRows($losers); ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="section-indices" class="asset-section hidden-section">
            <h3 class="section-label">Câmbio e Bolsas Mundiais</h3>
            <div class="indices-grid">
                <?php 
                $moedasPermitidas = ['USD', 'EUR', 'GBP', 'CNY', 'RUB'];
                foreach ($moedasPermitidas as $m): 
                    if (!isset($currencies[$m])) continue;
                    $curr = $currencies[$m];
                    $v = formatVariation((float)$curr['variation']);
                ?>
                <div class="index-card">
                    <span class="index-name"><?= $curr['name'] ?> (<?= $m ?>)</span>
                    <span class="index-points mono">R$ <?= number_format($curr['buy'], 4, ',', '.') ?></span>
                    <span class="index-var <?= $v['class'] ?>"><?= $v['html'] ?></span>
                </div>
                <?php endforeach; ?>

                <?php foreach ($indices as $key => $idx): 
                    $v = formatVariation((float)$idx['variation']);
                ?>
                <div class="index-card">
                    <span class="index-name"><?= $idx['name'] ?? $key ?></span>
                    <span class="index-points mono"><?= number_format($idx['points'], 2, ',', '.') ?></span>
                    <span class="index-var <?= $v['class'] ?>"><?= $v['html'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="section-crypto" class="asset-section hidden-section">
            <h3 class="section-label">Mercado Digital Global</h3>
            <div class="indices-grid">
                <?php if (!empty($cryptos)): 
                    $map = [
                        'bitcoin' => 'Bitcoin', 'ethereum' => 'Ethereum', 
                        'solana' => 'Solana', 'tether' => 'Tether'
                    ];
                    foreach ($map as $id => $name):
                        if (!isset($cryptos[$id])) continue;
                        $c = $cryptos[$id];
                        $v = formatVariation((float)$c['brl_24h_change']);
                ?>
                <div class="index-card">
                    <span class="index-name"><?= $name ?></span>
                    <span class="index-points mono">R$ <?= number_format($c['brl'], 2, ',', '.') ?></span>
                    <span class="index-var <?= $v['class'] ?>"><?= $v['html'] ?></span>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

    </main>

    <footer class="footer-master">
        <div class="footer-top">
            <div class="footer-brand-col">
                <div class="footer-tagline">Inteligência de Dados</div>
                <p class="footer-desc">
                    Terminal financeiro de alta performance desenvolvido para o ecossistema <strong>Green Monster</strong>. 
                    Focado em baixa latência e precisão analítica.
                </p>
            </div>
            <div class="footer-links-col">
                <h4>Data Feeds</h4>
                <ul>
                    <li><a href="https://brapi.dev" target="_blank">Brapi API (B3)</a></li>
                    <li><a href="https://hgbrasil.com" target="_blank">HG Brasil (Global)</a></li>
                    <li><a href="https://www.coingecko.com" target="_blank">CoinGecko (Cripto)</a></li>
                </ul>
            </div>
            <div class="footer-links-col">
                <h4>Ecossistema</h4>
                <ul>
                    <li><a href="https://github.com/Erick-Lim-Souza/AltaAgora">Código Fonte</a></li>
                    <li><a href="https://erickanalytics.netlify.app/">Portefólio</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span class="footer-by-finania">by <span>FinanIA</span> & Grupo Green Monster Project!</span>
            <div class="footer-legal">© <?= date('Y') ?> AltaAgora Terminal · Uso Informativo</div>
        </div>
    </footer>
</div>

<script>
// 1. Lógica do Seletor de Ativos (Tabs Segmentadas)
const segmentBtns = document.querySelectorAll('.segment-btn');
const assetSections = document.querySelectorAll('.asset-section');

segmentBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        // Altera botões
        segmentBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Altera seções
        const targetId = btn.getAttribute('data-target');
        assetSections.forEach(section => {
            section.classList.add('hidden-section');
            if (section.id === targetId) section.classList.remove('hidden-section');
        });
    });
});

// 2. Countdown de Refresh
(function () {
    let remaining = <?= $secondsLeft ?>;
    const el = document.getElementById('countdown');
    const elCard = document.getElementById('countdown-card');

    function update() {
        if (remaining <= 0) { location.reload(); return; }
        const m = Math.floor(remaining / 60);
        const s = remaining % 60;
        const display = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        if(el) el.textContent = display;
        if(elCard) elCard.textContent = display;
        remaining--;
    }
    update();
    setInterval(update, 1000);
})();

// 3. PWA Registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW erro:', err));
    });
}
</script>
</body>
</html>