#!/bin/bash

# Script for running seeders on Railway with monitoring and timeouts

echo "Starting database seeding process..."

# Run the main database seeder first
echo "Running main database seeder..."
php artisan db:seed --force
MAIN_SEEDER_STATUS=$?

if [ $MAIN_SEEDER_STATUS -ne 0 ]; then
    echo "Main database seeder failed with status $MAIN_SEEDER_STATUS!"
    echo "Trying to continue anyway..."
fi

# Run specific seeders with a timeout to ensure they complete
echo "Running UserSeeder..."
timeout 300 php artisan db:seed --class=UserSeeder --force
USER_SEEDER_STATUS=$?

if [ $USER_SEEDER_STATUS -eq 124 ]; then
    echo "UserSeeder was terminated due to timeout!"
elif [ $USER_SEEDER_STATUS -ne 0 ]; then
    echo "UserSeeder failed with status $USER_SEEDER_STATUS!"
else
    echo "UserSeeder completed successfully!"
fi

echo "Running ProductSeeder..."
timeout 300 php artisan db:seed --class=ProductSeeder --force
PRODUCT_SEEDER_STATUS=$?

if [ $PRODUCT_SEEDER_STATUS -eq 124 ]; then
    echo "ProductSeeder was terminated due to timeout!"
elif [ $PRODUCT_SEEDER_STATUS -ne 0 ]; then
    echo "ProductSeeder failed with status $PRODUCT_SEEDER_STATUS!"
else
    echo "ProductSeeder completed successfully!"
fi

# Add other specific seeders here if needed

echo "Database seeding process completed."

# Return success even if some seeders failed to avoid deployment failure
exit 0 