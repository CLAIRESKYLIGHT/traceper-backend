# Railway Deployment Guide

This guide will help you deploy your Laravel application and PostgreSQL database to Railway.

## Step 1: Deploy PostgreSQL Database on Railway

### Option A: Using Railway Dashboard (Recommended)

1. **Sign up/Login to Railway**
   - Go to [railway.app](https://railway.app/)
   - Sign up or log in to your account

2. **Create a New Project**
   - Click on "New Project"
   - Give your project a name (e.g., "traceper-laravel")

3. **Add PostgreSQL Database**
   - Click on "New" or "+" button
   - Select "Database"
   - Choose "PostgreSQL"
   - Click "Add" to provision the database

4. **Get Database Connection Details**
   - Railway automatically creates a `DATABASE_URL` environment variable
   - Click on the PostgreSQL service
   - Go to the "Variables" tab
   - You'll see `DATABASE_URL` which contains all connection details
   - Copy this value (you'll need it later)

### Option B: Using Railway CLI

```bash
# Install Railway CLI (if not already installed)
npm i -g @railway/cli

# Login to Railway
railway login

# Initialize Railway project
railway init

# Add PostgreSQL database
railway add postgresql

# Get the DATABASE_URL
railway variables
```

## Step 2: Configure Environment Variables

After your database is deployed, you need to set up environment variables for your application:

### Required Environment Variables:

1. **Database Connection**
   - `DATABASE_URL` - Automatically set by Railway (already configured)
   - `DB_CONNECTION=pgsql` - Set to use PostgreSQL

2. **Application Settings**
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_KEY` - Generate with: `php artisan key:generate --show`
   - `APP_URL` - Will be set automatically by Railway

3. **Other Settings**
   - `CACHE_DRIVER=file`
   - `SESSION_DRIVER=database`
   - `QUEUE_CONNECTION=sync`

### Setting Variables in Railway Dashboard:

1. Go to your project
2. Click on your web service (or create one)
3. Go to "Variables" tab
4. Add each environment variable

## Step 3: Deploy Your Application

### Option A: Deploy via GitHub (Recommended)

1. **Connect GitHub Repository**
   - In Railway dashboard, click "New" → "GitHub Repo"
   - Select your repository
   - Railway will automatically detect it's a Laravel app

2. **Configure Build Settings**
   - Railway will use the `Dockerfile` in your repository
   - Make sure your Dockerfile is in the root directory

3. **Set Environment Variables**
   - Add all required environment variables (see Step 2)
   - Railway will automatically inject `DATABASE_URL` from your PostgreSQL service

4. **Deploy**
   - Railway will automatically build and deploy your application
   - Migrations will run automatically via `docker-entrypoint.sh`

### Option B: Deploy via Railway CLI

```bash
# Make sure you're in the project directory
cd /path/to/traceper-laravel

# Link to Railway project
railway link

# Set environment variables
railway variables set APP_ENV=production
railway variables set APP_DEBUG=false
railway variables set DB_CONNECTION=pgsql
railway variables set CACHE_DRIVER=file
railway variables set SESSION_DRIVER=database
railway variables set QUEUE_CONNECTION=sync

# Generate and set APP_KEY
railway run php artisan key:generate --show
# Copy the key and set it:
railway variables set APP_KEY=your-generated-key-here

# Deploy
railway up
```

## Step 4: Run Database Migrations

Migrations should run automatically on deployment via the `docker-entrypoint.sh` script. However, you can also run them manually:

### Via Railway Dashboard:
1. Go to your web service
2. Click on "Deployments"
3. Click on the latest deployment
4. Open the "Shell" tab
5. Run: `php artisan migrate --force`

### Via Railway CLI:
```bash
railway run php artisan migrate --force
```

## Step 5: Seed Database (Optional)

If you have seeders, run them:

```bash
railway run php artisan db:seed --force
```

## Troubleshooting

### Database Connection Issues

1. **Check DATABASE_URL is set**
   - Verify `DATABASE_URL` is available in your web service variables
   - Railway automatically shares variables between services in the same project

2. **Verify Database is Running**
   - Check the PostgreSQL service status in Railway dashboard
   - Ensure it shows "Active" status

3. **Check Connection String Format**
   - Railway's `DATABASE_URL` format: `postgresql://user:password@host:port/database`
   - Laravel automatically parses this format

### Migration Issues

1. **Check Logs**
   - View deployment logs in Railway dashboard
   - Look for migration errors

2. **Run Migrations Manually**
   ```bash
   railway run php artisan migrate --force
   ```

### Application Not Starting

1. **Check APP_KEY**
   - Ensure `APP_KEY` is set
   - Generate a new one if needed: `php artisan key:generate --show`

2. **Check Logs**
   - View application logs in Railway dashboard
   - Check for PHP errors or configuration issues

## Next Steps

After successful deployment:

1. **Set up Custom Domain** (Optional)
   - Go to your service settings
   - Add a custom domain

2. **Set up Monitoring**
   - Railway provides built-in monitoring
   - Check the "Metrics" tab for performance data

3. **Configure Backups**
   - Railway PostgreSQL includes automatic backups
   - Check backup settings in the database service

## Useful Railway Commands

```bash
# View logs
railway logs

# Open shell in deployed environment
railway shell

# View variables
railway variables

# Run artisan commands
railway run php artisan [command]

# View service status
railway status
```

## Notes

- Railway automatically provides a `DATABASE_URL` environment variable when you add a PostgreSQL database
- The application is configured to use this `DATABASE_URL` automatically
- Migrations run automatically on each deployment
- Make sure your `APP_KEY` is set before deploying
- Railway provides free tier with generous limits for testing

