# Drivana demo container (PHP 8.3 + the extensions CodeIgniter 4 needs)
FROM php:8.3-cli

RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip libicu-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-jpeg --with-freetype \
 && docker-php-ext-install intl gd zip \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app

# dependencies first (cached unless composer.lock changes)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

COPY . .
RUN chmod +x start.sh \
 && mkdir -p writable/session writable/cache writable/logs writable/uploads public/uploads \
 && chmod -R 777 writable public/uploads

# DEMO_MODE=1 shows the demo logins + reset button on the sign-in pages
ENV DEMO_MODE=1 CI_ENVIRONMENT=production PORT=10000
EXPOSE 10000
CMD ["./start.sh"]
