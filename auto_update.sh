#!/bin/bash

APP_DIR="/home/bantj/merit_access_test"
VERSION_FILE="$APP_DIR/version.txt"
UPDATE_URL="https://yourserver.com/update.zip"
VERSION_URL="https://yourserver.com/version.txt"
DOWNLOAD_DIR="/home/bantj/merit_access_update"
FILE_NAME="update.zip"

mkdir -p $DOWNLOAD_DIR
latest_version=$(curl -s $VERSION_URL)
if [ -f $VERSION_FILE ]; then
    current_version=$(cat $VERSION_FILE)
else
    current_version="none"
fi

echo "Current version: $current_version"
echo "Latest version: $latest_version"

if [ "$latest_version" != "$current_version" ]; then
    echo "New version available. Updating..."
    curl -o $DOWNLOAD_DIR/$FILE_NAME $UPDATE_URL
    unzip -o $DOWNLOAD_DIR/$FILE_NAME -d $DOWNLOAD_DIR
    rm update.zip
    cp -r $DOWNLOAD_DIR/* $APP_DIR
    sudo rm -rf $DOWNLOAD_DIR
    echo "Update to version $latest_version completed successfully."
else
    echo "You already have the latest version."
fi
