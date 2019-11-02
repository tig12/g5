<?php
/********************************************************************************
    
    From person lists stored in local machine, downloads persons' full data
    
    @license    GPL
    @history    2019-10-31 17:17:16+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\wd\harvest;

use g5\Config;
use g5\patterns\Command;
use g5\transform\wd\Wikidata;
//use tiglib\arrays\csvAssociative;
//use tiglib\strings\slugify;
use tiglib\misc\dosleep;

class persons implements Command{
    
    
    const POSSIBLE_PARAMS = [
        'prepare' => "Stores person ids in a sqlite database",
        'dl' => "Uses sqlite database to download the full persons",
    ];
    
    
    // ******************************************************
    /**
        returns the path to sqlite database used to prepare person retrieval.
    **/
    private static function getDBPath(){
        return Config::$data['dirs']['1-wd-raw'] . DS . 'harvest-persons.sqlite3';
    }
    
    
    // *****************************************
    /** 
        @param $params array containing one string, which must be one of self::POSSIBLE_PARAMS keys.
        @return Empty string, echoes its output
    **/
    public static function execute($params=[]): string{
        $possibleParams_str = '';
        foreach(self::POSSIBLE_PARAMS as $k => $v){
            $possibleParams_str .= "  " . str_pad($k, 8) . ": $v\n";
        }
        if(count($params) == 0){
            return "MISSING PARAMETER. This command needs one parameter :\n" . $possibleParams_str;
        }
        if(count($params) > 1){
            return "USELESS PARAMETER : {$params[1]}. This command needs one parameter :\n" . $possibleParams_str;
        }
        $param = $params[0];
        if(!in_array($param, array_keys(self::POSSIBLE_PARAMS))){
            return "INVALID PARAMETER : '$param'.\nPossible values for parameter :\n" . $possibleParams_str;
        }
        $method = 'persons_' . $param;
        self::$method();
        return '';
    }
    
               
    // ******************************************************
    /**
        Fills sqlite database. 
    **/
    private static function persons_prepare(){
        $dblink = new \PDO('sqlite:' . self::getDBPath());
        $dblink->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $sql = 'drop table if exists harvest;';
        $dblink->exec($sql);
        $sql = 'create table harvest(id string primary key, done boolean not null check(done in(0,1)));';
        $dblink->exec($sql);
        $stmtInsert = $dblink->prepare("insert into harvest(id, done) values(:id, 0)");
        
        $p = '#http://www.wikidata.org/entity/(Q\d+)#';
        
        $allIds = [];
        $glob = glob(Wikidata::getRawPersonListBaseDir() . DS . '*' . DS . '*.json');
        foreach($glob as $infile){
            echo "Processing $infile\n";
            $raw = file_get_contents($infile);
            preg_match_all($p, $raw, $m);
            $allIds = array_merge($allIds, $m[1]);
        }
        
        echo "Performing array_unique\n";
        $allIds = array_unique($allIds);
        
        echo "Contains " . count($allIds) . " rows\n";
        
        /* 
        echo "Fill sqlite database\n";
        $i = 0;
        foreach($allIds as $id){
            $i++;
            if($i%1000 == 0){
                echo "$i\n";
            }
            $stmtInsert->execute([':id' => $id]);
        }
        $dblink->close();
        echo "Prepared sqlite database - " . count($allIds) . " rows.\n";
        */
    }
    

    // ******************************************************
    /**
        Uses sqlite batabase to download persons one by one on local machine.
    **/
    private static function persons_dl(){
        $dblink = new \PDO('sqlite:' . self::getDBPath());
        $dblink->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $rows = $dblink->query("select * from harvest")->fetchAll();
        foreach($rows as $row){
            $done = $row['done'];
            if($done == 1){
                continue;
            }
            $id = $row['id'];
        }
        $dblink->close();
    }
    

        
}// end class    
