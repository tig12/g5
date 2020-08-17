<?php 
/********************************************************************************
    Utilities to use geonames.org.
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2019-06-11 11:36:20+02:00, Thierry Graff : Creation from existing code.
********************************************************************************/
namespace g5\model;

use g5\Config;

Geonames::init();

class Geonames {
    
    /** Variable to cache the link to database. **/
    private static $dblink = null;
    
    /**  Directory where calls to geonames web service are cached **/
    public static $TMP_SERVICE_DIR;
    
    // ******************************************************
    /**
        @param $
    **/
    public static function init(){
        self::$TMP_SERVICE_DIR = Config::$data['dirs']['tmp'] . DS . 'geonames';
    }
    
    // ******************************************************
    public static function compute_dblink(){
        if(is_null(self::$dblink)){
            $host = Config::$data['geonames']['postgresql']['dbhost'];
            $port = Config::$data['geonames']['postgresql']['dbport'];
            $user = Config::$data['geonames']['postgresql']['dbuser'];
            $password = Config::$data['geonames']['postgresql']['dbpassword'];
            $dbname = Config::$data['geonames']['postgresql']['dbname'];
            $dsn = "pgsql:host=$host;port=$port;user=$user;password=$password;dbname=$dbname";
            self::$dblink = new \PDO($dsn);
            self::$dblink->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return self::$dblink;
    }
    
    
}// end class

