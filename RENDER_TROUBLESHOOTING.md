# Render Auto-Deploy Troubleshooting

If Render is not automatically deploying after you push commits, check the following:

## 1. Check Auto-Deploy Settings

1. Go to your Render service dashboard
2. Click on **Settings** (or the gear icon)
3. Scroll down to **"Auto-Deploy"** section
4. Make sure **"Auto-Deploy"** is **enabled** (toggle should be ON)
5. Verify the **Branch** is set to `main` (or whatever branch you're pushing to)

## 2. Check Branch Configuration

1. In your service settings, find **"Branch"** field
2. Make sure it matches the branch you're pushing to (usually `main` or `master`)
3. If you're pushing to a different branch, update this setting

## 3. Check Webhook Status

1. In your service settings, scroll to **"Webhooks"** section
2. You should see a webhook URL that looks like: `https://api.render.com/webhooks/...`
3. If there's no webhook or it shows an error:
   - Go to your GitHub repository
   - Go to **Settings** → **Webhooks**
   - Check if the Render webhook exists and is active
   - If missing, you may need to reconnect the repository in Render

## 4. Verify render.yaml is Detected

1. In Render dashboard, check if `render.yaml` is being used
2. If you manually configured the service (not using render.yaml), auto-deploy should still work
3. The `autoDeploy: true` in render.yaml only works if Render is using the YAML file

## 5. Manual Deploy

If auto-deploy isn't working, you can manually trigger a deploy:

1. Go to your service in Render dashboard
2. Click on **"Manual Deploy"** button (usually in the top right)
3. Select the branch and commit you want to deploy
4. Click **"Deploy"**

## 6. Check Recent Deploys

1. In your service dashboard, check the **"Events"** or **"Deploys"** tab
2. See if there are any failed webhook deliveries
3. Check if Render is detecting your commits

## 7. Reconnect Repository

If nothing works, try reconnecting:

1. Go to service **Settings**
2. Scroll to **"Repository"** section
3. Click **"Disconnect"** (if available)
4. Then **"Connect"** and reconnect your GitHub repository
5. This will set up a fresh webhook

## 8. Check GitHub Repository Settings

1. Go to your GitHub repository
2. **Settings** → **Webhooks**
3. Verify the Render webhook exists and shows recent deliveries
4. If webhook is failing, you may see error messages here

## Quick Fix: Manual Deploy

The fastest solution is to manually deploy:

1. Render Dashboard → Your Service
2. Click **"Manual Deploy"** 
3. Select your branch (`main`)
4. Click **"Deploy"**

This will immediately start a new deployment with your latest commit.

