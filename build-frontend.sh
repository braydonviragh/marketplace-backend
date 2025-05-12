#!/bin/bash

# Script to build frontend and copy to Laravel public directory root
# This script should be run from the marketplace-backend directory

echo "Building and integrating frontend into Laravel backend..."

# Check if frontend directory exists as a sibling to this directory
FRONTEND_DIR="../marketplace-frontend"
if [ ! -d "$FRONTEND_DIR" ]; then
  echo "Error: Frontend directory not found at $FRONTEND_DIR"
  exit 1
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
cp -R "$FRONTEND_DIR/dist/spa/"* "./public/"

echo "Frontend integration complete! Files copied to public directory" 