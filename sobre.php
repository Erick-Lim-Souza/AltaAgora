<?php
// ═══════════════════════════════════════════════════════
//  AltaAgora Terminal — sobre.php
//  Grupo Green Monster Project / by FinanIA
//  Página Institucional e Manifesto do Projeto
// ═══════════════════════════════════════════════════════
require_once 'config.php';
$lastUpdate = date('H:i:s');
?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre a Iniciativa · AltaAgora Terminal</title>
    
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0A0F1C">
    <link rel="apple-touch-icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='6' fill='%230A0F1C'/%3E%3Ctext x='4' y='23' font-family='monospace' font-size='18' font-weight='700' fill='%2300E5FF'%3EA%3C/text%3E%3C/svg%3E">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='6' fill='%230A0F1C'/%3E%3Ctext x='4' y='23' font-family='monospace' font-size='18' font-weight='700' fill='%2300E5FF'%3EA%3C/text%3E%3C/svg%3E">
    <link rel="stylesheet" href="style.css">
    
    <style>
        .about-container { max-width: 800px; margin: 0 auto; padding: 60px 20px 100px; }
        .about-text { font-size: 1.05rem; line-height: 1.8; color: var(--text-mid); margin-bottom: 28px; }
        .about-text strong { color: var(--text-hi); font-weight: 600; }
        .about-text i { color: var(--accent); font-style: normal; }
        
        .tech-stack { display: flex; flex-wrap: wrap; gap: 12px; margin: 40px 0; }
        .tech-badge { background: var(--bg-card-2); border: 1px solid var(--border); padding: 8px 18px; border-radius: 6px; font-family: var(--font-mono); font-size: 0.8rem; color: var(--accent); font-weight: 700; }
        
        .about-section-title { font-size: 0.8rem; font-weight: 800; color: var(--accent); margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.15em; display: flex; align-items: center; gap: 15px; margin-top: 60px; }
        .about-section-title::after { content: ''; height: 1px; flex: 1; background: var(--border); }
    </style>
</head>
<body style="display: flex; flex-direction: column; min-height: 100vh;">

<div class="scanlines" aria-hidden="true"></div>
<div class="layout">
    <div class="orb-1" aria-hidden="true"></div>

    <header class="topbar">
        <div class="topbar-left">
            <a href="index.php" class="logo">Alta<span class="logo-accent">Agora</span><span class="logo-dot">.</span></a>
        </div>
        <div class="topbar-right">
            <a href="index.php" style="color: var(--text-hi); font-size: 0.75rem; margin-right: 16px; font-family: var(--font-mono); text-transform: uppercase; text-decoration: none; border: 1px solid var(--border); padding: 6px 12px; border-radius: 4px;">← Voltar ao Terminal</a>
            <span class="live-dot"></span>
            <span class="update-time mono"><?= $lastUpdate ?></span>
        </div>
    </header>

    <main class="about-container">
        <section class="page-title">
            <h1 class="page-subtitle">// a iniciativa</h1>
            <p style="font-size: 2.5rem; font-weight: 800; color: var(--text-hi); margin-top: 10px; letter-spacing: -0.02em;">Sobre o <span class="accent">Terminal</span></p>
        </section>

        <h2 class="about-section-title">Propósito</h2>
        <p class="about-text">
            O <strong>AltaAgora</strong> é um terminal financeiro de alta performance desenvolvido pelo ecossistema <strong>Green Monster Project</strong>. O foco é a democratização de dados do mercado brasileiro e global através de uma interface <i>frictionless</i>, limpa e extremamente rápida.
        </p>
        <p class="about-text">
            Projetado como um hub híbrido, a plataforma consolida <strong>Ações da B3</strong>, <strong>Criptoativos</strong> e <strong>Índices Mundiais</strong> em uma única camada visual, eliminando o ruído informativo dos portais tradicionais.
        </p>

        <h2 class="about-section-title">Arquitetura de Dados</h2>
        <p class="about-text">
            A robustez do sistema reside na ingestão assíncrona de múltiplas fontes. Utilizamos a <strong>Brapi API</strong> para o core de ações, o feed da <strong>CoinGecko</strong> para o mercado digital e a <strong>HG Brasil</strong> para índices e câmbio em tempo real.
        </p>
        <p class="about-text">
            No backend, implementamos um sistema de <i>Caching Estratégico</i> em PHP 8.2 que gerencia os limites de cada fornecedor de forma independente, garantindo latência mínima e resiliência total contra falhas de conexão externa.
        </p>

        <div class="tech-stack">
            <span class="tech-badge">PHP 8.2</span>
            <span class="tech-badge">Multi-API REST</span>
            <span class="tech-badge">Docker Container</span>
            <span class="tech-badge">PWA Ready</span>
            <span class="tech-badge">Vanilla JS</span>
            <span class="tech-badge">Analytics Focused</span>
        </div>

        <h2 class="about-section-title">O Desenvolvedor</h2>
        <p class="about-text">
            Idealizado por <strong>Erick de Lima Souza</strong>. Com um background sólido em <i>Data Analytics</i> e fundamentos de <i>DevOps</i>, o projeto une a precisão exigida no tratamento de dados financeiros com a segurança de infraestruturas escaláveis.
        </p>
        <p class="about-text">
            A visão do Grupo Green Monster é transformar tecnologia em inteligência de mercado, permitindo que dados crus se tornem narrativas visuais estratégicas.
        </p>
    </main>

    <footer class="footer-master">
        <div class="footer-top">
            <div class="footer-brand-col">
                <div class="footer-tagline">Inteligência de Dados</div>
                <p class="footer-desc">
                    Parte integrante do ecossistema <strong>Green Monster</strong>. 
                    Soluções focadas em performance e precisão analítica.
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
                    <li><a href="https://erickanalytics.netlify.app/">Portfólio Profissional</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span class="footer-by-finania">by <span>FinanIA</span> & Grupo Green Monster Project!</span>
            <div class="footer-legal">© <?= date('Y') ?> AltaAgora Terminal · v1.2.0</div>
        </div>
    </footer>
</div>
</body>
</html>
