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

<link rel='stylesheet prefetch' href='css/fullcalendar.min.css'>
	
<style>
	.fc-event {
		margin: 5px 0px;
		padding: 0px 5px;
		line-height: 25px;
		height: 25px;
		overflow: hidden;
		}
	.fusionbellcont {
		margin:20px;
	}
	#tips {
		margin-top: 20px;
		}
	#external-events{
		max-height:300px;
		padding-right: 10px;
		overflow-y: scroll;
		overflow-x: hidden !important;
	}
	.table-borderless tbody tr td,
	.table-borderless tbody tr th,
	.table-borderless thead tr th,
	.table-borderless thead tr td,
	.table-borderless tfoot tr th,
	.table-borderless tfoot tr td {
	border: none;
	}
</style>
	
<table width='100%' border='0' cellpadding='0' cellspacing='0'>
	<tr>
		<td width='50%'><b>Schedule Editor</b><br><br></td>
		<td width='50%' align='right'>
			<a href='fusionbells.php' class='btn btndark'>Dashboard</a>
            <a href='fbschedule.php' class='btn btndark'>Schedule Calendar</a>
            <a href='fbsettings.php' class='btn btndark'>Settings</a>
            <a data-toggle="modal" data-target="#newScheduleModal" class='btn btndark'>Add Schedule</a>
		</td>
	</tr>
</table>


      <div class="row">

        <div class="col-md-2">

			<div class="row">
				<h4>Schedules</h4>
				<div id='external-events'>
				  <div id='external-events-listing'>
					<div class='fc-event' schedule="Default">Default</div>
				  </div>
				</div>
			</div>
			
			<div class="row">
				<div id="tips">
					<p>Tip: Select a schedule to view and modify.</p>
				</div>
			</div>

		</div>


        <div class="col-md-10">
			<h4 id="currentSchedule">No Schedule Selected</h4>
			<table class="table table-borderless">
			<tr><td>
				<a type="button" class="btn btndark" aria-label="Left Align" data-toggle="modal" data-target="#newBellModal">
            		<span class="glyphicon glyphicon-bell" aria-hidden="true"></span>
            		New Bell
				</a>
				<a type="button" class="btn btndark" aria-label="Left Align" data-toggle="modal" data-target="#delScheduleModal">
					<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
					Delete Schedule
				</a>
		
			</td></tr>
			</table>

            <table name="thisSchedule" id="thisSchedule" class="table table-hover">
                <thead>
                    <th class="col-xs-3">Time</th><th>Tone</th>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>
      </div>


    <!-- 
        New Bell Modal

        Christopher Fikes
        03/03/2017
     -->
    <div class="modal fade" id="newBellModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="gridSystemModalLabel">Add New Bell</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <table class="table table-condensed table-borderless">
                    <th class="col-xs-3">Hour</th><th class="col-xs-3">Minute</th><th>Ring Tone</th>
                    <tr>
                        <td><input class="form-control" id="hour" type="text" value="00" name="hour"></td>
                        <td><input class="form-control" id="minute" type="text" value="00" name="minute"></td>
                        <td><select class="form-control" id="tone"><?php selectTone(); ?></select></td>
                    </tr>
                    </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btndark" data-dismiss="modal" onclick="addRow();">Add</button>
        </div>
        </div>
    </div>
    </div>

    <!-- 
        Delete Bell Modal

        Christopher Fikes
        03/03/2017
     -->
    <div class="modal fade" id="delBellModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="gridSystemModalLabel">Delete Bell</h4>
        </div>
        <div class="modal-body">
                <form class="form-inline">
                        <input class="form-control" type="text" id="delmodaltime" disabled>
                        <input class="form-control" type="text" id="delmodaltone" disabled>
                </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btndark" data-dismiss="modal" onclick='delRow($("#delmodaltime").val());'>Delete Bell</button>
        </div>
        </div>
    </div>
    </div>

    <!-- 
        New Schedule Modal

        Christopher Fikes
        03/03/2017
     -->
    <div class="modal fade" id="newScheduleModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="gridSystemModalLabel">Create New Schedule</h4>
        </div>
        <div class="modal-body">
            <table class="table table-condensed table-borderless">  
                <th>Schedule Name</th>
                <tr><td><input class="form-control" type="text" id="newScheduleName"></td></tr>
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btndark" onclick='createNewSchedule();'>Create New Schedule</button>
        </div>
        </div>
    </div>
    </div>


    <!-- 
        Delete Schedule Modal

        Christopher Fikes
        03/03/2017
    -->
    <div class="modal fade" id="delScheduleModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="gridSystemModalLabel">Delete Schedule</h4>
        </div>
        <div class="modal-body">
            <h3>Confirm Deletion?</h3>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btndark" onclick='deleteSchedule();'>Delete Schedule</button>
        </div>
        </div>
    </div>
    </div>


    <!-- Touch Spinner -->
    <script src="js/jquery.bootstrap-touchspin.js"></script>
    <link href="css/jquery.bootstrap-touchspin.css" rel="stylesheet">

    <script>
        /*
        * Sorts Tables based on Time. 
        *
        * Christopher Fikes
        * 03/03/2017
        */
        function sortTable(){
            var rows = $('#thisSchedule tbody  tr').get();
            rows.sort(function(a, b) {
                var A = $(a).children('td').eq(0).text().toUpperCase();
                var B = $(b).children('td').eq(0).text().toUpperCase();
                if(A < B) { return -1; }
                if(A > B) { return 1; }
                return 0;
            });
            $.each(rows, function(index, row) {
                console.log(row);
                $('#thisSchedule').children('tbody').append(row);
            });
            console.log("Table Sorted");
        }


        /*
        * Adds row from user input into table. 
        *
        * Christopher Fikes
        * 03/03/2017
        */
        function addRow(){
            //Get Values from form
            var hour = $("#hour").val();
            var minute = $("#minute").val();  
            //Check length of tome for DB formating
            if(hour.length < 2) {
                hour = "0" + hour;
            }
            if( minute.length < 2) {
                minute = "0" + minute;
            }
            //Setup Variables for row creation
            var sentTime = hour+":"+minute;
            var sentTone = $("#tone").val();
            var sentNewBell = newBell(sentTime,sentTone);
            //Create row
            row = "<tr id=\"" + hour + ":" + minute + "\" tone=\"" + $("#tone").val() + "\"><td>" + hour + ":" + minute + "</td><td>" + $("#tone").val() + "</td></tr>";
            $('#thisSchedule').children('tbody').append(row);
            //Sort entries after new added
            sortTable();
            //Close Modal and reset modal values
            $('#newBellModal').modal('hide');
			$("#hour").val("0");
			$("#minute").val("0");
			$("#tone").val("");
        }


        /*
        * Deletes row from table. 
        *
        * Christopher Fikes
        * 03/03/2017
        */
        function delRow(rowid){   
            var row = document.getElementById(rowid);
            var del = deleteBell(rowid);
            row.parentNode.removeChild(row);
        }


        /*
        * Loads entries for specified schedule 
        *
        * Christopher Fikes
        * 03/03/2017
        */
		function loadScheduleList(schedule) {
			$('#thisSchedule').children('tbody').html("");
            //Grab from DB over AJAX JSON schedule
            $.ajax({
                'method' : "POST",
                'url' : "../fusionbells/api.php",
                'context' : document.body,
                'dataType' : 'json',
                'data' : { "call" : "readschedule", "schedule" : schedule },
                'success' : function (data) {
                    console.log(data);
                    $.each(data, function() {
                        //Not Used, but in here . . . 
                        var working = this.Time.split(':');
                        var hour = working[0];
                        var minute = working[1];
                        var row = "<tr id=\"" + this.Time + "\" tone=\"" + this.Tone + "\"><td>" + this.Time + "</td><td>" + this.Tone + "</td></tr>";
                        $('#thisSchedule').children('tbody').append(row);
                    });   
                }
            }).done(function(){
                console.log("DONE");
            });
		}


        /*
        * List of Schedules to choose from  
        *
        * Christopher Fikes
        * 03/03/2017
        */
        function loadSchedulesList(schedule) {
            $('#external-events-listing').html("");
            //Grab from DB over AJAX Json listing of Schedules containing bell times.
            $.ajax({
                'method' : "POST",
                'url' : "../fusionbells/api.php",
                'context' : document.body,
                'dataType' : 'json',
                'data' : { "call" : "readschedules" },
                'success' : function (data) {
                    //Create DIV objects in DOM from received data
                    $.each(data, function() {
                        var row = "<div class='fc-event' schedule='"+this.Schedule+"'>"+this.Schedule+"</div>";
                        $('#external-events-listing').append(row);
                    });   
                }
            }).done(function(){
                console.log("Loaded All Schedules");
            });
        }


        /*
        * Create NEW schedule containing default bell  
        *
        * Christopher Fikes
        * 03/03/2017
        */
        function createNewSchedule() {
            //Grab value from user input
            var name = $("#newScheduleName").val();
            console.log(name);
            //Send information to API over AJAX
            $.ajax({
                'method' : "POST",
                'url' : "../fusionbells/api.php",
                'context' : document.body,
                'dataType' : 'json',
                'data' : { "call" : "newschedule" , "schedulename" : name },
                'success' : function (data) {
                    //On Success close modal and reload page to flush
                    $('#newScheduleModal').modal('hide');
                    location.reload();
                }
            }).done(function(){
                //Nothing
            });
        }


        /*
        * Deletes schedule and all calendar events  
        *
        * Christopher Fikes
        * 03/03/2017
        */
        function deleteSchedule() {
            //Grab name of currently selected schedule
            var name = $("#currentSchedule").html();
            console.log(name);
            //Send request of deletion to API over AJAX
            $.ajax({
                'method' : "POST",
                'url' : "../fusionbells/api.php",
                'context' : document.body,
                'dataType' : 'json',
                'data' : { "call" : "delschedule" , "schedulename" : name },
                'success' : function (data) {
                    //Close Modal and refresh page to flush
                    $('#delScheduleModal').modal('hide');
                    location.reload();
                }
            }).done(function(){
                //Nothing
            });
        }


        /*
        * Deletes selected bell from current schedule  
        *
        * Christopher Fikes
        * 03/03/2017
        */
        function deleteBell(time) {
            var name = $("#currentSchedule").html();
            //Make Request to API with AJAX for deletion
            $.ajax({
                'method' : "POST",
                'url' : "../fusionbells/api.php",
                'context' : document.body,
                'dataType' : 'json',
                'data' : { "call" : "delbell" , "schedulename" : name ,"time" : time},
                'success' : function (data) {
                    //Console log return
                    console.log(data);
                }
            }).done(function(){
                
            });
        }


        /*
        * Adds  new bell to current schedule  
        *
        * Christopher Fikes
        * 03/03/2017
        */
        function newBell(time,tone) {
            //Currently selected schedule
            var name = $("#currentSchedule").html();
            //MAke request to API over AJAX for new bell creation
            $.ajax({
                'method' : "POST",
                'url' : "../fusionbells/api.php",
                'context' : document.body,
                'dataType' : 'json',
                'data' : { "call" : "newbell" , "schedulename" : name ,"time" : time, "tone" : tone},
                'success' : function (data) {
                    //Console log return
                    console.log(data);
                }
            }).done(function(){
                //Nothing
            });
        }



        /*
        * Monitor page for table row click events  
        *
        * Christopher Fikes
        * 03/03/2017
        */
        $(document).on( "click", "#thisSchedule tr", function() {
            $("#delmodaltime").val(this.getAttribute("id"));
            $("#delmodaltone").val(this.getAttribute("tone"));
            $('#delBellModal').modal('show');
        }); 


        /*
        * Monitor page for schedule selection  
        *
        * Christopher Fikes
        * 03/03/2017
        */
        $(document).on( "click", ".fc-event", function() {
            loadScheduleList(this.getAttribute("schedule"));
            $("#currentSchedule").html(this.getAttribute("schedule"));
        }); 


        /*
        * Setup touchspin controls for time selection  
        *
        * Christopher Fikes
        * 03/03/2017
        */
        $("input[name='hour']").TouchSpin({
                min: 0,
                max: 23,
                stepinterval: 1,
                verticalbuttons: true
        });
        $("input[name='minute']").TouchSpin({
                min: 0,
                max: 59,
                stepinterval: 1,
                verticalbuttons: true
        });


        //Initial request for schedules to be loaded
		loadSchedulesList();
    </script>


<?php
/*
* Integration piece into Fusion PBX
* Contributor(s):
* Mark J Crane <markjcrane@fusionpbx.com>
*/
require_once "resources/footer.php";
?>
