#!/bin/bash
set -e

# Build the Docker image
echo "Building Docker image for testing..."
docker build -t marketplace-backend-test .

# Run the container in detached mode with debug options
echo "Running container with debug mode..."
docker run -d \
  --name marketplace-debug \
  -e APP_ENV=local \
  -e APP_DEBUG=true \
  -e LOG_LEVEL=debug \
  -e RUN_MIGRATIONS=false \
  -e DB_CONNECTION=sqlite \
  -e DB_DATABASE=":memory:" \
  -p 8080:8080 \
  marketplace-backend-test

echo "Container started, waiting for 10 seconds to initialize..."
sleep 10

# Check if container is still running
if docker ps | grep -q marketplace-debug; then
  echo "✅ Container successfully started and is running"
  echo "Application should be available at http://localhost:8080"
  echo "Access the health check at http://localhost:8080/api/v1/health"
else
  echo "❌ Container failed to start or crashed. Retrieving logs..."
  
  # Get the container logs
  docker logs marketplace-debug > container_logs.txt
  
  echo "Logs saved to container_logs.txt"
  
  # Check if the container exists but is stopped
  if docker ps -a | grep -q marketplace-debug; then
    echo "Container is in stopped state, attempting to inspect..."
    docker inspect marketplace-debug > container_inspect.txt
    echo "Container inspection details saved to container_inspect.txt"
  fi
fi

echo "If you need to execute commands inside the container, run:"
echo "docker exec -it marketplace-debug bash"

echo "To stop and remove the container:"
echo "docker stop marketplace-debug && docker rm marketplace-debug" 