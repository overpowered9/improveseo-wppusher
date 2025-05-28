# ImproveSEO Plugin Testing Instructions

This document provides instructions for installing and testing the ImproveSEO plugin from the GitHub repository branch `Scheduling--Feature--Update`.

## Setup Method 1: Using GitHub Updater

GitHub Updater allows WordPress to directly install and update plugins hosted on GitHub. This is the recommended method for testers.

### Step 1: Install GitHub Updater Plugin

1. Download the GitHub Updater plugin from [https://github.com/afragen/github-updater/releases](https://github.com/afragen/github-updater/releases)
2. Install the plugin in your WordPress site via Plugins > Add New > Upload Plugin
3. Activate GitHub Updater

### Step 2: Add ImproveSEO Plugin via GitHub Updater

1. Go to Settings > GitHub Updater
2. Click on the "Install Plugin" tab
3. Enter the following information:
   - Plugin URI: `https://github.com/overpowered9/ImproveSEO`
   - Repository Branch: `Scheduling--Feature--Update`
4. Click "Install Plugin"
5. Activate the plugin once installed

### Step 3: Testing Updates

When new changes are pushed to the `Scheduling--Feature--Update` branch, GitHub Updater will detect the changes and offer an update option in your WordPress Updates section.

## Setup Method 2: Manual Installation (Alternative)

If you prefer manual installation, you can download the latest version from GitHub:

1. Visit the GitHub Actions page for this repository
2. Find the latest successful workflow run for the "Create Plugin Zip" action
3. Download the artifact named "improve-seo"
4. Install the zip file via the WordPress admin panel: Plugins > Add New > Upload Plugin

## Automatic Installation Script (For Advanced Users)

You can use the following script to install GitHub Updater automatically. Run this in your WordPress site's root directory:

```bash
# Download and install GitHub Updater
wget https://github.com/afragen/github-updater/archive/develop.zip
unzip develop.zip -d wp-content/plugins/
rm develop.zip
cd wp-content/plugins
mv github-updater-develop github-updater
wp plugin activate github-updater

# Configure GitHub Updater to track ImproveSEO plugin
wp github-updater install plugin --uri=https://github.com/overpowered9/ImproveSEO --branch=Scheduling--Feature--Update
```

## Testing Notes

When reporting issues, please include:

1. WordPress version
2. PHP version
3. Any error messages (screenshot preferred)
4. Steps to reproduce the issue

## Feedback

Please send all feedback to: [overpowered9@example.com]
