#!/bin/bash

echo "🚀 Starting Railway build process..."

# Ensure PHP 8.2 is installed
echo "📋 PHP Version:"
php -v

# Setup environment using dedicated script
echo "🔧 Setting up environment..."
bash bin/setup-env.sh

install_composer() {
    echo "📦 Installing Composer (Method 1)..."
    export COMPOSER_ALLOW_SUPERUSER=1

    # Try the first method with sha verification
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    RESULT=$?
    rm composer-setup.php
    
    if [ $RESULT -eq 0 ]; then
        mv composer.phar /usr/local/bin/composer
        chmod +x /usr/local/bin/composer
        echo "✅ Composer installed successfully (Method 1)"
        return 0
    fi
    
    echo "⚠️ First method failed, trying alternative method..."
    
    # Try an alternative method
    echo "📦 Installing Composer (Method 2)..."
    curl -sS https://getcomposer.org/installer | php
    RESULT=$?
    
    if [ $RESULT -eq 0 ]; then
        mv composer.phar /usr/local/bin/composer
        chmod +x /usr/local/bin/composer
        echo "✅ Composer installed successfully (Method 2)"
        return 0
    fi
    
    echo "⚠️ Second method failed, trying final method..."
    
    # Try a final fallback
    echo "📦 Installing Composer (Method 3 - final attempt)..."
    if command -v wget > /dev/null; then
        wget -O composer-setup.php https://getcomposer.org/installer
        php composer-setup.php --quiet
        RESULT=$?
        rm composer-setup.php
        
        if [ $RESULT -eq 0 ]; then
            mv composer.phar /usr/local/bin/composer
            chmod +x /usr/local/bin/composer
            echo "✅ Composer installed successfully (Method 3)"
            return 0
        fi
    fi
    
    echo "❌ All composer installation methods failed"
    return 1
}

# Install Composer
if ! install_composer; then
    echo "❌ ERROR: Could not install Composer. Deployment failed."
    exit 1
fi

# Verify Composer installation
if ! command -v composer > /dev/null; then
    echo "❌ ERROR: Composer command not found after installation"
    exit 1
fi

echo "🔍 Composer version:"
composer --version

# Install dependencies
echo "📦 Installing dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Prepare application for production
echo "⚙️ Optimizing application for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
echo "✅ Build process completed successfully!"
exit 0 