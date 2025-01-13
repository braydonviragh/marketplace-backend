# Rental Platform API

A Laravel-based API backend for a side project clothing rental marketplace platform. Users can post their clothes for rent and other users can view and rent them. Please import the JSON postman collection into postman to see Users, products, categories, sizes, rentals, and more. Especially note the detailed relationships that users and products have with categories, sizes, and rentals.

## Prerequisites

- PHP >= 8.1
- Composer
- MySQL/MariaDB
- XAMPP/MAMP for local development
- Postman for API testing

## Installation Steps

### 1. Environment Setup

Ensure you have PHP and Composer installed:

```bash
php -v
composer -v
```

### 2. Clone the Repository

```bash
git clone https://github.com/your-username/rental-platform-api.git
cd rental-platform-api

composer install    

```

### 3. Database Setup

1. Start XAMPP/MAMP and ensure MySQL service is running
2. Create a new database named `rental_platform`
3. Copy `.env.example` to `.env`:

```bash
cp .env.example .env

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rental_platform
DB_USERNAME=root
DB_PASSWORD=

```

### 4. Application Setup

1. Generate application key:

```bash
php artisan key:generate
```

2. Run database migrations and seeders:

```bash
php artisan migrate:fresh --seed
```

This will create and populate:
- Categories (Dresses, Tops, Handbags, etc.)
- Sizes (Letter: XS-XXL, Number: 00-22, Waist: 24"-48")
- Brands (Nike, Zara, H&M, etc.)
- Colors
- Sample Users (including admin user)
- Sample Products


### 5. API Testing Setup

1. Import the Postman collection:
   - Open Postman
   - Import `Rental_Platform_API.postman_collection.json`
   - Create a new environment and set:
     ```
     base_url: http://127.0.0.1/laravel/mp-backend/public
     ```
Adjust based on your MAMP/XAMPP setup local server path
 Authorization has been commented out for now 

### 6. Available API Endpoints

#### Users
- GET /api/v1/users
- GET /api/v1/users/{id}
- POST /api/v1/users
- PUT /api/v1/users/{id}
- DELETE /api/v1/users/{id}

#### Super Admins

#### Products
- GET /api/v1/products
- GET /api/v1/products/{id}
- POST /api/v1/products
- PUT /api/v1/products/{id}
- DELETE /api/v1/products/{id}

#### Categories
- GET /api/v1/categories
- GET /api/v1/categories/{id}

#### Sizes
- GET /api/v1/letter-sizes
- GET /api/v1/number-sizes
- GET /api/v1/waist-sizes

#### Rentals
- GET /api/v1/rentals
- POST /api/v1/rentals
- PUT /api/v1/rentals/{id}

### 7. Testing the API

Test User Endpoints:
1. Register a new user
2. View list of users
3. View a specific user's profile
4. Update the user's profile
5. Delete the user

Test Category Endpoints:
1. List all categories
2. View a specific category's details

Test Size Endpoints:
1. List all sizes (letter, number, waist)
2. View a specific size's details

Test Product Endpoints:
1. List all products
2. Create new products with different size types based on category
 - GET filters in progress (to filter by category, size, price, location)

Some API's will return error as they are in progress 

### Common Issues & Troubleshooting

1. Database Connection Issues:

```bash
# Verify database connection
php artisan db:show

# Clear cache if needed
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

2. Seeding Issues:

```bash
# Refresh autoloader
composer dump-autoload

# Retry seeding
php artisan db:seed
```

### Development Notes

- Products have different size types based on category:
  - Letter sizes (XS-XXL): Tops, Sweaters, Blazers
  - Number sizes (00-22): Dresses, Skirts, Suits
  - Waist sizes (24"-48"): Jeans, Pants, Shorts
  - No size: Accessories, Jewelry, Handbags

- Categories are pre-seeded with common clothing types
- Each product must belong to a category and have appropriate size type

### Next Steps

1. Implement authentication and authorization for 2FA Texting Authentication
2. Implement payment gateway for rental transactions
3. Implement notifications for rental requests and updates
4. Implement user reviews and ratings
5. Implement search and filtering options
6. Implement user dashboard for managing rentals and products
7. Implement admin dashboard for managing users, products, and rentals
8. Implement API documentation and testing
9. Implement error handling and logging
10. Implement API rate limiting and security best practices
