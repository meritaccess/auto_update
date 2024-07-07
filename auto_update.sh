#!/bin/bash
# DATABASE
DB_USER="ma"
DB_PASS="FrameWork5414*"
DB_NAME="MeritAccessLocal"
USER="meritaccess"

# DIRECTORIES
APP_DIR_PYTHON="/home/$USER/merit_access"
APP_DIR_WEB="/var/www/html"
DATABASE_UPDATE_DIR="/home/$USER/database_update"

# UPDATE SOURCE (URL or Github Repository)
WEB_UPDATE="meritaccess/html"
DATABASE_UPDATE="meritaccess/database_update"

# REGEX
GITHUB_REPO_REGEX="^[a-zA-Z0-9_-]+\/[a-zA-Z0-9_-]+$"
URL_REGEX="[(http(s)?):\/\/(www\.)?a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)"

# MISCELLANEOUS
LOG_FILE="/home/$USER/logs/update.log"
PYTHON="/usr/bin/python"
NETWORK_TIMEOUT=30


create_device_node(){
    local device_name=$1
    local major_number=$2
    local minor_number=$3

    if [ ! -e "$device_name" ]; then
        echo "Creating device node: $device_name with major number $major_number and minor number $minor_number"
        sudo mknod "$device_name" c "$major_number" "$minor_number"
    fi
}


create_directories(){
    mkdir -p /home/$USER/logs
    mkdir -p $APP_DIR_PYTHON
    mkdir -p $APP_DIR_WEB
    mkdir -p $DATABASE_UPDATE_DIR
    touch $LOG_FILE
}


log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> $LOG_FILE
}

handle_error() {
    log_message "ERROR: $1"
}

get_update_mode() {
    local SQL_QUERY="SELECT VALUE AS v FROM ConfigDU WHERE property='update_mode';"
    update_mode=$(mysql -u$DB_USER -p$DB_PASS $DB_NAME -se "$SQL_QUERY")
    return "$update_mode"
}
is_github_repo() {
    local input=$1
    if [[ $input =~ $GITHUB_REPO_REGEX ]]; then
        return 0
    else
        return 1
    fi
}


is_url() {
    local input=$1
    if [[ $input =~ $URL_REGEX ]]; then
        return 0
    else
        return 1
    fi
}


get_update_source() {
    local SQL_QUERY="SELECT VALUE AS v FROM ConfigDU WHERE property='appupdate';"
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

download_update(){
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



install_update() {
    local APP_NAME=$1
    local APP_DIR=$2
    local asset_url=$3

    log_message "Updating $APP_NAME"

    unzipped_dir=$(download_update $APP_DIR $asset_url) || { handle_error "Failed to download the update."; return; }

    if [ "$(ls -A $APP_DIR)" ]; then
        rm -r $APP_DIR/* || { handle_error "Failed to remove old version."; return; }
        log_message "Removed old version"
    else
        log_message "No old version installed"
    fi

    cp -r "$APP_DIR"_temp/$unzipped_dir/* $APP_DIR >> $LOG_FILE 2>&1 || { handle_error "Failed to copy new files to application directory."; return; }
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


update_version() {
    local app_name=$1
    local update_source=$2
    local app_dir=$3
    local download_dir=$4
    local extra_update_command=$5
    
    if is_github_repo $update_source; then
        log_message "Downloading from github repository"
        latest_release_info=$(curl -s https://api.github.com/repos/$update_source/releases/latest)
        latest_version=$(fetch_latest_version "$latest_release_info")
        asset_url=$(fetch_asset_url "$latest_release_info")

    elif is_url $update_source; then
        # TO DO
        log_message "Downloading from URL"
        latest_version=""
        asset_url=""
        return 0

    else
        log_message "Wrong update source. Please provide a valid GitHub repository or URL."
        return 1
    fi

    current_version=$(get_current_version "$app_dir/version.txt")
    log_message "Current version: $current_version"
    log_message "Latest version: $latest_version"
    log_message "Asset URL: $asset_url"

    if [ "$latest_version" != "$current_version" ]; then
        log_message "New version available. Updating..."
        install_update "$app_name" $app_dir $download_dir "$asset_url"
        eval "$extra_update_command"
        log_message "Update to version $latest_version completed successfully."
    else
        log_message "You already have the latest version."
    fi
}

update_merit_access_web() {
    update_version "Merit Access Web" "$WEB_UPDATE" "$APP_DIR_WEB" "$DOWNLOAD_DIR_WEB" ""
}

update_merit_access() {
    local update_source=$(get_update_source)
    update_version "Merit Access Python" "$update_source" "$APP_DIR_PYTHON" "$DOWNLOAD_DIR_PYTHON" ""
}

update_database() {
    local mysql_command='mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$DATABASE_UPDATE_DIR/update.sql" || { handle_error "Failed to execute update.sql"; return; }'
    update_version "Database" "$DATABASE_UPDATE" "$DATABASE_UPDATE_DIR" "$DOWNLOAD_DIR_DATABASE" "$mysql_command"
}

# Wiegand device nodes
create_device_node /dev/wie1 240 0
create_device_node /dev/wie2 239 0

# Update
create_directories
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

# Run Merit Access App
# $PYTHON $APP_DIR_PYTHON/main.py || handle_error "Failed to run Merit Access App"
