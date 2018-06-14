![alt text](https://raw.githubusercontent.com/cfikes/FusionBells/master/img/fblogo.png)

FusionPBX Multicast Bell System

#  Installation

For Debian Installations clone into

* cd /var/www/fusionpbx/app
* git clone https://github.com/cfikes/FusionBells fusionbells

Fix Permissions 

* chown -R www-data:www-data /var/www/fusionpbx/app/fusionbells
* chmod +x /var/www/fusionpbx/app/fusionbells/ffmpeg 

Install TTS Engine for TTS Generations

* echo "deb http://ftp.de.debian.org/debian sid main non-free" >> /etc/apt/sources.list

* apt-get update && apt-get -y install libttspico-utils

Add Through FusionPBX a Menu to 

* /app/fusionbells/fusionbells.php

# Version 1.0 GUI Demo

https://www.youtube.com/watch?v=KD0SaYbUtKs&feature=youtu.be
