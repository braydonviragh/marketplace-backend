#!/bin/bash

# Change to the application directory
cd "$(dirname "$0")"

echo "🔄 Running composer dump-autoload..."

# Run composer dump-autoload with optimization
if composer dump-autoload -o; then
    echo "✅ Autoload files have been regenerated successfully!"
else
    echo "❌ Failed to regenerate autoload files!"
    exit 1
fi

# If we're in a git repository, we can optionally add the updated files
if [ -d ".git" ] && [ "$1" == "--git-add" ]; then
    echo "📦 Adding generated files to git..."
    git add vendor/composer/autoload*.php
fi

exit 0 