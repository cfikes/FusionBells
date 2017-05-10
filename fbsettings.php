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
* Integration piece into Fusion PBX
* Contributor(s):
* Mark J Crane <markjcrane@fusionpbx.com>
*/
include "root.php";
require_once "resources/require.php";
require_once "resources/check_auth.php";

if (permission_exists('system_status_sofia_status')
	|| permission_exists('system_status_sofia_status_profile')
	|| if_group("superadmin")) {
	//access granted
}
else {
	echo "access denied";
	exit;
}
//add multi-lingual support
	$language = new text;
	$text = $language->get();

/*
* NOTE:
* Page needs to be updated to operate over AJAX and API Calls
*
* Christopher Fikes
* 03/05/2017
*/


//Functions for query
function pullSettings(){
	//Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
    try {
            //Connect to DB
            $fusionBellDB = new PDO("sqlite:$UUID.db");
            
            //Pull Settings
            $Result = $fusionBellDB->query("SELECT * FROM settings ORDER BY SettingName ASC;");
            if(count($Result,0) != 0) {
                echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
                foreach($Result as $row){
                    $SettingName = $row['SettingName'];
                    $SettingValue = $row['SettingValue'];
                    
					//Check for special form control needs
					switch($SettingName) {

						//Bell Tones need to be selected from the FS. Scans the FS for tone files
						//and creates the select group selecting the default if one found.
						case 'Bell Tone' :
							$dir = __DIR__ . '/tones';
							$tones = scandir($dir);
							echo '<tr><td width="20%" class="vncell" style="text-align: left;">' . $SettingName . '</td><td class="row_style1">';
							echo '<select name="'. $SettingName .'" class="form-control">';
							//echo '<select name="'. preg_replace('/\s+/', '', $SettingName) .'" class="form-control">';
							foreach($tones as $key => $value) {
								if (!is_dir("$dir/$value")) {
									if ($value == $SettingValue) {
										echo '<option selected value="'. $value . '">'. $value . '</option>';
									} else {
										echo '<option value="'. $value . '">'. $value . '</option>';
									}
								}
							}
							echo '</select>';
							echo '</td></tr>';
							break;
						
						//Schedules need to be selected from the DB. List unique schedules
						//and creates the select group selecting the default if one found.
						case 'Default Schedule' :
							echo '<tr><td width="20%" class="vncell" style="text-align: left;">' . $SettingName . '</td><td class="row_style1">';
							$ResultSchedule = $fusionBellDB->query("SELECT distinct Schedule FROM schedules ORDER BY Schedule ASC;");
							if(count($ResultSchedule,0) !=0) {
								echo '<select name="'. $SettingName .'" class="form-control">';
								//echo '<select name="'. preg_replace('/\s+/', '', $SettingName) .'" class="form-control">';
								foreach($ResultSchedule as $row){
									$ScheduleName = $row['Schedule'];
										if ($SettingValue == $ScheduleName) {
											echo '<option selected value="'. $ScheduleName . '">'. $ScheduleName . '</option>';
										} else {
											echo '<option value="'. $ScheduleName . '">'. $ScheduleName . '</option>';
										}
									}
								echo '</select>';
							} else {
								echo '<select disabled name="DefaultSchedule" class="form-control">';
								echo '<option value="NO">No Schedules Created</option>';
								echo '</select>';
							}
							echo '</td></tr>';
							break;
						
						//Tones need to be selected from the FS. Scans the FS for tone files
						//and creates the select group selecting the default if one found.
						case 'Fire Drill Tone' :
							$dir = __DIR__ . '/tones';
							$tones = scandir($dir);
							echo '<tr><td width="20%" class="vncell" style="text-align: left;">' . $SettingName . '</td><td class="row_style1">';
							echo '<select name="'. $SettingName .'" class="form-control">';
							//echo '<select name="'. preg_replace('/\s+/', '', $SettingName) .'" class="form-control">';
							foreach($tones as $key => $value) {
								if (!is_dir("$dir/$value")) {
									if ($value == $SettingValue) {
										echo '<option selected value="'. $value . '">'. $value . '</option>';
									} else {
										echo '<option value="'. $value . '">'. $value . '</option>';
									}
								}
							}
							echo '</select>';
							echo '</td></tr>';
							break;

						//Tones need to be selected from the FS. Scans the FS for tone files
						//and creates the select group selecting the default if one found.
						case 'Fire Tone' :
							$dir = __DIR__ . '/tones';
							$tones = scandir($dir);
							echo '<tr><td width="20%" class="vncell" style="text-align: left;">' . $SettingName . '</td><td class="row_style1">';
							echo '<select name="'. $SettingName .'" class="form-control">';
							//echo '<select name="'. preg_replace('/\s+/', '', $SettingName) .'FireTone" class="form-control">';
							foreach($tones as $key => $value) {
								if (!is_dir("$dir/$value")) {
									if ($value == $SettingValue) {
										echo '<option selected value="'. $value . '">'. $value . '</option>';
									} else {
										echo '<option value="'. $value . '">'. $value . '</option>';
									}
								}
							}
							echo '</select>';
							echo '</td></tr>';
							break;

						//Tones need to be selected from the FS. Scans the FS for tone files
						//and creates the select group selecting the default if one found.
						case 'Weather Drill Tone' :
							$dir = __DIR__ . '/tones';
							$tones = scandir($dir);
							echo '<tr><td width="20%" class="vncell" style="text-align: left;">' . $SettingName . '</td><td class="row_style1">';
							echo '<select name="'. $SettingName .'" class="form-control">';
							//echo '<select name="'. preg_replace('/\s+/', '', $SettingName) .'" class="form-control">';
							foreach($tones as $key => $value) {
								if (!is_dir("$dir/$value")) {
									if ($value == $SettingValue) {
										echo '<option selected value="'. $value . '">'. $value . '</option>';
									} else {
										echo '<option value="'. $value . '">'. $value . '</option>';
									}
								}
							}
							echo '</select>';
							echo '</td></tr>';
							break;

						//Tones need to be selected from the FS. Scans the FS for tone files
						//and creates the select group selecting the default if one found.
						case 'Weather Tone' :
							$dir = __DIR__ . '/tones';
							$tones = scandir($dir);
							echo '<tr><td width="20%" class="vncell" style="text-align: left;">' . $SettingName . '</td><td class="row_style1">';
							echo '<select name="'. $SettingName .'" class="form-control">';
							//echo '<select name="'. preg_replace('/\s+/', '', $SettingName) .'" class="form-control">';
							foreach($tones as $key => $value) {
								if (!is_dir("$dir/$value")) {
									if ($value == $SettingValue) {
										echo '<option selected value="'. $value . '">'. $value . '</option>';
									} else {
										echo '<option value="'. $value . '">'. $value . '</option>';
									}
								}
							}
							echo '</select>';
							echo '</td></tr>';
							break;

						//Acting Zone Box
						case 'Acting ZoneBOX' :
							echo '<tr><td width="20%" class="vncell" style="text-align: left;">' . $SettingName . '</td><td class="row_style1">';
							echo '<select name="'. $SettingName .'" class="form-control">';
							if ($SettingValue == "Enabled") {
								echo '<option selected value="Enabled">Enabled</option>';
								echo '<option value="Disabled">Disabled</option>';
							} elseif ($SettingValue == "Disabled") {
								echo '<option value="Enabled">Enabled</option>';
								echo '<option selected value="Disabled">Disabled</option>';
							} else {
								echo '<option value="Enabled">Enabled</option>';
								echo '<option value="Disabled">Disabled</option>';
							}
							
							echo '</select>';
							echo '</td></tr>';
							break;

						default:
							echo '<tr><td width="20%" class="vncell" style="text-align: left;">' . $SettingName . '</td><td class="row_style1"><input type="text" class="form-control" name="'. $SettingName . '" value ="' . $SettingValue .  '"/></td></tr>';
							break;

					}


                }
                echo "</table>";

				echo "</br><a class='btn btndark' id='savebtn' onclick='saveSettings();'>Save Settings</a>";
            }
		}
    //Issues connecting
        catch (PDOException $e) {
            print 'Exception : ' . $e->getMessage();
        }
    
}

//show the content
	require_once "resources/header.php";
	$document['title'] = $text['title-fusionbells'];
	echo '<link rel="stylesheet" type="text/css" href="css/overides.css">';


	//header
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
	echo "<tr>\n";
	echo "<td width='50%'>\n";
	echo "<b>Fusion Bells Settings</b>";
	echo "<br><br>";
	echo "</td>\n";
	echo "<td width='50%' align='right'>\n";
	echo "<a href='fusionbells.php' class='btn btndark'>Dashboard</a> ";
	echo "<a href='fbpagezones.php' class='btn btndark'>Zones</a> ";
	echo "<a href='fbtones.php' class='btn btndark'>Tone Editor</a> ";
	echo "<a href='fbzonebox.php' class='btn btndark'>ZoneBox</a> ";
	echo "</td></tr>";	
	echo "</table>";
	
    pullSettings();
?>

<script>
	/*
	* Save Settings through API. 
	*
	* Christopher Fikes
	* 03/03/2017
	*/
	function saveSettings(){

		var settingCount = $('.form-control').length;
		console.log(settingCount);
		var i = 0;

		$('.form-control').each(function(){
			var settingName = $(this).attr("name");
			var settingValue = $(this).val();
			console.log(settingName + " " + settingValue);
			if( $(this).val() != "" ) {
				$("#savebtn").html("Saving Settings. . .");
				$("#savebtn").addClass("disabled");
				$.ajax({
					'method' : "POST",
					'url' : "../fusionbells/api.php",
					'context' : document.body,
					'dataType' : 'json',
					'data' : { "call" : "savesetting", "settingName" : settingName, "settingValue" : settingValue},
					'success' : function (data) {
						console.log(data);
						  if( data.msg = "complete") {
							  i++;
							if ( settingCount == i ) {
								//Reactivat Button
								$("#savebtn").removeClass("disabled");
								$("#savebtn").html("Save Settings");
							}	
						  }
					}
				}).done(function(){
					//Nothing
				});
			}
 		});
	}
	

</script>



<?php
/*
* Integration piece into Fusion PBX
* Contributor(s):
* Mark J Crane <markjcrane@fusionpbx.com>
*/
require_once "resources/footer.php";
	
?>