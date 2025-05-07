#!/bin/bash

# This script ensures the proper environment file setup for Railway deployment

# Display current directory and files for debugging
echo "🔍 Current directory: $(pwd)"
echo "📂 Checking for environment files..."

# Check if .env file exists in the Railway environment
if [ -f ".env" ]; then
    echo "✅ Found existing .env file"
    # Backup existing .env for safety
    cp .env .env.bak
    echo "💾 Created backup at .env.bak"
else
    echo "⚠️ No .env file found in deployment environment"
    
    # Check if we have an .env.example file
    if [ -f ".env.example" ]; then
        echo "🔄 Using .env.example as template"
        cp .env.example .env
        echo "✅ Created .env file from template"
    else
        echo "❌ ERROR: No .env.example file found!"
        echo "Please ensure your project includes an .env.example file"
        exit 1
    fi
fi

# Ensure APP_KEY is set
if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
    echo "🔑 Generating application key"
    php artisan key:generate --force
    echo "✅ App key generated"
else
    echo "✅ App key already set"
fi

# Set production environment variables
echo "⚙️ Setting production environment variables"
if grep -q "^APP_ENV=" .env; then
    sed -i.bak 's/^APP_ENV=.*/APP_ENV=production/' .env
    rm -f .env.bak
else
    echo "APP_ENV=production" >> .env
fi

if grep -q "^APP_DEBUG=" .env; then
    sed -i.bak 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env
    rm -f .env.bak
else
    echo "APP_DEBUG=false" >> .env
fi

# Show result
echo "📝 Environment file contents summary:"
grep "^APP_" .env | sort

echo "✅ Environment setup completed"
exit 0 