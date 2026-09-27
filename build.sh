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
  --exclude=".DS_Store" \
  --exclude=".git" \
  --exclude=".github" \
  --exclude=".claude" \
  --exclude=".gitignore" \
  --exclude=".distignore" \
  --exclude=".vscode" \
  --exclude=".wordpress-org" \
  --exclude="build.sh" \
  --exclude="composer.json" \
  --exclude="composer.lock" \
  --exclude="node_modules" \
  --exclude="tests" \
  --exclude="README.md" \
  --exclude="CLAUDE.md" \
  --exclude="TODO.md" \
  --exclude="package.json" \
  --exclude="package-lock.json" \
  --exclude="phpcs.xml.dist" \
  --exclude="release" \
  . "$PLUGIN_DIR/"

# Create zip
cd "$RELEASE_DIR"
zip -r "$PLUGIN_SLUG.zip" "$PLUGIN_SLUG" --quiet
cd ..

# Restore the dev dependencies the build step removed, so linting and tests
# keep working right after a build.
composer install --quiet

echo "Done: $RELEASE_DIR/$PLUGIN_SLUG.zip"
