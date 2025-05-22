#!/bin/bash
# GitHub Updater Installer for WordPress
# For testing ImproveSEO plugin
# Usage: bash install-github-updater.sh

# Check if we're in a WordPress directory
if [ ! -f "wp-config.php" ]; then
  echo "Error: This script must be run from a WordPress root directory."
  exit 1
fi

echo "Installing GitHub Updater plugin for WordPress..."

# Create temporary directory
mkdir -p temp_plugin_install
cd temp_plugin_install

# Download the latest GitHub Updater release
echo "Downloading GitHub Updater..."
curl -s https://api.github.com/repos/afragen/github-updater/releases/latest \
| grep "browser_download_url.*zip" \
| cut -d : -f 2,3 \
| tr -d \" \
| wget -qi -

# Check if download succeeded
if [ ! -f github-updater.zip ]; then
  echo "Failed to download GitHub Updater. Please download and install manually."
  cd ..
  rm -rf temp_plugin_install
  exit 1
fi

# Extract the plugin
echo "Extracting GitHub Updater..."
unzip -q github-updater.zip
rm github-updater.zip

# Move to plugins directory
echo "Installing GitHub Updater..."
mv github-updater ../wp-content/plugins/

# Clean up
cd ..
rm -rf temp_plugin_install

echo "✓ GitHub Updater successfully installed!"
echo "Please activate the plugin in your WordPress admin panel."
echo "Then add ImproveSEO plugin using these settings:"
echo "- Plugin URI: https://github.com/overpowered9/ImproveSEO"
echo "- Branch: Scheduling--Feature--Update"

exit 0
