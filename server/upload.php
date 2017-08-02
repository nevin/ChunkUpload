<?php

require_once("classes/videoUploadClass.php");

$video = new VideoUploader("uploads","chunk");
$video->saveUploads();
$video->createJsonFileList();

