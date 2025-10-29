FROM php:8.2-fpm

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP nécessaires
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    zip \
    pdo \
    pdo_sqlite \
    pdo_mysql

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /app

# Copier uniquement composer.json et composer.lock pour le cache
COPY composer.json composer.lock ./

# Installer les dépendances PHP (sera caché si composer.json/lock n'ont pas changé)
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

# Copier le reste des fichiers
COPY . .

# Finaliser l'installation de composer avec les scripts
RUN composer install --no-interaction --optimize-autoloader

# Créer le fichier .env s'il n'existe pas
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Générer la clé d'application
RUN php artisan key:generate --force

# Créer les dossiers nécessaires et définir les permissions
RUN mkdir -p storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data /app

# Exposer le port
EXPOSE 9000

# Commande par défaut
CMD ["php-fpm"]