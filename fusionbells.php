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

//show the content
	require_once "resources/header.php";
	$document['title'] = $text['title-fusionbells'];

?>

<link rel="stylesheet" type="text/css" href="css/overides.css">

<?php
/*
* Zone Domains
* 
* Christopher Fikes
* 03/10/2017
*/
	require_once "includes.php";
	domainDB();
?>

<?php navMenu(); ?>
				<!-- Actions on this page -->
				<li class="dropdown">
					<a class="dropdown-toggle text-left" data-toggle="dropdown" href="#">
						<span class="glyphicon glyphicon-cog" title="Actions"></span>
						<span class="hidden-sm" style="margin-left: 5px;">Actions</span>
					</a>
					<ul class="dropdown-menu">
						<li><a href="#" onclick="ringbell('Normal');"> Ring Normal</a></li>
						<li><a href="#" onclick="ringbell('FireDrill');"> Fire Drill</a></li>
						<li><a href="#" onclick="ringbell('WeatherDrill');"> Weather Drill</a></li>
						<!--<li role="separator" class="divider"></li>-->
					</ul>
				</li>
<?php navMenuClose(); ?>

<br>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tbody>
		<tr><th class="th" colspan="2" align="left">Current Schedule</th></tr>
		<tr><td width="20%" class="vncell" style="text-align: left;">Current Time</td><td class="row_style1" id="currentTime">00:00</td></tr>
		<tr><td width="20%" class="vncell" style="text-align: left;">Schedule Name</td><td class="row_style1" id="nextSchedule">Normal Bells</td></tr>
		<tr><td width="20%" class="vncell" style="text-align: left;">Next Ring</td><td class="row_style1" id="nextRing">3:00 PM</td></tr>
	</tbody>
</table>



<script src="js/manualoverride.js"></script>

<div align='center'>
<br>
<p>Fusion Bells is developed and provided by FikesMedia</br><img src='img/fikesmedialogo.png' style='height: 28px;'> <img src="img/fblogo.png" style="height:28px;"></p>
</div>


<?php
//
// Domain Test DEVELOPMENT
//
echo "<hr>Zone Information";
echo "<br>NAME " . $_SESSION["domain_name"];
echo "<br>UUID " . $_SESSION["domain_uuid"];

?>


<script>
	/*
	* Gets Current Schedule and Next Ring 
	*
	* Christopher Fikes
	* 03/05/2017
	*/
	function getNextRing() {
		$('#nextSchedule').html("");
		$('#nextRing').html("");
		$.ajax({
			'method' : "POST",
			'url' : "../fusionbells/api.php",
			'context' : document.body,
			'dataType' : 'json',
			'data' : { "call" : "nextring" },
			'success' : function (data) {
				$.each(data, function() {
					$('#nextSchedule').html(this.Schedule);
					$('#nextRing').html(this.Time);
				});   
			},
			'error' : function (jqXHR, exception) {
				console.log(exception);
				var responseText = jQuery.parseJSON(jqXHR.responseText);
				console.log(responseText);
			}
		}).done(function(){
			//Nothing
			console.log("Finished Next Ring");
		});
	}


	/*
	* Gets Current Time 
	*
	* Christopher Fikes
	* 03/05/2017
	*/
	function getTime() {
		$.ajax({
			'method' : "POST",
			'url' : "../fusionbells/api.php",
			'context' : document.body,
			'dataType' : 'json',
			'data' : { "call" : "gettime" },
			'success' : function (data) {
				$.each(data, function() {
					console.log(this.Time);
					$('#currentTime').html(this.Time);
				});   
			}
		}).done(function(){
			//Nothing
		});
	}

//Initial Grab of next getNextRing
getNextRing();
getTime();
//Every 60 Seconds get next ring.
setInterval(function() {
	getNextRing();
	getTime();
}, 60000);
</script>


<?php
//include the footer
	require_once "resources/footer.php";
?>
