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

<table width='100%' border='0' cellpadding='0' cellspacing='0'>
	<tr>
		<td width='50%'><b>Tone Editor</b><br><br></td>
		<td width='50%' align='right'>
			<a href='fusionbells.php' class='btn btndark'>Dashboard</a>
            <a href='fbsettings.php' class='btn btndark'>Settings</a>
            <a data-toggle="modal" data-target="#ttsModal" class='btn btndark'>New TTS Tone</a>
		</td>
	</tr>
</table>


<style>
#tonepreview {
    width: 100%;
}
</style>

<!-- DROPZONE FILE UPLOAD -->
<link href="css/dropzone.css" type="text/css" rel="stylesheet" />


<div class="row">
    <div class="col-md-8">
        <h4>File Listings</h4>
            <table name="filelisting" id="filelisting" class="table table-hover">
                <tbody>
                </tbody>
            </table>
    </div>
    <div class="col-md-4">
        <div class="row">    
            <h4>Preview <span id="previewtitle"></span></h4>
            <audio id="tonepreview" autoplay="autoplay" controls="controls" width="100%"></audio>
        </div>
        <div class="row">
            <a class="btn btndark" data-toggle="modal" data-target="#delToneModal">Delete Tone</span></a>
        </div>
        <div class="row">
            <h4>Upload</h4>
            <form id="toneDropZone" action="uploads.php" class="dropzone"></form>
        </div>
    </div>
</div>



    <!-- 
        TTS Modal

        Christopher Fikes
        03/06/2017
    -->
    <div class="modal fade" id="ttsModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="gridSystemModalLabel">New TTS Tone</h4>
        </div>
        <div class="modal-body">
            <input class="form-control" type="text" id="newttsname" placeholder="Filename Ex. Announcement">
            <textarea class="form-control" id="newttstxt"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btndark" onclick='createTTS();'>Save TTS Tone</button>
        </div>
        </div>
    </div>
    </div>


    <!-- 
        Delete Tone Modal

        Christopher Fikes
        03/03/2017
     -->
    <div class="modal fade" id="delToneModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="gridSystemModalLabel">Delete Tone</h4>
        </div>
        <div class="modal-body">
                <form class="form-inline">
                        <input class="form-control" type="text" id="delmodaltone" disabled>
                </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btndark" data-dismiss="modal" onclick='delTone($("#delmodaltone").val());'>Delete Tone</button>
        </div>
        </div>
    </div>
    </div>


<script src="js/dropzone.js"></script>

<script>

/*
* Creates listing of files
*
* Christopher Fikes
* 03/06/2017
*/
function listTones(){
    $('#filelisting').children('tbody').html("");
    $.ajax({
        'method' : "POST",
        'url' : "../fusionbells/api.php",
        'context' : document.body,
        'dataType' : 'json',
        'data' : { "call" : "gettones" },
        'success' : function (data) {
            $.each(data, function() {
                row = "<tr id=\"" + this.file + "\"><td>" + this.file + "</td></tr>";
                $('#filelisting').children('tbody').append(row);
            });  
    }
	}).done(function(){
		console.log("loaded files");
	});

}

function createTTS() {
    var filename = $("#newttsname").val().replace(/\s/g,'') + ".wav";
    console.log(filename);
    var text = $("#newttstxt").val();

    $.ajax({
        'method' : "POST",
        'url' : "../fusionbells/api.php",
        'context' : document.body,
        'dataType' : 'json',
        'data' : { "call" : "newtts", "filename" : filename, "text" : text },
        'success' : function (data) {
            if(data.msg = "complete") {
                $('#ttsModal').modal('hide');
                $("#newttsname").val("");
                $("#newttstxt").val("");
                listTones();
            }
        }
	}).done(function(){
		console.log("TTS Complete");
	});

}

function delTone(filename) {
    $.ajax({
        'method' : "POST",
        'url' : "../fusionbells/api.php",
        'context' : document.body,
        'dataType' : 'json',
        'data' : { "call" : "deltone", "filename" : filename},
        'success' : function (data) {
            if(data.msg = "complete") {
                $("#previewtitle").html("");
                listTones();
            }
        }
    }).done(function(){
        console.log("Deletion Complete");
    });
}

//Dropzone configuration
Dropzone.autoDiscover = false;

$(function() {
  var toneDropZone = new Dropzone("#toneDropZone");

    toneDropZone.on("addedfile", function(file) {
        /* Maybe display some more file information on your page */
    });

    toneDropZone.on("complete", function(file) {
        setTimeout(function () {
            toneDropZone.removeFile(file);
            listTones();
        },2000);
    });
})

Dropzone.options.toneDropZone = {
    dictDefaultMessage: "Click Here or Drag and Drop Files Here to Upload",
    paramName: "file",
    maxFilesize: 10,
    acceptedFiles: ".wav, .mp3, .ogg",
    createImageThumbnails: false,
    accept: function(file, done) {
        if (file.name == " ") {
        done("Naha, you don't.");
        }
        else { done(); }
  }
};


//Preview File
$(document).on( "click", "#filelisting tr", function() {
    var fileName = "tones/"+this.getAttribute("id");
    $("#previewtitle").html(this.getAttribute("id"));
    $("#delmodaltone").val(this.getAttribute("id"));
    $("#tonepreview").attr("src",fileName).trigger("play");
});

listTones();


</script>

<?php
/*
* Integration piece into Fusion PBX
* Contributor(s):
* Mark J Crane <markjcrane@fusionpbx.com>
*/
require_once "resources/footer.php";
?>
