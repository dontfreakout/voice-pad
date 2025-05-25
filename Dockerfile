# Dockerfile for Laravel Production Environment
# Frontend build stage
FROM node:20-alpine AS frontend-builder
WORKDIR /app/frontend

# Copy package.json and install dependencies
# Ensure package.json is at the root of your project context when Docker build runs
COPY package.json ./
# If you have a package-lock.json, uncomment the next line and ensure it's copied
# COPY package-lock.json ./
RUN npm install --legacy-peer-deps

# Copy frontend configuration and source files
# Ensure these files/directories are at the root or adjust paths accordingly relative to your Docker build context
COPY vite.config.js ./
COPY resources/js ./resources/js
COPY resources/css ./resources/css
# If you have tailwind.config.js at the root of your project, uncomment the next line
# COPY tailwind.config.js ./
# If you have postcss.config.js at the root of your project, uncomment the next line
# COPY postcss.config.js ./

# Build frontend assets
RUN npm run build
# This assumes 'npm run build' outputs to 'public/build' directory relative to /app/frontend

# 1. Base Image
# Using PHP 8.4 FPM as specified in composer.json for broader compatibility
FROM php:8.4-fpm

# 2. Install System Dependencies & PHP Extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libgd-dev \
    ffmpeg \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql gd exif pcntl bcmath opcache intl zip pdo_pgsql \
    && pecl install redis \
    && docker-php-ext-enable pdo_mysql \
    && docker-php-ext-enable redis \
    && docker-php-ext-enable exif \
    && docker-php-ext-enable opcache \
    && docker-php-ext-enable intl \
    && docker-php-ext-enable pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Set Working Directory
WORKDIR /srv/app

# 5. Copy Application Files
# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock ./
# Install vendor dependencies (production only)
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist --optimize-autoloader

# Copy the rest of the application code
COPY . .

RUN rm -rf /srv/app/storage/logs/*


# Copy built frontend assets from the frontend-builder stage
COPY --from=frontend-builder /app/frontend/public /srv/app/public

# 6. Permissions
# Set ownership to www-data (user that PHP-FPM runs as)
RUN chown -R www-data:www-data /srv/app/storage /srv/app/bootstrap/cache
RUN chmod -R 775 /srv/app/storage /srv/app/bootstrap/cache

# 7. Optimize Laravel for Production
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache
# If you use events, uncomment the line below
# RUN php artisan event:cache

# 8. Expose Port for PHP-FPM
EXPOSE 9000

# 9. Entrypoint/CMD
# Start PHP-FPM
CMD ["php-fpm"]
