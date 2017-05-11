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

	//FB Includes
	require_once "includes.php";
?>
<link rel="stylesheet" type="text/css" href="css/overides.css">

<?php
function getSyncLog(){
	session_start();
    //GET UUID
    $UUID = $_SESSION["domain_uuid"];
    try {
		//Connect to DB
		$fusionBellDB = new PDO("sqlite:$UUID.db");
		//Pull Logs
		$Result = $fusionBellDB->prepare("SELECT * FROM zoneboxsync ORDER BY ID DESC;");
		//Execute
        $Result->execute();
        if(count($Result,0) != 0) {
			echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
			echo "<tr><th>Source</th><th>Date</th><th>Time</th><th>Schedule</th></tr>";
			foreach($Result as $row){
				echo '<tr>';
				echo '<td class="row_style1"><a href="http://' . $row['Address'] . '" target=_blank>' . $row['Address'] . '</a></td>';
				echo '<td class="row_style1">' . $row['Date'] . '</td>';
				echo '<td class="row_style1">' . $row['Time'] . '</td>';
				echo '<td class="row_style1">' . $row['Schedule'] . '</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
	}
	//Issues connecting
	catch (PDOException $e) {
		print 'Exception : ' . $e->getMessage();
	}
}
?>
<?
	include "includes.php";
?>

<?php navMenu(); ?>
<?php navMenuClose(); ?>

<!--	
<table width='100%' border='0' cellpadding='0' cellspacing='0'>
	<tr>
		<td width='50%'><b>ZoneBox</b><br><br></td>
		<td width='50%' align='right'>
			<a href='fusionbells.php' class='btn btndark'>Dashboard</a>
            <a href='fbsettings.php' class='btn btndark'>Settings</a>
		</td>
	</tr>
</table>
-->


<?php getSyncLog();	?>


<?php
/*
* Integration piece into Fusion PBX
* Contributor(s):
* Mark J Crane <markjcrane@fusionpbx.com>
*/
require_once "resources/footer.php";
?>