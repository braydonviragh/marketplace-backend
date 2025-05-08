# Marketplace API Postman Collection

This repository includes a comprehensive Postman collection that documents all the available API endpoints in the Marketplace backend. The collection is split into multiple files to make it manageable.

## Files

1. **Rental_Platform_API.postman_collection.json** - The main Postman collection with the core API endpoints
2. **postman-collection-additions.json** - Supplementary collection with additional endpoints
3. **postman-collection-additions-part2.json** - More supplementary endpoints

## How to Use

1. Import all three files into Postman:
   - Open Postman
   - Click on "Import" button
   - Select "Files" tab and import all three JSON files

2. Set up your environment variables:
   - Create a new environment in Postman
   - Set the following variables:
     - `base_url`: Your API base URL (e.g., `http://localhost:8000` for local development or `https://marketplace-backend.up.railway.app` for production)
     - `token`: Your authentication token (will be populated automatically after login)

3. Use the collections to test the API endpoints:
   - Start with the Auth/Login endpoint to get a token
   - The token will be automatically set in the environment variables
   - Explore and test other endpoints

## Endpoints Overview

The collection includes the following categories of endpoints:

### Authentication

- Login/Register
- Email verification
- Phone verification
- Password reset
- Token refresh

### Users

- User profile management
- Onboarding
- User search and details

### Products

- Product listing and search
- Product creation and management
- Product attributes (brands, colors, sizes)

### Rentals & Offers

- Rental creation and management
- Offer submission and negotiation
- Rental confirmation

### Payments & Transactions

- Stripe integration
- Payment processing
- Transaction history
- Balance management

### Admin

- Super admin management
- User management
- System configuration

## Sample Requests and Responses

Each endpoint in the collection includes:

1. A properly formatted request with all required parameters
2. Sample success responses to show the expected data format
3. Sample error responses to help with debugging and error handling

## Adding to the Collection

To add more endpoints to the collection:

1. Export the collections from Postman
2. Add your new endpoints following the same format
3. Update this README if necessary

## Notes for Testing

- For phone verification in test mode, use `+14155552671` as the test phone number and `000000` as the verification code
- When testing Stripe functionality, make sure to use test mode credentials
- The test environment includes seed data that you can use for testing

## Webhook Testing

For webhook testing, you can use the Stripe webhook endpoint with properly formatted Stripe event data. In test mode, the webhook will process events without requiring a valid Stripe signature.

## Environment-Specific Settings

Some endpoints may behave differently based on the environment:

- **Development**: More detailed error messages and debug information
- **Production**: Streamlined responses with sensitive information hidden

Make sure to set the appropriate `base_url` in your Postman environment to match the environment you're testing against. 