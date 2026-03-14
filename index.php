<?php
// ═══════════════════════════════════════════════════════
//  AltaAgora — index.php
//  Terminal Híbrido: Brapi (Ações) + HG Brasil (Índices e Moedas)
// ═══════════════════════════════════════════════════════
require_once 'functions.php';

// Novas chamadas separadas para Altas e Baixas
$gainers        = getBrapiStocks('desc', 15);
$losers         = getBrapiStocks('asc', 15);
$hgData         = getHgData();
$indices        = $hgData['stocks'] ?? [];
$currencies     = $hgData['currencies'] ?? [];

$lastUpdate     = date('H:i:s');
$hasError       = empty($gainers);
$apiKeysMissing = (!API_KEY_SET || !BRAPI_KEY_SET);
$secondsLeft    = getSecondsUntilRefresh();

$topStock  = $hasError ? null : $gainers[0];
$avgChange = $hasError ? 0 : array_sum(array_column($gainers, 'change_percent')) / max(1, count($gainers));
$totalVol  = $hasError ? 0 : array_sum(array_column($gainers, 'volume'));

// Gerador de minigráfico (Sparkline) procedural para portfólio
function generateSparkline($seed, $isPositive) {
    srand(crc32($seed) + date('z')); // Muda o desenho 1x por dia
    $points = []; $y = 12;
    for ($x = 0; $x <= 60; $x += 10) {
        $points[] = "$x,$y";
        $y += rand(-6, 6);
        $y = max(2, min(22, $y)); // Mantém dentro dos limites do SVG
    }
    // Garante que a ponta final aponte pra cima ou pra baixo dependendo do fechamento
    $yLast = $isPositive ? rand(2, 8) : rand(16, 22);
    $points[count($points)-1] = "60,$yLast";
    return implode(" ", $points);
}

// Renderizador de linhas da tabela para evitar duplicar HTML nas abas
function renderTableRows($stocks, $isLosers = false) {
    $maxPct = max(array_map('abs', array_column($stocks, 'change_percent'))) ?: 1;
    foreach ($stocks as $i => $stock):
        $var    = formatVariation($stock['change_percent']);
        $barPct = round((abs($stock['change_percent']) / $maxPct) * 100);
        $isPos  = $stock['change_percent'] >= 0;
?>
        <tr class="table-row" 
            data-symbol="<?= strtolower($stock['symbol']) ?>" 
            data-price="<?= $stock['price'] ?>" 
            data-vol="<?= $stock['volume'] ?>" 
            data-rank="<?= $i ?>"
            data-pct="<?= $stock['change_percent'] ?>"
            data-logo="<?= $stock['logo'] ?>"
            style="--row-delay:<?= $i * 0.035 ?>s">
            <td><span class="rank rank-<?= $i < 3 ? ($i + 1) : 'n' ?>"><?= $i + 1 ?></span></td>
            <td>
                <div class="asset-cell">
                    <?php if ($stock['logo']): ?>
                    <img src="<?= h($stock['logo']) ?>" alt="" class="stock-logo" loading="lazy">
                    <?php else: ?>
                    <span class="stock-initials"><?= mb_substr($stock['symbol'], 0, 2) ?></span>
                    <?php endif; ?>
                    <strong class="asset-ticker"><?= $stock['symbol'] ?></strong>
                </div>
            </td>
            <td class="col-company"><?= $stock['name'] ?></td>
            
            <td class="col-chart">
                <svg class="sparkline-svg" viewBox="0 0 60 24">
                    <polyline class="sparkline-line <?= $var['class'] ?>" points="<?= generateSparkline($stock['symbol'], $isPos) ?>" />
                </svg>
            </td>

            <td class="mono col-price"><?= formatMoney($stock['price']) ?></td>
            <td class="mono <?= $var['class'] ?>"><?= ($stock['change_price'] >= 0 ? '+' : '') ?><?= formatMoney($stock['change_price']) ?></td>
            <td><span class="pct-pill <?= $var['class'] ?>"><?= $var['symbol'] ?>&thinsp;<?= $var['value'] ?>%</span></td>
            <td class="mono col-vol"><?= formatVolume($stock['volume']) ?></td>
            <td class="col-bar">
                <div class="bar-track"><div class="bar-fill <?= $var['class'] ?>" style="--w:<?= $barPct ?>%"></div></div>
                <span class="bar-pct"><?= $barPct ?>%</span>
            </td>
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
    <meta name="description" content="AltaAgora — Ações em alta e baixa do pregão B3 em tempo real.">
    <meta name="robots" content="noindex, nofollow">
    <title>AltaAgora · Mercado B3</title>
    
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#050810">
    <link rel="apple-touch-icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='6' fill='%23050810'/%3E%3Ctext x='4' y='23' font-family='monospace' font-size='18' font-weight='700' fill='%2300ffa3'%3EA%3C/text%3E%3C/svg%3E">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='6' fill='%23050810'/%3E%3Ctext x='4' y='23' font-family='monospace' font-size='18' font-weight='700' fill='%2300ffa3'%3EA%3C/text%3E%3C/svg%3E">
    <link rel="stylesheet" href="style.css">
    
    <style>
        .footer-premium { border-top: 1px solid var(--border); background: var(--bg-card); padding: 48px 5% 24px; margin-top: 40px; font-size: 0.8rem; position: relative; z-index: 10; }
        .footer-premium-top { display: flex; flex-wrap: wrap; gap: 40px; justify-content: space-between; margin-bottom: 40px; max-width: 1200px; margin-inline: auto; }
        .footer-brand-col { flex: 1; min-width: 280px; max-width: 450px; }
        .footer-desc { color: var(--text-mid); margin-top: 16px; line-height: 1.6; font-size: 0.85rem; }
        .footer-links-col { min-width: 200px; }
        .footer-links-col h4 { color: var(--text-lo); font-family: var(--font-mono); font-size: 0.75rem; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 20px; }
        .footer-links-col ul { list-style: none; display: flex; flex-direction: column; gap: 12px; }
        .footer-links-col a { color: var(--text-mid); transition: color 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .footer-links-col a::before { content: '›'; color: var(--accent); font-weight: 700; }
        .footer-links-col a:hover { color: var(--accent); }
        .footer-premium-bottom { max-width: 1200px; margin-inline: auto; border-top: 1px solid var(--border-bright); padding-top: 24px; }
        .footer-disclaimer { color: var(--text-lo); font-size: 0.72rem; line-height: 1.6; margin-bottom: 24px; text-align: justify; }
        .footer-disclaimer strong { color: var(--text-mid); }
        .footer-credits-wrap { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; color: var(--text-mid); font-size: 0.75rem; }
        .footer-credits-wrap strong { color: var(--text-hi); }
        @media (max-width: 768px) { .footer-premium-top { flex-direction: column; gap: 32px; } .footer-credits-wrap { flex-direction: column; text-align: center; justify-content: center; } }
    </style>
</head>
<body>

<div class="scanlines"  aria-hidden="true"></div>
<div class="bg-grid"    aria-hidden="true"></div>
<div class="orb orb-1"  aria-hidden="true"></div>
<div class="orb orb-2"  aria-hidden="true"></div>

<div class="layout">

    <header class="topbar">
        <div class="topbar-left">
            <span class="logo">Alta<span class="logo-accent">Agora</span><span class="logo-dot">.</span></span>
        </div>

        <div class="topbar-center">
            <?php if (!empty($indices)): ?>
            <div class="ticker-wrap">
                <div class="ticker">
                    <?php
                    for ($pass = 0; $pass < 2; $pass++):
                    foreach ($indices as $idx => $d):
                        $v = formatVariation((float)($d['variation'] ?? 0));
                    ?>
                    <span class="ticker-item">
                        <span class="ticker-name"><?= h($d['name'] ?? $idx) ?></span>
                        <span class="ticker-val mono"><?= number_format((float)($d['points'] ?? 0), 2, ',', '.') ?></span>
                        <span class="ticker-var <?= $v['class'] ?>"><?= $v['symbol'] ?>&thinsp;<?= $v['value'] ?>%</span>
                    </span>
                    <?php endforeach; endfor; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="topbar-right">
            <a href="sobre.php" style="color: var(--text-mid); font-size: 0.75rem; margin-right: 16px; font-family: var(--font-mono); text-transform: uppercase; transition: color 0.2s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-mid)'">// Sobre o Projeto</a>

            <div class="search-wrap">
                <span style="color:var(--text-mid); margin-right:6px;">🔍</span>
                <input type="text" id="searchInput" class="search-input" placeholder="Filtrar ticker..." autocomplete="off">
            </div>

            <span class="live-dot" title="Dados ao vivo"></span>
            <span class="update-time mono"><?= $lastUpdate ?></span>

            <div class="countdown-wrap" title="Próxima atualização da tela">
                <span class="countdown-label">REFRESH</span>
                <span class="countdown-timer mono" id="countdown" data-seconds="<?= $secondsLeft ?>">
                    <?= sprintf('%02d:%02d', intdiv($secondsLeft, 60), $secondsLeft % 60) ?>
                </span>
                <div class="countdown-bar">
                    <div class="countdown-bar-fill" id="countdown-bar" style="--pct:<?= $secondsLeft > 0 ? round(($secondsLeft / PAGE_REFRESH) * 100) : 0 ?>%"></div>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">

        <?php if ($apiKeysMissing): ?>
        <div class="alert-banner alert-warning">
            <span class="alert-icon">⚙</span>
            <div>
                <strong>Variáveis de ambiente incompletas.</strong>
                <span>Defina <code>HG_API_KEY</code> e <code>BRAPI_KEY</code> no painel do Render.</span>
            </div>
        </div>
        <?php endif; ?>

        <section class="page-title">
            <div class="page-title-inner">
                <p class="page-eyebrow">// terminal financeiro híbrido</p>
                <h1>Mercado <span class="accent">B3</span></h1>
                <p class="page-subtitle">
                    Painel do pregão atual &middot; Bolsa B3 · São Paulo<br>
                    <span style="color:var(--text-lo); font-size: 0.8em; margin-top: 4px; display: inline-block;">
                        Atualização: Índices/Moedas (<?= CACHE_TIME_HG / 60 ?>min) · Ações (<?= CACHE_TIME_BRAPI / 60 ?>min)
                    </span>
                    <button id="notifyBtn" class="filter-btn" style="margin-left:12px;">🔔 Alertas de Disparo</button>
                </p>
            </div>
            <div class="title-line"></div>
        </section>

        <?php if ($hasError && !$apiKeysMissing): ?>
        <div class="alert-banner alert-error">
            <span class="alert-icon">⚠</span>
            <p>Não foi possível carregar os dados das ações na B3. Verifique sua chave da Brapi ou tente novamente em instantes.</p>
        </div>
        <?php elseif (!$hasError): ?>

        <div class="stat-grid">
            <div class="stat-card stat-card--highlight" style="--delay:0s">
                <div class="stat-label">▲ MAIOR ALTA</div>
                <div class="stat-symbol"><?= $gainers[0]['symbol'] ?></div>
                <div class="stat-price mono"><?= formatMoney($gainers[0]['price']) ?></div>
            </div>
            <div class="stat-card" style="--delay:.07s; border-color: rgba(255,74,107,.25);">
                <div class="stat-label" style="color: var(--red);">▼ MAIOR QUEDA</div>
                <div class="stat-symbol"><?= $losers[0]['symbol'] ?></div>
                <div class="stat-price mono"><?= formatMoney($losers[0]['price']) ?></div>
            </div>
            <div class="stat-card" style="--delay:.14s">
                <div class="stat-label"># VOLUME ALTAS</div>
                <div class="stat-big mono"><?= formatVolume($totalVol) ?></div>
                <div class="stat-sub">ações negociadas no Top 15</div>
            </div>
            <div class="stat-card stat-card--countdown" style="--delay:.21s">
                <div class="stat-label">↻ PRÓX. ATUALIZAÇÃO</div>
                <div class="stat-big mono" id="countdown-card">
                    <?= sprintf('%02d:%02d', intdiv($secondsLeft, 60), $secondsLeft % 60) ?>
                </div>
                <div class="stat-sub">segundos restantes</div>
                <div class="progress-ring-wrap">
                    <svg class="progress-ring" viewBox="0 0 44 44">
                        <circle class="ring-bg" cx="22" cy="22" r="18" />
                        <circle class="ring-fill" cx="22" cy="22" r="18" id="ring-fill" stroke-dasharray="113.1" stroke-dashoffset="<?= 113.1 - (113.1 * ($secondsLeft / PAGE_REFRESH)) ?>" />
                    </svg>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 28px;">
            
            <?php if (!empty($currencies) && isset($currencies['source'])): ?>
            <section class="indices-section" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
                    <h3 class="section-label" style="margin-bottom: 0;">// câmbio oficial</h3>
                    <span class="currency-source">Moeda Base: <strong>BRL (Real)</strong></span>
                </div>
                <div class="indices-grid">
                    <?php 
                    $allowed = ['USD', 'EUR', 'GBP', 'BTC'];
                    foreach ($allowed as $curr):
                        if (!isset($currencies[$curr])) continue;
                        $c = $currencies[$curr];
                        $v = formatVariation((float)($c['variation'] ?? 0));
                    ?>
                    <div class="index-card">
                        <span class="index-name"><?= h($c['name'] ?? $curr) ?> (<?= $curr ?>)</span>
                        <span class="index-points mono">R$ <?= number_format((float)($c['buy'] ?? 0), 4, ',', '.') ?></span>
                        <span class="index-var <?= $v['class'] ?>"><?= $v['html'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php if (!empty($indices)): ?>
            <section class="indices-section" style="margin-bottom: 0;">
                <h3 class="section-label" style="margin-bottom: 18px;">// índices globais</h3>
                <div class="indices-grid">
                    <?php foreach (array_slice($indices, 0, 4) as $idx => $d): $v = formatVariation((float)($d['variation'] ?? 0)); ?>
                    <div class="index-card">
                        <span class="index-name"><?= h($d['name'] ?? $idx) ?></span>
                        <span class="index-points mono"><?= number_format((float)($d['points'] ?? 0), 2, ',', '.') ?></span>
                        <span class="index-var <?= $v['class'] ?>"><?= $v['html'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </div>

        <section class="table-section">
            <div class="table-header" style="flex-wrap: wrap; gap: 16px;">
                <div class="table-tabs">
                    <button class="tab-btn active" data-target="gainers">Maiores Altas</button>
                    <button class="tab-btn" data-target="losers">Maiores Quedas</button>
                </div>
                
                <div class="table-controls">
                    <span style="font-size: 0.7rem; color: var(--text-mid);">Ordenar por:</span>
                    <button class="filter-btn active" data-sort="rank">Impacto</button>
                    <button class="filter-btn" data-sort="price">Preço R$</button>
                    <button class="filter-btn" data-sort="vol">Volume</button>
                </div>
            </div>

            <div class="table-wrap">
                <table class="data-table" id="stocksTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ativo</th>
                            <th>Empresa</th>
                            <th class="col-chart">Trend</th>
                            <th>Preço</th>
                            <th>Var R$</th>
                            <th>Var %</th>
                            <th>Volume</th>
                            <th>Força</th>
                        </tr>
                    </thead>
                    
                    <tbody id="gainers" class="tab-content active">
                        <?php renderTableRows($gainers); ?>
                    </tbody>
                    
                    <tbody id="losers" class="tab-content">
                        <?php renderTableRows($losers, true); ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php endif; ?>

    </main>

    <footer class="footer-premium">
        <div class="footer-premium-top">
            <div class="footer-brand-col">
                <span class="logo">Alta<span class="logo-accent">Agora</span><span class="logo-dot">.</span></span>
                <p class="footer-desc">Terminal financeiro de alta performance desenvolvido para oferecer uma visão limpa, rápida e direta das maiores movimentações da bolsa brasileira.</p>
            </div>
            <div class="footer-links-col">
                <h4>Fontes de Dados</h4>
                <ul>
                    <li><a href="https://brapi.dev" target="_blank" rel="noopener">Brapi API (Ações)</a></li>
                    <li><a href="https://hgbrasil.com" target="_blank" rel="noopener">HG Brasil (Índices e Câmbio)</a></li>
                </ul>
            </div>
            <div class="footer-links-col">
                <h4>Projeto</h4>
                <ul>
                    <li><a href="https://github.com/Erick-Lim-Souza/AltaAgora" target="_blank" rel="noopener">Código Fonte (GitHub)</a></li>
                    <li><a href="https://linkedin.com/in/erickdelimasouza" target="_blank" rel="noopener">Desenvolvedor</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-premium-bottom">
            <div class="footer-disclaimer">
                <strong>Aviso Legal:</strong> Os dados e cotações exibidos neste portal são consumidos via APIs públicas de terceiros (Brapi e HG Brasil) e podem apresentar divergências ou atraso (delay). O AltaAgora tem caráter estritamente informativo. As informações aqui contidas não constituem recomendações de compra ou venda.
            </div>
            <div class="footer-credits-wrap">
                <span>&copy; <?= date('Y') ?> AltaAgora. Todos os direitos reservados.</span>
                <span>Criado por <strong>Erick de Lima Souza</strong> &middot; Green Monster Project</span>
            </div>
        </div>
    </footer>

</div>

<script>
// ═══════════════════════════════════════════════
//  1. PWA REGISTRATION
// ═══════════════════════════════════════════════
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW falhou:', err));
    });
}

// ═══════════════════════════════════════════════
//  2. SISTEMA DE NOTIFICAÇÕES (ALERTA > 5%)
// ═══════════════════════════════════════════════
const notifyBtn = document.getElementById('notifyBtn');
if (notifyBtn && "Notification" in window) {
    if (Notification.permission === "granted") notifyBtn.style.display = 'none';
    
    notifyBtn.addEventListener('click', () => {
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                notifyBtn.style.display = 'none';
                new Notification("AltaAgora Alertas", { body: "Avisaremos se alguma ação disparar mais de 5%!", icon: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='6' fill='%23050810'/%3E%3Ctext x='4' y='23' font-family='monospace' font-size='18' font-weight='700' fill='%2300ffa3'%3EA%3C/text%3E%3C/svg%3E" });
            }
        });
    });

    // Checa ações em tempo real
    if (Notification.permission === "granted") {
        document.querySelectorAll('.table-row').forEach(row => {
            let pct = parseFloat(row.dataset.pct);
            let symbol = row.dataset.symbol.toUpperCase();
            if (pct >= 5.0 || pct <= -5.0) { // Alerta para Altas ou Quedas fortes
                if (!sessionStorage.getItem('notified_' + symbol)) {
                    const txt = pct > 0 ? "🚀 Disparou!" : "📉 Despencou!";
                    new Notification(symbol + " " + txt, {
                        body: "A ação está com " + pct + "% de variação.",
                        icon: row.dataset.logo || ""
                    });
                    sessionStorage.setItem('notified_' + symbol, 'true');
                }
            }
        });
    }
}

// ═══════════════════════════════════════════════
//  3. PESQUISA RÁPIDA (QUICK SEARCH)
// ═══════════════════════════════════════════════
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.table-row').forEach(row => {
            const symbol = row.dataset.symbol;
            if (symbol.includes(term)) {
                row.classList.remove('hidden-row');
            } else {
                row.classList.add('hidden-row');
            }
        });
    });
}

// ═══════════════════════════════════════════════
//  4. TABS E ORDENAÇÃO DINÂMICA
// ═══════════════════════════════════════════════
// Tabs Logic
const tabBtns = document.querySelectorAll('.tab-btn[data-target]');
const tabContents = document.querySelectorAll('.tab-content');

tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        tabBtns.forEach(b => b.classList.remove('active'));
        tabContents.forEach(c => c.classList.remove('active'));
        
        btn.classList.add('active');
        document.getElementById(btn.dataset.target).classList.add('active');
    });
});

// Sort Logic
const btns = document.querySelectorAll('.filter-btn[data-sort]');
btns.forEach(btn => {
    btn.addEventListener('click', () => {
        btns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const sortType = btn.dataset.sort;
        
        // Aplica a ordenação nas duas tabelas
        ['gainers', 'losers'].forEach(tabId => {
            const tbody = document.getElementById(tabId);
            if (!tbody) return;
            const rows = Array.from(tbody.querySelectorAll('.table-row'));

            rows.sort((a, b) => {
                let valA = parseFloat(a.dataset[sortType]);
                let valB = parseFloat(b.dataset[sortType]);
                
                if (sortType === 'rank') return valA - valB; 
                return valB - valA; 
            });

            rows.forEach(row => tbody.appendChild(row));
        });
    });
});

// ═══════════════════════════════════════════════
//  5. COUNTDOWN DE REFRESH
// ═══════════════════════════════════════════════
(function () {
    const TOTAL = <?= PAGE_REFRESH ?>;
    let remaining = <?= $secondsLeft ?>;
    const elTopbar = document.getElementById('countdown'), elCard = document.getElementById('countdown-card'), elBar = document.getElementById('countdown-bar'), elRing = document.getElementById('ring-fill');
    const CIRCUMF = 113.1;
    function pad(n) { return String(n).padStart(2, '0'); }
    function fmt(s) { return pad(Math.floor(s / 60)) + ':' + pad(s % 60); }
    function update() {
        if (remaining <= 0) { location.reload(); return; }
        const display = fmt(remaining);
        if (elTopbar) elTopbar.textContent = display;
        if (elCard) elCard.textContent = display;
        if (elBar) elBar.style.setProperty('--pct', Math.round((remaining / TOTAL) * 100) + '%');
        if (elRing) elRing.style.strokeDashoffset = (CIRCUMF - (CIRCUMF * (remaining / TOTAL))).toFixed(2);
        const urgency = remaining <= 30;
        [elTopbar, elCard].forEach(el => { if (el) el.classList.toggle('countdown-urgent', urgency); });
        remaining--;
    }
    update(); setInterval(update, 1000);
})();

// Hover Highlights
document.querySelectorAll('.table-row').forEach(row => {
    row.addEventListener('mouseenter', () => row.classList.add('row-hl'));
    row.addEventListener('mouseleave', () => row.classList.remove('row-hl'));
});
</script>
</body>
</html>
