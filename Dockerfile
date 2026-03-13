# ─────────────────────────────────────────────
#  AltaAgora — Dockerfile para Render
#  PHP 8.2 + Apache
# ─────────────────────────────────────────────
FROM php:8.2-apache

# Extensões necessárias
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libssl-dev \
    && docker-php-ext-install curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite e mod_headers do Apache
RUN a2enmod rewrite headers expires deflate

# Configurar Apache para respeitar .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copiar todos os arquivos do projeto
COPY . /var/www/html/

# Criar diretório de cache com permissão correta
RUN mkdir -p /var/www/html/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/cache

# Remover index.html padrão do Apache se existir
RUN rm -f /var/www/html/index.html 2>/dev/null || true

# Porta padrão do Apache
EXPOSE 80

CMD ["apache2-foreground"]
