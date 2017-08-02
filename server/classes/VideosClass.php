<?php

require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__).'/../libraries/getid3/getid3.php');
require_once(dirname(__FILE__)."/UtilClass.php");

class Videos extends getID3 {
	private $sourceFolder;
	private $exculdedFile =   array('.', '..','.DS_Store');
    private $filesList = [];
    private $serverInfo = [];
    private $apiDataFolder;
    private $apiInfoFile;
    private $apiData = "";
	public function __construct(){
		$this->sourceFolder =  Config::UPLOAD_FOLDER;
		$this->serverInfo = Util::getServerDetails();
		$this->apiDataFolder = Config::APIINFO_FOLDER;
        $this->apiInfoFile = Config::APIINFO_FILE;
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
       if (!file_exists($dirToScan)) {
           		mkdir($dirToScan);
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


	public function getAllFilesListFromJson(){

		$jsonDataFolder = $this->apiDataFolder;
        $apiInfoFIle  = $this->apiInfoFile;
        $fileUrl = $jsonDataFolder.DIRECTORY_SEPARATOR.$apiInfoFIle ;
        $fp = fopen($fileUrl, 'r');
        $this->apiData =fread($fp , filesize($fileUrl));
        fclose($fp);
        return $this->apiData;
	}

	public function getFiles($source = null){
		$fileSourceSetting = $source ? $source : Config::API_DEFAULTSOURCE;
		if ($fileSourceSetting == 'file'){
		   return $this->getAllFiles();
		}else{	 
		   return $this->getAllFilesListFromJson();
		}

	}


}