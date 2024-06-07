#!/bin/bash

USER="meritaccess"
APP_DIR="/home/$USER/merit_access_test"
VERSION_FILE="$APP_DIR/version.txt"
REPO="meritaccess/merit_access"
DOWNLOAD_DIR="/home/$USER/merit_access_update"
LOG_FILE="/home/$USER/logs/update.log"
NETWORK_TIMEOUT = 20

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
    SECONDS=0
    while ! ping -c 1 -W 1 github.com &> /dev/null; do
        if [ $SECONDS -ge $NETWORK_TIMEOUT ]; then
            handle_error "Network timeout after $NETWORK_TIMEOUT seconds."
        fi
        log_message "Waiting for network..."
        sleep 5
    done
    log_message "Network is up."
}


fetch_latest_version() {
    echo "$1" | jq -r .tag_name
}

fetch_asset_url() {
    echo "$1" | jq -r .zipball_url
}

update_application() {
    local asset_url=$1
    local asset_name="update.zip"
    echo "$asset_url"
    mkdir -p $DOWNLOAD_DIR || handle_error "Failed to create download directory."

    curl -L -o $DOWNLOAD_DIR/$asset_name $asset_url || handle_error "Failed to download the update."
    log_message "Download successful."

    unzip -o $DOWNLOAD_DIR/$asset_name -d $DOWNLOAD_DIR >> $LOG_FILE 2>&1 || handle_error "Failed to unzip the update package."
    log_message "Unzip successful."
    
    unzipped_dir=$(unzip -Z -1 $DOWNLOAD_DIR/$asset_name | head -n 1 | cut -d '/' -f 1)
    log_message "Unzipped directory: $unzipped_dir"

    rm -r $APP_DIR/*
    log_message "Removed old version"

    cp -r $DOWNLOAD_DIR/$unzipped_dir/* $APP_DIR >> $LOG_FILE 2>&1 || handle_error "Failed to copy new files to application directory."
    log_message "Copied new files to application directory."

    rm -rf $DOWNLOAD_DIR || handle_error "Failed to remove temporary download directory."
    log_message "Removed temporary download directory."

    echo $latest_version > $VERSION_FILE || handle_error "Failed to update version file."
    log_message "Update to version $latest_version completed successfully."
}


wait_for_network

latest_release_info=$(curl -s https://api.github.com/repos/$REPO/releases/latest)
latest_version=$(fetch_latest_version "$latest_release_info")
asset_url=$(fetch_asset_url "$latest_release_info")

if [ -f $VERSION_FILE ]; then
    current_version=$(cat $VERSION_FILE)
else
    current_version="none"
fi

log_message "Current version: $current_version"
log_message "Latest version: $latest_version"
log_message "Asset URL: $asset_url"

if [ "$latest_version" != "$current_version" ]; then
    log_message "New version available. Updating..."
    update_application "$asset_url"
else
    log_message "You already have the latest version."
fi
