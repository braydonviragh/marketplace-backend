#!/bin/bash

# Railway Deployment Script
echo "Preparing to deploy to Railway..."

# 1. Make sure we're using production settings
cp .env.example .env
sed -i 's/APP_ENV=local/APP_ENV=production/g' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/g' .env

# 2. Optimize for production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Deploy to Railway
echo "Deploying to Railway..."
railway up

# 4. Generate deployment URL
echo "Generating deployment URL..."
railway link
railway domain

echo "Deployment complete!" 