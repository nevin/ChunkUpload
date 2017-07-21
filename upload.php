<?php


// echo "<pre>";
// var_dump($_REQUEST);

if($_GET){
	header("HTTP/1.1 204 No Content");
	return false;
}



if($_POST){
//	print_r($_POST);
//	print_r($_FILES);
//	print_r($_FILES["file"]);
	//echo $_FILES['file']['name'];

}




/**
* 
*/
class VideoUploader 
{
	private $uploadFolder;
	private $chunkFolder;
	private $parameters;
	private $file;
	private $fileIdentifier;
	public function __construct ($uploadFolder,$chunkFolder)
	{
		$this->uploadFolder = $uploadFolder;
		$this->chunkFolder = $chunkFolder;
		$validateUploadFolder =  $this->validateFolder($this->uploadFolder);
	    $validateChunkFolder = $this->validateFolder($this->chunkFolder);


		
	}
	public function validateFolder($folderName){
		if (!file_exists($folderName)) {
    		mkdir($folderName);
		}
		return true;
	}

	public function getUploaderFolder (){
		return $this->uploadFolder ;
	}
	public function getChunkFolder (){
		return $this->chunkFolder;
	}

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

   
   public function getAllParam()
    {
        return isset($this->params) ? $this->params : null;
    }
    public function getParam($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

  
    public function getFileName()
    {
        return $this->getParam('flowFilename');
    }

    
    public function getTotalSize()
    {
        return $this->getParam('flowTotalSize');
    }

    
    public function getIdentifier()
    {
        return $this->getParam('flowIdentifier');
    }

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

    
    public function getDefaultChunkSize()
    {
        return $this->getParam('flowChunkSize');
    }

    
    public function getCurrentChunkNumber()
    {
        return $this->getParam('flowChunkNumber');
    }

    
    public function getCurrentChunkSize()
    {
        return $this->getParam('flowCurrentChunkSize');
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

    public function validateChunkAfterMove(){
    	$file = $this->getFile();
    	print_r($file);

        if (!$file) {
            return false;
        }

        if (!isset($file['tmp_name']) || !isset($file['size']) || !isset($file['error'])) {
            return false;
        }

        // if (!file_exists($file)) {
        //         return false;
        //     }
         echo $this->getChunkPath($this->getCurrentChunkNumber());
       // echo $chunkSize = filesize();

    }
    
    
}



$video = new VideoUploader("uploads","chunk");
$video->uploadRequest();
if($video->validateChunk()){
	$video->saveChunk();
	$video->validateChunkAfterMove();
}

echo "<pre>";

print_r($video->getAllParam());
print_r($video->getFile());

