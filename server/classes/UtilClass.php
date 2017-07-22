<?php

/**
* 
*/
class Util  
{
	
	public function getServerDetails (){
		$serverDetails['hostUrl'] = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_SERVER['HTTPS_HOST'];
		$serverDetails['rootFolder'] = str_replace([$_SERVER["DOCUMENT_ROOT"],basename(__DIR__)], ["",""], dirname(__FILE__));
		return $serverDetails;
	}
}