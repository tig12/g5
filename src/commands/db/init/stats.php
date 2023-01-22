<?php
/******************************************************************************
    
    Fills table stats
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-07-21 21:34:39+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\init;

use tiglib\patterns\Command;
use g5\model\DB5;

class stats implements Command {
    
    
    /** 
        @param  $params array with one element 'full' or 'small'
        @return report.
    **/
    public static function execute($params=[]): string {
        if(count($params) != 1){
            return "INVALID USAGE - This command doesn't needs one parameter:\n"
                . "  - small : echoes a minimal report\n"
                . "  - full : echoes a detailed report\n";
        }
        
        $report = "--- db init stats ---\n";
        $report_full = '';
        
        $dblink = DB5::getDbLink();
        
        $dblink->exec("delete from stats");
        
        $query = "select count(*) from person";
        $N = $dblink->query($query)->fetch()[0];
        $report_full .= "N = $N\n";
        
        $query = "select count(*) from person where
                    length(birth->>'date') > 10
                 or length(birth->>'date-ut') > 10";
        $N_time = $dblink->query($query)->fetch()[0];
        $report_full .= "N_time = $N_time\n";
        
        $N_notime = $N - $N_time;
        $report_full .= "N_notime = $N_notime\n";
        
        $query = 'select count(*) from api_issue';
        $N_issues = $dblink->query($query)->fetch()[0];
        $report_full .= "N_issues = $N_issues\n";
        
        $countries = [];
        $query = "select birth->'place'->>'cy' as country, count(*) as count from person
            group by birth->'place'->>'cy'
            order by count(*) desc";
        $stmt = $dblink->query($query);
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $countries[$row['country']] = $row['count'];
        }
        
        // TODO rewrite with pl/pgsql ?
        $years = [];
        $query = "select substr(birth->>'date', 1, 4) as year, count(*) as count from person
            where birth->>'date-ut' is null
            group by year
            order by year";
        $stmt = $dblink->query($query);
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $years[$row['year']] = $row['count'];
        }
        $query = "select substr(birth->>'date-ut', 1, 4) as year, count(*) as count from person
            where birth->>'date' is null
            group by year
            order by year";
        $stmt = $dblink->query($query);
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            if(!isset($years[$row['year']])){
                $years[$row['year']] = 0;
            }
            $years[$row['year']] += $row['count'];
        }
        ksort($years);
        
        //
        // insert
        //
        $stmt = $dblink->prepare("insert into stats(
            n,
            n_time,
            n_notime,
            n_issues,
            countries,
            years
            )values(?,?,?,?,?,?)");
        $stmt->execute([
            $N,
            $N_time,
            $N_notime,
            $N_issues,
            json_encode($countries),
            json_encode($years)
        ]);
        
        return $params[0] == 'small' ? $report : $report_full;
    }
    
} // end class
