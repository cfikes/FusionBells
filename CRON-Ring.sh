#!/bin/bash

# Fusion Bells Zoned Ringing

ringZone(){
 curl -k -d key=${1} https://127.0.0.1/app/fusionbells/ringzone.php > /dev/null
}

# For Each Database File Execute CURL request
for f in /var/www/fusionpbx/app/fusionbells/*.db
do
 Filename=${f##*/}
 #Execute Curl in the background to speed up delays
 ringZone ${Filename%.*} &
done