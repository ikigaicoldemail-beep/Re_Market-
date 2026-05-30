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

# PHP deps
RUN composer install --no-dev --optimize-autoloader

# Node deps + Vite build
RUN npm ci && npm run build

RUN chmod -R 777 storage bootstrap/cache

CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=$PORT
