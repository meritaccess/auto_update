#!/bin/bash

USER="bantj"
APP_DIR="/home/$USER/merit_access_test"
VERSION_FILE="$APP_DIR/version.txt"
UPDATE_URL="https://codeload.github.com/meritaccess/update/zip/refs/heads/main"
VERSION_URL="https://yourserver.com/version.txt"
DOWNLOAD_DIR="/home/$USER/merit_access_update"
ZIP_NAME="update-main.zip"
FILE_NAME="update-main"
LOG_FILE="/home/$USER/logs/update.log"

# Ensure log file exists
mkdir -p /home/$USER/logs
touch $LOG_FILE

# Wait for the network to be ready
while ! ping -c 1 github.com &> /dev/null; do
    echo "Waiting for network..." >> $LOG_FILE
    sleep 5
done

mkdir -p $DOWNLOAD_DIR

# latest_version=$(curl -s $VERSION_URL)
latest_version="4.0.0"
if [ -f $VERSION_FILE ]; then
    current_version=$(cat $VERSION_FILE)
else
    current_version="none"
fi

echo "Current version: $current_version" >> $LOG_FILE
echo "Latest version: $latest_version" >> $LOG_FILE

if [ "$latest_version" != "$current_version" ]; then
    echo "New version available. Updating..." >> $LOG_FILE
    if curl -o $DOWNLOAD_DIR/$ZIP_NAME $UPDATE_URL; then
        if unzip -o $DOWNLOAD_DIR/$ZIP_NAME -d $DOWNLOAD_DIR >> $LOG_FILE 2>&1; then
            rm $DOWNLOAD_DIR/$ZIP_NAME >> $LOG_FILE 2>&1
            cp -r $DOWNLOAD_DIR/$FILE_NAME/* $APP_DIR >> $LOG_FILE 2>&1
            sudo rm -rf $DOWNLOAD_DIR >> $LOG_FILE 2>&1
            echo "Update to version $latest_version completed successfully." >> $LOG_FILE
        else
            echo "Failed to unzip the update package." >> $LOG_FILE
        fi
    else
        echo "Failed to download the update." >> $LOG_FILE
    fi
else
    echo "You already have the latest version." >> $LOG_FILE
fi
