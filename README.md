# Auto Update
Script for handling automatic updates of Merit Access

## Database setup
In ConfigDU table set appupdate=meritaccess/merit_access (github repository)

## Setup
In the script set USER to your linux username and APP_DIR to the location of Merit Access App <br />
Set DB_USER, DB_PASS, DB_NAME to your database credentials

```
sudo apt-get install jq
sudo chmod 777 auto_update.sh
sudo nano /etc/systemd/system/auto_update.service
```
Add this to the file /etc/systemd/system/auto_update.service (set ExecStart= to path to auto_update.sh and user= to your linux username)
```
[Unit]
Description=Merit Access Update Service
After=network.target mysqld.service
Requires=mysqld.service

[Service]
Type=simple
User=meritaccess
Group=meritaccess
WorkingDirectory=/home/meritaccess
ExecStart=/bin/bash /home/meritaccess/auto_update/auto_update.sh
Restart=on.failure

[Install]
WantedBy=multi-user.target
```
Restart services and reboot
```
sudo systemctl daemon-reload
sudo systemctl enable auto_update.service
sudo reboot
```
