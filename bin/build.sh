#!/bin/bash

echo "🚀 Starting Railway build process..."

# Ensure PHP 8.2 is installed
echo "📋 PHP Version:"
php -v

# Setup environment using dedicated script
echo "🔧 Setting up environment..."
bash bin/setup-env.sh

# Install dependencies using PHP's built-in composer installer
echo "📦 Installing Composer and dependencies..."
export COMPOSER_ALLOW_SUPERUSER=1
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Prepare application for production
echo "⚙️ Optimizing application for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Build process completed successfully!"
exit 0 