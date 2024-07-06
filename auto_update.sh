#!/bin/bash
DB_USER="ma"
DB_PASS="FrameWork5414*"
DB_NAME="MeritAccessLocal"
USER="meritaccess"

APP_DIR_PYTHON="/home/$USER/merit_access"
APP_DIR_WEB="/var/www/html/"
DATABASE_UPDATE_DIR="/home/$USER/database_update"

REPO_WEB="meritaccess/html"
REPO_DATABASE="meritaccess/database_update"

LOG_FILE="/home/$USER/logs/update.log"
PYTHON="/usr/bin/python"
NETWORK_TIMEOUT=30


sudo mknod /dev/wie1 c 240 0
sudo mknod /dev/wie2 c 239 0
mkdir -p /home/$USER/logs
mkdir -p $APP_DIR_PYTHON
mkdir -p $APP_DIR_WEB
touch $LOG_FILE

log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> $LOG_FILE
}

handle_error() {
    log_message "ERROR: $1"
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
    while ! ping -c 1 -W 1 github.com &> /dev/null || ! nc -zv github.com 443 &> /dev/null; do
        if [ $SECONDS -ge $NETWORK_TIMEOUT ]; then
            handle_error "Network timeout after $NETWORK_TIMEOUT seconds or port 443 is blocked."
            return 1
        fi
        log_message "Waiting for network and port 443 availability..."
        sleep 5
    done
    log_message "Network and port 443 are up."
    return 0
}


fetch_latest_version() {
    echo "$1" | jq -r .tag_name | tr -d '[:space:]'
}

fetch_asset_url() {
    echo "$1" | jq -r .zipball_url | tr -d '[:space:]'
}

download_update_github(){
    local APP_DIR=$1
    local asset_url=$2
    local asset_name="update.zip"

    mkdir -p "$APP_DIR"_temp || handle_error "Failed to create download directory."

    curl -L -o "$APP_DIR"_temp/$asset_name $asset_url || { handle_error "Failed to download the update."; return; }
    log_message "Download successful."

    unzip -o "$APP_DIR"_temp/$asset_name -d "$APP_DIR"_temp >> $LOG_FILE 2>&1 || { handle_error "Failed to unzip the update package."; return; }
    log_message "Unzip successful."

    unzipped_dir=$(unzip -Z -1 "$APP_DIR"_temp/$asset_name | head -n 1 | cut -d '/' -f 1)
    log_message "Unzipped directory: $unzipped_dir"

    echo "$unzipped_dir"
}

update_application() {
    local APP_NAME=$1
    local APP_DIR=$2
    local asset_url=$3

    log_message "Updating $APP_NAME"

    unzipped_dir=$(download_update_github $APP_DIR $asset_url) || { handle_error "Failed to download the update."; return; }

    sudo rm -r $APP_DIR/* || { handle_error "Failed to remove old version."; return; }
    log_message "Removed old version"

    sudo cp -r "$APP_DIR"_temp/$unzipped_dir/* $APP_DIR >> $LOG_FILE 2>&1 || { handle_error "Failed to copy new files to application directory."; return; }
    log_message "Copied new files to application directory."

    rm -rf "$APP_DIR"_temp || { handle_error "Failed to remove temporary download directory."; return; }
    log_message "Removed temporary download directory."

    if [ -e $APP_DIR/requirements.txt ]; then
        pip install -r $APP_DIR/requirements.txt || { handle_error "Failed to install required Python packages."; return; }
        log_message "Installed required Python packages."
    fi

}


get_current_version(){
    local VERSION_FILE=$1
    if [ -f $VERSION_FILE ]; then
        current_version=$(cat $VERSION_FILE)
    else
        current_version="none"
    fi
    echo $current_version | tr -d '[:space:]'
}


update_merit_access_web(){
    latest_release_info=$(curl -s https://api.github.com/repos/$REPO_WEB/releases/latest)
    latest_version=$(fetch_latest_version "$latest_release_info")
    asset_url=$(fetch_asset_url "$latest_release_info")
    current_version=$(get_current_version "$APP_DIR_WEB/version.txt")

    log_message "Current version: $current_version"
    log_message "Latest version: $latest_version"
    log_message "Asset URL: $asset_url"

    if [ "$latest_version" != "$current_version" ]; then
        log_message "New version available. Updating..."
        update_application "Merit Access Web" $APP_DIR_WEB $DOWNLOAD_DIR_WEB "$asset_url"
        log_message "Update to version $latest_version completed successfully."
    else
        log_message "You already have the latest version."
    fi

}

update_merit_access(){
    repo=$(get_repo)
    latest_release_info=$(curl -s https://api.github.com/repos/$repo/releases/latest)
    latest_version=$(fetch_latest_version "$latest_release_info")
    asset_url=$(fetch_asset_url "$latest_release_info")
    current_version=$(get_current_version "$APP_DIR_PYTHON/version.txt")

    log_message "Current version: $current_version"
    log_message "Latest version: $latest_version"
    log_message "Asset URL: $asset_url"

    if [ "$latest_version" != "$current_version" ]; then
        log_message "New version available. Updating..."
        update_application "Merit Access Python" $APP_DIR_PYTHON $DOWNLOAD_DIR_PYTHON "$asset_url"
        log_message "Update to version $latest_version completed successfully."
    else
        log_message "You already have the latest version."
    fi
}

update_database(){
    latest_release_info=$(curl -s https://api.github.com/repos/$REPO_DATABASE/releases/latest)
    latest_version=$(fetch_latest_version "$latest_release_info")
    asset_url=$(fetch_asset_url "$latest_release_info")
    current_version=$(get_current_version $DATABASE_UPDATE_DIR/version.txt)

    log_message "Current version: $current_version"
    log_message "Latest version: $latest_version"
    log_message "Asset URL: $asset_url"

    if [ "$latest_version" != "$current_version" ]; then
        log_message "New version available. Updating..."
        update_application "Database" $DATABASE_UPDATE_DIR $DOWNLOAD_DIR_DATABASE "$asset_url"
        UPDATE_SQL_FILE=$DATABASE_UPDATE_DIR/update.sql
        mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$UPDATE_SQL_FILE" || { handle_error "Failed to execute update.sql"; return; }
        log_message "Update to version $latest_version completed successfully."
    else
        log_message "You already have the latest version."
    fi
    
}


get_update_mode
update_mode=$?
log_message "Update mode: $update_mode"

if [ $update_mode -eq 0 ]; then

    wait_for_network
    network_status=$?

    if [ $network_status -eq 0 ]; then
        update_database
        update_merit_access
        update_merit_access_web
    fi
fi

# $PYTHON $APP_DIR_PYTHON/main.py || handle_error "Failed to run Merit Access App"