<?php

require_once(dirname(__FILE__)."/classes/VideosClass.php");

$videos = new Videos();
//echo $videos->getAllFiles();

echo $videos->getFiles('file');
