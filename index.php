<?php
// ═══════════════════════════════════════════════════════
//  AltaAgora — index.php
// ═══════════════════════════════════════════════════════
require_once 'functions.php';

$topStocks      = getTopGainers(15);
$indices        = getMarketIndices();
$lastUpdate     = date('H:i:s');
$hasError       = empty($topStocks);
$apiKeyMissing  = !API_KEY_SET;
$secondsLeft    = getSecondsUntilRefresh();

$topStock  = $hasError ? null : $topStocks[0];
$avgChange = $hasError ? 0 : array_sum(array_column($topStocks, 'change_percent')) / count($topStocks);
$totalVol  = $hasError ? 0 : array_sum(array_column($topStocks, 'volume'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AltaAgora — Ações com maiores altas do pregão B3 em tempo real.">
    <meta name="robots" content="noindex, nofollow">
    <title>AltaAgora · Ações em Alta — Pregão B3</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='6' fill='%23050810'/%3E%3Ctext x='4' y='23' font-family='monospace' font-size='18' font-weight='700' fill='%2300ffa3'%3EA%3C/text%3E%3C/svg%3E">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ░░ LAYERS ░░ -->
<div class="scanlines"  aria-hidden="true"></div>
<div class="bg-grid"    aria-hidden="true"></div>
<div class="orb orb-1"  aria-hidden="true"></div>
<div class="orb orb-2"  aria-hidden="true"></div>

<div class="layout">

    <!-- ════ TOPBAR ════ -->
    <header class="topbar">
        <div class="topbar-left">
            <span class="logo">Alta<span class="logo-accent">Agora</span><span class="logo-dot">.</span></span>
        </div>

        <div class="topbar-center">
            <?php if (!empty($indices)): ?>
            <div class="ticker-wrap">
                <div class="ticker">
                    <?php
                    // Duplicado para loop infinito
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
            <span class="live-dot" title="Dados ao vivo"></span>
            <span class="update-time mono"><?= $lastUpdate ?></span>

            <!-- ── COUNTDOWN ── -->
            <div class="countdown-wrap" title="Próxima atualização dos dados">
                <span class="countdown-label">REFRESH</span>
                <span class="countdown-timer mono" id="countdown"
                      data-seconds="<?= $secondsLeft ?>">
                    <?= sprintf('%02d:%02d', intdiv($secondsLeft, 60), $secondsLeft % 60) ?>
                </span>
                <div class="countdown-bar">
                    <div class="countdown-bar-fill" id="countdown-bar"
                         style="--pct:<?= $secondsLeft > 0 ? round(($secondsLeft / CACHE_TIME) * 100) : 0 ?>%">
                    </div>
                </div>
            </div>

            <span class="session-tag">PREGÃO B3</span>
        </div>
    </header>

    <main class="main-content">

        <!-- ════ AVISO: API KEY AUSENTE ════ -->
        <?php if ($apiKeyMissing): ?>
        <div class="alert-banner alert-warning">
            <span class="alert-icon">⚙</span>
            <div>
                <strong>Variável de ambiente não configurada.</strong>
                <span>Defina <code>HG_API_KEY</code> no painel do Render em
                <em>Settings → Environment Variables</em> e aguarde o redeploy.</span>
            </div>
        </div>
        <?php endif; ?>

        <!-- ════ PAGE TITLE ════ -->
        <section class="page-title">
            <div class="page-title-inner">
                <p class="page-eyebrow">// terminal financeiro</p>
                <h1>Ações <span class="accent">em Alta</span></h1>
                <p class="page-subtitle">
                    Top gainers do pregão atual &middot; Cache de <?= CACHE_TIME / 60 ?> min &middot; Bolsa B3 · São Paulo
                </p>
            </div>
            <div class="title-line"></div>
        </section>

        <?php if ($hasError && !$apiKeyMissing): ?>
        <!-- ════ ERRO DE API ════ -->
        <div class="alert-banner alert-error">
            <span class="alert-icon">⚠</span>
            <p>Não foi possível carregar os dados. Verifique sua chave de API ou tente novamente em instantes.</p>
        </div>

        <?php elseif (!$hasError): ?>

        <!-- ════ STAT CARDS ════ -->
        <?php
        $topVar = formatVariation($topStock['change_percent']);
        $avgVar = formatVariation($avgChange);
        ?>
        <div class="stat-grid">
            <div class="stat-card stat-card--highlight" style="--delay:0s">
                <div class="stat-label">▲ MAIOR ALTA</div>
                <div class="stat-symbol"><?= $topStock['symbol'] ?></div>
                <div class="stat-price mono"><?= formatMoney($topStock['price']) ?></div>
                <div class="stat-var <?= $topVar['class'] ?> mono"><?= $topVar['symbol'] ?>&thinsp;<?= $topVar['value'] ?>%</div>
                <div class="stat-name"><?= $topStock['name'] ?></div>
            </div>

            <div class="stat-card" style="--delay:.07s">
                <div class="stat-label">~ MÉDIA DO GRUPO</div>
                <div class="stat-big <?= $avgVar['class'] ?> mono"><?= $avgVar['symbol'] ?><?= $avgVar['value'] ?>%</div>
                <div class="stat-sub">top <?= count($topStocks) ?> ações</div>
            </div>

            <div class="stat-card" style="--delay:.14s">
                <div class="stat-label"># VOLUME TOTAL</div>
                <div class="stat-big mono"><?= formatVolume($totalVol) ?></div>
                <div class="stat-sub">ações negociadas</div>
            </div>

            <div class="stat-card stat-card--countdown" style="--delay:.21s">
                <div class="stat-label">↻ PRÓX. ATUALIZAÇÃO</div>
                <div class="stat-big mono" id="countdown-card">
                    <?= sprintf('%02d:%02d', intdiv($secondsLeft, 60), $secondsLeft % 60) ?>
                </div>
                <div class="stat-sub">segundos restantes</div>
                <div class="progress-ring-wrap">
                    <svg class="progress-ring" viewBox="0 0 44 44">
                        <circle class="ring-bg"   cx="22" cy="22" r="18" />
                        <circle class="ring-fill" cx="22" cy="22" r="18"
                            id="ring-fill"
                            stroke-dasharray="113.1"
                            stroke-dashoffset="<?= 113.1 - (113.1 * ($secondsLeft / CACHE_TIME)) ?>"
                        />
                    </svg>
                </div>
            </div>
        </div>

        <!-- ════ TABELA ════ -->
        <section class="table-section">
            <div class="table-header">
                <h2 class="table-title">TOP <?= count($topStocks) ?> &mdash; Maiores Altas</h2>
                <div class="table-header-right">
                    <span class="table-badge">B3 &middot; São Paulo</span>
                </div>
            </div>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ativo</th>
                            <th>Empresa</th>
                            <th>Preço</th>
                            <th>Var R$</th>
                            <th>Var %</th>
                            <th>Volume</th>
                            <th>Força</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $maxPct = max(array_column($topStocks, 'change_percent')) ?: 1;
                    foreach ($topStocks as $i => $stock):
                        $var    = formatVariation($stock['change_percent']);
                        $barPct = round(($stock['change_percent'] / $maxPct) * 100);
                    ?>
                        <tr class="table-row" style="--row-delay:<?= $i * 0.035 ?>s">
                            <td>
                                <span class="rank rank-<?= $i < 3 ? ($i + 1) : 'n' ?>"><?= $i + 1 ?></span>
                            </td>
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
                            <td class="mono col-price"><?= formatMoney($stock['price']) ?></td>
                            <td class="mono <?= $var['class'] ?>">
                                <?= ($stock['change_price'] >= 0 ? '+' : '') ?><?= formatMoney($stock['change_price']) ?>
                            </td>
                            <td>
                                <span class="pct-pill <?= $var['class'] ?>"><?= $var['symbol'] ?>&thinsp;<?= $var['value'] ?>%</span>
                            </td>
                            <td class="mono col-vol"><?= formatVolume($stock['volume']) ?></td>
                            <td class="col-bar">
                                <div class="bar-track">
                                    <div class="bar-fill <?= $var['class'] ?>" style="--w:<?= $barPct ?>%"></div>
                                </div>
                                <span class="bar-pct"><?= $barPct ?>%</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ════ ÍNDICES ════ -->
        <?php if (!empty($indices)): ?>
        <section class="indices-section">
            <h3 class="section-label">// índices do mercado</h3>
            <div class="indices-grid">
                <?php foreach ($indices as $idx => $d):
                    $v = formatVariation((float)($d['variation'] ?? 0));
                ?>
                <div class="index-card">
                    <span class="index-name"><?= h($d['name'] ?? $idx) ?></span>
                    <span class="index-points mono"><?= number_format((float)($d['points'] ?? 0), 2, ',', '.') ?></span>
                    <span class="index-var <?= $v['class'] ?>"><?= $v['html'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php endif; ?>

    </main>

    <!-- ════ FOOTER ════ -->
    <footer class="footer">
        <span class="footer-brand">Alta<span class="accent">Agora</span></span>
        <span class="footer-sep">·</span>
        <span>Dados via <a href="https://hgbrasil.com" target="_blank" rel="noopener">HG Brasil</a></span>
        <span class="footer-sep">·</span>
        <span>Apenas para fins informativos</span>
        <span class="footer-sep">·</span>
        <span class="footer-credits">
            Criado por <strong>Erick de Lima Souza</strong> &middot; <strong>Green Monster Project</strong>
        </span>
        <span class="footer-sep">·</span>
        <span class="mono">&copy; <?= date('Y') ?></span>
    </footer>

</div><!-- /layout -->

<script>
// ═══════════════════════════════════════════════
//  AltaAgora — Countdown + Auto-refresh
// ═══════════════════════════════════════════════
(function () {
    const TOTAL        = <?= CACHE_TIME ?>;           // segundos totais do cache
    let   remaining    = <?= $secondsLeft ?>;         // segundos restantes do PHP

    const elTopbar     = document.getElementById('countdown');
    const elCard       = document.getElementById('countdown-card');
    const elBar        = document.getElementById('countdown-bar');
    const elRing       = document.getElementById('ring-fill');
    const CIRCUMF      = 113.1; // 2 * π * 18

    function pad(n) { return String(n).padStart(2, '0'); }

    function fmt(s) {
        const m = Math.floor(s / 60);
        const sec = s % 60;
        return pad(m) + ':' + pad(sec);
    }

    function update() {
        if (remaining <= 0) {
            // Recarregar página para buscar dados frescos
            location.reload();
            return;
        }

        const display = fmt(remaining);
        if (elTopbar)  elTopbar.textContent  = display;
        if (elCard)    elCard.textContent     = display;

        // Barra linear na topbar
        const pct = Math.round((remaining / TOTAL) * 100);
        if (elBar) elBar.style.setProperty('--pct', pct + '%');

        // Anel SVG no card
        if (elRing) {
            const offset = CIRCUMF - (CIRCUMF * (remaining / TOTAL));
            elRing.style.strokeDashoffset = offset.toFixed(2);
        }

        // Cor de urgência nos últimos 30s
        const urgency = remaining <= 30;
        [elTopbar, elCard].forEach(el => {
            if (el) el.classList.toggle('countdown-urgent', urgency);
        });

        remaining--;
    }

    // Iniciar imediatamente + tick a cada segundo
    update();
    setInterval(update, 1000);
})();

// Highlight de linha
document.querySelectorAll('.table-row').forEach(row => {
    row.addEventListener('mouseenter', () => row.classList.add('row-hl'));
    row.addEventListener('mouseleave', () => row.classList.remove('row-hl'));
});
</script>
</body>
</html>
