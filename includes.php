<?php

function selectToneOLD(){
    $dir = __DIR__ . '/tones';
    $tones = scandir($dir);
    foreach($tones as $key => $value) {
        if (!is_dir("$dir/$value")) {
            echo '<option value="'. $value . '">'. $value . '</option>';
        }
    }
}

function selectTone(){
	$UUID = $_SESSION["domain_uuid"];
	$dir = __DIR__ . "/tones/$UUID";
	$tones = scandir($dir);
	foreach($tones as $key => $value) {
		if(!is_dir("$dir/$value")) {
			echo '<option value="' . $value . '">'. $value . '</option>';
		}
	}
}

//Copy Tones into private DIR
function copyTones(){
	$UUID = $_SESSION["domain_uuid"];
	$dir = __DIR__ . "/tones/$UUID";
	mkdir("tones/$UUID");
	copy("tones/DefaultTone.wav","tones/$UUID/DefaultTone.wav");
	copy("tones/FireDrill.wav","tones/$UUID/FireDrill.wav");
	copy("tones/WeatherDrill.wav","tones/$UUID/WeatherDrill.wav");
}

function selectZone(){
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
    try {
        //Connect to DB
        $fusionBellDB = new PDO("sqlite:$UUID.db");
        //Ask for just unique values from schedules table
        $Result = $fusionBellDB->query("SELECT distinct ZoneName FROM zones ORDER BY ZoneName ASC;");
        //If any results continue
        if(count($Result,0) != 0) {
            //For each returned result append array 
            foreach($Result as $row){
				echo '<option value="'. $row['ZoneName'] . '">'. $row['ZoneName'] . '</option>';
            }
        }//End if any results continue
    }//End Try
    //Issues connecting
    catch (PDOException $e) {
        //Set JSON Message with Error Text
        echo $e->getMessage();
    }
}

//DrawNavMenu
function navMenu(){
	echo '<nav class="navbar navbar-inverse">';
	echo '<div class="container-fluid" style="width: calc(100% - 20px); padding: 0;">';
	echo '<div class="navbar-header">';
	echo '<button type="button" class="navbar-toggle collapsed" style="margin-right: -2%;" data-toggle="collapse" data-target="#second_navbar" aria-expanded="false" aria-controls="navbar">';
	echo '<span class="sr-only">Toggle navigation</span>';
	echo '<span class="icon-bar" style="margin-top: 1px;"></span>';
	echo '<span class="icon-bar"></span>';
	echo '<span class="icon-bar"></span>';
	echo '</button>';
	echo '<a><img id="fb_brand_image" class="navbar-logo" style="margin-right: -2%;" src="img/fblogo.png" title="Fusion Bells"></a>';
	echo '</div>';
	echo '<div class="collapse navbar-collapse" id="second_navbar">';
	echo '<ul class="nav navbar-nav navbar-right">';
	echo '<li class="dropdown">';
	echo '<a class="dropdown-toggle text-left" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-send" title="Menu"></span><span class="hidden-sm" style="margin-left: 5px;">Menu</span></a>';
	echo '<ul class="dropdown-menu">';
	echo '<li><a href="fusionbells.php"> Dashboard</a></li>';
	echo '<li><a href="fbschedule.php"> Schedule Calendar</a></li>';
	echo '<li><a href="fbscheduleedit.php"> Schedule Editor</a></li>';
	echo '<li role="separator" class="divider"></li>';
	echo '<li><a href="fbsettings.php"> Settings</a></li>';
	echo '<li><a href="fbtones.php"> Tone Editor</a></li>';
	echo '<li><a href="fbpagezones.php"> Zones</a></li>';
	echo '<li><a href="fbzonebox.php"> ZoneBox</a></li>';
	echo '</ul></li>';
}

function navMenuClose(){
	echo '</ul></div></div></nav>';
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
			$fusionBellDB->exec("CREATE TABLE IF NOT EXISTS zones (ID INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, ZoneName TEXT NOT NULL UNIQUE, ZoneValue TEXT NOT NULL)");
			$fusionBellDB->exec("CREATE TABLE IF NOT EXISTS schedules (ID INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, Schedule TEXT NOT NULL, Time TEXT NOT NULL, Tone TEXT NOT NULL, Zone TEXT)");
			$fusionBellDB->exec("CREATE TABLE IF NOT EXISTS calendar (ID INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, Date TEXT NOT NULL, Schedule TEXT NOT NULL)");
			$fusionBellDB->exec("CREATE TABLE IF NOT EXISTS zoneboxsync (ID INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, Address TEXT NOT NULL, Date TEXT NOT NULL, Time TEXT NOT NULL, Schedule TEXT NOT NULL)");
			//Insert Sample Schedule
			$fusionBellDB->exec("INSERT INTO schedules (Schedule, Time, Tone, Zone) VALUES ('Sample Schedule', '23:59','NO_TONE.WAV','All Tone')");
			//$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('All Tone Address','239.255.0.1:4100')");
			$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('Default Schedule','Sample Schedule')");
			$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('Bell Tone','DefaultTone.wav')");
			$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('Fire Drill Tone','DefaultTone.wav')");
			$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('Fire Tone','DefaultTone.wav')");
			$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('Weather Drill Tone','DefaultTone.wav')");
			$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('Weather Tone','DefaultTone.wav')");
			$fusionBellDB->exec("INSERT INTO settings (SettingName,SettingValue) VALUES ('Acting ZoneBOX','Enabled')");
			//Added for Zoning
			$fusionBellDB->exec("INSERT INTO zones (ZoneName,ZoneValue) VALUES ('All Tone','239.255.0.1:4100')");
			$fusionBellDB->exec("INSERT INTO zones (ZoneName,ZoneValue) VALUES ('Sample Zone','239.255.0.1:4101')");
			copyTones();
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
