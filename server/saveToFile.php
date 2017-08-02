<?php

require_once("classes/videoUploadClass.php");

$video = new VideoUploader();
print_r($video->getAllFiles());
$video->createJsonFileList();