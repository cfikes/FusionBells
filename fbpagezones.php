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
function pullZones(){
	//Begin Session
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
    try {
            //Connect to DB
            $fusionBellDB = new PDO("sqlite:$UUID.db");
            
            //Pull Settings
            $Result = $fusionBellDB->query("SELECT * FROM zones ORDER BY ZoneName ASC;");
            if(count($Result,0) != 0) {
                echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
                foreach($Result as $row){
                    $ZoneName = $row['ZoneName'];
                    $ZoneValue = $row['ZoneValue'];
                    
					//Check for special form control needs
					switch($ZoneName) {

						//Protect All Tone from Deletion
						case 'All Tone' :
							echo '<tr><td width="20%" class="vncell" style="text-align: left;">' . $ZoneName . '</td><td colspan="2" class="row_style1"><input type="text" class="form-control" name="'. $ZoneName . '" value ="' . $ZoneValue .  '"/></td></tr>';
							break;

						default:
							echo '<tr><td width="20%" class="vncell" style="text-align: left;">' . $ZoneName . '</td><td class="row_style1"><input type="text" class="form-control" name="'. $ZoneName . '" value ="' . $ZoneValue .  '"/></td><td width="5%"><a class="btn btndark" onclick="delZone(\'' . $ZoneName . '\');">Delete</a></td></tr>';
							break;

					}


                }
                echo "</table>";

				echo "</br><a class='btn btndark' id='savebtn' onclick='saveZones();'>Save Settings</a>";
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
	echo "<b>Fusion Bells Zones</b>";
	echo "<br><br>";
	echo "</td>\n";
	echo "<td width='50%' align='right'>\n";
	echo "<a href='fusionbells.php' class='btn btndark'>Dashboard</a> ";
	echo "<a href='fbsettings.php' class='btn btndark'>Settings</a> ";
	echo "<a data-toggle='modal' data-target='#newZoneModal' class='btn btndark'>New Zone</a> ";
	echo "<a href='fbzonebox.php' class='btn btndark'>ZoneBox</a> ";
	echo "</td></tr>";	
	echo "</table>";
	
    pullZones();
?>

    <!-- 
        New Zone Modal

        Christopher Fikes
        05/10/2017
     -->
    <div class="modal fade" id="newZoneModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="gridSystemModalLabel">Create New Zone</h4>
        </div>
        <div class="modal-body">
            <table class="table table-condensed table-borderless">  
                <th>Zone Name</th><th>Zone Address</td>
                <tr><td><input class="form-control" type="text" id="newZoneName"></td><td><input class="form-control" type="text" id="newZoneValue"></td></tr>
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btndark" onclick='createNewZone();'>Create New Zone</button>
        </div>
        </div>
    </div>
    </div>


<script>
	/*
	* Save Settings through API. 
	*
	* Christopher Fikes
	* 03/03/2017
	*/
	function saveZones(){

		var zoneCount = $('.form-control').length;
		zoneCount = zoneCount-2;
		console.log("Zone Count " + zoneCount);
		var i = 0;

		$('.form-control').each(function(){
			var zoneName = $(this).attr("name");
			var zoneValue = $(this).val();
			console.log(zoneName + " " + zoneValue);
			if( $(this).val() != "" ) {
				$("#savebtn").html("Saving Zones. . .");
				$("#savebtn").addClass("disabled");
				$.ajax({
					'method' : "POST",
					'url' : "../fusionbells/api.php",
					'context' : document.body,
					'dataType' : 'json',
					'data' : { "call" : "savezone", "zoneName" : zoneName, "zoneValue" : zoneValue},
					'success' : function (data) {
						console.log(data);
						  if( data.msg = "complete") {
							  i++;
							if ( zoneCount == i ) {
								//Reactivat Button
								$("#savebtn").removeClass("disabled");
								$("#savebtn").html("Save Zones");
							}	
						  }
					}
				}).done(function(){
					//Nothing
				});
			}
 		});
	}
	
	
	/*
	* Create Zone through API. 
	*
	* Christopher Fikes
	* 05/10/2017
	*/
	function createNewZone(){
			var zoneName = $("#newZoneName").val();
			var zoneValue = $("#newZoneValue").val();
			console.log(zoneName + " " + zoneValue);
			$.ajax({
				'method' : "POST",
				'url' : "../fusionbells/api.php",
				'context' : document.body,
				'dataType' : 'json',
				'data' : { "call" : "newzone", "zoneName" : zoneName, "zoneValue" : zoneValue},
				'success' : function (data) {
					console.log(data);
					  if( data.msg = "complete") {
							location.reload();
					  }
				}
			}).done(function(){
				//Nothing
			});
	}
		
	/*
	* Delete Zone through API. 
	*
	* Christopher Fikes
	* 05/10/2017
	*/
	function delZone(zoneName){
			console.log(zoneName);
			$.ajax({
				'method' : "POST",
				'url' : "../fusionbells/api.php",
				'context' : document.body,
				'dataType' : 'json',
				'data' : { "call" : "delzone", "zonename" : zoneName},
				'success' : function (data) {
					console.log(data);
					  if( data.msg = "complete") {
							location.reload();
					  }
				}
			}).done(function(){
				//Nothing
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