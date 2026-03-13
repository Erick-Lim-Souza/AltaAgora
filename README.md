<div align="center">

```
░█▀█░█░░░▀█▀░█▀█░█▀█░█▀▀░█▀█░█▀▄░█▀█
░█▀█░█░░░░█░░█▀█░█▀█░█░█░█░█░█▀▄░█▀█
░▀░▀░▀▀▀░░▀░░▀░▀░▀░▀░▀▀▀░▀▀▀░▀░▀░▀░▀
```

**Terminal financeiro de alta performance para o mercado B3**

[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![HG Brasil API](https://img.shields.io/badge/API-HG%20Brasil-00ffa3?style=flat-square)](https://hgbrasil.com)
[![License](https://img.shields.io/badge/License-MIT-blue?style=flat-square)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Live-00ffa3?style=flat-square)]()
[![Made by](https://img.shields.io/badge/Made%20by-Green%20Monster%20Project-2d2d2d?style=flat-square)]()

<br/>

> *Dados reais do pregão B3 · Cache inteligente · Interface terminal futurista*

</div>

---

## 📡 Sobre o Projeto

**AltaAgora** é um painel financeiro minimalista e de alta performance que exibe em tempo real as **ações com maiores altas** do pregão da B3 (Bolsa de Valores do Brasil). Desenvolvido com estética de terminal tech e dados ao vivo via API HG Brasil.

O projeto nasceu da necessidade de ter uma visão limpa, rápida e direta das maiores movimentações do mercado brasileiro — sem ruído, sem anúncios, sem distrações.

---

## ✦ Features

- 📈 **Top 15 ações em alta** do pregão atual, ordenadas por variação percentual
- 📊 **Indicadores de mercado** — IBOVESPA, S&P 500, Nasdaq e outros índices globais
- ⚡ **Ticker animado** com índices rolando na barra superior
- 🎯 **Cards de resumo** — maior alta, média do grupo, volume total
- 🏎️ **Cache inteligente** de 5 minutos — chamadas rápidas, sem abuse da API
- 🔒 **Segurança** — headers HTTP, sanitização de output, SSL verificado
- 📱 **Responsivo** — funciona em desktop, tablet e mobile
- 🔄 **Auto-refresh** automático a cada 5 minutos

---

## 🗂️ Estrutura do Projeto

```
altaagora/
│
├── index.php          # Página principal — lógica + template HTML
├── config.php         # Configurações, API key, cache
├── functions.php      # Funções de API, formatação, helpers
├── style.css          # Estilo completo — tema dark terminal
├── .htaccess          # Segurança Apache, headers HTTP, cache estático
│
└── cache/             # Gerado automaticamente (ignorado pelo Git)
    └── .htaccess      # Bloqueia acesso HTTP ao diretório de cache
```

---

## 🚀 Como Rodar Localmente

### Pré-requisitos

- PHP 8.1 ou superior
- Extensão cURL habilitada
- Chave de API gratuita da [HG Brasil](https://hgbrasil.com)

### Instalação

```bash
# 1. Clone o repositório
git clone https://github.com/seu-usuario/altaagora.git
cd altaagora

# 2. Configure sua chave de API
# Abra config.php e substitua:
define('API_KEY', 'SUA_CHAVE_AQUI');
# ou (recomendado em produção):
export HG_API_KEY="sua_chave_aqui"

# 3. Suba um servidor local PHP
php -S localhost:8080

# 4. Acesse no navegador
# http://localhost:8080
```

> **Dica de segurança:** Em produção, use variáveis de ambiente em vez de hardcodar a chave no arquivo.

---

## 🔑 Configuração da API

O projeto usa a **[API HG Brasil](https://hgbrasil.com)** — uma das melhores APIs de dados financeiros do Brasil.

1. Crie uma conta gratuita em [console.hgbrasil.com](https://console.hgbrasil.com)
2. Gere sua chave de API
3. Configure no `config.php` ou via variável de ambiente:

```php
// config.php
define('API_KEY', getenv('HG_API_KEY') ?: 'sua_chave_aqui');
```

**Endpoints utilizados:**
| Endpoint | Uso |
|---|---|
| `/finance/stock_price?symbol=get-high` | Top ações em alta |
| `/finance?fields=stocks` | Índices do mercado |

---

## 🛡️ Segurança

O projeto implementa diversas camadas de proteção:

| Medida | Descrição |
|---|---|
| **XSS Prevention** | `htmlspecialchars()` em todos os outputs |
| **SSL Verification** | cURL com `CURLOPT_SSL_VERIFYPEER = true` |
| **HTTP Headers** | `X-Frame-Options`, `X-XSS-Protection`, `CSP`, `Referrer-Policy` |
| **File Protection** | `.htaccess` bloqueia acesso a `config.php`, `functions.php` e `/cache/` |
| **Cache Seguro** | Permissão `0755`, gravação com `LOCK_EX` |
| **Env Variables** | Suporte a `getenv()` para a API key |
| **Dir Listing** | `Options -Indexes` desabilitado |

---

## ⚙️ Configurações do Cache

| Parâmetro | Valor | Descrição |
|---|---|---|
| `CACHE_TIME` | 300s (5 min) | Tempo de vida do cache |
| `CACHE_DIR` | `./cache/` | Diretório de armazenamento |
| Auto-refresh | 300s | Reload automático da página |

---

## 🎨 Stack Técnica

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.1+ nativo (sem frameworks) |
| Frontend | HTML5 + CSS3 puro (sem build tools) |
| Dados | HG Brasil Finance API |
| Fontes | Syne + Space Mono (Google Fonts) |
| Servidor | Apache / Nginx com `.htaccess` |
| Cache | Sistema de arquivos local (JSON) |

---

## 📸 Preview

```
┌─────────────────────────────────────────────────────────┐
│  STCK. Terminal B3    ● IBOV 127.432  ▲ LIVE  10:42:31  │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Ações em Alta                                          │
│  Top gainers do pregão atual                            │
│  ─────────────────────────────────────────────────────  │
│                                                         │
│  ▲ MAIOR ALTA    ~ MÉDIA       # VOLUME    ✦ ATIVOS     │
│  MGLU3           +4.21%        312.4M      15           │
│  R$ 3,47         média top 15  negociadas  rastreados   │
│                                                         │
│  #   Ativo   Empresa          Preço   Var%    Volume    │
│  1°  MGLU3   Magazine Luiza   R$3,47  ▲4.21%  87.3M    │
│  2°  CVCB3   CVC Brasil       R$2,81  ▲3.90%  41.2M    │
│  ...                                                    │
└─────────────────────────────────────────────────────────┘
```

---

## 📋 Roadmap

- [ ] Filtro por setor (Financeiro, Consumo, Tech...)
- [ ] Gráfico de candle intraday por ativo
- [ ] Modo claro / escuro toggle
- [ ] Alertas de preço via notificação do browser
- [ ] Histórico de altas por dia
- [ ] Comparador de ações

---

## 🤝 Contribuindo

Contribuições são bem-vindas! Para contribuir:

```bash
# Fork o projeto
# Crie sua branch
git checkout -b feature/minha-feature

# Commit suas mudanças
git commit -m "feat: adiciona minha feature"

# Push para a branch
git push origin feature/minha-feature

# Abra um Pull Request
```

---

## ⚠️ Aviso Legal

> Este projeto é desenvolvido **apenas para fins informativos e educacionais**. Os dados exibidos são fornecidos pela API HG Brasil e podem apresentar atraso. **Não utilize este painel como base para decisões de investimento.** Consulte sempre um profissional certificado pela CVM.

---

## 📄 Licença

Distribuído sob a licença MIT. Veja [`LICENSE`](LICENSE) para mais informações.

---

<div align="center">

## 👨‍💻 Créditos

<br/>

**Desenvolvido e criado por**

### Erick de Lima Souza

*Full Stack Developer*

<br/>

**Um projeto**

```
 ██████╗ ██████╗ ███████╗███████╗███╗   ██╗
██╔════╝ ██╔══██╗██╔════╝██╔════╝████╗  ██║
██║  ███╗██████╔╝█████╗  █████╗  ██╔██╗ ██║
██║   ██║██╔══██╗██╔══╝  ██╔══╝  ██║╚██╗██║
╚██████╔╝██║  ██║███████╗███████╗██║ ╚████║
 ╚═════╝ ╚═╝  ╚═╝╚══════╝╚══════╝╚═╝  ╚═══╝

███╗   ███╗ ██████╗ ███╗   ██╗███████╗████████╗███████╗██████╗
████╗ ████║██╔═══██╗████╗  ██║██╔════╝╚══██╔══╝██╔════╝██╔══██╗
██╔████╔██║██║   ██║██╔██╗ ██║███████╗   ██║   █████╗  ██████╔╝
██║╚██╔╝██║██║   ██║██║╚██╗██║╚════██║   ██║   ██╔══╝  ██╔══██╗
██║ ╚═╝ ██║╚██████╔╝██║ ╚████║███████║   ██║   ███████╗██║  ██║
╚═╝     ╚═╝ ╚═════╝ ╚═╝  ╚═══╝╚══════╝   ╚═╝   ╚══════╝╚═╝  ╚═╝

██████╗ ██████╗  ██████╗      ██╗███████╗ ██████╗████████╗
██╔══██╗██╔══██╗██╔═══██╗     ██║██╔════╝██╔════╝╚══██╔══╝
██████╔╝██████╔╝██║   ██║     ██║█████╗  ██║        ██║
██╔═══╝ ██╔══██╗██║   ██║██   ██║██╔══╝  ██║        ██║
██║     ██║  ██║╚██████╔╝╚█████╔╝███████╗╚██████╗   ██║
╚═╝     ╚═╝  ╚═╝ ╚═════╝  ╚════╝ ╚══════╝ ╚═════╝   ╚═╝
```

*Construindo ferramentas que fazem diferença.*

<br/>

---

*AltaAgora © 2025 · Green Monster Project · Feito com ☕ e muita linha de código*

</div>