<?php
/******************************************************************************
    
    Computes groups of persons with the same occupation.
    This can be done only for all occupations in the same run.
    
    @license    GPL
    @history    2021-08-01 16:36:23+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\fill;

use g5\patterns\Command;
use g5\model\DB5;
use g5\model\Group;
use g5\model\Occupation;

class occugroup implements Command {
    
    /** 
        @param  $params Empty array
        @return report.
    **/
    public static function execute($params=[]): string {
        if(count($params) != 0){
            return "INVALID USAGE - Useless parameter: {$params[0]}\n";
        }
        $report = "--- db fill occugroup ---\n";
        $t1 = microtime(true);
        
        $occuSlugNames = Occupation::getAllSlugNames();
        $allAncestors = Group::getAllAncestors();
        $todos = array_keys($allAncestors);
        //
        // initialize the groups
        //
        $groups = [];
        foreach($todos as $todo){
            $test = Group::getBySlug($todo); // DB
            if(is_null($test)){
                $test = new Group();
                $test->data['slug'] = $todo;
                $test->data['name'] = $occuSlugNames[$todo];
                $test->data['type'] = Group::TYPE_OCCU;
                $test->data['description'] = "Persons whose occupation is " . $occuSlugNames[$todo] . '.';
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
                    $groups[$ancestor]->addMember($personId);
                }
            }
        }
        //
        // insert in database
        //
        $N = 0; // only useful for report
        foreach($groups as $group){
            if(is_null($group->data['id'])){
                $group->insert(); // DB
            }
            else{
                $group->update(); // DB
            }
            $N += $group->data['n'];
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $report .= "Inserted $N lines in person_groop ($dt s)\n";
        return $report;
    }
    
} // end class
