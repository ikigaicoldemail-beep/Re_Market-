FROM php:8.2-cli-bookworm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    libsqlite3-dev \
    default-mysql-client \
    python3 \
    python3-pip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd pdo_mysql pdo_sqlite zip pcntl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/entrypoint.sh /usr/local/bin/docker-entrypoint-app

COPY scripts/requirements-visual-search.txt /tmp/requirements-visual-search.txt

RUN pip3 install --break-system-packages --no-cache-dir -r /tmp/requirements-visual-search.txt

RUN chmod +x /usr/local/bin/docker-entrypoint-app

ENTRYPOINT ["docker-entrypoint-app"]
