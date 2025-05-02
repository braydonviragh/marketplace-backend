# Marketplace Backend - Railway Deployment

This document contains instructions on how to deploy the Laravel Marketplace Backend to Railway.

## Prerequisites

1. Make sure you have a Railway account (https://railway.app)
2. Install the Railway CLI (optional) if you prefer command-line deployment:
   ```
   npm i -g @railway/cli
   ```

## PHP Version

This project uses PHP 8.2. The configuration files (runtime.txt, nixpacks.toml, Procfile) have been set up to ensure Railway uses the correct PHP version.

## Deployment Steps

### Method 1: Using the Railway Dashboard (Recommended)

1. Go to [Railway Dashboard](https://railway.app/dashboard)
2. Click "New Project" and select "Deploy from GitHub repo"
3. Select the GitHub repository that contains your Laravel project
4. Railway will automatically detect the Laravel project and set up the build configuration
5. Once the build completes, you can access your API at the assigned domain

### Method 2: Using Railway CLI

1. Login to Railway:
   ```
   railway login
   ```

2. Link your project:
   ```
   railway link
   ```

3. Deploy your project:
   ```
   railway up
   ```

## Environment Variables

Make sure to set the following environment variables in your Railway project settings:

- `APP_KEY`: Your Laravel app key (generated with `php artisan key:generate --show`)
- `APP_ENV`: Set to `production`
- `APP_DEBUG`: Set to `false` for production
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Your database credentials
- `STRIPE_KEY`, `STRIPE_SECRET`: Your Stripe API credentials
- Other environment variables as needed for your specific application

## Database Setup

Railway will provision a database for you when you add the database plugin.

1. In your Railway project, click "New"
2. Select "Database" and choose the type (MySQL, PostgreSQL)
3. Railway will automatically add the database connection variables to your environment

## Post-Deployment Steps

After successful deployment, run the necessary Laravel commands:

1. Run migrations:
   ```
   railway run php artisan migrate
   ```

2. Seed the database (if needed):
   ```
   railway run php artisan db:seed
   ```

## Troubleshooting

- **Build Errors**: Check the build logs in the Railway dashboard for specific error messages
- **Application Errors**: Check the logs by running `railway logs`
- **PHP Version Issues**: Make sure your `runtime.txt` and `nixpacks.toml` files specify the correct PHP version (8.2)
- **Composer Issues**: Ensure your `composer.json` has compatible dependencies for PHP 8.2+

## Additional Resources

- [Railway Documentation](https://docs.railway.app/)
- [Laravel Deployment Best Practices](https://laravel.com/docs/9.x/deployment) 