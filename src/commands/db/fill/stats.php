<?php
/******************************************************************************
    
    Fills table stats
    
    @license    GPL
    @history    2021-07-21 21:34:39+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\fill;

use g5\patterns\Command;
use g5\model\DB5;

class stats implements Command {
    
    
    // *****************************************
    // Implementation of Command
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
        
        $report = "--- db fill stats ---\n";
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
        
        $query = "select count(*) from person where
                    length(birth->>'date') = 10
                 or length(birth->>'date-ut') = 10";
        $N_day = $dblink->query($query)->fetch()[0];
        $report_full .= "N_day = $N_day\n";
        
        $sum = $N_time + $N_day;
        $report_full .= "N_time + N_day = $sum\n";
        if($sum != $N){
            return "ANOMALY: normally, N_time + N_day = N\n" . $report_full;
        }
        
        $countries = [];
        $query = "select birth->'place'->>'cy' as country, count(*) as count from person
            group by birth->'place'->>'cy'
            order by count(*) desc";
        $stmt = $dblink->query($query);
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $countries[$row['country']] = $row['count'];
        }
        
        // TODO rewrite with pl/pgsql
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
            n_day,
            countries,
            years
            )values(?,?,?,?,?)");
        $stmt->execute([
            $N,
            $N_time,
            $N_day,
            json_encode($countries),
            json_encode($years)
        ]);
        
        return $params[0] == 'small' ? $report : $report_full;
    }
    
} // end class
