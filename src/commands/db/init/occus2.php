<?php
/******************************************************************************
    
    Completes occus1
    For all groups of type Group::TYPE_OCCU
    - Fills table person_groop for occupation groups
    - Completes table groop with fields 'n' and 'children'.
    This can be done only for all occupations in the same run.
    
    Computation of occupation groups is a 3-steps process :
    - The groups are created in class occus1
    - The persons are created (by tmp2db commands)
    - The groups are filled by current command
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-08-01 16:36:23+02:00, Thierry Graff : Creation
    @pre        commands/db/init/occus1 must have been executed before
********************************************************************************/
namespace g5\commands\db\init;

use tiglib\patterns\Command;
use g5\model\DB5;
use g5\model\Group;
use g5\model\Occupation;

class occus2 implements Command {
    
    /** 
        @param  $params Empty array
        @return report.
    **/
    public static function execute($params=[]): string {
        if(count($params) != 0){
            return "INVALID USAGE - Useless parameter: {$params[0]}\n";
        }
        $report = "--- db init occus2 ---\n";
        $t1 = microtime(true);
        
        $allAncestors = Group::getAllAncestors();
        $todos = array_keys($allAncestors);
        //
        // initialize the groups
        //
        $groups = [];
        foreach($todos as $todo){
            $test = Group::getBySlug($todo); // DB
            if($test->data['type'] != Group::TYPE_OCCU){
                continue;
            }
            $groups[$todo] = $test;
        }
        //
        // fill members
        //
        $dblink = DB5::getDbLink();
        $stmt = $dblink->query("select id,occus from person");
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $occus = json_decode($row['occus'], true);
            $personId = $row['id'];
            foreach($occus as $occu){
                $groups[$occu]->addMember($personId);
                foreach($allAncestors[$occu] as $ancestor){
                    $groups[$ancestor]->addMember($personId); // HERE data['n'] is incremented
                }
            }
        }
        //
        // Compute field children and insert in database
        //
        $N = 0; // only useful for report
        foreach($groups as $group){
            $group->data['children'] = Group::getDescendants($group->data['slug'], includeSeed:false);
            $group->update(); // DB
            $N += $group->data['n'];
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $report .= "Inserted $N lines in person_groop ($dt s)\n";
        return $report;
    }
    
} // end class
