#!/bin/bash
set -e

echo "======= Starting Docker build test ======="

# Build the Docker image
echo "Building Docker image..."
docker build -t marketplace-backend-test .

# Check if the build was successful
if [ $? -eq 0 ]; then
    echo "✅ Docker build successful"
else
    echo "❌ Docker build failed"
    exit 1
fi

# Run the container with required environment variables
echo "Running container for test..."
docker run --rm -p 8080:8080 \
    -e APP_ENV=production \
    -e APP_DEBUG=true \
    -e LOG_LEVEL=debug \
    -e RUN_MIGRATIONS=false \
    -e DB_CONNECTION=mysql \
    -e DB_HOST=host.docker.internal \
    -e DB_PORT=3306 \
    -e DB_DATABASE=your_database \
    -e DB_USERNAME=your_username \
    -e DB_PASSWORD=your_password \
    --name marketplace-test \
    marketplace-backend-test

# Note: The container will stay running. To stop it, use:
# docker stop marketplace-test 