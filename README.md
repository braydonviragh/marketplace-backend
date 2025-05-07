# Spindle - Clothing Rental Marketplace (Backend API)

A modern, full-featured Laravel-based API backend for a clothing rental marketplace platform. Spindle allows users to list their clothing items for rent and other users to discover and rent them with a seamless payment flow via Stripe integration.

## Project Overview

Spindle is a peer-to-peer clothing rental platform built to make fashion more sustainable and accessible. The backend provides a comprehensive API that powers the Vue/Quasar frontend application with features including:

- Secure user authentication with email and phone verification (tbd)
- Product management with detailed categorization and filtering
- Rental and offer negotiation system
- Integrated Stripe payment processing (tbd)
- User reviews and ratings (tbd)
- Geolocation-based product search
- Admin dashboard for platform management (tbd)

## Key Features

- **User Accounts**: People can sign up, verify their identity, and manage their profiles
- **Clothing Listings**: Users can photograph and list their clothing items with details like size, brand, and rental price
- **Smart Search**: Renters can find exactly what they need with filters for size, style, location, and more
- **Rental Process**: A complete flow from inquiries to bookings with date management
- **Secure Payments**: Integrated payment processing that protects both the renter and owner
- **Seller Tools**: Track earnings, manage rentals, and see performance statistics
- **Location Features**: Find items available near you with distance filtering

## Tech Stack

- **Framework**: Laravel 9+
- **PHP Version**: 8.1+
- **Database**: MySQL
- **Authentication**: Laravel Sanctum with custom phone verification
- **Payment Processing**: Stripe API integration
- **Image Storage**: AWS S3 compatible storage
- **Deployment**: Docker support with Railway configuration

## Prerequisites

- PHP 8.1+
- Composer
- MySQL/MariaDB
- Node.js and NPM (for frontend)
- Stripe account (for payment processing)
- Twilio account (for SMS verification)

## Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/braydonviragh/marketplace-backend.git
cd marketplace-backend
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

```bash
cp .env.example .env
```

Then edit `.env` file with your database credentials and other settings:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=marketplace_db
DB_USERNAME=root
DB_PASSWORD=your_password

# Stripe API Configuration (Test mode)
STRIPE_KEY=pk_test_your_key
STRIPE_SECRET=sk_test_your_secret
STRIPE_TEST_MODE=true

# Twilio Configuration
TWILIO_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_twilio_auth_token
TWILIO_VERIFY_SID=your_verify_sid
TWILIO_TEST_MODE=true

# CORS Settings
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:9000
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:9000
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Database Migrations and Seed Data

```bash
php artisan migrate --seed
```

This will create and populate:
- User roles and permissions
- Product categories (Dresses, Tops, Handbags, etc.)
- Size types (Letter: XS-XXL, Number: 00-22, Waist: 24"-48")
- Popular clothing brands
- Colors and styles
- Sample users (including admin)
- Sample products with images

### 6. Start the Development Server

```bash
php artisan serve
```

Your API will be available at `http://127.0.0.1:8000`

## Example API Requests

Here are some common requests you can make using the included Postman collection:

### 1. View All Products

```http
GET /api/v1/products?per_page=15
Accept: application/json
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "title": "Blue Levi's Denim Jacket",
            "description": "Vintage style denim jacket, perfect condition",
            "price": "25.00",
            "brand": {
                "id": 1,
                "name": "Levi's",
                "slug": "levis"
            },
            "category": {
                "id": 1,
                "name": "Jackets",
                "slug": "jackets"
            },
            "size": {
                "type": "letter",
                "id": 3,
                "name": "M",
                "slug": "m"
            },
            "owner": {
                "id": 1,
                "username": "johndoe"
            },
            "images": [
                {
                    "id": 1,
                    "url": "https://storage.example.com/images/product1_1.jpg",
                    "order": 1
                }
            ],
            "is_available": true,
            "created_at": "2024-03-21T12:00:00.000000Z"
        }
    ],
    "links": {
        "first": "http://127.0.0.1:8000/api/v1/products?page=1",
        "last": "http://127.0.0.1:8000/api/v1/products?page=5",
        "prev": null,
        "next": "http://127.0.0.1:8000/api/v1/products?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "path": "http://127.0.0.1:8000/api/v1/products",
        "per_page": 15,
        "to": 15,
        "total": 75
    }
}
```

### 2. View All Categories

```http
GET /api/v1/categories
Accept: application/json
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "name": "Jackets",
            "slug": "jackets",
            "description": "Jackets and outerwear"
        },
        {
            "id": 2,
            "name": "Dresses",
            "slug": "dresses",
            "description": "All types of dresses"
        },
        {
            "id": 3,
            "name": "Tops",
            "slug": "tops",
            "description": "T-shirts, blouses, and tops"
        }
    ]
}
```

### 3. View All Sizes

```http
GET /api/v1/sizes/letter
Accept: application/json
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "name": "XS",
            "slug": "xs"
        },
        {
            "id": 2,
            "name": "S",
            "slug": "s"
        },
        {
            "id": 3,
            "name": "M",
            "slug": "m"
        },
        {
            "id": 4,
            "name": "L",
            "slug": "l"
        },
        {
            "id": 5,
            "name": "XL",
            "slug": "xl"
        }
    ]
}
```

### 4. View All Brands

```http
GET /api/v1/brands
Accept: application/json
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "name": "Nike",
            "slug": "nike"
        },
        {
            "id": 2,
            "name": "Adidas",
            "slug": "adidas"
        },
        {
            "id": 3,
            "name": "Levi's",
            "slug": "levis"
        }
    ]
}
```

### 5. View a Specific Product

```http
GET /api/v1/products/1
Accept: application/json
```

Response:
```json
{
    "data": {
        "id": 1,
        "title": "Blue Levi's Denim Jacket",
        "description": "Vintage style denim jacket, perfect condition",
        "price": "25.00",
        "brand": {
            "id": 3,
            "name": "Levi's",
            "slug": "levis"
        },
        "category": {
            "id": 1,
            "name": "Jackets",
            "slug": "jackets"
        },
        "size": {
            "type": "letter",
            "id": 3,
            "name": "M",
            "slug": "m"
        },
        "color": {
            "id": 1,
            "name": "Blue",
            "hex_code": "0000FF"
        },
        "owner": {
            "id": 1,
            "username": "johndoe",
            "name": "John Doe"
        },
        "images": [
            {
                "id": 1,
                "url": "https://storage.example.com/images/product1_1.jpg",
                "order": 1
            },
            {
                "id": 2,
                "url": "https://storage.example.com/images/product1_2.jpg",
                "order": 2
            }
        ],
        "location": {
            "city": "Toronto",
            "province": "Ontario",
            "postal_code": "M5V 2T6"
        },
        "is_available": true,
        "created_at": "2024-03-21T12:00:00.000000Z",
        "updated_at": "2024-03-21T12:00:00.000000Z"
    }
}
```

### 6. Filter Products by Category

```http
GET /api/v1/products?category_id=1
Accept: application/json
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "title": "Blue Levi's Denim Jacket",
            "description": "Vintage style denim jacket, perfect condition",
            "price": "25.00",
            "category": {
                "id": 1,
                "name": "Jackets",
                "slug": "jackets"
            },
            "images": [
                {
                    "id": 1,
                    "url": "https://storage.example.com/images/product1_1.jpg",
                    "order": 1
                }
            ],
            "is_available": true
        },
        {
            "id": 5,
            "title": "Leather Bomber Jacket",
            "description": "Classic leather bomber jacket",
            "price": "45.00",
            "category": {
                "id": 1,
                "name": "Jackets",
                "slug": "jackets"
            },
            "images": [
                {
                    "id": 10,
                    "url": "https://storage.example.com/images/product5_1.jpg",
                    "order": 1
                }
            ],
            "is_available": true
        }
    ],
    "links": {
        "first": "http://127.0.0.1:8000/api/v1/products?category_id=1&page=1",
        "last": "http://127.0.0.1:8000/api/v1/products?category_id=1&page=2",
        "prev": null,
        "next": "http://127.0.0.1:8000/api/v1/products?category_id=1&page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 2,
        "path": "http://127.0.0.1:8000/api/v1/products",
        "per_page": 15,
        "to": 15,
        "total": 25
    }
}
```

### 7. Filter Products by Brand

```http
GET /api/v1/products?brand_id=3
Accept: application/json
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "title": "Blue Levi's Denim Jacket",
            "description": "Vintage style denim jacket, perfect condition",
            "price": "25.00",
            "brand": {
                "id": 3,
                "name": "Levi's",
                "slug": "levis"
            },
            "images": [
                {
                    "id": 1,
                    "url": "https://storage.example.com/images/product1_1.jpg",
                    "order": 1
                }
            ],
            "is_available": true
        }
    ],
    "links": {
        "first": "http://127.0.0.1:8000/api/v1/products?brand_id=3&page=1",
        "last": "http://127.0.0.1:8000/api/v1/products?brand_id=3&page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "http://127.0.0.1:8000/api/v1/products",
        "per_page": 15,
        "to": 3,
        "total": 3
    }
}
```

### 8. Filter Products by Size

```http
GET /api/v1/products?letter_size_id=3
Accept: application/json
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "title": "Blue Levi's Denim Jacket",
            "description": "Vintage style denim jacket, perfect condition",
            "price": "25.00",
            "size": {
                "type": "letter",
                "id": 3,
                "name": "M",
                "slug": "m"
            },
            "images": [
                {
                    "id": 1,
                    "url": "https://storage.example.com/images/product1_1.jpg",
                    "order": 1
                }
            ],
            "is_available": true
        }
    ]
}
```

### 9. Filter Products by Style

```http
GET /api/v1/products?style_id=1
Accept: application/json
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "title": "Blue Levi's Denim Jacket",
            "description": "Vintage style denim jacket, perfect condition",
            "price": "25.00",
            "style": {
                "id": 1,
                "name": "Men's",
                "slug": "mens"
            },
            "images": [
                {
                    "id": 1,
                    "url": "https://storage.example.com/images/product1_1.jpg",
                    "order": 1
                }
            ],
            "is_available": true
        }
    ]
}
```

### 10. Advanced Product Filtering

```http
GET /api/v1/products?category_id=1&brand_id=3&letter_size_id=3&price_min=20&price_max=50&color_id=1&city=Toronto&province=Ontario&is_available=true&sort_by=price_asc&per_page=15
Accept: application/json
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "title": "Blue Levi's Denim Jacket",
            "description": "Vintage style denim jacket, perfect condition",
            "price": "25.00",
            "brand": {
                "id": 3,
                "name": "Levi's",
                "slug": "levis"
            },
            "category": {
                "id": 1,
                "name": "Jackets",
                "slug": "jackets"
            },
            "size": {
                "type": "letter",
                "id": 3,
                "name": "M",
                "slug": "m"
            },
            "color": {
                "id": 1,
                "name": "Blue",
                "hex_code": "0000FF"
            },
            "location": {
                "city": "Toronto",
                "province": "Ontario"
            },
            "images": [
                {
                    "id": 1,
                    "url": "https://storage.example.com/images/product1_1.jpg",
                    "order": 1
                }
            ],
            "is_available": true
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "http://127.0.0.1:8000/api/v1/products",
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}
```

### 11. Search Products

```http
GET /api/v1/products?search=denim+jacket
Accept: application/json
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "title": "Blue Levi's Denim Jacket",
            "description": "Vintage style denim jacket, perfect condition",
            "price": "25.00",
            "images": [
                {
                    "id": 1,
                    "url": "https://storage.example.com/images/product1_1.jpg",
                    "order": 1
                }
            ],
            "is_available": true
        }
    ]
}
```

### 12. View a User

```http
GET /api/v1/users/1
Accept: application/json
Authorization: Bearer YOUR_TOKEN
```

Response:
```json
{
    "data": {
        "id": 1,
        "username": "johndoe",
        "name": "John Doe",
        "email": "john@example.com",
        "phone_number": "+14155552671",
        "phone_verified_at": "2024-03-21T12:00:00.000000Z",
        "email_verified_at": "2024-03-21T12:00:00.000000Z",
        "profile": {
            "style": {
                "id": 1,
                "name": "Men's",
                "slug": "mens"
            },
            "favorite_brands": [
                {
                    "id": 1,
                    "name": "Nike"
                },
                {
                    "id": 3,
                    "name": "Levi's"
                }
            ]
        },
        "created_at": "2024-03-21T12:00:00.000000Z"
    }
}
```

### 13. Create a User (Register)

```http
POST /api/v1/auth/register
Content-Type: application/json

{
    "phone_number": "+14155552671",
    "email": "user@example.com",
    "password": "password123",
    "terms_accepted": true
}
```

Response:
```json
{
    "status": "success",
    "message": "Registration successful. Please verify your email.",
    "data": {
        "user": {
            "id": 1,
            "email": "user@example.com",
            "phone_number": "+14155552671",
            "terms_accepted": true,
            "terms_accepted_at": "2024-03-21T12:00:00.000000Z",
            "created_at": "2024-03-21T12:00:00.000000Z",
            "updated_at": "2024-03-21T12:00:00.000000Z"
        },
        "access_token": "1|example_token_string",
        "token_type": "Bearer"
    }
}
```

### 14. Create a Product

```http
POST /api/v1/products
Content-Type: multipart/form-data
Authorization: Bearer YOUR_TOKEN

title: Blue Levi's Denim Jacket
description: Vintage style denim jacket, perfect condition
category_id: 1
brand_id: 3
style_id: 1
color_id: 1
price: 25.00
size_type: letter
size_id: 3
city: Toronto
province: Ontario
postal_code: M5V 2T6
images[0][image]: (file upload)
images[0][order]: 1
images[1][image]: (file upload)
images[1][order]: 2
```

Response:
```json
{
    "status": "success",
    "message": "Product created successfully",
    "data": {
        "id": 1,
        "title": "Blue Levi's Denim Jacket",
        "description": "Vintage style denim jacket, perfect condition",
        "price": "25.00",
        "user_id": 1,
        "category_id": 1,
        "brand_id": 3,
        "style_id": 1,
        "color_id": 1,
        "city": "Toronto",
        "province": "Ontario",
        "postal_code": "M5V 2T6",
        "created_at": "2024-03-21T12:00:00.000000Z",
        "updated_at": "2024-03-21T12:00:00.000000Z",
        "images": [
            {
                "id": 1,
                "url": "https://storage.example.com/images/product1_1.jpg",
                "order": 1
            },
            {
                "id": 2,
                "url": "https://storage.example.com/images/product1_2.jpg",
                "order": 2
            }
        ]
    }
}
```

## API Documentation

The API is documented using a comprehensive Postman collection. Import the `Rental_Platform_API.postman_collection.json` file into Postman to explore and test all endpoints.

The main API groups include:

### Authentication
- Registration with email and phone verification
- Login/logout
- Password reset
- Token refresh

### Products
- List, filter, and search products
- Create, update, and delete product listings
- Manage product images
- Categories, sizes, brands, and colors

### Rentals & Offers
- Create and manage rental offers
- Accept/reject rental requests
- Confirm rentals
- Rental history and status tracking

### Payments
- Connect Stripe accounts
- Process payments for rentals
- Manage payment methods
- Balance and transaction history
- Withdrawals to bank accounts

### Users
- Profile management

## Deployment

The project includes Docker configuration files and Railway deployment scripts for easy deployment.

### Docker Deployment

```bash
docker-compose up -d
```

### Railway Deployment

```bash
railway up
```

## Frontend Integration

See the [Spindle Frontend Repository](https://github.com/braydonviragh/marketplace-frontend) for instructions on setting up the Vue/Quasar frontend application.

