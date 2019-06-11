<?php 
/********************************************************************************
    Utilities to connect to geonames database.
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2019-06-11 11:36:20+02:00, Thierry Graff : Creation from existing code.
********************************************************************************/
namespace g5\model;

use g5\Config;

class Geonames{
    
    /**
        Variable to cache the link to database.
    **/
    private static $dblink = null;
    
    // ******************************************************
    public static function compute_dblink(){
        if(is_null(self::$dblink)){
            $host = Config::$data['postgresql']['dbhost'];
            $port = Config::$data['postgresql']['dbport'];
            $user = Config::$data['postgresql']['dbuser'];
            $password = Config::$data['postgresql']['dbpassword'];
            $dbname = Config::$data['postgresql']['dbname'];
            $dsn = "pgsql:host=$host;port=$port;user=$user;password=$password;dbname=$dbname";
            self::$dblink = new \PDO($dsn);
            self::$dblink->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return self::$dblink;
    }
    
    
}// end class

