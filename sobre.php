<?php
// ═══════════════════════════════════════════════════════
//  AltaAgora — sobre.php
//  Página institucional e manifesto do projeto
// ═══════════════════════════════════════════════════════
require_once 'config.php';
$lastUpdate = date('H:i:s');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sobre a iniciativa AltaAgora: Arquitetura, dados e tecnologia.">
    <title>Sobre a Iniciativa · AltaAgora</title>
    
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#050810">
    <link rel="apple-touch-icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='6' fill='%23050810'/%3E%3Ctext x='4' y='23' font-family='monospace' font-size='18' font-weight='700' fill='%2300ffa3'%3EA%3C/text%3E%3C/svg%3E">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='6' fill='%23050810'/%3E%3Ctext x='4' y='23' font-family='monospace' font-size='18' font-weight='700' fill='%2300ffa3'%3EA%3C/text%3E%3C/svg%3E">
    <link rel="stylesheet" href="style.css">
    
    <style>
        .about-content { max-width: 800px; margin: 0 auto; padding: 40px 20px 80px; }
        .about-text { font-size: 1.05rem; line-height: 1.8; color: var(--text-mid); margin-bottom: 24px; }
        .about-text strong { color: var(--text-hi); }
        .tech-stack { display: flex; flex-wrap: wrap; gap: 12px; margin: 32px 0; }
        .tech-badge { background: var(--bg-card-2); border: 1px solid var(--border); padding: 8px 16px; border-radius: 99px; font-family: var(--font-mono); font-size: 0.8rem; color: var(--accent); }
        .about-section-title { font-family: var(--font-mono); font-size: 0.85rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-lo); margin: 48px 0 16px; border-bottom: 1px solid var(--border); padding-bottom: 8px; }
        
        .footer-premium { border-top: 1px solid var(--border); background: var(--bg-card); padding: 48px 5% 24px; margin-top: auto; font-size: 0.8rem; position: relative; z-index: 10; }
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
<body style="display: flex; flex-direction: column; min-height: 100vh;">

<div class="scanlines"  aria-hidden="true"></div>
<div class="bg-grid"    aria-hidden="true"></div>
<div class="orb orb-1"  aria-hidden="true"></div>
<div class="orb orb-2"  aria-hidden="true"></div>

<header class="topbar">
    <div class="topbar-left">
        <a href="index.php" class="logo">Alta<span class="logo-accent">Agora</span><span class="logo-dot">.</span></a>
    </div>

    <div class="topbar-right">
        <a href="index.php" style="color: var(--text-mid); font-size: 0.8rem; margin-right: 16px; font-family: var(--font-mono); text-transform: uppercase; transition: color 0.2s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-mid)'">← Voltar ao Terminal</a>
        <span class="live-dot" title="Status Online"></span>
        <span class="update-time mono"><?= $lastUpdate ?></span>
        <span class="session-tag">SYSTEM_INFO</span>
    </div>
</header>

<main class="about-content">
    <section class="page-title" style="padding-top: 20px;">
        <div class="page-title-inner">
            <p class="page-eyebrow">// the project</p>
            <h1>Sobre a <span class="accent">Iniciativa</span></h1>
            <p class="page-subtitle">Democratizando dados da B3 com alta performance.</p>
        </div>
        <div class="title-line"></div>
    </section>

    <h2 class="about-section-title">O Propósito</h2>
    <p class="about-text">
        O <strong>AltaAgora</strong> nasceu de uma necessidade clara: acompanhar a volatilidade do mercado financeiro brasileiro através de uma interface limpa, rápida e sem o ruído dos grandes portais de notícias. O foco aqui é puramente no <em>Data Feed</em>.
    </p>
    <p class="about-text">
        Projetado como um terminal web híbrido, a aplicação compila os principais *gainers* e *losers* do pregão atual da <strong>B3 (Brasil Bolsa Balcão)</strong> em tempo real, além de monitorar o câmbio global, entregando uma experiência *frictionless* tanto para investidores quanto para entusiastas do mercado de capitais.
    </p>

    <h2 class="about-section-title">Arquitetura & Engenharia</h2>
    <p class="about-text">
        Para garantir resiliência e evitar o temido <em>Single Point of Failure</em> (SPOF), o AltaAgora consome dados simultâneos de múltiplas fontes: a <strong>Brapi API</strong> alimenta o core de ações, enquanto a <strong>HG Brasil</strong> sustenta o ticker de índices globais e câmbio.
    </p>
    <p class="about-text">
        No backend, um sistema de *caching* inteligente gerencia o *rate limit* de cada API de forma independente, preservando cotas e garantindo tempo de resposta na casa dos milissegundos. Toda a infraestrutura é *containerizada* com Docker, aplicando rígidas políticas de CSP (Content Security Policy) e proteção de endpoints.
    </p>

    <div class="tech-stack">
        <span class="tech-badge">PHP 8.2</span>
        <span class="tech-badge">Docker</span>
        <span class="tech-badge">RESTful APIs</span>
        <span class="tech-badge">PWA Ready</span>
        <span class="tech-badge">Vanilla JS</span>
        <span class="tech-badge">CSS Grid</span>
    </div>

    <h2 class="about-section-title">O Desenvolvedor</h2>
    <p class="about-text">
        Idealizado e desenvolvido por <strong>Erick de Lima Souza</strong>. Combinando um background profundo em <em>Data Analytics</em>, fundamentos de <em>DevOps</em> e Ciência da Computação, o projeto une a precisão exigida no tratamento de dados financeiros com a robustez e segurança de servidores escaláveis.
    </p>
    <p class="about-text">
        A paixão por gestão financeira corporativa e análise de dados foi o motor para construir uma ferramenta que vai além do código, entregando inteligência de mercado de forma direta e otimizada.
    </p>

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
                <li><a href="https://hgbrasil.com" target="_blank" rel="noopener">HG Brasil (Índices)</a></li>
                <li><a href="https://www.b3.com.br" target="_blank" rel="noopener">B3 - Brasil Bolsa Balcão</a></li>
            </ul>
        </div>
        <div class="footer-links-col">
            <h4>Projeto</h4>
            <ul>
                <li><a href="https://github.com/Erick-Lim-Souza/AltaAgora" target="_blank" rel="noopener">Código Fonte (GitHub)</a></li>
                <li><a href="https://ericklima-dev.netlify.app/" target="_blank" rel="noopener">Desenvolvedor</a></li>
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

</body>
</html>
