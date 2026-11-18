# Imagem do site Acesso+ para o Railway: PHP 8.3 + Apache + driver do PostgreSQL (Supabase)
FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

# Configurações do PHP para produção
RUN { \
      echo "date.timezone=America/Sao_Paulo"; \
      echo "display_errors=Off"; \
      echo "log_errors=On"; \
      echo "error_log=/dev/stderr"; \
      echo "session.cookie_httponly=1"; \
    } > /usr/local/etc/php/conf.d/acessomais.ini

# Não deixa ninguém baixar arquivos ocultos (.env, .git) nem os arquivos de build
RUN { \
      echo '<FilesMatch "^\.|^(Dockerfile|banco_de_dados\.sql)$">'; \
      echo '    Require all denied'; \
      echo '</FilesMatch>'; \
      echo 'DirectoryIndex index.html index.php'; \
    } > /etc/apache2/conf-enabled/acessomais.conf

COPY . /var/www/html/
RUN rm -f /var/www/html/.env && chown -R www-data:www-data /var/www/html

# O Railway informa a porta pela variável PORT; o Apache passa a escutar nela ao iniciar
CMD ["sh", "-c", "sed -ri \"s/Listen [0-9]+/Listen ${PORT:-80}/\" /etc/apache2/ports.conf && sed -ri \"s/:[0-9]+>/:${PORT:-80}>/\" /etc/apache2/sites-enabled/000-default.conf && exec apache2-foreground"]
