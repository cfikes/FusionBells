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


//Yealink XML Browser Plugin




/*
* Plays desired tone from the dashboard
*
* Christopher Fikes
* 03/03/2017
*/
function manualBell($UUID,$TONE) {
	try {
        //Connect to DB
        $fusionBellDB = new PDO("sqlite:$UUID.db");
		//Set ring type from POST
		switch ($TONE) {
			case 'Normal' :
				$toneType = "\"Bell Tone\"";
				break;
			case 'FireDrill' :
				$toneType = "\"Fire Drill Tone\"";
				break;
			case 'WeatherDrill' :
				$toneType = "\"Weather Drill Tone\"";
				break;
			case 'Silence' :
				$toneType = "Silence";
				break;
			default :
				$toneType = "\"Bell Tone\"";
				break;
		}
		if ($toneType !="Silence") {
			//Query WAV file used for specified tone
			$Result = $fusionBellDB->query("SELECT SettingValue FROM settings WHERE SettingName = $toneType LIMIT 1;");
			//Set value is any return given
			if(count($Result,0) != 0) {
				foreach($Result as $row){
					$tone = $row['SettingValue'];
				}
			}
		} else { $tone = "Silence.wav"; }
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
			header('Content-Type: text/xml');
			echo '<YealinkIPPhoneTextScreen Timeout="5" LockIn="no" Beep="no" ><Title wrap="yes">Fusion Bells</Title><Text>Completed Action</Text></YealinkIPPhoneTextScreen>';
	}
	//Issues connecting
	catch (PDOException $e) {
	}
}//End manualBell Function


/*
* Get bell schedule for specified getSchedules
* and return as formated JSON
*
* Christopher Fikes
* 05/04/2017
*/
function getSchedule($UUID,$SCHEDULE) {
    $schedule = $SCHEDULE;
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
            $Result = $fusionBellDB->prepare('SELECT Time,Tone,Zone FROM schedules WHERE Schedule = ? ORDER BY Time');
            $Result->execute(array($schedule));
            //If any results continue
            if(count($Result,0) != 0) {
				//Array 
				$returnJSON = array();
                //Setup Counter
                $i = 0;
                //For each returned result append array 
                foreach($Result as $row){
					$Text = $row['Time'] . "  " . substr($row['Tone'],0,-4) . "  " . $row['Zone'];
                    array_push($returnJSON, $Text);
                    $i++;
                }
                //Return array as formated JSON
                header('Content-Type: application/json');
                return $returnJSON;
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
* Get Zones and return as formated JSON
*
* Christopher Fikes
* 05/10/2017
*/
function getZones($UUID) {
	try {
		//Connect to DB
		$fusionBellDB = new PDO("sqlite:$UUID.db");
		//Query for specified schedule
		$Result = $fusionBellDB->prepare('SELECT distinct ZoneName,ZoneValue FROM zones ORDER BY ZoneName');
		$Result->execute();
		//If any results continue
		if(count($Result,0) != 0) {
			//Array 
			$returnJSON = array();
			//Setup Counter
			$i = 0;
			//For each returned result append array 
			foreach($Result as $row){
				$Text = $row['ZoneName'] . "  " . $row['ZoneValue'];
				array_push($returnJSON, $Text);
				$i++;
			}
			//Return array as formated JSON
			header('Content-Type: application/json');
			return $returnJSON;
		}//End if any results continue
	} //End Try
	//Issues connecting
	catch (PDOException $e) {
		//Set JSON Message with Error Text
		$returnValue = [ "msg" => "error", "error" => $e->getMessage() ];
		header('Content-Type: application/json');
		echo json_encode($returnValue);
	} //End capture errors
} //End getZones function


/*
* Gets the next bell ring for today.
*
* Christopher Fikes
* 03/06/2017
*/
function getNextRing($UUID) {
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
						$returnJSON=array("Schedule"=>$Schedule,"Time"=>$Time);
					}
				}
				//Else Schedule has played out.
				else {
					$returnJSON=array("Schedule"=>$Schedule,"Time"=>"Schedule Finished");
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
						$returnJSON=array("Schedule"=>$Schedule,"Time"=>$Time);
					}
				}
				//Else Schedule has played out.
				else {
					$returnJSON=array("Schedule"=>$Schedule,"Time"=>"Schedule Finished");
				}
			}
		}
		
		//header('Content-Type: application/json');
		return $returnJSON;
	}
    //Issues connecting
    catch (PDOException $e) {
		//Print Error Message
        print 'Exception : ' . $e->getMessage();
    }
}//End getNextRing Function


function sendManualMenu($UUID){
	$SERVER = $_SERVER['SERVER_ADDR'];
	
	header('Content-Type: text/xml');
	echo '<YealinkIPPhoneTextMenu style="numbered" Beep="no" WrapList="yes" Timeout="30" LockIn="yes">';
	echo '<Title Warp="yes">Manual Bell Control</Title>';
	echo '<MenuItem>';
	echo '<Prompt> Normal Bell</Prompt>';
	echo '<URI>https://'.$SERVER.'/app/fusionbells/xmlbrowser.php?app=manual&amp;tone=Normal&amp;uuid='.$UUID.'</URI>';
	echo '</MenuItem>';
	echo '<MenuItem>';
	echo '<Prompt> Fire Drill</Prompt>';
	echo '<URI>https://'.$SERVER.'/app/fusionbells/xmlbrowser.php?app=manual&amp;tone=FireDrill&amp;uuid='.$UUID.'</URI>';
	echo '</MenuItem>';
	echo '<MenuItem>';
	echo '<Prompt> Weather Drill</Prompt>';
	echo '<URI>https://'.$SERVER.'/app/fusionbells/xmlbrowser.php?app=manual&amp;tone=WeatherDrill&amp;uuid='.$UUID.'</URI>';
	echo '</MenuItem>';
	echo '<MenuItem>';
	echo '<Prompt> Silent Tone Diagnostics</Prompt>';
	echo '<URI>https://'.$SERVER.'/app/fusionbells/xmlbrowser.php?app=manual&amp;tone=Silence&amp;uuid='.$UUID.'</URI>';
	echo '</MenuItem>';
	echo '</YealinkIPPhoneTextMenu>';
	
}

function devTest($UUID) {
	/*
	$Dev01 = getNextRing($UUID);
	echo $Dev01['Schedule'];
	echo $Dev01['Time'];
	echo "<br>";
	$Schedule = getSchedule($UUID,$Dev01['Schedule']);
	echo "Count of Array  " . count($Schedule) . "<br>";
	print_r($Schedule);
	*/
	sendSchedule($UUID);
}

function sendSchedule($UUID){
	$active = getNextRing($UUID);
	$scheduleName = $active['Schedule'];
	$nextRing = $active['Time'];
	$schedule = getSchedule($UUID,$scheduleName);
	
	header('Content-Type: text/xml');
	echo '<YealinkIPPhoneTextMenu style="none" Timeout="15" LockIn="no" Beep="no">';
	echo '<Title Warp="yes">'. $scheduleName .'</Title>';
	echo '<SoftKey index = "1"><Label>Exit</Label><URI>SoftKey:Exit</URI></SoftKey>';
	
	$n = 0;
	$count = count($schedule);
	while ($n < $count) {
		echo '<MenuItem><Prompt>' . $schedule[$n] . '</Prompt><URI></URI></MenuItem>';
		$n++;
	}
	echo '</YealinkIPPhoneTextMenu>';
}


function sendZones($UUID){
	$zones = getZones($UUID);
	
	header('Content-Type: text/xml');
	echo '<YealinkIPPhoneTextMenu style="none" Timeout="15" LockIn="no" Beep="no">';
	echo '<Title Warp="yes">Available Zones</Title>';
	echo '<SoftKey index = "1"><Label>Exit</Label><URI>SoftKey:Exit</URI></SoftKey>';
	
	$n = 0;
	$count = count($zones);
	while ($n < $count) {
		echo '<MenuItem><Prompt>' . $zones[$n] . '</Prompt><URI></URI></MenuItem>';
		$n++;
	}
	echo '</YealinkIPPhoneTextMenu>';
}


function sendYealinkError() {
	header('Content-Type: text/xml');
		echo '<YealinkIPPhoneTextScreen Timeout="15" LockIn="no" Beep="no"><Title wrap="yes">Fusion Bells </Title><Text> ERROR PLEASE SEE ADMINISTRATOR </Text>';
		echo '</YealinkIPPhoneTextScreen >';
}

function sendYealinkMenu($UUID) {
	$SERVER = $_SERVER['SERVER_ADDR'];
	header('Content-Type: text/xml');
	echo '<YealinkIPPhoneTextScreen Timeout="15" LockIn="no" Beep="no"><Title wrap="yes">Fusion Bells </Title><Text> Developed by FikesMedia Powered by FusionPBX</Text>';
	echo '<SoftKey index = "1"><Label>Exit</Label><URI>SoftKey:Exit</URI></SoftKey>';
	echo '<SoftKey index = "2"><Label>Schedule</Label><URI>https://'.$SERVER.'/app/fusionbells/xmlbrowser.php?app=schedule&amp;uuid='. $UUID . '</URI></SoftKey>';
	echo '<SoftKey index = "3"><Label>Zones</Label><URI>https://'.$SERVER.'/app/fusionbells/xmlbrowser.php?app=zones&amp;uuid='. $UUID . '</URI></SoftKey>';
	echo '<SoftKey index = "4"><Label>Manual</Label><URI>https://'.$SERVER.'/app/fusionbells/xmlbrowser.php?app=manualmenu&amp;uuid='. $UUID . '</URI></SoftKey>';
	echo '</YealinkIPPhoneTextScreen >';
}


/*
* MAIN
* Get Variables from submission and route data
*
*/
$UUID = $_GET['uuid'];
$APP = $_GET['app'];
$TONE = $_GET['tone'];

if (!empty($UUID)) {

	switch($APP) {
		case 'manual' :
			manualBell($UUID,$TONE);
			//echo "VALUES  $TONE   $UUID";
			break;
		case 'manualmenu' :
			sendManualMenu($UUID);
			break;
		case 'dev' :
			devTest($UUID);
			break;
		case 'schedule' :
			sendSchedule($UUID);
			break;
		case 'zones' :
			sendZones($UUID);
			break;
		default :
			sendYealinkMenu($UUID);
			break;
			
	}

} else {
	sendYealinkError();
}




