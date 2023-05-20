<?php
/******************************************************************************
    
    Fills table groop from csv file data/db/occu/all-occus.csv
    Each occu is stored in db as a groop of type Group::TYPE_OCCU.
    Creation of occupation groups is only partially done by this command,
    and must be completed by occus2.
    In particular, current command doesn't compute
        - field n (nb of persons with this occupation).
        - field children.
    See command occus2.
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-07-28 20:59:37+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\init;

use tiglib\patterns\Command;
use g5\app\Config;
use g5\model\DB5;
use g5\model\Group;
use g5\model\Occupation;
use tiglib\arrays\csvAssociative;

class occus1 implements Command {
    
    /** 
        @param  $params Empty array
        @return report.
    **/
    public static function execute($params=[]): string {
        if(count($params) != 0){
            return "USELESS PARAMETER {$params[0]}\n";
        }
        $report = "--- db init occus1 ---\n";
        
        $dblink = DB5::getDbLink();
        $dblink->exec("delete from groop where type='" . Group::TYPE_OCCU . "'");
        $stmt_insert = $dblink->prepare(
            "insert into groop(
                slug,
                wd,
                name,
                description,
                type,
                parents
            )values(?,?,?,?,?,?)"
        );
        
        $N = 0;
        $lines = csvAssociative::compute(Occupation::getDefinitionFile());
        foreach($lines as $line){
            if($line['slug'] == ''){
                continue; // skip blank lines
            }
            Occupation::insert($line);
            $N++;
        }
        $report .= "Inserted $N occupation groups\n";
        return $report;
    }
    
} // end class
