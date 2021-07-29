<?php
/******************************************************************************
    
    Fills table occu (occupations) from csv files located in data/model/occu
    
    @license    GPL
    @history    2021-07-28 20:59:37+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\fill;

use g5\patterns\Command;
use g5\Config;
use g5\model\DB5;
use g5\model\Occupation;
use tiglib\arrays\csvAssociative;

class occu implements Command {
    
    /** 
        @param  $params Empty array
        @return report.
    **/
    public static function execute($params=[]): string {
        if(count($params) != 0){
            return "USELESS PARAMETER {$params[0]}\n";
        }
        $report = "--- db fill occu ---\n";
        
        $dblink = DB5::getDbLink();
        $dblink->exec("delete from occu");
        $stmt_insert = $dblink->prepare("insert into occu(slug,wd,name,parents)values(?,?,?,?)");
        
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
                    json_encode($parents),
                ]);
                $N++;
            }
        }
        $report .= "Inserted $N occupations\n";
        return $report;
    }
    
} // end class
