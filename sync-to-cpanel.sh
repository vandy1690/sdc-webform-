#!/bin/bash

# Sync DDEV version to cPanel package
# Usage: ./sync-to-cpanel.sh

echo "🔄 Syncing DDEV version to cPanel package..."

# Remove old cPanel directory
rm -rf dist_cpanel

# Create new cPanel directory
mkdir -p dist_cpanel

# Copy files (excluding development-only files)
rsync -av \
  --exclude='.ddev' \
  --exclude='backend' \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='*.db' \
  --exclude='update-*.js' \
  --exclude='setup.sh' \
  --exclude='sync-to-cpanel.sh' \
  --exclude='dist_cpanel' \
  ./ dist_cpanel/

# Fix links for cPanel (remove leading slashes)
echo "🔗 Fixing links for cPanel..."

# Fix HTML files
find dist_cpanel -name "*.html" -exec sed -i '' 's|href="/|href="|g' {} \;
find dist_cpanel -name "*.html" -exec sed -i '' 's|src="/|src="|g' {} \;
find dist_cpanel -name "*.html" -exec sed -i '' 's|action="/|action="|g' {} \;

# Fix fetch URLs in JavaScript
find dist_cpanel -name "*.html" -exec sed -i '' 's|fetch("/api/|fetch("./api/|g' {} \;
find dist_cpanel -name "*.html" -exec sed -i '' "s|fetch('/api/|fetch('./api/|g" {} \;

echo "✅ Links fixed for cPanel!"

# Create cPanel zip
cd dist_cpanel
zip -r ../sdc_webform_cpanel.zip . > /dev/null
cd ..

echo "✅ Sync complete!"
echo "📦 Updated package: sdc_webform_cpanel.zip"
echo "📁 Updated directory: dist_cpanel/"
echo ""
echo "🚀 Ready to upload to cPanel!"
