# Deployment Guide: Render

This guide will help you deploy your Laravel application to Render with a PostgreSQL database.

## Prerequisites

1. GitHub account with your code pushed to a repository
2. Render account (sign up at https://render.com)

## Step 1: Set up Database

You can use either Render's PostgreSQL database (recommended - simpler) or Railway's database.

### Option A: Render PostgreSQL Database (Recommended)

1. Go to [Render Dashboard](https://dashboard.render.com)
2. Click "New" → "PostgreSQL"
3. Configure the database:
   - **Name**: traceper-db (or your preferred name)
   - **Database**: Leave as default or choose a name
   - **User**: Leave as default or choose a username
   - **Region**: Choose closest to your web service
   - **Plan**: Choose Free or Starter (Free has limitations)
4. Click "Create Database"
5. Wait for the database to be provisioned
6. Once created, you'll see the connection details:
   - **Internal Database URL** (for services in same region)
   - **External Database URL** (for external connections)
   - Individual connection fields are also available

**Note**: Render provides connection details automatically. You can use the `DATABASE_URL` environment variable or individual `DB_*` variables.

### Option B: Railway PostgreSQL Database

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
   - **Environment**: 
     - **First choice**: Look for **"PHP"** in the dropdown
     - **If PHP not available**: Choose **"Docker"** 
     - **If neither available**: Choose **"Other"** or **"Custom"** and configure manually
     - **❌ DO NOT choose "Node"** - This is a PHP application, Node is only used for building assets
   - **Build Command** (if PHP is selected): 
     ```bash
     composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan storage:link
     ```
   - **Start Command** (if PHP is selected): 
     ```bash
     php artisan serve --host=0.0.0.0 --port=$PORT
     ```
   - **Plan**: Choose Free or Starter (Free has limitations)

**Important Notes:**
- If you see **"PHP"** as an option, select it - this is the correct choice
- If you see **"Docker"**, select it and Render should auto-detect the `render.yaml` file
- **Never select "Node"** - Node.js is only used during the build process to compile frontend assets (Vite), but the application itself runs on PHP
- The `render.yaml` file will help auto-configure if Render supports it

5. Add Environment Variables:

   **If using Render Database (Option A):**
   - `APP_ENV` = `production`
   - `APP_DEBUG` = `false`
   - `APP_KEY` = (Generate with: `php artisan key:generate --show`)
   - `APP_URL` = `https://your-app-name.onrender.com` (update after first deploy)
   - `DATABASE_URL` = (Copy from Render database dashboard - Internal Database URL)
   - OR use individual variables:
     - `DB_CONNECTION` = `pgsql`
     - `DB_HOST` = (from Render database)
     - `DB_PORT` = `5432`
     - `DB_DATABASE` = (from Render database)
     - `DB_USERNAME` = (from Render database)
     - `DB_PASSWORD` = (from Render database)
   - `LOG_LEVEL` = `error`
   - `CACHE_DRIVER` = `file`
   - `SESSION_DRIVER` = `database`
   - `QUEUE_CONNECTION` = `sync`

   **If using Railway Database (Option B):**
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
- **Render Database**: Verify `DATABASE_URL` or individual `DB_*` variables are correct
- **Railway Database**: Verify all Railway database credentials are correct
- Check if database is running in the respective dashboard
- Ensure `DB_CONNECTION=pgsql` is set
- For Render database, make sure you're using the **Internal Database URL** if both services are in the same region

### Build Failures
- Check build logs in Render dashboard
- Ensure `composer.json` and `package.json` are committed
- Verify Node.js version compatibility

### Storage Issues
- The `storage:link` command should run automatically
- If files aren't accessible, manually run: `php artisan storage:link` in Render shell

### Migration Issues
- Run migrations manually in Render shell
- Check database permissions in your database provider (Render or Railway)
- Ensure database user has proper permissions

### Auto-Deploy Not Working
If Render is not automatically deploying after you push commits:

1. **Check Auto-Deploy Settings**:
   - Go to your service → Settings
   - Find "Auto-Deploy" section
   - Make sure it's **enabled** (toggle ON)
   - Verify the **Branch** matches your push branch (usually `main`)

2. **Check Webhook**:
   - In service Settings → Webhooks section
   - Verify webhook URL exists
   - Check GitHub repository → Settings → Webhooks for webhook status

3. **Manual Deploy** (Quick Fix):
   - Go to your service dashboard
   - Click **"Manual Deploy"** button
   - Select your branch and commit
   - Click **"Deploy"**

4. **Reconnect Repository** (if needed):
   - Service Settings → Repository
   - Disconnect and reconnect your GitHub repository
   - This will refresh the webhook

See [RENDER_TROUBLESHOOTING.md](./RENDER_TROUBLESHOOTING.md) for more detailed troubleshooting steps.

## Environment Variables Reference

Required variables for production:

**Using Render Database:**
```
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:... (generate with php artisan key:generate)
APP_URL=https://your-app.onrender.com
DATABASE_URL=postgresql://user:password@host:5432/database
# OR use individual variables:
DB_CONNECTION=pgsql
DB_HOST=your-render-db-host
DB_PORT=5432
DB_DATABASE=your-database-name
DB_USERNAME=your-username
DB_PASSWORD=your-password
```

**Using Railway Database:**
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

- **Render free tier**: 
  - Web services spin down after 15 minutes of inactivity
  - PostgreSQL databases on free tier have usage limits (90 days retention, 1GB storage)
- **Railway free tier**: Has usage limits
- **Recommendation**: Using Render for both web service and database is simpler and keeps everything in one place
- Consider upgrading for production use
- Always keep your `.env` file secure and never commit it

