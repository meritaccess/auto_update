# Auto Update
Script for handling automatic updates of Merit Access

## Setup
In the script set USER to your linux username
```
sudo apt-get install jq
sudo chmod 777 auto_update.sh
sudo nano /etc/systemd/system/auto_update.service
```
Add this to the file /etc/systemd/system/auto_update.service (set ExecStart= to path to auto_update.sh and user= to your linux username)
```
[Unit]
Description=Run auto_update.sh at startup
After=network.target

[Service]
Type=simple
User=meritaccess
ExecStart=/home/meritaccess/auto_update/auto_update.sh

[Install]
WantedBy=multi-user.target
```
Restart services and reboot
```
sudo systemctl daemon-reload
sudo systemctl enable auto_update.service
sudo reboot
```
