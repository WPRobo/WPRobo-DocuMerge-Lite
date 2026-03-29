#!/bin/bash
#
# WPRobo DocuMerge Lite — Production Build Script
#
# Creates a clean production ZIP ready for WordPress.org submission.
# Reads version from the main plugin file header automatically.
#
# Usage:
#   bash deploy.sh
#
# Output:
#   production/wpr-documerge-{version}.zip
#
# What's INCLUDED:
#   - All PHP source files (src/, templates/, emails/, blocks/)
#   - Compiled CSS + JS (assets/css/, assets/js/)
#   - Vendor JS libraries (assets/vendor/)
#   - Images (assets/images/)
#   - Languages (.pot file)
#   - readme.txt, uninstall.php, composer.json
#   - Main plugin file (wprobo-documerge.php)
#
# What's EXCLUDED:
#   - SCSS source files (assets/src/)
#   - Node modules (node_modules/)
#   - Git files (.git/, .gitignore)
#   - GitHub workflows (.github/)
#   - Documentation (docs/)
#   - Dev config (package.json, package-lock.json, .editorconfig)
#   - Deploy script itself (deploy.sh)
#   - Dist ignore (.distignore)
#   - Markdown files (*.md) except readme.txt
#   - OS files (.DS_Store, Thumbs.db)
#   - Log files (*.log)
#   - Temp/test files
#

set -e

# ── Resolve paths ──────────────────────────────────────────────
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PLUGIN_DIR="$SCRIPT_DIR"

# ── Extract version from main plugin file ──────────────────────
VERSION=$(grep -m 1 "Version:" "$PLUGIN_DIR/wprobo-documerge.php" | sed 's/.*Version:[[:space:]]*//' | sed 's/[[:space:]]*$//')

if [ -z "$VERSION" ]; then
    echo "ERROR: Could not extract version from wprobo-documerge.php"
    exit 1
fi

echo "=========================================="
echo "  WPRobo DocuMerge Lite — Build v${VERSION}"
echo "=========================================="
echo ""

# ── Create production directory ────────────────────────────────
PROD_DIR="$PLUGIN_DIR/production"
BUILD_DIR="$PROD_DIR/wprobo-docu-merge"
ZIP_NAME="wpr-documerge-${VERSION}.zip"

# Clean previous build.
if [ -d "$PROD_DIR" ]; then
    echo "[1/6] Cleaning previous build..."
    rm -rf "$PROD_DIR"
fi

mkdir -p "$BUILD_DIR"
echo "[1/6] Created production directory."

# ── Copy production files ──────────────────────────────────────
echo "[2/6] Copying production files..."

# Main plugin file.
cp "$PLUGIN_DIR/wprobo-documerge.php" "$BUILD_DIR/"
cp "$PLUGIN_DIR/uninstall.php" "$BUILD_DIR/"
cp "$PLUGIN_DIR/readme.txt" "$BUILD_DIR/"
cp "$PLUGIN_DIR/composer.json" "$BUILD_DIR/"

# PHP source.
cp -R "$PLUGIN_DIR/src" "$BUILD_DIR/"

# Templates.
cp -R "$PLUGIN_DIR/templates" "$BUILD_DIR/"

# Blocks.
if [ -d "$PLUGIN_DIR/blocks" ]; then
    cp -R "$PLUGIN_DIR/blocks" "$BUILD_DIR/"
fi

# Compiled assets (CSS + JS only, not source).
mkdir -p "$BUILD_DIR/assets/css"
mkdir -p "$BUILD_DIR/assets/js"
cp -R "$PLUGIN_DIR/assets/css/" "$BUILD_DIR/assets/css/"
cp -R "$PLUGIN_DIR/assets/js/" "$BUILD_DIR/assets/js/"

# Vendor JS libraries.
if [ -d "$PLUGIN_DIR/assets/vendor" ]; then
    cp -R "$PLUGIN_DIR/assets/vendor" "$BUILD_DIR/assets/"
fi

# Images.
if [ -d "$PLUGIN_DIR/assets/images" ]; then
    cp -R "$PLUGIN_DIR/assets/images" "$BUILD_DIR/assets/"
fi

# Languages.
if [ -d "$PLUGIN_DIR/languages" ]; then
    cp -R "$PLUGIN_DIR/languages" "$BUILD_DIR/"
fi

# Emails.
if [ -d "$PLUGIN_DIR/emails" ]; then
    cp -R "$PLUGIN_DIR/emails" "$BUILD_DIR/"
fi

echo "   Copied: src/, templates/, blocks/, assets/, languages/, emails/"

# ── Remove dev artifacts from copied files ─────────────────────
echo "[3/6] Cleaning dev artifacts..."

# Remove any .DS_Store, Thumbs.db, desktop.ini.
find "$BUILD_DIR" -name ".DS_Store" -delete 2>/dev/null || true
find "$BUILD_DIR" -name "Thumbs.db" -delete 2>/dev/null || true
find "$BUILD_DIR" -name "desktop.ini" -delete 2>/dev/null || true
find "$BUILD_DIR" -name "*.log" -delete 2>/dev/null || true

# Remove any .gitkeep files.
find "$BUILD_DIR" -name ".gitkeep" -delete 2>/dev/null || true

echo "   Cleaned: .DS_Store, Thumbs.db, .gitkeep, *.log"

# ── Verify critical files exist ────────────────────────────────
echo "[4/6] Verifying build..."

ERRORS=0

check_file() {
    if [ ! -f "$BUILD_DIR/$1" ]; then
        echo "   MISSING: $1"
        ERRORS=$((ERRORS + 1))
    fi
}

check_dir() {
    if [ ! -d "$BUILD_DIR/$1" ]; then
        echo "   MISSING DIR: $1"
        ERRORS=$((ERRORS + 1))
    fi
}

check_file "wprobo-documerge.php"
check_file "uninstall.php"
check_file "readme.txt"
check_dir "src"
check_dir "src/Core"
check_dir "src/Form"
check_dir "src/Admin"
check_dir "src/Document"
check_dir "src/Template"
check_dir "templates"
check_dir "assets/css"
check_dir "assets/js"
check_file "assets/css/admin/main.min.css"
check_file "assets/css/frontend/form.min.css"
check_file "assets/js/admin/main.min.js"
check_file "assets/js/admin/form-builder.min.js"
check_file "assets/js/admin/settings.min.js"
check_file "assets/js/frontend/form-renderer.min.js"

if [ $ERRORS -gt 0 ]; then
    echo ""
    echo "ERROR: $ERRORS missing file(s). Build aborted."
    exit 1
fi

# Count files.
FILE_COUNT=$(find "$BUILD_DIR" -type f | wc -l | tr -d ' ')
echo "   Verified: $FILE_COUNT files in build."

# ── Verify NO dev files leaked ─────────────────────────────────
echo "[5/6] Checking for dev file leaks..."

LEAKS=0

check_no_file() {
    if [ -f "$BUILD_DIR/$1" ] || [ -d "$BUILD_DIR/$1" ]; then
        echo "   LEAK: $1 should not be in production!"
        LEAKS=$((LEAKS + 1))
    fi
}

check_no_file "package.json"
check_no_file "package-lock.json"
check_no_file ".gitignore"
check_no_file ".distignore"
check_no_file ".editorconfig"
check_no_file "deploy.sh"
check_no_file "docs"
check_no_file "node_modules"
check_no_file ".git"
check_no_file ".github"
check_no_file "assets/src"

# Check for any .md files (except readme.txt which is not .md).
MD_FILES=$(find "$BUILD_DIR" -name "*.md" 2>/dev/null | wc -l | tr -d ' ')
if [ "$MD_FILES" -gt 0 ]; then
    echo "   LEAK: Found $MD_FILES .md file(s) in build!"
    find "$BUILD_DIR" -name "*.md" -exec echo "         {}" \;
    LEAKS=$((LEAKS + 1))
fi

if [ $LEAKS -gt 0 ]; then
    echo ""
    echo "WARNING: $LEAKS dev file leak(s) detected. Cleaning..."
    find "$BUILD_DIR" -name "*.md" -delete 2>/dev/null || true
fi

echo "   No dev files in production build."

# ── Create ZIP ─────────────────────────────────────────────────
echo "[6/6] Creating ZIP: $ZIP_NAME"

cd "$PROD_DIR"
zip -r -q "$ZIP_NAME" "wprobo-docu-merge/"

# Get ZIP size.
ZIP_SIZE=$(du -h "$PROD_DIR/$ZIP_NAME" | cut -f1)

echo ""
echo "=========================================="
echo "  BUILD COMPLETE"
echo "=========================================="
echo ""
echo "  Plugin:   WPRobo DocuMerge Lite"
echo "  Version:  $VERSION"
echo "  ZIP:      production/$ZIP_NAME"
echo "  Size:     $ZIP_SIZE"
echo "  Files:    $FILE_COUNT"
echo ""
echo "  Ready for WordPress.org submission."
echo "=========================================="
