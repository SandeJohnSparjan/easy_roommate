<?php

class Database
{
    public function dbConnection(){
        $host = "localhost";
        $dbName = "easyroommate";
        $username = "root";
        $password = "";

        $con = 'mysql:host='.$host.';dbname='.$dbName.';charset=utf8';
        try{
            $db_con = new PDO($con, $username, $password);
            $db_con->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            return $db_con;
        }catch (PDOException $errMsg){
            echo $errMsg->getMessage();
            exit;
        }

    }
}
