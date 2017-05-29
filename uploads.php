<?php
session_start();
$UUID = $_SESSION['domain_uuid'];
$ds = DIRECTORY_SEPARATOR;
 
$storeFolder = "tones/$UUID";

if (!empty($_FILES)) {
    $tempFile = $_FILES['file']['tmp_name'];
    $targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds;
    $targetFile =  $targetPath. $_FILES['file']['name'];
    move_uploaded_file($tempFile,$targetFile);
}
?> 