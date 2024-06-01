# Auto Update
Script for handling automatic updates of Merit Access

## Setup
```
sudo chmod 777 auto_update.sh
```
Create a new file /etc/systemd/system/auto_update.service with the following content:
```
[Unit]
Description=Run auto_update.sh at startup
After=network.target

[Service]
Type=simple
User=bantj
ExecStart=/home/bantj/auto_update.sh

[Install]
WantedBy=default.target
```
Run
```
sudo systemctl daemon-reload
sudo systemctl enable auto_update.service
sudo reboot
```
