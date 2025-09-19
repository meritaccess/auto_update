#!/bin/bash
# DATABASE
DB_USER="ma"
DB_PASS="FrameWork5414*"
DB_NAME="MeritAccessLocal"
USER="meritaccess"
HOSTNAME=$(hostname)

# DIRECTORIES
APP_DIR_PYTHON="/home/$USER/merit_access"
APP_DIR_WEB="/var/www/html"
DATABASE_UPDATE_DIR="/home/$USER/database_update"
EXTRA_SCRIPT_DIR="/home/$USER/extra_script"
AFTER_IMAGE="/home/$USER/after-image.sh"

# UPDATE SOURCE (URL or Github Repository)
MERIT_ACCESS_UPDATE="merit_access"
WEB_UPDATE="html"
DATABASE_UPDATE="database_update"
EXTRA_SCRIPT="extra_script"

# REGEX
GITHUB_REPO_REGEX="^[a-zA-Z0-9_-]+\/[a-zA-Z0-9_-]+$"
URL_REGEX="[(http(s)?):\/\/(www\.)?a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b(:[0-9]{1,5})?([-a-zA-Z0-9@:%_\+.~#?&\/=]*)"
IP_REGEX="^(http(s)?:\/\/)?((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){3}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})(:[0-9]{1,5})?(\/[a-zA-Z0-9\$\-\_\.\+\!\*\'\(\)\,\/]*)?$"

# MISCELLANEOUS
LOG_FILE="/home/$USER/logs/update.log"
PYTHON="/usr/bin/python"
NETWORK_TIMEOUT=30
UPDATE_SUCCESSFUL=1
FACTORY_DEFAULT_DIR="/home/$USER/auto_update/factory_default"
HOLD_CONFIG_BTN_TIME_S=10
FACTORY_RESET=0

# PINS
SYS_LED_RED=0
SYS_LED_GREEN=1
SYS_LED_BLUE=2
CONFIG_BTN=3

# UPDATE
set_led_color() {
  local red_value=$1
  local green_value=$2
  local blue_value=$3

  pigs p $SYS_LED_RED "$red_value"
  pigs p $SYS_LED_GREEN "$green_value"
  pigs p $SYS_LED_BLUE "$blue_value"
}

execute_script(){
    local script=$1
    if [ -e "$script" ]; then
        chmod +x "$script"
        sudo "$script"
    fi
}

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
    mkdir -p $EXTRA_SCRIPT_DIR
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
    update_mode=$(mysql -u$DB_USER -p"$DB_PASS" $DB_NAME -se "$SQL_QUERY")
    return "$update_mode"
}

is_github_repo() {
    local input=$1
    if [[ $input =~ $GITHUB_REPO_REGEX ]]; then
        return 0
    fi
    return 1
}

is_url() {
    local input=$1
    if [[ $input =~ $URL_REGEX ]]; then
        return 0
    elif [[ $input =~ $IP_REGEX ]]; then
        return 0
    fi
    return 1
}

get_update_source() {
    local SQL_QUERY="SELECT VALUE AS v FROM ConfigDU WHERE property='appupdate';"
    repo=$(mysql -u$DB_USER -p"$DB_PASS" $DB_NAME -se "$SQL_QUERY")
    echo "$repo"
}

split_url() {
    local full_url=$1
    local url="${full_url%%:*}"
    local port="${full_url##*:}"
    if [ "$url" = "$port" ]; then
        port=""
    fi
    echo "$url" "$port"
}

check_network() {
    local url=$1
    local port=$2

    if [ -z "$port" ]; then
        ping -c 1 -W 1 "$url" &> /dev/null
    else
        nc -z "$url" "$port" &> /dev/null
    fi
}

wait_for_network() {
    read -r url port <<< "$(split_url "$1")"
    log_message "Starting network check."
    SECONDS=0
    while ! check_network "$url" "$port"; do
        if [ $SECONDS -ge $NETWORK_TIMEOUT ]; then
            handle_error "Network timeout after $NETWORK_TIMEOUT seconds. Maybe port $port is not open."
            return 1
        fi
        log_message "Waiting for network..."
        sleep 5
    done
    log_message "Network is up."
    return 0
}

fetch_latest_version() {
    echo "$1" | jq -r .tag_name | tr -d '[:space:]'
}

fetch_asset_url() {
    echo "$1" | jq -r .zipball_url | tr -d '[:space:]'
}

download_update(){
    local app_dir=$1
    local asset_url=$2
    local asset_name="update.zip"

    mkdir -p "$app_dir"_temp || { handle_error "Failed to create download directory."; return 1; }

    curl -L -o "$app_dir"_temp/$asset_name "$asset_url" || { handle_error "Failed to download the update."; return 1; }
    log_message "Download successful."

    unzip -o "$app_dir"_temp/$asset_name -d "$app_dir"_temp >> $LOG_FILE 2>&1 || { handle_error "Failed to unzip the update package."; return 1; }
    log_message "Unzip successful."

    unzipped_dir=$(unzip -Z -1 "$app_dir"_temp/$asset_name | head -n 1 | cut -d '/' -f 1) || { handle_error "Failed to get unzipped directory"; return 1; }
    log_message "Unzipped directory: $unzipped_dir"

    echo "$unzipped_dir"
}

install_update() {
    local app_name=$1
    local app_dir=$2
    local asset_url=$3

    log_message "Updating $app_name"

    unzipped_dir=$(download_update "$app_dir" "$asset_url") || { handle_error "Failed to download the update."; return 1; }
    if [ -z "$unzipped_dir" ]; then
        handle_error "Failed to get unzipped directory."
        return 1
    fi

    if [ "$(ls -A "$app_dir")" ]; then
        rm -rf "$app_dir"/{*,.[^.]*,..?*} || { handle_error "Failed to remove old version."; return 1; }
        log_message "Removed old version"
    else
        log_message "No old version installed"
    fi

    cp -r "$app_dir"_temp/"$unzipped_dir"/* "$app_dir" >> $LOG_FILE 2>&1 || { handle_error "Failed to copy new files to application directory."; return; }
    log_message "Copied new files to application directory."

    rm -rf "$app_dir"_temp || { handle_error "Failed to remove temporary download directory."; return 1; }
    log_message "Removed temporary download directory."

    if [ -e "$app_dir"/requirements.txt ]; then
        pip install --no-index --find-links="$app_dir"/pip_packages/ -r "$app_dir"/requirements.txt || { handle_error "Failed to install required Python packages."; return 1; }
        log_message "Installed required Python packages."
    fi
    UPDATE_SUCCESSFUL=0
    return 0

}

get_current_version(){
    local VERSION_FILE=$1
    if [ -f "$VERSION_FILE" ]; then
        current_version=$(cat "$VERSION_FILE")
    else
        current_version="none"
    fi
    echo "$current_version" | tr -d '[:space:]'
}

update_version() {
    local app_name=$1
    local update_source=$2
    local app_dir=$3
    local extra_update_command=$4
    
    if is_github_repo "$update_source"; then
        log_message "Downloading from github repository"
        latest_release_info=$(curl -s https://api.github.com/repos/"$update_source"/releases/latest)
        latest_version=$(fetch_latest_version "$latest_release_info")
        asset_url=$(fetch_asset_url "$latest_release_info")

    elif is_url "$update_source"; then
        log_message "Downloading from URL"
        # check if version.txt exists
        if curl --output /dev/null --silent --head --fail "$update_source/version.txt"; then
            latest_version=$(curl -L -s "$update_source"/version.txt)
        else
            log_message "Update URL for $app_name not reachable. Add version.txt"
            return 1
        fi
        
        # check if update.zip exists
        if curl --output /dev/null --silent --head --fail "$update_source/update.zip"; then
            asset_url=$update_source/update.zip
        else
            log_message "Update URL for $app_name not reachable. Add update.zip"
            return 1
        fi
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
        install_update "$app_name" "$app_dir" "$asset_url"
        if [ $UPDATE_SUCCESSFUL -ne 0 ]; then
            log_message "Update was NOT successful"
            return 1
        fi
        eval "$extra_update_command"
        log_message "Update to version $latest_version completed successfully."
    else
        log_message "You already have the latest version."
    fi
}

update_merit_access_web() {
    update_version "Merit Access Web" "$update_source/$WEB_UPDATE" "$APP_DIR_WEB" ""
    UPDATE_SUCCESSFUL=1
}

update_merit_access() {
    update_version "Merit Access Python" "$update_source/$MERIT_ACCESS_UPDATE" "$APP_DIR_PYTHON" ""
    UPDATE_SUCCESSFUL=1
}

update_database() {
    local mysql_command='mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$DATABASE_UPDATE_DIR/update.sql"' || { handle_error "Failed to execute update.sql"; return 1; }
    update_version "Database" "$update_source/$DATABASE_UPDATE" "$DATABASE_UPDATE_DIR" "$mysql_command"
    UPDATE_SUCCESSFUL=1
}

update_extra_script() {
    update_version "Extra Script" "$update_source/$EXTRA_SCRIPT" "$EXTRA_SCRIPT_DIR" ""
    UPDATE_SUCCESSFUL=1
}

# FACTORY RESET
read_pin() {
    local pin=$1
    pigs r "$pin"
}

detect_factory_reset() {
    local hold=${HOLD_CONFIG_BTN_TIME_S:-10}
    local pin="$CONFIG_BTN"
    
    if [ "$(read_pin "$pin")" -ne 0 ]; then
        log_message "No factory reset detected"
        return 1
    fi
    
    local start now elapsed
    start=$(date +%s)
    
    while true; do
        if [ "$(read_pin "$pin")" -ne 0 ]; then
            log_message "No factory reset detected"
            return 1
        fi
        now=$(date +%s)
        elapsed=$(( now - start ))
        
        if [ "$elapsed" -ge "$hold" ]; then
            FACTORY_RESET=1
            for _ in $(seq 1 3); do
                set_led_color 255 0 0
                sleep 0.2
                set_led_color 0 0 0
                sleep 0.2
            done

            set_led_color 255 0 0
            factory_reset
            return 0
        fi
        
        sleep 0.1
    done
}

install_factory_default() {
    local app_name=$1
    local app_dir=$2
    local factory_default=$3

    log_message "Installing factory default $app_name"

    if [ -z "$factory_default" ]; then
        handle_error "Failed to get factory_default directory."
        return 1
    fi

    cp -r "$factory_default"/* "$app_dir" >> $LOG_FILE 2>&1 || { handle_error "Failed to copy new files to application directory."; return; }
    log_message "Copied new files to application directory."

    if [ -e "$app_dir"/requirements.txt ]; then
        pip install --no-index --find-links="$app_dir"/pip_packages/ -r "$app_dir"/requirements.txt || { handle_error "Failed to install required Python packages."; return 1; }
        log_message "Installed required Python packages."
    fi
    return 0
}

recreate_dirs() {
    log_message "Deleting directories"
    rm -rf $APP_DIR_PYTHON $APP_DIR_WEB $DATABASE_UPDATE_DIR $EXTRA_SCRIPT_DIR || { handle_error "Failed to delete directories"; }
    log_message "Creating directories"
    mkdir -p $APP_DIR_PYTHON $APP_DIR_WEB $DATABASE_UPDATE_DIR $EXTRA_SCRIPT_DIR || { handle_error "Failed to create directories"; }
}

factory_reset() {
    log_message "Starting factory reset"

    recreate_dirs

    # Database
    log_message "Dropping database"
    if ! [ -z "$(mysql -u"$DB_USER" -p"$DB_PASS" -e "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '$DB_NAME';")" ]; then
    mysql -u"$DB_USER" -p"$DB_PASS" -e "DROP DATABASE $DB_NAME;" || { handle_error "Failed to drop database"; }
    else
        log_message "$DB_NAME does not exist, skipping DROP"
    fi
    
    install_factory_default "Database" $DATABASE_UPDATE_DIR "$FACTORY_DEFAULT_DIR/database_update"
    log_message "Creating database"
    mysql -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE $DB_NAME" || { handle_error "Failed to CREATE DATABASE $DB_NAME"; }
    mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME"  < $DATABASE_UPDATE_DIR/create.sql || { handle_error "Failed to run create.sql"; }
    log_message "Updating database"
    mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < $DATABASE_UPDATE_DIR/update.sql || { handle_error "Failed to run update.sql"; }

    # Merit Access
    install_factory_default "Merit Access" $APP_DIR_PYTHON "$FACTORY_DEFAULT_DIR/merit_access"
    
    # HTML
    install_factory_default "HTML" $APP_DIR_WEB "$FACTORY_DEFAULT_DIR/html"

    # Extra Script
    install_factory_default "Extra Script" $EXTRA_SCRIPT_DIR "$FACTORY_DEFAULT_DIR/extra_script"

    log_message "Finished factory reset"
}


# Check factory reset
set_led_color 255 255 255
pigs m "$CONFIG_BTN" r       # set as input
pigs pud "$CONFIG_BTN" u     # enable pull-up
detect_factory_reset

if [ $FACTORY_RESET -eq 1 ]; then
    set_led_color 255 255 255
    $PYTHON $APP_DIR_PYTHON/main.py || handle_error "Failed to run Merit Access App"
    exit 
fi

# Run after-image
if [ "$HOSTNAME" == "cm4" ] || [ "$HOSTNAME" == "MDUD83ADD06DB00" ]; then
    set_led_color 255 0 0
    execute_script $AFTER_IMAGE
    set_led_color 255 255 255
fi

set_led_color 0 0 255
# Wiegand device nodes
create_device_node /dev/wie1 240 0
create_device_node /dev/wie2 239 0

# Update
create_directories
get_update_mode
update_mode=$?
log_message "Update mode: $update_mode"

if [ $update_mode -eq 0 ]; then

        update_source=$(get_update_source)

        if is_github_repo "$update_source"/test; then
            wait_for_network "github.com":443
            network_status=$?
        else
            wait_for_network "$update_source"
            network_status=$?
        fi

    if [ $network_status -eq 0 ]; then
        update_database
        update_merit_access
        update_merit_access_web
        update_extra_script
    fi
fi

# Execute extra script
execute_script "$EXTRA_SCRIPT_DIR/extra_script.sh"

set_led_color 255 255 255
# Run Merit Access App
$PYTHON $APP_DIR_PYTHON/main.py || handle_error "Failed to run Merit Access App"
