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
* Includes for server functions for FusionBells
*
* Christopher Fikes
* 03/03/2017
*/
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
</style>

<table width='100%' border='0' cellpadding='0' cellspacing='0'>
	<tr>
		<td width='50%'>
			<b>Schedule Calendar</b><br><br>
		</td>
		<td width='50%' align='right'>
			<a href='fusionbells.php' class='btn btndark'>Dashboard</a>
			<a href='fbsettings.php' class='btn btndark'>Settings</a>
			<a href='fbscheduleedit.php' class='btn btndark'>Schedule Editor</a>
		</td>
	</tr>	
</table>

<div class="fusionbellcont">
	<div class="row">
		<div class="col-md-2">
			<div class="row">
				<h4>Schedules</h4>
				<div id='external-events'>
				  <div id='external-events-listing'>
					<?php getSchedulesInclude(); ?>
				  </div>
				</div>
			</div>
			
			<div class="row">
				<div id="tips">
					<p>
					Tip: Drag schedule from calendar back to schedules to delete.
					</p>
				</div>
			</div>
			
		</div>
		<div class="col-md-10">
			<div id='calendar'></div>
		</div>
	</div>
</div>


<script src='js/jquery-ui.min.js'></script>
<script src='js/fullcalendar.min.js'></script>

<script>
/*
* Remove a non default day and return status. 
*
* Christopher Fikes
* 03/03/2017
*/
function removeCalendarDay(day,schedule) {
	$.ajax({
		'method' : "POST",
		'url' : "../fusionbells/api.php",
		'context' : document.body,
		'dataType' : 'json',
		'data' : { "call" : "delcalendarday", "day" : day, "schedule" : schedule },
		'success' : function (data) {
			if(data.msg == "error") {
				console.log(data.error);
				return false;
			}
			else if (data.msg == "complete") {
				console.log(data.msg);
				return true;
			}
		}
	}).done(function(){
		console.log("Completed removeCalendarDay Function");
	});
}



$(document).ready(function() {

        /* initialize the external events
        -----------------------------------------------------------------*/

        $('#external-events .fc-event').each(function() {

            // store data so the calendar knows to render an event upon drop
            $(this).data('event', {
                title: $.trim($(this).text()), // use the element's text as the event title
				stick: true // maintain when user navigates (see docs on the renderEvent method)
				
            });

            // make the event draggable using jQuery UI
            $(this).draggable({
                zIndex: 999,
                revert: true,      // will cause the event to go back to its
                revertDuration: 0,  //  original position after the drag
				scroll: false,
				helper: 'clone'
            });

        });

        /* initialize the calendar
        -----------------------------------------------------------------*/
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: '',
                right: 'title'
            },
			events: {
				url: "../fusionbells/api.php",
				type: 'POST',
				data: {
					call : "getcalendardays"
				},
				error: function() {
					console.log("Error Fetching Events");
				}
			},
            editable: true,
            droppable: true,
			dropAccept: '.fc-event',
            dragRevertDuration: 0,
			eventOverlap: false,
			resourceEventOverlap: false,
			/*
			* DELETE Calendar Day. 
			*/
            eventDragStop: function( event, jsEvent, ui, view ) {
                if(isEventOverDiv(jsEvent.clientX, jsEvent.clientY)) {
					var startDate = event.start.format();
					console.log(event._id);
					console.log(event.title + " Removed from " + event.start.format());
					//Make Request to remove from DB
					$.ajax({
							'method' : "POST",
							'url' : "../fusionbells/api.php",
							'context' : document.body,
							'dataType' : 'json',
							'data' : { "call" : "delcalendarday", "day" : startDate, "schedule" : event.title },
							'success' : function (data) {
								if(data.msg == "error") {
									console.log(data.error);
									
								}
								else if (data.msg == "complete") {
									console.log(data.msg);
									$('#calendar').fullCalendar('removeEvents', event._id);
								}
							}
						}).done(function(){
							console.log("Completed removeCalendarDay Function");
						});
                }
            },//End Delete Calendar Day
			//Clicked event just for troubleshooting
			eventClick: function(event,element) {
				console.log(event._id);
				console.log(event.title + " " + event.start.format());
			},
			/*
			* Update Moved Calendar Day. 
			*/
			eventDrop: function(event,delta,revertFunc,jsEvent,ui) {
				var startDate = event.start.format();
				console.log(event._id);
				console.log(event.title + " was dropped on " + event.start.format());
				$.ajax({
					'method' : "POST",
					'url' : "../fusionbells/api.php",
					'context' : document.body,
					'dataType' : 'json',
					'data' : { "call" : "movecalendarday", "day" : startDate, "ID" : event._id },
					'success' : function (data) {
						if(data.msg == "error") {
							//If Failed, Log Error and remove from calendar
							console.log("ERROR "+ data.error);
							revertFunc();
						}
						else if (data.msg == "complete") {
							console.log("Moved");
						}
					}
				}).done(function(){
					console.log("Completed moveCalendarDay Function");
				});	
			},//END Move Calendar Day
			/*
			* Add NEW Calendar Day. 
			*/
			eventReceive: function(event) {
				var startDate = event.start.format();
				//Make Request to DB to Create Event
				$.ajax({
					'method' : "POST",
					'url' : "../fusionbells/api.php",
					'context' : document.body,
					'dataType' : 'json',
					'data' : { "call" : "addcalendarday", "day" : startDate, "schedule" : event.title },
					'success' : function (data) {
						if(data.msg == "error") {
							//If Failed, Log Error and remove from calendar
							console.log("ERROR "+ data.error);
							$('#calendar').fullCalendar('removeEvents', event._id);
						}
						else if (data.msg == "complete") {
							//Update to DB assigned ID
							event._id = data.recordid;
						}
					}
				}).done(function(){
					console.log("Completed addCalendarDay Function");
				});	
			}//End New Calendar Day

        });

		//Variable for defition of location of drop box
        var isEventOverDiv = function(x, y) {
            var external_events = $( '#external-events' );
            var offset = external_events.offset();
            offset.right = external_events.width() + offset.left;
            offset.bottom = external_events.height() + offset.top;
            // Compare
            if (x >= offset.left && y >= offset.top && x <= offset.right && y <= offset.bottom) { 
				return true; 
			} else {
            	return false;
			}

        }
    });
</script>

<?php
/*
* Integration piece into Fusion PBX
* Contributor(s):
* Mark J Crane <markjcrane@fusionpbx.com>
*/
require_once "resources/footer.php";
?>
