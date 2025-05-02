#!/bin/bash

echo "🚂 Starting Railway deployment preparation..."

# Update PHP version in composer.json
echo "📦 Updating PHP version requirement in composer.json..."
sed -i.bak 's/"php": "\^8\.1"/"php": "^8.2"/g' composer.json
rm composer.json.bak

# Check for existing .env file or use .env.example as a fallback
echo "🔧 Checking environment files..."
if [ ! -f ".env" ]; then
    echo "No .env file found, copying from .env.example"
    cp .env.example .env
else
    echo "Using existing .env file"
fi

# Ensure production settings are applied
echo "🔧 Updating environment variables for production..."
if ! grep -q "^APP_ENV=production" .env; then
    # Add or update APP_ENV
    if grep -q "^APP_ENV=" .env; then
        sed -i.bak 's/^APP_ENV=.*/APP_ENV=production/' .env
        rm .env.bak
    else
        echo "APP_ENV=production" >> .env
    fi
fi

if ! grep -q "^APP_DEBUG=false" .env; then
    # Add or update APP_DEBUG
    if grep -q "^APP_DEBUG=" .env; then
        sed -i.bak 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env
        rm .env.bak
    else
        echo "APP_DEBUG=false" >> .env
    fi
fi

# Apply Laravel optimizations for production
echo "⚡ Optimizing Laravel for production..."
if which composer > /dev/null; then
    composer install --no-dev --optimize-autoloader
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan clear-compiled
else
    echo "❌ Composer not found, skipping Laravel optimizations."
fi

echo "✅ Deployment preparation completed!"
echo "ℹ️ To deploy to Railway, push your changes and execute 'railway up' if you're using the CLI."
echo "🌐 If you're using GitHub integration, your app will deploy automatically after push." 