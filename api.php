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
* Add a non default day and return the ID for
* FullCalendar.io to update view. 
*
* Christopher Fikes
* 03/03/2017
*/
function insertCalendarDay(){
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
    //Grab POST Values
    $day = $_POST['day'];
    $schedule = $_POST['schedule'];
    //Check if values are null, return error if so.
    if( is_null($schedule) || is_null($day) || $schedule == "" || $day == "" ){ 
        //Create JSON Message of Error
        $returnValue = ['msg' => 'error', "error" => "Empty Values Sent DAY $day SCHEDULE $schedule"];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    }//End of Check Null and Empty
    //Check for invalid Lengths, return error if so
    elseif ( strlen($day) != 10 ) {
        //Create JSON Message of Error
        $returnValue = ['msg' => 'error', "error" => "Invalid Date Length"];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    }//End of Check Day Length
    //Compelete the transaction
    else {
        try {
            //Connect to DB and insert posted Values
            $fusionBellDB = new PDO("sqlite:$UUID.db");
            //Prepare Statement for insert
            $Insert = $fusionBellDB->prepare('INSERT INTO calendar (Date,Schedule) VALUES (?,?)');
            //Bind Parameters for Insert and Execute
            $Insert->execute(array($day,$schedule));
            //Fetch back new record information
            //Prepare Statement for Query
            $Return = $fusionBellDB->prepare('SELECT * FROM calendar WHERE Date = ? AND Schedule = ? LIMIT 1');
            //Bind Parameters for Query and Execute
            $Return->execute(array($day,$schedule));
            //Set Variables of Returned Data
            foreach($Return as $row) {
                $RecordID =  $row['ID'];
                $RecordDate = $row['Date'];
                $RecordSchedule = $row['Schedule'];
            }
            //Create JSON Message with Record Values
            $returnValue = ['msg' => 'complete', "recordid" => $RecordID, "recorddate" => $RecordDate, "recordschedule" => $RecordSchedule];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }//End of Try
        //Issues connecting
        catch (PDOException $e) {
            //Set JSON Message with Error Text
            $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }//End of Catch
    }//End of Else of Bad POST Data
}//End of insertCalendarDay Function



/*
* Remove a non default day and return status. 
*
* Christopher Fikes
* 03/03/2017
*/
function removeCalendarDay(){
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
 //Grab POST Values
    $day = $_POST['day'];
    $schedule = $_POST['schedule'];
    //Check if values are null, return error if so.
    if( is_null($schedule) || is_null($day) || $schedule == "" || $day == "" ){ 
        //Create JSON Message of Error
        $returnValue = ['msg' => 'error', "error" => "Empty Values Sent"];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    }//End of Check Null and Empty
    //Check for invalid Lengths, return error if so
    elseif ( strlen($day) != 10 ) {
        //Create JSON Message of Error
        $returnValue = ['msg' => 'error', "error" => "Invalid Date Length"];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    }//End of Check Day Length
    //Compelete the transaction
    else {
        try {
            //Connect to DB and insert posted Values
            $fusionBellDB = new PDO("sqlite:$UUID.db");
            $Return = $fusionBellDB->prepare('DELETE FROM calendar WHERE Date = ? AND Schedule = ?');
            //Bind Parameters for Query and Execute
            $Return->execute(array($day,$schedule));
            //Create JSON Message with Record Values
            $returnValue = ['msg' => 'complete'];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }//End of Try
        //Issues connecting
        catch (PDOException $e) {
            //Set JSON Message with Error Text
            $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }//End of Catch
    }//End of Else of Bad POST Data
}//End of removeCalendarDay Function



/*
* Update a non default day and return status. 
*
* Christopher Fikes
* 03/03/2017
*/
function updateCalendarDay(){
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
 //Grab POST Values
    $day = $_POST['day'];
    $ID = $_POST['ID'];

    //Check if values are null, return error if so.
    if( is_null($ID) || is_null($day) || $day == "" || $ID == "" ){ 
        //Create JSON Message of Error
        $returnValue = ['msg' => 'error', "error" => "Empty Values Sent"];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    }//End of Check Null and Empty
    //Check for invalid Lengths, return error if so
    elseif ( strlen($day) != 10 ) {
        //Create JSON Message of Error
        $returnValue = ['msg' => 'error', "error" => "Invalid Date Length"];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    }//End of Check Day Length
    //Compelete the transaction
    else {
        try {
            //Connect to DB and insert posted Values
            $fusionBellDB = new PDO("sqlite:$UUID.db");
            $Return = $fusionBellDB->prepare('UPDATE calendar SET Date = ? WHERE ID = ?');
            //Bind Parameters for Query and Execute
            $Return->execute(array($day,$ID));
            //Create JSON Message with Record Values
            $returnValue = ['msg' => 'complete'];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }//End of Try
        //Issues connecting
        catch (PDOException $e) {
            //Set JSON Message with Error Text
            $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }//End of Catch
    }//End of Else of Bad POST Data
}//End of updateCalendarDay Function


/*
* Returns All Scheduled Calendar Days in JSON
*  for FullCalendar.io to update view. 
*
* Christopher Fikes
* 03/03/2017
*/
function returnCalendarDays(){
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
    try {
        //Connect to DB and insert posted Values
        $fusionBellDB = new PDO("sqlite:$UUID.db");
        $Return = $fusionBellDB->prepare('SELECT * FROM calendar');
        //Execute
        $Return->execute();
        //Setup Counter
        $i = 0;
        //Loop Through each row creating a separate associate array
        foreach($Return as $row){
            //Format for FullCalendar.io
            $returnJSON[$i] = array("title" => $row['Schedule'], "start" => $row['Date'], "id" => $row['ID'], "allDay" => true);
            $i++;
        }
        //Create JSON Message with Record Values
        header('Content-Type: application/json');
        echo json_encode($returnJSON);
    }//End of Try
    //Issues connecting
    catch (PDOException $e) {
        //Set JSON Message with Error Text
        $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    }//End of Catch
}//End of returnCalendarDays Function




/*
* Plays desired tone from the dashboard
*
* Christopher Fikes
* 03/03/2017
*/
function manualBell() {
	//Check if valid POST Type
    if(is_null($_POST['tone'] || $_POST['tone'] == "")){ die("NoType"); }
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
	try {
        //Connect to DB
        $fusionBellDB = new PDO("sqlite:$UUID.db");
		//Set ring type from POST
		switch ($_POST['tone']) {
			case 'Normal' :
				$toneType = "\"Bell Tone\"";
				break;
			case 'FireDrill' :
				$toneType = "\"Fire Drill Tone\"";
				break;
			case 'WeatherDrill' :
				$toneType = "\"Weather Drill Tone\"";
				break;
			default :
				$toneType = "\"Bell Tone\"";
				break;
		}
		//Query WAV file used for specified tone
		$Result = $fusionBellDB->query("SELECT SettingValue FROM settings WHERE SettingName = $toneType LIMIT 1;");
		//Set value is any return given
		if(count($Result,0) != 0) {
			foreach($Result as $row){
				$tone = $row['SettingValue'];
			}
		}
        //Query if this is the ZoneBOX
        $ActingZoneBox = $fusionBellDB->prepare('SELECT SettingValue FROM settings WHERE SettingName = ? LIMIT 1');
        $ActingZoneBox->execute(array("Acting ZoneBOX"));
		//Set value is any return given
        foreach ($ActingZoneBox as $row) {
                $Setting = $row['SettingValue'];
		}
		//I am the ZoneBox 
		if ($Setting == "Disabled") {
			sendManualBell($tone);
		} 
		//This is the ZoneBox
		elseif ($Setting == "Enabled") {
			//Query Multicast address to use
			$Result = $fusionBellDB->query("SELECT ZoneValue FROM zones WHERE ZoneName = 'All Tone' LIMIT 1;");
			//Set value is any return given
			if(count($Result,0) != 0) {
				foreach($Result as $row){
					$address = $row['ZoneValue'];
				}
			}
			//Call System command with specified parameters
			$exec="./ffmpeg -re -i ./tones/$tone -filter_complex 'aresample=16000,asetnsamples=n=160' -acodec g722 -ac 1 -vn -f rtp udp://$address &"; 
			exec($exec);
		}
	}
	//Issues connecting
	catch (PDOException $e) {
		//Set JSON Message with Error Text
		$returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
		header('Content-Type: application/json');
		echo json_encode($returnValue);
	}
}//End manualBell Function



/*
* Get bell schedule for specified getSchedules
* and return as formated JSON
*
* Christopher Fikes
* 03/03/2017
*/
function getSchedule() {
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
    //Set desired schedule from POST
    $schedule = $_POST['schedule'];
    //Error if Null or blank and send back error message
    if(is_null($schedule) || $schedule == ""){ 
        $returnValue = ['error' => 'no values input for GET Schedule'];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    } else {
        try {
            //Connect to DB
            $fusionBellDB = new PDO("sqlite:$UUID.db");
            //Query for specified schedule
            $Result = $fusionBellDB->prepare('SELECT Schedule,Time,Tone,Zone FROM schedules WHERE Schedule = ? ORDER BY Time');
            $Result->execute(array($schedule));
            //If any results continue
            if(count($Result,0) != 0) {
                //Setup Counter
                $i = 0;
                //For each returned result append array 
                foreach($Result as $row){
                    $returnJSON[$i] = $row;
                    $i++;
                }
                //Return array as formated JSON
                header('Content-Type: application/json');
                echo json_encode($returnJSON);
            }//End if any results continue
        } //End Try
        //Issues connecting
        catch (PDOException $e) {
            //Set JSON Message with Error Text
            $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        } //End capture errors
    } //End Else no Errors
} //End getSchedule function


/*
* Get all zones and return as formated JSON
*
* Christopher Fikes
* 05/09/2017
*/
function getZones(){
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
    try {
        //Connect to DB
        $fusionBellDB = new PDO("sqlite:$UUID.db");
        //Ask for just unique values from schedules table
        $Result = $fusionBellDB->query("SELECT distinct ZoneName FROM zones ORDER BY Schedule ASC;");
        //If any results continue
        if(count($Result,0) != 0) {
            //Setup Counter
            $i = 0;
            //For each returned result append array 
            foreach($Result as $row){
                $returnJSON[$i] = $row;
                $i++;
            }
            //Return array as formated JSON
            header('Content-Type: application/json');
            echo json_encode($returnJSON);
        }//End if any results continue
    }//End Try
    //Issues connecting
    catch (PDOException $e) {
        //Set JSON Message with Error Text
        $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    }
}



/*
* Deletes zone and attached schedules
*
* Christopher Fikes
* 05/09/2017
*/
function delZone(){
	//Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
    //Sets name from POST
	$name = $_POST['zonename'];
    if(is_null($name) || $name == ""){ 
        $returnValue = ['error' => 'no values input for delZone'];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    } else {
        try {
            //Connect to DB
            $fusionBellDB = new PDO("sqlite:$UUID.db");
            //Del zone with post vars
			$delZone = $fusionBellDB->prepare('DELETE FROM zones WHERE ZoneName = ?');
            //Bind Parameters for Query and Execute
            $delZone->execute(array($name));
			$delSchedule = $fusionBellDB->prepare('DELETE FROM schedules WHERE Zone = ?');
            //Bind Parameters for Query and Execute
            $delSchedule->execute(array($name));
            //Return results as formated JSON
            $returnValue = ['Status' => "complete"];
            echo json_encode($returnValue);
        }//End Try DB code
        //Issues connecting
        catch (PDOException $e) {
            //Set JSON Message with Error Text
            $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }//End catch
    }//End Else no errors
}//End delZone


/*
* Get all schedules and return as formated JSON
*
* Christopher Fikes
* 03/03/2017
*/
function getSchedules(){
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
                $returnJSON[$i] = $row;
                $i++;
            }
            //Return array as formated JSON
            header('Content-Type: application/json');
            echo json_encode($returnJSON);
        }//End if any results continue
    }//End Try
    //Issues connecting
    catch (PDOException $e) {
        //Set JSON Message with Error Text
        $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    }
}


/*
* Creates a new schedule by adding a new
* schedule name with a midnight ring.
*
* Christopher Fikes
* 03/03/2017
*/
function createNewSchedule(){
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
    //Sets name from POST
    $name = $_POST['schedulename'];
    //Set first bell to midnight
    $time = "23:59";
    //Check is name is null or blank if true error
    if(is_null($name) || $name == ""){ 
        $returnValue = ['error' => 'no values input for Create New Schedule'];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    } else {
        try {
            //Connect to DB
            $fusionBellDB = new PDO("sqlite:$UUID.db");
            //Get default tone from Settings
            $Result = $fusionBellDB->query("SELECT SettingValue FROM settings WHERE SettingName = 'Bell Tone' Limit 1;");
            foreach($Result as $row){
                $defaultTone = $row['SettingValue'];
            }
            //Insert new bell at midnight using name from post with default tone
            $Result = $fusionBellDB->prepare('INSERT INTO schedules (Schedule,Time,Tone) VALUES (?,?,?)');
            $Result->execute(array($name,$time,$defaultTone));
            //Return array as formated JSON
            $returnValue = ['Status' => 'Completed'];
            echo json_encode($returnValue);
        }//End try DB code
        //Issues connecting
        catch (PDOException $e) {
            //Set JSON Message with Error Text
            $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }//End Catch
    }//End Else no errors
}//End createNewSchedule function



/*
* Deletes all references to schedule 
* including special dates.
*
* Christopher Fikes
* 03/03/2017
*/
function deleteSchedule(){
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
    //Set name from POST
    $schedule = $_POST['schedulename'];
    //If null or blank error and send back error
    if(is_null($schedule) || $schedule == ""){ 
        $returnValue = ['error' => 'no values input for Delete Schedule'];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    } else {
        try {
            //Connect to DB
            $fusionBellDB = new PDO("sqlite:$UUID.db");
            //Delete all from schedules
            $Return = $fusionBellDB->prepare('DELETE FROM schedules WHERE Schedule = ?');
            //Bind Parameters for Query and Execute
            $Return->execute(array($schedule));
            //Delete all from calendar
            $ReturnCal = $fusionBellDB->prepare('DELETE FROM calendar WHERE Schedule = ?');
            //Bind Parameters for Query and Execute
            $ReturnCal->execute(array($schedule));
            //Return results as formated JSON
            $returnValue = ['Status' => "Completed Deletion $schedule"];
            echo json_encode($returnValue);
        }//End Try DB code
        //Issues connecting
        catch (PDOException $e) {
            //Set JSON Message with Error Text
            $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }//End catch
    }//End Else no errors
}//End deleteSchedule function


/*
* Deletes bell from schedule. 
*
* Christopher Fikes
* 03/03/2017
*/
function deleteBell(){
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
    //Get Schedule name and time form POST
    $name = $_POST['schedulename'];
    $time = $_POST['time'];
	$zone = $_POST['zone'];
    //Check for empty values and send back error
    if(is_null($name) || is_null($time) || $name == "" || $time == ""){ 
        $returnValue = ['error' => 'no values input for Delete Schedule'];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    } else {
        try {
            //Connect to DB
            $fusionBellDB = new PDO("sqlite:$UUID.db");
            //Delete bell from above reference
            $Result = $fusionBellDB->prepare('DELETE FROM schedules WHERE Schedule = ? AND Time = ? AND Zone = ?');
            $Result->execute(array($name,$time,$zone));
            //Return Formated JSON
            $returnValue = ['Status' => "Completed"];
            echo json_encode($returnValue);

        }
        //Issues connecting
        catch (PDOException $e) {
            //Set JSON Message with Error Text
            $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }
    }
}



/*
* Creates new bell for schedule. 
*
* Christopher Fikes
* 03/03/2017
*/
function newBell(){
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
    //Get information from POST
    $name = $_POST['schedulename'];
    $time = $_POST['time'];
    $tone = $_POST['tone'];
	$zone = $_POST['zone'];
    //Check for empty values and send back error
    if(is_null($name) || $name == "" || is_null($time) || $time == "" || is_null($tone) || $tone == "" || is_null($zone) || $zone == ""){ 
        $returnValue = ['error' => 'no values input for New Bell'];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    } else {
        try {
            //Connect to DB
            $fusionBellDB = new PDO("sqlite:$UUID.db");
            //Insert into Schedules New Bell
            $Result = $fusionBellDB->prepare('INSERT INTO schedules (Schedule,Time,Tone,Zone) VALUES (?,?,?,?)');
            $Result->execute(array($name,$time,$tone,$zone));
            //Return Formated JSON
            $returnValue = ['Status' => 'Completed'];
            echo json_encode($returnValue);
        }
        //Issues connecting
        catch (PDOException $e) {
            //Set JSON Message with Error Text
            $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }
    }
}



/*
* Save Setting
*
* Christopher Fikes
* 03/03/2017
*/
function saveSetting(){
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
 //Grab POST Values
    $settingName = $_POST['settingName'];
    $settingValue = $_POST['settingValue'];
    //Check if values are null, return error if so.
    if( is_null($settingName) || is_null($settingValue) || $settingName == "" || $settingValue == "" ){ 
        //Create JSON Message of Error
        $returnValue = ['msg' => 'error', "error" => "Empty Values Sent"];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    }//End Check Values
    else {
        try {
            //Connect to DB and insert posted Values
            $fusionBellDB = new PDO("sqlite:$UUID.db");
            $Return = $fusionBellDB->prepare('UPDATE settings SET SettingValue = ? WHERE SettingName = ?');
            //Bind Parameters for Query and Execute
            $Return->execute(array($settingValue,$settingName));
            //Create JSON Message with Record Values
            $returnValue = ['msg' => 'complete'];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }//End of Try
        //Issues connecting
        catch (PDOException $e) {
            //Set JSON Message with Error Text
            $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }//End of Catch
    }//End of Else of Bad POST Data
}//End of saveSetting Function


/*
* Save Zone
*
* Christopher Fikes
* 05/10/2017
*/
function saveZone(){
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
 //Grab POST Values
    $zoneName = $_POST['zoneName'];
    $zoneValue = $_POST['zoneValue'];
    //Check if values are null, return error if so.
    if( is_null($zoneName) || is_null($zoneValue) || $zoneName == "" || $zoneValue == "" ){ 
        //Create JSON Message of Error
        $returnValue = ['msg' => 'error', "error" => "Empty Values Sent"];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    }//End Check Values
    else {
        try {
            //Connect to DB and insert posted Values
            $fusionBellDB = new PDO("sqlite:$UUID.db");
            $Return = $fusionBellDB->prepare('UPDATE zones SET ZoneValue = ? WHERE ZoneName = ?');
            //Bind Parameters for Query and Execute
            $Return->execute(array($zoneValue,$zoneName));
            //Create JSON Message with Record Values
            $returnValue = ['msg' => 'complete'];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }//End of Try
        //Issues connecting
        catch (PDOException $e) {
            //Set JSON Message with Error Text
            $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }//End of Catch
    }//End of Else of Bad POST Data
}//End of saveZone Function




/*
* New Zone
*
* Christopher Fikes
* 05/10/2017
*/
function createNewZone(){
    //Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
 //Grab POST Values
    $zoneName = $_POST['zoneName'];
    $zoneValue = $_POST['zoneValue'];
    //Check if values are null, return error if so.
    if( is_null($zoneName) || is_null($zoneValue) || $zoneName == "" || $zoneValue == "" ){ 
        //Create JSON Message of Error
        $returnValue = ['msg' => 'error', "error" => "Empty Values Sent"];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    }//End Check Values
    else {
        try {
            //Connect to DB and insert posted Values
            $fusionBellDB = new PDO("sqlite:$UUID.db");
            $Return = $fusionBellDB->prepare('INSERT INTO zones (ZoneName,ZoneValue) VALUES (?,?)');
            //Bind Parameters for Query and Execute
            $Return->execute(array($zoneName,$zoneValue));
            //Create JSON Message with Record Values
            $returnValue = ['msg' => 'complete'];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }//End of Try
        //Issues connecting
        catch (PDOException $e) {
            //Set JSON Message with Error Text
            $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        }//End of Catch
    }//End of Else of Bad POST Data
}//End of saveZone Function



/*
* Get All Tones and return as formated JSON
*
* Christopher Fikes
* 03/03/2017
*/
function getTones(){
    $dir = __DIR__ . '/tones';
    $tones = scandir($dir);
    $returnValue = array();
    $i = 0;
    foreach($tones as $key => $value) {
        if (!is_dir("$dir/$value")) {
            $returnValue[$i] = array("file" => $value);
            $i++;
        }
    }
    header('Content-Type: application/json');
    echo json_encode($returnValue);
}



/*
* Create new tone from TTS Engine
*
* Christopher Fikes
* 03/03/2017
*/
function createTTSTone(){
    //Setup Variables
    $dir = __DIR__ . '/tones';
    $saveName = $_POST['filename'];
    error_log(" THIS IS THE SAVE DIR  $dir/$saveName");
    $text = $_POST['text'];
    if(!is_null($saveName) || $saveName == "" | !is_null($text) || $text == "") {
        //Call System command with specified parameters
        $exec = "/usr/bin/pico2wave -w $dir/$saveName \"$text\" &"; 
        exec($exec);
        //Return Status
        $returnValue = ['msg' => 'complete'];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    } else {
        error_log("VALUES: $dir  $saveName  $text");
    }
} //End createTTSTone



/*
* Deletes tone from library
*
* Christopher Fikes
* 03/05/2017
*/
function delTone() {
	//Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
    $dir = __DIR__ . '/tones';
    $file = $_POST['filename'];
    $defaultTone = "";
    try {
        //Connect to DB and insert posted Values
        $fusionBellDB = new PDO("sqlite:$UUID.db");
        //Get Default Tone
        $Default = $fusionBellDB->prepare('SELECT SettingName, SettingValue FROM settings WHERE SettingName = ?');
        //Bind Parameters for Query and Execute
        $Default->execute(array("Bell Tone"));
        if(count($Default,0) != 0){
            foreach($Default as $row){
                $defaultTone = $row['SettingValue'];
            }
        } 
    }//End of Try
    //Issues connecting
    catch (PDOException $e) {
        //Set JSON Message with Error Text
        $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
        header('Content-Type: application/json');
        echo json_encode($returnValue);
    }//End of Catch
    if(!is_null($file) || $file == "" || $file == $defaultTone) {
        if (!unlink("$dir/$file")) {
            $returnValue = ['msg' => 'error'];
            header('Content-Type: application/json');
            echo json_encode($returnValue);
        } else {
            try {
                $Update = $fusionBellDB->prepare('UPDATE schedules SET Tone = ? WHERE Tone = ?');
                //Bind Parameters for Query and Execute
                $Update->execute(array($defaultTone,$file));
                //Create JSON Message with Record Values
                $returnValue = ['msg' => 'complete'];
                header('Content-Type: application/json');
                echo json_encode($returnValue);
            }//End of Try
            //Issues connecting
            catch (PDOException $e) {
                //Set JSON Message with Error Text
                $returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
                header('Content-Type: application/json');
                echo json_encode($returnValue);
            }//End of Catch
        }
    }
}


/*
* Gets the next bell ring for today.
*
* Christopher Fikes
* 03/06/2017
*/
function getNextRing() {
	//Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
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
				//Check to see if there are any more bells in thes schedule for today
				$Special02 = $fusionBellDB->prepare("SELECT count(*) FROM schedules WHERE Schedule = ? AND Time > ?");
				$Special02->execute(array($Schedule,$timeNow));
				$Count = $Special02->fetchColumn();
				//If results found, schedule has bells left
				if($Count != 0) {
					$Special03 = $fusionBellDB->prepare("SELECT * FROM schedules WHERE Schedule = ? AND Time > ? ORDER BY Time LIMIT 1");
					$Special03->execute(array($Schedule,$timeNow));
					while ($row = $Special03->fetch(PDO::FETCH_ASSOC)) {
						$Schedule = $row['Schedule'];
						$Tone = $row['Tone'];
						$Time = $row['Time'];
						$returnJSON[0]=array("Schedule"=>$Schedule,"Time"=>$Time);
					}
				}
				//Else Schedule has played out.
				else {
					$returnJSON[0]=array("Schedule"=>$Schedule,"Time"=>"Schedule Finished");
				}
			}
		} 
		//Today has no schedules set using default
		else {
			$Default01 = $fusionBellDB->prepare("SELECT SettingValue FROM settings WHERE SettingName = ? ");
			$Default01->execute(array("Default Schedule"));
			while ($row = $Default01->fetch(PDO::FETCH_ASSOC)) {
				$Schedule = $row['SettingValue'];
				//Check to see if there are any more bells in thes schedule for today
				$Default02 = $fusionBellDB->prepare("SELECT count(*) FROM schedules WHERE Schedule = ? AND Time > ?");
				$Default02->execute(array($Schedule,$timeNow));
				$Count = $Default02->fetchColumn();
				//If results found, schedule has bells left
				if($Count != 0) {
					$Special03 = $fusionBellDB->prepare("SELECT * FROM schedules WHERE Schedule = ? AND Time > ? ORDER BY Time LIMIT 1");
					$Special03->execute(array($Schedule,$timeNow));
					while ($row = $Special03->fetch(PDO::FETCH_ASSOC)) {
						$Schedule = $row['Schedule'];
						$Tone = $row['Tone'];
						$Time = $row['Time'];
						$returnJSON[0]=array("Schedule"=>$Schedule,"Time"=>$Time);
					}
				}
				//Else Schedule has played out.
				else {
					$returnJSON[0]=array("Schedule"=>$Schedule,"Time"=>"Schedule Finished");
				}
			}
		}
		header('Content-Type: application/json');
		echo json_encode($returnJSON);
	}
    //Issues connecting
    catch (PDOException $e) {
		//Print Error Message
        print 'Exception : ' . $e->getMessage();
    }
}//End getNextRing Function



/*
* Time and Date
*
* Christopher Fikes
* 03/06/2017
*/
function getTime() {
    $dateNow = date("Y-m-d");
    $timeNow = date("H:i");
    $returnJSON[0]=array("Time"=>$timeNow,"Date"=>$dateNow);
    //Return array as formated JSON
    header('Content-Type: application/json');
    echo json_encode($returnJSON);
}



/*
* ZoneBOX Send Manual Bell
*
* Christopher Fikes
* 03/06/2017
*/
function sendManualBell($Tone){
	session_start();
	//Get UUID
	$UUID = $_SESSION['domain_uuid'];
	//End if tone not set
	if (is_null($Tone) || $Tone == "") {
		exit();
	} 
	else {
		try {
			//Connect to DB
			$fusionBellDB = new PDO("sqlite:$UUID.db");
			$Manual = $fusionBellDB->prepare("SELECT Address FROM zoneboxsync ORDER BY ID DESC LIMIT 1");
			$Manual->execute();
			while ($row = $Manual->fetch(PDO::FETCH_ASSOC)) {
				$zoneBoxAddress = $row['Address'];
			}
		}
		//Issues connecting
		catch (PDOException $e) {
			//Set JSON Message with Error Text
			error_log( $e->getMessage() );
		} //End capture errors
		
		//Send Request to ZoneBOX
		$ch = curl_init();
		$URL = "http://$zoneBoxAddress/manual.php";
		//Build CURL Settings
		curl_setopt($ch, CURLOPT_URL, $URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//Set mode to POST
		curl_setopt($ch, CURLOPT_POST, true);
		//Setup data to send
		$data = array('tone' => "$Tone", 'key'=> "$UUID");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		//Send
		$output = curl_exec($ch);
		//Close
		curl_close($ch);
	}
}


/*
* ZoneBOX Send Offline Schedule
*
* Christopher Fikes
* 03/06/2017
*/
function sendOfflineSchedule() {
	$dateNow = date("Y-m-d");
    $timeNow = date("H:i");
	$Client = $_SERVER['REMOTE_ADDR'];
	$UUID = $_POST['key'];
	require "includes.php";
	$schedule = getCurrentScheduleInclude($UUID);
	if(is_null($schedule) || $schedule == "" || is_null($UUID) || $UUID == ""){ 
        error_log($_SERVER['REMOTE_ADDR'] . " provided invalid information.");
    } else {
		error_log("CONTACTED:   $dateNow  $timeNow  $Client  $UUID");
		//Prevent False DB's from getting created.
		if (file_exists("$UUID.db")) {
			try {
				//Connect to DB
				$fusionBellDB = new PDO("sqlite:$UUID.db");
				//Query for specified schedule
				$Result = $fusionBellDB->prepare('SELECT Schedule,Time,Tone FROM schedules WHERE Schedule = ? AND Zone = ? ORDER BY Time');
				$Result->execute(array($schedule,"All Tone"));
				//If any results continue
				if(count($Result,0) != 0) {
					//Setup Counter
					$i = 0;
					//For each returned result append array 
					foreach($Result as $row){
						$returnJSON[$i] = $row;
						$i++;
					}
					//Update Sync Table
					updateSyncEntry($UUID,$Client,$dateNow,$timeNow,$schedule);
					//Return array as formated JSON
					header('Content-Type: application/json');
					echo json_encode($returnJSON);
				}//End if any results continue
			} //End Try
			//Issues connecting
			catch (PDOException $e) {
				//Set JSON Message with Error Text
				error_log( $e->getMessage() );
			} //End capture errors
		}
	} //End Else
}//End Send Offline Schedule



//Begin Operations by grabbing 
if (!empty($_POST)) {
    switch ($_POST['call']) {
        case 'ring' :
        checkForBellNow();
            break;
        case 'manual' :
            manualBell();
            break;
        case 'readschedule' :
            getSchedule();
            break;
        case 'readschedules' :
            getSchedules();
            break;
        case 'newschedule' :
            createNewSchedule();
            break;
        case 'delschedule' :
            deleteSchedule();
            break;
        case 'delbell' :
            deleteBell();
            break;
        case 'newbell' :
            newBell();
            break;
        case 'addcalendarday' :
            insertcalendarday();
            break;
        case 'delcalendarday' :
            removecalendarday();
            break;
        case 'movecalendarday' :
            updateCalendarDay();
            break;
        case 'getcalendardays' : 
            returnCalendarDays();
            break;
        case 'savesetting' :
            saveSetting();
            break;
		case 'savezone' :
            saveZone();
            break;
        case 'gettones' :
            getTones();
            break;
        case 'newtts' :
            createTTSTone();
            break;
        case 'deltone' :
            delTone();
            break;
        case 'nextring' :
            getNextRing();
            break;
        case 'gettime' :
            getTime();
            break;
		case 'offlineschedule' :
			sendOfflineSchedule();
			break;
		case 'getzones' :
			getZones();
			break;
		case 'delzone' :
			delZone();
			break;
		case 'newzone' :
			createNewZone();
			break;
    }
} else {
    echo "No Valid POST";
}


?>