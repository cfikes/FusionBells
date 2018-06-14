#!/bin/bash

#
# Installs FusionBells into an existing FusionPBX installation.
#
echo "FusionBells is provided by FikesMedia"
echo "-------------------------------------"
echo " "
read -p "Continue with installation into [/var/www/fusionpbx/app/]? " -n 1 -r
if [[ $REPLY =~ ^[Yy]$ ]]
then
    cd /var/www/fusionpbx/app/
    git clone https://github.com/cfikes/FusionBells fusionbells
    chown -R www-data:www-data /var/www/fusionpbx/app/fusionbells
    chmod +x /var/www/fusionpbx/app/fusionbells/ffmpeg
    echo "deb http://ftp.de.debian.org/debian sid main non-free" >> /etc/apt/sources.list
    apt-get update && apt-get install apt-get -y install libttspico-utils
    # Give Instructions on adding Menu
    echo "Log into FusionPBX and select Advanced -> Menu Manager -> default"
    echo "Click + and enter the following information."
    echo " "
    echo "-------------------IMPORTANT------------------"
    echo "Title:       FusionBells"
    echo "Link:        /app/fusionbells/fusionbells.php"
    echo "Target:      Internal"
    echo "Icon:        Bell"
    echo "Parent Menu: Home"
    echo "Groups:      superadmin, admin"
    echo "----------------------------------------------"
    echo " "
    echo "Installation Complete"
    echo " "
    read -p "Add cron job for bell? " -n 1 -r
    if [[ $REPLY =~ ^[Yy]$ ]] 
    then
      echo "# FikesMedia FusionBells Zone Ringer" >> /etc/cron.d/fusionbells
      echo "* * * * * root bash /var/www/fusionpbx/app/fusionbells/CRON-Ring.sh" >> /etc/cron.d/fusionbells
      echo " "
      echo "Cron Job Installed."
    fi
fi
