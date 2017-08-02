<?php
require_once(dirname(__FILE__).'/../libraries/getid3/getid3.php');
require_once(dirname(__FILE__)."/UtilClass.php");
require_once(dirname(__FILE__)."/config.php");
class VideoUploader  extends getID3 
{
	private $uploadFolder;
	private $chunkFolder;
    private $apiDataFolder;
    private $apiInfoFile;
	private $params;
	private $file;
	private $fileIdentifier;
	private $setting_deleteChunks;
    private $exculdedFile =   array('.', '..','.DS_Store');
    private $serverInfo = [];

	// constructor function which defines the basic settings   like the uploads folder location and the chunk folder

	public function __construct ($uploadFolder = null,$chunkFolder = null,$deleteChunkSetting = true)
	{
		$this->uploadFolder = $uploadFolder ? $uploadFolder : Config::UPLOAD_FOLDER;
		$this->chunkFolder = $chunkFolder ? $chunkFolder : Config::UPLOAD_TEMP;
        $this->apiDataFolder = Config::APIINFO_FOLDER;
		$this->setting_deleteChunks = $deleteChunkSetting;
        $this->apiInfoFile = Config::APIINFO_FILE;

		$this->validateFolder($this->uploadFolder);
	    $this->validateFolder($this->chunkFolder);
        $this->validateFolder($this->apiDataFolder);
        $this->serverInfo = Util::getServerDetails();
	}

	// fucntion to check whether the folder exist or not
	public function validateFolder($folderName){
		if (!file_exists($folderName)) {
    		mkdir($folderName);
		}
		return true;
	}

	// getter method for  getting the upload fodler 
	public function getUploaderFolder (){
		return $this->uploadFolder ;
	}
	// getter method for getting the chunks folder
	public function getChunkFolder (){
		return $this->chunkFolder;
	}

	// function for assigning the file reques to a variable
	public function uploadRequest($params = null, $file = null)
    {
        if ($params === null) {
            $params = $_REQUEST;
        }

        if ($file === null && isset($_FILES['file'])) {
            $file = $_FILES['file'];
        }

        $this->params = $params;
        $this->file = $file;
        $this->fileIdentifier = $this->setIdentifier();
    }

   // function to get all the parameters of the file object
   public function getAllParam()
    {
        return isset($this->params) ? $this->params : null;
    }

    // function to get the parameters by name
    public function getParam($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    // returns the identifier parameter in the file request
    public function getIdentifier()
    {
        return $this->getParam('flowIdentifier');
    }

	// get the file name from the file value  
    public function getFileName()
    {
        return $this->getParam('flowFilename');
    }

    // returns  the totalsize
    public function getTotalSize()
    {
        return $this->getParam('flowTotalSize');
    }

    // set the fileidentifier parameter
    public function setIdentifier(){

    	$this->fileIdentifier = $this->getIdentifier();
    }

    
    public function getRelativePath()
    {
        return $this->getParam('flowRelativePath');
    }

    
    public function getTotalChunks()
    {
        return $this->getParam('flowTotalChunks');
    }

    
    //get the current chunk number     
    public function getCurrentChunkNumber()
    {
        return $this->getParam('flowChunkNumber');
    }

    
    public function getCurrentChunkSize()
    {
        return $this->getParam('flowCurrentChunkSize');
    }


    public function getChunkSize()
    {
        return $this->getParam('flowChunkSize');
    }

    public function getFile()
    {
        return $this->file;
    }

    public function validateChunk()
    {
        $file = $this->getFile();

        if (!$file) {
            return false;
        }

        if (!isset($file['tmp_name']) || !isset($file['size']) || !isset($file['error'])) {
            return false;
        }

        if ($this->getCurrentChunkSize() != $file['size']) {
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        return true;
    }
    
    public function saveChunk()
    {
        $file = $this->getFile();
        return $this->moveUploadedFile($file['tmp_name'], $this->getChunkPath($this->getCurrentChunkNumber()));
    }

    public function moveUploadedFile($source,$dest){
        return move_uploaded_file($source, $dest);
    } 

    public function getChunkPath($index)
    {
	    return $this->getChunkFolder().DIRECTORY_SEPARATOR.sha1(basename($this->getIdentifier()).'_'. (int) $index);
    }

    public function checkChunkExists()
    {
        return file_exists($this->getChunkPath($this->getCurrentChunkNumber()));
    }

    public function validateChunkAfterMove(){
    	$file = $this->getFile();
    	

        if (!$file) {
            return false;
        }

        if (!isset($file['tmp_name']) || !isset($file['size']) || !isset($file['error'])) {
            return false;
        }
       
        $chunkFileSaved = $this->getChunkPath($this->getCurrentChunkNumber());
        if(!file_exists($chunkFileSaved)){
            return false;
        }

         if(filesize($chunkFileSaved) != $file['size']){
            return false;
         }

         return true;


    }

    // validate whether all the files are recieved.

    public function validateFile()
    {
        $totalChunks = $this->getTotalChunks();
        $totalChunksSize = 0;

        for ($i = $totalChunks; $i >= 1; $i--) {
            $file = $this->getChunkPath($i);
            if (!file_exists($file)) {
                return false;
            }
            $totalChunksSize += filesize($file);
        }

        return $this->getTotalSize() == $totalChunksSize;
    }
    
     public function saveToSingleFile()
    {
        

        $destination = $this->getUploaderFolder()."/".$this->getFileName();

        $fh = fopen($destination, 'wb');
        
        $totalChunks = $this->getTotalChunks();

        try {

            for ($i = 1; $i <= $totalChunks; $i++) {
                $file = $this->getChunkPath($i);
                $chunk = fopen($file, "rb");

                if (!$chunk) {
                    throw new Exception('failed to open chunk: '.$file);
                }
                stream_copy_to_stream($chunk, $fh);
                fclose($chunk);
            }
        } catch (Exception $e) {
  
            fclose($fh);
            throw $e;
        }

        fclose($fh);

        return true;
    }
// controlled by the variable setting_deleteChunks , default value is true
    public function deleteAllChunks(){

        $totalChunks = $this->getTotalChunks();

        for ($i = 1; $i <= $totalChunks; $i++) {
            $path = $this->getChunkPath($i);
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }



// main call for process the upload request

    public function saveUploads(){

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            //check whether chunk exists
            if ($this->checkChunkExists()) {
                // if exists return ok as header, which wont upload
                header("HTTP/1.1 200 Ok");
            } else {
            	//flow js requires 204 error as return to do the post actions
                header("HTTP/1.1 204 No Content");
                return false;
            }
        } else {
        	// set the file parameter
            $this->uploadRequest();
            if ($this->validateChunk()) {
                $this->saveChunk();
                $this->validateChunkAfterMove();
            } else {
                // error, invalid chunk upload request, retry
                header("HTTP/1.1 400 Bad Request");
                return false;
            }
        }

        if ($this->validateFile() && $this->saveToSingleFile($destination)) {
        	if($this->setting_deleteChunks){
        		$this->deleteAllChunks();
        	}

            return true;
        }

        return false;
    }   

    public function getUploadFolderUrl (){
        return $this->serverInfo["REQUEST_SCHEME"]."://".$this->serverInfo["hostUrl"].$this->serverInfo["rootFolder"];
    }

    public function getAllFiles($path=null){
        $dirToScan = $this->uploadFolder;
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

    function createJsonFileList(){
        $jsonData = $this->getAllFiles();
        $jsonDataFolder = $this->apiDataFolder;
        $apiInfoFIle  = $this->apiInfoFile;
        $fileUrl = $jsonDataFolder.DIRECTORY_SEPARATOR.$apiInfoFIle ;
        $fp = fopen($fileUrl, 'c');
        fwrite($fp, $jsonData);
        fclose($fp);
    }
}
?>