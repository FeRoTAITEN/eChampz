#!/bin/bash

# ============================================
# Cursor Complete Update Script
# ============================================

set -e

CURSOR_APP="/opt/cursor/cursor.AppImage"
BACKUP_DIR="/opt/cursor/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/cursor_${TIMESTAMP}.AppImage"

echo "========================================="
echo "Cursor Update Script - Full Version"
echo "========================================="
echo ""

# Check sudo
if [ "$EUID" -ne 0 ]; then 
    echo "❌ This script needs sudo privileges"
    echo "Please run: sudo $0"
    exit 1
fi

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Step 1: Stop Cursor
echo "📌 Step 1: Stopping Cursor..."
if pgrep -f "cursor.AppImage" > /dev/null; then
    pkill -9 -f "cursor.AppImage" || true
    sleep 3
    echo "✅ Cursor stopped"
else
    echo "ℹ️  Cursor is not running"
fi

# Step 2: Create backup
echo ""
echo "📌 Step 2: Creating backup..."
if [ -f "$CURSOR_APP" ]; then
    cp "$CURSOR_APP" "$BACKUP_FILE"
    echo "✅ Backup created: $BACKUP_FILE"
    CURRENT_VERSION=$($CURSOR_APP --appimage-version 2>&1 || echo "unknown")
    echo "   Current version: $CURRENT_VERSION"
else
    echo "❌ Cursor AppImage not found at $CURSOR_APP"
    exit 1
fi

# Step 3: Get update info
echo ""
echo "📌 Step 3: Checking for updates..."
UPDATE_INFO=$($CURSOR_APP --appimage-updateinfo 2>&1 || echo "")
echo "   Update info: $UPDATE_INFO"

if [ -z "$UPDATE_INFO" ] || [ "$UPDATE_INFO" == "None" ]; then
    echo "ℹ️  No update information available"
    echo "   You may already have the latest version"
    exit 0
fi

# Step 4: Try to download update
echo ""
echo "📌 Step 4: Downloading update..."

# Extract URL from update info (format: zsync|URL)
ZSYNC_URL=$(echo "$UPDATE_INFO" | grep -o "https://[^|]*" | head -1)
APPIMAGE_URL=$(echo "$ZSYNC_URL" | sed 's/\.zsync$//')

echo "   ZSync URL: $ZSYNC_URL"
echo "   AppImage URL: $APPIMAGE_URL"

TEMP_DIR="/tmp/cursor-update-$$"
mkdir -p "$TEMP_DIR"
cd "$TEMP_DIR"

# Method 1: Try direct download
echo ""
echo "   Trying Method 1: Direct download..."
if curl -L --fail --silent --show-error -H "User-Agent: AppImageUpdate/1.0" -o cursor-new.AppImage "$APPIMAGE_URL" 2>&1; then
    if [ -f "cursor-new.AppImage" ] && [ -s "cursor-new.AppImage" ]; then
        FILE_TYPE=$(file -b cursor-new.AppImage)
        if echo "$FILE_TYPE" | grep -qE "AppImage|ELF|executable"; then
            echo "   ✅ Download successful via Method 1"
            DOWNLOAD_SUCCESS=true
        else
            echo "   ❌ Downloaded file is not a valid AppImage"
            echo "   File type: $FILE_TYPE"
            DOWNLOAD_SUCCESS=false
        fi
    else
        DOWNLOAD_SUCCESS=false
    fi
else
    DOWNLOAD_SUCCESS=false
fi

# Method 2: Try with wget
if [ "$DOWNLOAD_SUCCESS" != "true" ]; then
    echo ""
    echo "   Trying Method 2: wget download..."
    if wget --no-check-certificate -O cursor-new.AppImage "$APPIMAGE_URL" 2>&1; then
        if [ -f "cursor-new.AppImage" ] && [ -s "cursor-new.AppImage" ]; then
            FILE_TYPE=$(file -b cursor-new.AppImage)
            if echo "$FILE_TYPE" | grep -qE "AppImage|ELF|executable"; then
                echo "   ✅ Download successful via Method 2"
                DOWNLOAD_SUCCESS=true
            else
                DOWNLOAD_SUCCESS=false
            fi
        else
            DOWNLOAD_SUCCESS=false
        fi
    else
        DOWNLOAD_SUCCESS=false
    fi
fi

# Method 3: Try zsync
if [ "$DOWNLOAD_SUCCESS" != "true" ] && command -v zsync > /dev/null 2>&1; then
    echo ""
    echo "   Trying Method 3: zsync update..."
    if zsync -i "$CURSOR_APP" "$ZSYNC_URL" 2>&1; then
        if [ -f "cursor-latest.appimage" ]; then
            mv cursor-latest.appimage cursor-new.AppImage
            echo "   ✅ Update successful via Method 3 (zsync)"
            DOWNLOAD_SUCCESS=true
        else
            DOWNLOAD_SUCCESS=false
        fi
    else
        DOWNLOAD_SUCCESS=false
    fi
fi

# Check if download was successful
if [ "$DOWNLOAD_SUCCESS" != "true" ]; then
    echo ""
    echo "❌ All download methods failed"
    echo ""
    echo "========================================="
    echo "Manual Update Required"
    echo "========================================="
    echo ""
    echo "The automatic download failed. Please update Cursor manually:"
    echo ""
    echo "1. Visit: https://cursor.sh"
    echo "2. Download the latest AppImage for Linux"
    echo "3. Run the following command:"
    echo "   sudo cp /path/to/downloaded/cursor.AppImage $CURSOR_APP"
    echo "   sudo chmod +x $CURSOR_APP"
    echo ""
    echo "Your current installation is backed up at:"
    echo "   $BACKUP_FILE"
    echo ""
    exit 1
fi

# Step 5: Verify and install
echo ""
echo "📌 Step 5: Verifying and installing..."

chmod +x cursor-new.AppImage

# Get versions
NEW_VERSION=$(./cursor-new.AppImage --appimage-version 2>&1 || echo "unknown")
echo "   New version: $NEW_VERSION"

# Compare versions
if [ "$CURRENT_VERSION" == "$NEW_VERSION" ]; then
    echo "   ℹ️  Versions are the same. No update needed."
    rm -rf "$TEMP_DIR"
    exit 0
fi

# Install new version
echo ""
echo "📌 Step 6: Installing new version..."
mv cursor-new.AppImage "$CURSOR_APP"
chmod +x "$CURSOR_APP"

# Cleanup
rm -rf "$TEMP_DIR"

echo ""
echo "========================================="
echo "✅ Cursor Updated Successfully!"
echo "========================================="
echo ""
echo "Old version: $CURRENT_VERSION"
echo "New version: $NEW_VERSION"
echo ""
echo "Backup saved at: $BACKUP_FILE"
echo ""
echo "You can now start Cursor!"
echo "========================================="


