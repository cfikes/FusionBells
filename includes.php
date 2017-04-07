<?php

function selectTone(){
    $dir = __DIR__ . '/tones';
    $tones = scandir($dir);
    foreach($tones as $key => $value) {
        if (!is_dir("$dir/$value")) {
            echo '<option value="'. $value . '">'. $value . '</option>';
        }
    }
}

//For Calendar View
function getSchedulesInclude(){
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
    try {
        //Connect to DB
        $fusionBellDB = new PDO("sqlite:$UUID.db");
        //Ask for just unique values from schedules table
        $Result = $fusionBellDB->query("SELECT distinct Schedule FROM schedules ORDER BY Schedule ASC;");
        //If any results continue
        if(count($Result,0) != 0) {
            //Setup Counter
            $i = 0;
            //For each returned result append array 
            foreach($Result as $row){
                echo "<div class='fc-event' schedule='". $row['Schedule'] . "'>". $row['Schedule'] . "</div>";
            }
        }//End if any results continue
    }//End Try
    //Issues connecting
    catch (PDOException $e) {
        //Set JSON Message with Error Text
        echo $e->getMessage();
    }
}

//Create Database for NEW Domain
function domainDB(){
	session_start();
	//Grab Domain from SESSION
	$domain = $_SESSION["domain_name"];
	$UUID = $_SESSION["domain_uuid"];

	//If DB already exist stop
	if(file_exists("$UUID.db")) {
		
	} else {
		try {
			//Connect to DB
			$fusionBellDB = new PDO("sqlite:$UUID.db");
			//Create Tables
			$fusionBellDB->exec("CREATE TABLE IF NOT EXISTS settings (ID INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, SettingName TEXT NOT NULL UNIQUE, SettingValue TEXT NOT NULL)");
			$fusionBellDB->exec("CREATE TABLE IF NOT EXISTS schedules (ID INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, Schedule TEXT NOT NULL, Time TEXT NOT NULL, Tone TEXT NOT NULL)");
			$fusionBellDB->exec("CREATE TABLE IF NOT EXISTS calendar (ID INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, Date TEXT NOT NULL, Schedule TEXT NOT NULL)");
			$fusionBellDB->exec("CREATE TABLE IF NOT EXISTS zoneboxsync (ID INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, Address TEXT NOT NULL, Date TEXT NOT NULL, Time TEXT NOT NULL, Schedule TEXT NOT NULL)");
			//Insert Sample Schedule
			$fusionBellDB->exec("INSERT INTO schedules (Schedule, Time, Tone) VALUES ('Sample Schedule', '23:59','NO_TONE.WAV')");
			$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('All Tone Address','239.255.0.1:4100')");
			$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('Default Schedule','Sample Schedule')");
			$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('Bell Tone','DefaultTone.wav')");
			$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('Fire Drill Tone','DefaultTone.wav')");
			$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('Fire Tone','DefaultTone.wav')");
			$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('Weather Drill Tone','DefaultTone.wav')");
			$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('Weather Tone','DefaultTone.wav')");
			$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('Acting ZoneBOX','Enabled')");
			
		}
		//Issues connecting
		catch (PDOException $e) {
			//Print Error Message
			print 'Exception : ' . $e->getMessage();
		}
	}
}


//Get current schedule
function getCurrentScheduleInclude($UUID) {
	if (is_null($UUID) || $UUID == "") {
		error_log("getCurrentScheduleInclude was not provided a UUID");
	}
	else {
		$dateNow = date("Y-m-d");
		$timeNow = date("H:i");
		try {
			//Connect to DB
			$fusionBellDB = new PDO("sqlite:$UUID.db");
			//Select cout of schedules on this day
			$Special = $fusionBellDB->prepare("SELECT count(*) FROM calendar WHERE Date = ?");
			$Special->execute(array($dateNow));
			$Count = $Special->fetchColumn();
			//If result found, today has a specified schedule
			if($Count != 0) {
				//Get Schedule name for today
				$Special01 =  $fusionBellDB->prepare("SELECT Schedule FROM calendar WHERE Date = ? LIMIT 1"); 
				$Special01->execute(array($dateNow));
				while ($row = $Special01->fetch(PDO::FETCH_ASSOC)) {
					$Schedule = $row['Schedule'];
				}
				return $Schedule;
			} 
			//Nothing from above, return Default Schedule Name
			else {
				$Default01 = $fusionBellDB->prepare("SELECT SettingValue FROM settings WHERE SettingName = ? ");
				$Default01->execute(array("Default Schedule"));
				while ($row = $Default01->fetch(PDO::FETCH_ASSOC)) {
					$Schedule = $row['SettingValue'];
				}
				return $Schedule;
			}
		}
		//Issues connecting
		catch (PDOException $e) {
			//Print Error Message
			error_log($e->getMessage());
		}
	}
}

//Update Sync Entry For ZoneBOX
function updateSyncEntry($UUID,$Address,$Date,$Time,$Schedule) {
	if (is_null($UUID) || $UUID == "" || is_null($Address) || $Address == "" || is_null($Date) || $Date == "" || is_null($Time) || $Time == "" || is_null($Schedule) || $Schedule == "") {
		error_log("updateSyncEntry was not provided information");
	}
	else {
		try {
			//Connect to DB
			$fusionBellDB = new PDO("sqlite:$UUID.db");
			//Update ZoneBoxSync Table  -  Address, Date, Time, Schedule
			$Update = $fusionBellDB->prepare("INSERT INTO zoneboxsync (Address,Date,Time,Schedule) VALUES (?,?,?,?)");
			$Update->execute(array($Address,$Date,$Time,$Schedule));
			$fusionBellDB->exec("DELETE FROM zoneboxsync WHERE ID NOT IN ( SELECT ID FROM ( SELECT ID FROM zoneboxsync ORDER BY ID DESC LIMIT 15 ) x )");
		}
		//Issues connecting
		catch (PDOException $e) {
			//Print Error Message
			error_log($e->getMessage());
		}
	}
}

?>