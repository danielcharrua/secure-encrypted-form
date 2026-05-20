#!/bin/bash
set -e

PLUGIN_SLUG="secure-encrypted-form"
RELEASE_DIR="release"
PLUGIN_DIR="$RELEASE_DIR/$PLUGIN_SLUG"

echo "Building $PLUGIN_SLUG..."

# Clean previous build
rm -rf "$RELEASE_DIR"
mkdir -p "$PLUGIN_DIR"

# Install production dependencies
composer install --no-dev --optimize-autoloader --quiet

# Copy plugin files, excluding dev-only files
rsync -a \
  --exclude=".git" \
  --exclude=".github" \
  --exclude=".gitignore" \
  --exclude=".distignore" \
  --exclude=".vscode" \
  --exclude=".wordpress-org" \
  --exclude="build.sh" \
  --exclude="composer.json" \
  --exclude="composer.lock" \
  --exclude="node_modules" \
  --exclude="release" \
  . "$PLUGIN_DIR/"

# Create zip
cd "$RELEASE_DIR"
zip -r "$PLUGIN_SLUG.zip" "$PLUGIN_SLUG" --quiet
cd ..

echo "Done: $RELEASE_DIR/$PLUGIN_SLUG.zip"
