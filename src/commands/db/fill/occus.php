<?php
/******************************************************************************
    
    Fills table groop from csv files located in data/model/occu
    Created groups have type Group::TYPE_OCCU.
    Creation of occupation groups is only partially done by this command,
    and must be completed by commands/db/fill/occugroups.
    In particular, current command doesn't compute
        - field n (nb of persons with this occupation).
        - field children.
    See command occugroups.
    @license    GPL
    @history    2021-07-28 20:59:37+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\fill;

use g5\patterns\Command;
use g5\Config;
use g5\model\DB5;
use g5\model\Group;
use g5\model\Occupation;
use tiglib\arrays\csvAssociative;

class occus implements Command {
    
    /** 
        @param  $params Empty array
        @return report.
    **/
    public static function execute($params=[]): string {
        if(count($params) != 0){
            return "USELESS PARAMETER {$params[0]}\n";
        }
        $report = "--- db fill occus ---\n";
        
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
        foreach(Occupation::DEFINITION_FILES as $file){
            $lines = csvAssociative::compute(Occupation::getDefinitionDir() . DS . $file);
            foreach($lines as $line){
                if($line['slug'] == ''){
                    continue; // skip blank lines
                }
                if(strpos($line['wd'], '+') !== false){
                    // happens for canoeist-kayaker Q13382566+Q16004471
                    // useless here because canoeist Q13382566 and kayaker Q16004471
                    // (useful to match with cura and Ertel in tmp2db classes)
                    continue;
                }
                $parents = [];
                // obliged to add this test to prevent a bug:
                // if parent is empty, explode returns an array containing one empty string
                if($line['parents'] != ''){
                    $parents = explode('+', $line['parents']);
                }
                $stmt_insert->execute([
                    $line['slug'],
                    $line['wd'],
                    $line['en'],
                    'Persons whose occupation is ' . $line['en'] . '.',
                    Group::TYPE_OCCU,
                    json_encode($parents),
                ]);
                $N++;
            }
        }
        $report .= "Inserted $N occupation groups\n";
        return $report;
    }
    
} // end class
