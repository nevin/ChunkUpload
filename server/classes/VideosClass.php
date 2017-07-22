<?php

require_once(dirname(__FILE__).'/../libraries/getid3/getid3.php');
require_once(dirname(__FILE__)."/UtilClass.php");

class Videos extends getID3 {
	private $sourceFolder;
	private $exculdedFile =   array('.', '..','.DS_Store');
    private $filesList = [];
    private $serverInfo = [];
	public function __construct($location){
		$this->sourceFolder =  $location;
		$this->serverInfo = Util::getServerDetails();
	}

	public function getSourceFolderName(){
		return $this->sourceFolder;
	}

	public function getUploadFolderUrl (){
		return $this->serverInfo["REQUEST_SCHEME"]."://".$this->serverInfo["hostUrl"].$this->serverInfo["rootFolder"];
	}
	public function getAllFiles($path=null){
		$dirToScan = $this->sourceFolder;
		if($path){
			$dirToScan = $path;
		}
       
	    $filesList = array_values(array_diff(scandir($dirToScan), $this->exculdedFile));
	    $fileDetails =[];

	    foreach ($filesList as $key => $filename) {
	    	$filePath = $dirToScan.DIRECTORY_SEPARATOR.$filename;
	    	$fileAnalysisResult = $this->analyze($filePath);
	    	// echo "<pre>";
	    	// print_r($fileAnalysisResult);

	    
	    	$fileProperties['filename'] = $fileAnalysisResult['filename'] ;
	    	$path_parts = pathinfo($fileAnalysisResult['filename']);
	    	$fileProperties['name'] = $path_parts['filename'];
	    	$fileProperties['fileformat'] = $fileAnalysisResult['fileformat'];
	    	$fileProperties['filesize'] = $fileAnalysisResult['filesize'];
	    	$fileProperties['encoding']  = $fileAnalysisResult['encoding'] ;
	    	$fileProperties['mime_type'] = $fileAnalysisResult['mime_type'];
	    	$fileProperties['playtime_string'] = $fileAnalysisResult['playtime_string'];
	    	$fileProperties['video'] = $fileAnalysisResult['video'] ;
	    	$fileProperties['filepath'] = $filePath ;
	    	$fileProperties['videoUrl'] = $this->getUploadFolderUrl().$filePath;
	    	$fileList[] = $fileProperties; 

	    }
	    $this->fileList = $fileList;
        return json_encode($this->fileList);
	}
}