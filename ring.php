<?php


/*
* Project Name:  FusionBells for FusionPBX
* 
* Description: Multicast Bell Notification for 
* education environments. Uses FFMPEG for transcoding 
* and network delivery.
*
* Requirements: FusionPBX, SQLite and Multicast  network
* configuration.
*
* Developer: FikesMedia.com 
* Contact: support@fikesmedia.com
* Copyright: Copyright 2017 FikesMedia All Right Reserved
*/


/*
* Checks this moment for bells and rings
*
* Christopher Fikes
* 03/05/2017
*/
$dateNow = date("Y-m-d");
$timeNow = date("H:i");
    try {
        //Connect to DB
        $fusionBellDB = new PDO('sqlite:fusionbells.db');
        $Special = $fusionBellDB->prepare('SELECT calendar.Schedule,schedules.Time,schedules.Tone FROM schedules inner join calendar ON schedules.Schedule = calendar.Schedule WHERE calendar.Date = ? AND schedules.Time >= ? ORDER BY schedules.Time LIMIT 1');
        $Special->execute(array($dateNow,$timeNow));
        //Assign Values to use from results
		if(count($Special,0) != 0) {
			foreach ($Special as $row) {
				$Schedule = $row['Schedule'];
				$Tone = $row['Tone'];
				$Time = $row['Time'];
			}
		}
		//If Results Set Values This is a calendar identified bell time
        if ( isset($Tone) && isset($Time) && isset($Schedule) ) {
            echo "Special Moment\n";
            echo "Ringing $Schedule at $Time Playing $Tone\n";
			//Query Multicast address to use
			$Result = $fusionBellDB->query("SELECT SettingValue FROM settings WHERE SettingName = 'All Tone Address' LIMIT 1;");
			//Set value is any return given
			if(count($Result,0) != 0) {
				foreach($Result as $row){
					$Address = $row['SettingValue'];
				}
			}
			if ( $Time == $timeNow ) {
				//Call System command with specified parameters
				$exec="./ffmpeg -re -i ./tones/$Tone -filter_complex 'aresample=16000,asetnsamples=n=160' -acodec g722 -ac 1 -vn -f rtp udp://$Address &"; 
				exec($exec);
			}
        } else {
            //Not calendar specified schedule, check for default bell
            echo "This Moment is not special.\n";
            $Default = $fusionBellDB->prepare('SELECT settings.SettingValue,schedules.Time,schedules.Tone FROM schedules inner join settings ON schedules.Schedule = settings.SettingValue WHERE schedules.Time = ? ORDER BY schedules.Time LIMIT 1');
            $Default->execute(array($timeNow));
			//Set value is any return given
			if(count($Default,0) != 0){
				foreach($Default as $row){
					$Schedule = $row['SettingValue'];
					$Tone = $row['Tone'];
					$Time = $row['Time'];
				}
			}
            if ( isset($Tone) && isset($Time) && isset($Schedule) ) {
                echo "Default Moment\n";
                echo "Ringing Default Schedule $Schedule at $Time Playing $Tone\n";
				//Query Multicast address to use
				$Result = $fusionBellDB->query("SELECT SettingValue FROM settings WHERE SettingName = 'All Tone Address' LIMIT 1;");
				//Set value is any return given
				if(count($Result,0) != 0) {
					foreach($Result as $row){
						$Address = $row['SettingValue'];
					}
				}
				//Call System command with specified parameters
				$exec="./ffmpeg -re -i ./tones/$Tone -filter_complex 'aresample=16000,asetnsamples=n=160' -acodec g722 -ac 1 -vn -f rtp udp://$Address &"; 
				exec($exec);
            } else {
                echo "NOTING SETUP FOR THIS MOMENT\n";
            }
        }
    }
    //Issues connecting
    catch (PDOException $e) {
		//Print Error Message
        print 'Exception : ' . $e->getMessage();
    }
?>