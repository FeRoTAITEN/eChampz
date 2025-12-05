#!/bin/bash

# Cursor Update Script
# This script updates Cursor AppImage to the latest version

set -e

CURSOR_PATH="/opt/cursor/cursor.AppImage"
BACKUP_PATH="/opt/cursor/cursor.AppImage.backup"
TEMP_DIR="/tmp/cursor-update"
DOWNLOAD_URL="https://downloads.cursor.com/production/client/linux/x64/appimage/cursor-latest.appimage"

echo "========================================="
echo "Cursor Update Script"
echo "========================================="

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "This script needs sudo privileges to update Cursor"
    echo "Please run: sudo $0"
    exit 1
fi

# Stop Cursor if running
echo "Stopping Cursor..."
pkill -f "cursor.AppImage" || echo "Cursor is not running"
sleep 2

# Create backup
echo "Creating backup..."
if [ -f "$CURSOR_PATH" ]; then
    cp "$CURSOR_PATH" "$BACKUP_PATH"
    echo "Backup created at $BACKUP_PATH"
fi

# Create temp directory
mkdir -p "$TEMP_DIR"
cd "$TEMP_DIR"

# Try to download latest version
echo "Downloading latest Cursor..."
echo "Note: If download fails, you may need to download manually from https://cursor.sh"

# Try multiple methods
if command -v wget &> /dev/null; then
    wget --no-check-certificate -O cursor-new.AppImage "$DOWNLOAD_URL" 2>&1 || {
        echo "Download failed. Trying alternative method..."
        curl -L -H "User-Agent: Mozilla/5.0" -o cursor-new.AppImage "$DOWNLOAD_URL" 2>&1 || {
            echo "All download methods failed."
            echo "Please download manually from: https://cursor.sh"
            echo "Then run: sudo cp /path/to/downloaded/cursor.AppImage $CURSOR_PATH"
            exit 1
        }
    }
elif command -v curl &> /dev/null; then
    curl -L -H "User-Agent: Mozilla/5.0" -o cursor-new.AppImage "$DOWNLOAD_URL" 2>&1 || {
        echo "Download failed."
        echo "Please download manually from: https://cursor.sh"
        exit 1
    }
else
    echo "Neither wget nor curl is available. Please install one of them."
    exit 1
fi

# Verify downloaded file
if [ ! -f "cursor-new.AppImage" ] || [ ! -s "cursor-new.AppImage" ]; then
    echo "Downloaded file is invalid or empty"
    exit 1
fi

# Check if it's a valid AppImage
if file cursor-new.AppImage | grep -q "AppImage\|ELF\|executable"; then
    echo "Valid AppImage downloaded"
else
    echo "Downloaded file doesn't appear to be a valid AppImage"
    echo "File type: $(file cursor-new.AppImage)"
    exit 1
fi

# Make it executable
chmod +x cursor-new.AppImage

# Test version
echo "Checking version..."
NEW_VERSION=$(./cursor-new.AppImage --appimage-version 2>&1 || echo "unknown")
CURRENT_VERSION=$($CURSOR_PATH --appimage-version 2>&1 || echo "unknown")

echo "Current version: $CURRENT_VERSION"
echo "New version: $NEW_VERSION"

# Replace old file
echo "Installing new version..."
mv cursor-new.AppImage "$CURSOR_PATH"
chmod +x "$CURSOR_PATH"

# Cleanup
rm -rf "$TEMP_DIR"

echo "========================================="
echo "Cursor updated successfully!"
echo "Backup saved at: $BACKUP_PATH"
echo "You can now start Cursor"
echo "========================================="


