# Deployment Guide: Render + Railway

This guide will help you deploy your Laravel application to Render with a Railway PostgreSQL database.

## Prerequisites

1. GitHub account with your code pushed to a repository
2. Render account (sign up at https://render.com)
3. Railway account (sign up at https://railway.app)

## Step 1: Set up Railway Database

1. Go to [Railway Dashboard](https://railway.app/dashboard)
2. Click "New Project"
3. Click "New" → "Database" → "Add PostgreSQL"
4. Wait for the database to be provisioned
5. Click on the PostgreSQL service
6. Go to the "Variables" tab
7. Copy the following connection details:
   - `PGHOST` (host)
   - `PGPORT` (port, usually 5432)
   - `PGDATABASE` (database name)
   - `PGUSER` (username)
   - `PGPASSWORD` (password)

## Step 2: Deploy to Render

1. Go to [Render Dashboard](https://dashboard.render.com)
2. Click "New" → "Web Service"
3. Connect your GitHub repository
4. Configure the service:
   - **Name**: traceper-laravel (or your preferred name)
   - **Environment**: PHP
   - **Build Command**: 
     ```bash
     composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan storage:link
     ```
   - **Start Command**: 
     ```bash
     php artisan serve --host=0.0.0.0 --port=$PORT
     ```
   - **Plan**: Choose Free or Starter (Free has limitations)

5. Add Environment Variables:
   - `APP_ENV` = `production`
   - `APP_DEBUG` = `false`
   - `APP_KEY` = (Generate with: `php artisan key:generate --show`)
   - `APP_URL` = `https://your-app-name.onrender.com` (update after first deploy)
   - `DB_CONNECTION` = `pgsql`
   - `DB_HOST` = (from Railway, e.g., `containers-us-west-xxx.railway.app`)
   - `DB_PORT` = `5432`
   - `DB_DATABASE` = (from Railway)
   - `DB_USERNAME` = (from Railway, usually `postgres`)
   - `DB_PASSWORD` = (from Railway)
   - `LOG_LEVEL` = `error`
   - `CACHE_DRIVER` = `file`
   - `SESSION_DRIVER` = `database`
   - `QUEUE_CONNECTION` = `sync`

6. Click "Create Web Service"

## Step 3: Run Migrations

After the first deployment, you need to run migrations:

1. In Render dashboard, go to your service
2. Click on "Shell" tab
3. Run:
   ```bash
   php artisan migrate --force
   ```

4. (Optional) If you have seeders, run:
   ```bash
   php artisan db:seed --force
   ```

## Step 4: Update APP_URL

1. After deployment, Render will provide a URL like `https://traceper-laravel.onrender.com`
2. Go to your service settings
3. Update the `APP_URL` environment variable to match your Render URL

## Step 5: Verify Deployment

1. Visit your Render URL
2. Check if the application loads
3. Test API endpoints if applicable
4. Check logs in Render dashboard if there are issues

## Troubleshooting

### Database Connection Issues
- Verify all Railway database credentials are correct
- Check if Railway database is running
- Ensure `DB_CONNECTION=pgsql` is set

### Build Failures
- Check build logs in Render dashboard
- Ensure `composer.json` and `package.json` are committed
- Verify Node.js version compatibility

### Storage Issues
- The `storage:link` command should run automatically
- If files aren't accessible, manually run: `php artisan storage:link` in Render shell

### Migration Issues
- Run migrations manually in Render shell
- Check database permissions in Railway

## Environment Variables Reference

Required variables for production:
```
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:... (generate with php artisan key:generate)
APP_URL=https://your-app.onrender.com
DB_CONNECTION=pgsql
DB_HOST=your-railway-host.railway.app
DB_PORT=5432
DB_DATABASE=railway
DB_USERNAME=postgres
DB_PASSWORD=your-password
```

## Notes

- Render free tier spins down after 15 minutes of inactivity
- Railway free tier has usage limits
- Consider upgrading for production use
- Always keep your `.env` file secure and never commit it

