#!/bin/bash

# Script to build frontend and copy to Laravel public directory root
# This script should be run from the marketplace-backend directory

echo "Building and integrating frontend into Laravel backend..."

# If the frontend directory exists as a sibling to this directory, use it
# Otherwise, try to clone it from the repository if FRONTEND_REPO environment variable is set
FRONTEND_DIR="../marketplace-frontend"
if [ ! -d "$FRONTEND_DIR" ]; then
  echo "Frontend directory not found at $FRONTEND_DIR"
  
  # Check if we have a repository URL to clone
  if [ -n "$FRONTEND_REPO" ]; then
    echo "Cloning frontend repository from $FRONTEND_REPO"
    git clone "$FRONTEND_REPO" "$FRONTEND_DIR"
    if [ ! -d "$FRONTEND_DIR" ]; then
      echo "Error: Failed to clone frontend repository."
      exit 1
    fi
  else
    echo "Error: Frontend directory not found and FRONTEND_REPO environment variable not set."
    echo "Creating an empty frontend directory for Railway deployment."
    mkdir -p "$FRONTEND_DIR"
    # Create a minimal package.json
    cat > "$FRONTEND_DIR/package.json" << EOF
{
  "name": "marketplace-frontend",
  "version": "0.0.1",
  "private": true,
  "scripts": {
    "build": "mkdir -p dist/spa && echo '<html><body><h1>Marketplace Frontend</h1><p>Default placeholder page.</p></body></html>' > dist/spa/index.html"
  }
}
EOF
  fi
fi

# Navigate to frontend directory and build it
echo "Building frontend..."
cd "$FRONTEND_DIR"
npm install
npm run build

# Check if build was successful
if [ ! -d "dist/spa" ]; then
  echo "Error: Frontend build failed. dist/spa directory not found."
  exit 1
fi

# Copy the built frontend directly to Laravel public directory
echo "Copying frontend build to Laravel public directory root..."
cd - # Return to backend directory
mkdir -p "./public"
cp -R "$FRONTEND_DIR/dist/spa/"* "./public/"

# Set proper permissions on the public directory
echo "Setting proper permissions on public directory..."
chmod -R 755 "./public/"
if command -v chown &> /dev/null; then
  chown -R www-data:www-data "./public/" 2>/dev/null || true
fi

# Create a test file to verify public directory is writable
echo "<html><body>Test file - Verify directory is writable</body></html>" > "./public/test.html"

echo "Frontend integration complete! Files copied to public directory"
echo "Public directory files:"
ls -la "./public/" 
