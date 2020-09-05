<?php
/******************************************************************************
    
    Lists duplicate data in Cura data
    
    @license    GPL
    @history    2020-09-03 14:01:29+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cura\look;

use g5\patterns\Command;
use g5\commands\cura\CuraRouter;
use g5\model\DB5;
use g5\model\Person;

class dup implements Command {
    
    // *****************************************
    // Implementation of Command
    /** 
        @param  $params empty array
        @return Report
    **/
    public static function execute($params=[]): string {
        
        $filesCura = CuraRouter::computeDatafiles('all');
        
        $dblink = DB5::getDbLink();
        $persons = [];
        foreach($dblink->query("select * from person") as $row){
            $p = Person::row2person($row);
            if(!isset($p->data['ids-in-sources']['cura'])){
                continue;
            }
            $persons[$p->data['ids-in-sources']['cura']] = $p;
        }
        
        $dups = [];
        foreach($persons as $p){
            $tmp = [];
            foreach($p->data['ids-in-sources'] as $k => $v){
                if(in_array($k, $filesCura)){
                    $tmp[] = "$k-$v"; // = Gauquelin id
                }
            }
            if(count($tmp) > 1){
                $dups[] = $tmp;
            }
        }
        
        $N2 = $N3 = $N4 = 0;
        $res = '<table class="wikitable margin alternate">' . "\n"
             . "    <tr><th>Ids</th><th>Person</th></tr>\n";
        foreach($dups as $dup){
            $N++;
            if(count($dup) == 2){
                $N2++;
            }
            else if(count($dup) == 3){
                $N3++;
            }
            else {
                $N4++;
            }
            $p =& $persons[$dup[0]]; // $dup[0] = Gauquelin id
            $res .= "    <tr>\n";
            $res .= "        <td>" . implode('<br>', $dup) . "</td>\n";
            $date = $p->data['birth']['date'] ?? $p->data['birth']['date-ut'];
            $res .= "        <td>" . $p->data['name']['family'] . ' ' . $p->data['name']['given'] . '<br>' . $date . "</td>\n";
            $res .= "    </tr>\n";
        }
        $res .= "</table>\n";
        $res .= "$N2 persons appear twice\n";
        $res .= "$N3 persons appear 3 times\n";
        $res .= "$N4 persons appear more than 3 times\n";
        return $res;
    }
    
} // end class
