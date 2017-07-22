<?php

require_once(dirname(__FILE__)."/classes/VideosClass.php");

$videos = new Videos('uploads');
echo $videos->getAllFiles();
