FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    libgmp-dev \
    nginx \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip pdo_pgsql gmp

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first to leverage cache
COPY composer.json composer.lock ./

# Install project dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy package files for frontend build
COPY package.json package-lock.json ./

# Install node dependencies
RUN npm install

# Copy the rest of the application
COPY . .

# Build frontend assets
RUN npm run build

# Configure Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Nginx Configuration
RUN echo "server { \
    listen 80; \
    index index.php index.html; \
    server_name localhost; \
    root /var/www/html/public; \
    location / { \
    try_files \$uri \$uri/ /index.php?\$query_string; \
    } \
    location ~ \.php$ { \
    include fastcgi_params; \
    fastcgi_pass 127.0.0.1:9000; \
    fastcgi_index index.php; \
    fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name; \
    } \
    location ~ /\.(?!well-known).* { \
    deny all; \
    } \
    }" > /etc/nginx/sites-available/default

# Create startup script
COPY .deploy/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
