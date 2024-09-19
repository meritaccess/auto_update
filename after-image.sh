#!/bin/bash
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "/home/meritaccess/logs/update.log"
}

# add permissions
sudo chown -R meritaccess:meritaccess /var/www/

# change hostname to MDU
mac_address_eth=$(ip link show eth0 | grep ether | awk '{print $2}')
my_mdu=$(echo $mac_address_eth | tr -d ':')
mdu=$(echo "MDU${my_mdu}" | awk '{print toupper($0)}')

log_message "MAC address of eth0 is: $mac_address_eth"
sudo sed -i "s/127.0.1.1[[:space:]]\+cm4/127.0.1.1       $mdu/" "/etc/hosts"
sudo hostnamectl set-hostname $mdu
log_message "Hostname changed"

hostnamectl
cat  /etc/hosts

# expand drive
sudo raspi-config --expand-rootfs
sudo rm -rf home/meritaccess/imgclone
log_message "Drive expanded. Rebooting system."
sudo reboot
