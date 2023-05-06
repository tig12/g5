<?php
/******************************************************************************
    Inserts issues for the missing names of serie A.
    This is done at the end of the build process to save execution time and simplify the code.
    Avoids to create issues in gauq/A/tmp2db and delete them in Müller and skeptics tmp2db classes
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-05-06 09:15:31+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\init;

use tiglib\patterns\Command;
use g5\model\DB5;
use g5\model\wiki\issue;

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
        $dblink = DB5::getDbLink();
        $N_inserted = 0;
        $stmt_insert = $dblink->prepare("insert into issue(id_person,slug,type,description)values(?,?,?,?)");
        $baseDescription = 'Missing Name in Gauquelin file ';
        $t1 = microtime(true);
        $stmt = $dblink->query("select id,slug,partial_ids->>'lerrcp' as \"lerrcp\" from person where slug like 'gauquelin-%'");
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $id_person = $row['id'];
            $issue_slug = Issue::computeSlugFromPersonSlugAndType($row['slug'], Issue::TYPE_NAME);
            $tmp = explode('-', $row['lerrcp']); // $row['lerrcp'] = string like 'A2-217'
            $file = $tmp[0]; // $file = string like 'A2'
            $description = $baseDescription . $file;
            $stmt_insert->execute([$id_person, $issue_slug, Issue::TYPE_NAME, $description]);
            $N_inserted++;
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $report .= "Inserted $N_inserted name issues ($dt s)\n";
        return $report;
    }
    
} // end class
