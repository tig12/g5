<?php
/******************************************************************************
    
    For all persons of the database, checks that the rules described in https://tig12.github.io/g5/db-person.html are respected.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-01-21 17:49:20+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\commands\db\look;

use tiglib\patterns\Command;
use g5\model\DB5;
use g5\model\Person;

class checknames implements Command {
    
    /** 
        @param  $params empty array
        @return Report
    **/
    public static function execute($params=[]): string {
        
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        $res = [];
        
        $g5link = DB5::getDblink();
        $stmt = $g5link->query("select id, slug, name, ids_in_sources from person");
        
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $nameArray = json_decode($row['name'], true);
            try{
                Person::checkName($nameArray);
            }
            catch(\Exception $e){
                echo $row['id'] . ' ' . $row['slug']  . ' - ' . $row['ids_in_sources'] . "\n";
                echo $e->getMessage();
                exit;
            }
        }
    }
    
} // end class
