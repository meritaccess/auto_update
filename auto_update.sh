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

log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> $LOG_FILE
}

handle_error() {
    log_message "ERROR: $1"
    exit 1
}

wait_for_network() {
    log_message "Starting network check."
    while ! ping -c 1 -W 1 github.com &> /dev/null; do
        log_message "Waiting for network..."
        sleep 5
    done
    log_message "Network is up."
}

fetch_latest_version() {
    # Uncomment the next line to use the actual version check
    # curl -s $VERSION_URL
    echo "4.0.0"
}

update_application() {
    mkdir -p $DOWNLOAD_DIR || handle_error "Failed to create download directory."

    curl -o $DOWNLOAD_DIR/$ZIP_NAME $UPDATE_URL || handle_error "Failed to download the update."
    log_message "Download successful."

    unzip -o $DOWNLOAD_DIR/$ZIP_NAME -d $DOWNLOAD_DIR >> $LOG_FILE 2>&1 || handle_error "Failed to unzip the update package."
    log_message "Unzip successful."

    rm $DOWNLOAD_DIR/$ZIP_NAME || handle_error "Failed to remove zip file."
    log_message "Removed zip file."

    cp -r $DOWNLOAD_DIR/$FILE_NAME/* $APP_DIR >> $LOG_FILE 2>&1 || handle_error "Failed to copy new files to application directory."
    log_message "Copied new files to application directory."

    sudo rm -rf $DOWNLOAD_DIR || handle_error "Failed to remove temporary download directory."
    log_message "Removed temporary download directory."

    echo $latest_version > $VERSION_FILE || handle_error "Failed to update version file."
    log_message "Update to version $latest_version completed successfully."
}

# Main script execution
wait_for_network

latest_version=$(fetch_latest_version)
if [ -f $VERSION_FILE ]; then
    current_version=$(cat $VERSION_FILE)
else
    current_version="none"
fi

log_message "Current version: $current_version"
log_message "Latest version: $latest_version"

if [ "$latest_version" != "$current_version" ]; then
    log_message "New version available. Updating..."
    update_application
else
    log_message "You already have the latest version."
fi
