#!/bin/bash
DB_USER="ma"
DB_PASS="FrameWork5414*"
DB_NAME="MeritAccessLocal"
USER="meritaccess"
APP_DIR="/home/$USER/merit_access"
VERSION_FILE="$APP_DIR/version.txt"
DOWNLOAD_DIR="/home/$USER/merit_access_update"
LOG_FILE="/home/$USER/logs/update.log"
PYTHON="/usr/bin/python"
NETWORK_TIMEOUT=30


sudo mknod /dev/wie1 c 240 0
sudo mknod /dev/wie2 c 239 0
mkdir -p /home/$USER/logs
mkdir -p $APP_DIR
touch $LOG_FILE

log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> $LOG_FILE
}


handle_error() {
    log_message "ERROR: $1"
    return 1
}

get_update_mode() {
    SQL_QUERY="SELECT VALUE AS v FROM ConfigDU WHERE property='update_mode';"
    update_mode=$(mysql -u$DB_USER -p$DB_PASS $DB_NAME -se "$SQL_QUERY")
    return "$update_mode"
}

get_repo() {
    SQL_QUERY="SELECT VALUE AS v FROM ConfigDU WHERE property='appupdate';"
    repo=$(mysql -u$DB_USER -p$DB_PASS $DB_NAME -se "$SQL_QUERY")
    echo "$repo"
}

wait_for_network() {
    log_message "Starting network check."
    SECONDS=0
    while ! ping -c 1 -W 1 github.com &> /dev/null; do
        if [ $SECONDS -ge $NETWORK_TIMEOUT ]; then
            handle_error "Network timeout after $NETWORK_TIMEOUT seconds."
            return 1
        fi
        log_message "Waiting for network..."
        sleep 5
    done
    log_message "Network is up."
    return 0
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

    pip install -r $APP_DIR/requirements.txt || handle_error "Failed to install required Python packages."
    log_message "Installed required Python packages."

    echo $latest_version > $VERSION_FILE || handle_error "Failed to update version file."
    log_message "Update to version $latest_version completed successfully."
}

get_update_mode
update_mode=$?
log_message "Update mode: $update_mode"

if [ $update_mode -eq 0 ]; then

    wait_for_network
    network_status=$?

    if [ $network_status -eq 0 ]; then
        get_repo
        repo=$(get_repo)
        latest_release_info=$(curl -s https://api.github.com/repos/$repo/releases/latest)
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
    fi
fi

$PYTHON $APP_DIR/main.py || handle_error "Failed to run Merit Access App"
log_message "Successfully run Merit Access App"
