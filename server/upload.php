<?php

require_once("videoUploadClass.php");

$video = new VideoUploader("uploads","chunk");
$video->saveUploads();

