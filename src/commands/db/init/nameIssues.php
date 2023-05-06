<?php
/******************************************************************************
    Inserts issues for the missing names of serie A.
    This is done at the end of the build process to save execution time and simplify the code.
    Avoids to create issues in gauq/A/tmp2db and delete them in Müller and skeptics tmp2db classes
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-05-06 09:15:31+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\init;

use g5\model\wiki\Issue;
use g5\model\wiki\Wikiproject;
use g5\model\Person;
use g5\model\DB5;
use tiglib\patterns\Command;

class nameIssues implements Command {
    
    /** 
        @param  $params empty array
        @return report.
    **/
    public static function execute($params=[]): string {
        if(count($params) != 0){
            return "USELESS PARAMETER {$params[0]}\n";
        }
        $report = "--- db init nameIssues ---\n";
        $t1 = microtime(true);
        $dblink = DB5::getDbLink();
        $N_inserted = 0;
        $baseDescription = 'Missing name in Gauquelin file ';
        $wp = Wikiproject::createFromSlug('fix-name');
        $stmt = $dblink->query("select slug from person where slug like 'gauquelin-%'");
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $p = Person::createFromSlug($row['slug']);
            $tmp = explode('-', $p->data['partial-ids']['lerrcp']); // $tmp = string like 'A2-217'
            $file = $tmp[0]; // $file = string like 'A2'
            $description = $baseDescription . $file;
            $issue = new Issue($p, Issue::TYPE_NAME, $description);
            $id_issue = $issue->insert();
            if($id_issue == -1){
                echo "PROBLEM - could not insert issue {$issue->data['slug']}\n";
                exit;
            }
            $N_inserted++;
            $issue->linkToWikiproject($wp);
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $report .= "Inserted $N_inserted name issues ($dt s)\n";
        return $report;
    }
    
} // end class
