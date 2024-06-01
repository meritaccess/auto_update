# Auto Update
Script for handling automatic updates of Merit Access

## Setup
```
sudo chmod 777 auto_update.sh
sudo nano /etc/systemd/system/auto_update.service
```
Add this to the file /etc/systemd/system/auto_update.service
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
Restat services and reboot
```
sudo systemctl daemon-reload
sudo systemctl enable auto_update.service
sudo reboot
```
