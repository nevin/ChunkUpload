<?php

class DBConnection{

    protected $db;
    protected $username = "videoadmin";
    protected $pass = "admin123";
    protected $server = "mysql:host=mysql;dbname=uploader;charset=utf8;port=3306";

    public function DBConnection(){

    $conn = NULL;

        try{
            $conn = new PDO($this->server, $this->username, $this->pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e){
                echo 'ERROR: ' . $e->getMessage();
                }    
            $this->db = $conn;
    }
    
    public function getConnection(){
        return $this->db;
    }
}


$db = new DBConnection();

?>

