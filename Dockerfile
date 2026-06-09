FROM php:8.2-cli

WORKDIR /app

# System deps
RUN apt-get update && apt-get install -y git unzip zip curl && \
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

# Laravel required folders BEFORE composer install
RUN mkdir -p storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache

RUN chmod -R 777 storage bootstrap/cache

# PHP deps
RUN composer install --no-dev --optimize-autoloader

# Node deps + Vite build
RUN npm ci && npm run build

CMD php artisan serve --host=0.0.0.0 --port=${PORT}