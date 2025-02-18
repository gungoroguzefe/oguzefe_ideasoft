# PHP 8.1 ve Apache içeren resmi Docker imajını kullan
FROM php:8.1-apache

# Gerekli PHP eklentilerini ve kütüphanelerini yükle
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install zip pdo pdo_mysql mysqli

# Apache mod_rewrite'ı etkinleştir (CodeIgniter için gerekli)
RUN a2enmod rewrite

# Çalışma dizini
WORKDIR /var/www/html

# Projedeki tüm dosyaları içine kopyala
COPY . .

# Apache kullanıcısına dosya izinlerini ver
RUN chown -R www-data:www-data /var/www/html

# Apache'yi başlat
CMD ["apache2-foreground"]