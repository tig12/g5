<?php
/******************************************************************************
    Gauquelin5 database
    @license    GPL
    @history    2019-12-27 05:50:58+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

use g5\app\Config;

class DB5{
    
    // Trust levels, see https://tig12.github.io/gauquelin5/check.html
    const TRUST_HC = 1;                                                                  
    const TRUST_BC = 2;
    const TRUST_BC_CHECK = 2.5;
    const TRUST_BR = 3;
    const TRUST_CHECK = 4;
    const TRUST_REST = 5;
    
    /** Separator used to build uids **/
// TODO suppress
    const SEP = '/';
    
    /**
        Pattern to check a date.
        @todo put elsewhere ?
    **/
    const PDATE = '/\d{4}-\d{2}-\d{2}/';
    
    private static $dblink = null;
    
    /** Connection to g5 database **/
    public static function getDblink(){
        if(is_null(self::$dblink)) {
            $host = Config::$data['db5']['postgresql']['dbhost'];
            $port = Config::$data['db5']['postgresql']['dbport'];
            $user = Config::$data['db5']['postgresql']['dbuser'];
            $password = Config::$data['db5']['postgresql']['dbpassword'];
            $dbname = Config::$data['db5']['postgresql']['dbname'];
            $dsn = "pgsql:host=$host;port=$port;user=$user;password=$password;dbname=$dbname";
            self::$dblink = new \PDO($dsn);
            self::$dblink->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return self::$dblink;
    }
    
}// end class
