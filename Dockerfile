FROM php:8.2-cli-bookworm

WORKDIR /app

ENV HF_HOME=/app/storage/app/model-cache/huggingface \
    TORCH_HOME=/app/storage/app/model-cache/torch \
    VISUAL_SEARCH_PYTHON=python3 \
    VISUAL_SEARCH_MODEL=ViT-B-32 \
    VISUAL_SEARCH_PRETRAINED=openai \
    VISUAL_SEARCH_DEVICE=cpu \
    VISUAL_SEARCH_TIMEOUT=900

# System deps
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    python3 \
    python3-pip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev && \
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

# Visual-search ML deps (torch/openclip/faiss) — enabled for image-similarity search.
RUN pip3 install --break-system-packages --no-cache-dir -r scripts/requirements-visual-search.txt

# Laravel required folders BEFORE composer install
RUN mkdir -p storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/model-cache/huggingface \
    storage/app/model-cache/torch \
    bootstrap/cache

RUN chmod -R 777 storage bootstrap/cache

# Pre-download the OpenCLIP model (ViT-B-32/openai) into the image cache so
# visual search doesn't fetch ~350MB at runtime on Railway's ephemeral disk.
RUN python3 -c "import open_clip; open_clip.create_model_and_transforms('ViT-B-32', pretrained='openai')"

# PHP deps
RUN composer install --no-dev --optimize-autoloader

# Node deps + Vite build
RUN npm ci && npm run build

RUN chmod -R 777 storage bootstrap/cache

CMD php artisan config:clear || true; \
    php artisan storage:link || true; \
    (php artisan migrate --force || true) & \
    php artisan serve --host=0.0.0.0 --port=$PORT
