FROM php:8.2-apache

# 1. Install ekstensi PHP & Node.js (untuk Vite/Tailwind)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    curl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath exif

# 2. Aktifkan URL Cantik Apache
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 3. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Pindahkan kodingan Mas Nur
WORKDIR /var/www/html
COPY . .

# 5. Install paket PHP (Laravel)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 6. Install paket Node.js dan Rakit Tampilan (Vite Build)
RUN npm install
RUN npm run build

# 7. Berikan Izin Akses Folder (Agar Livewire bisa jalan)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 80