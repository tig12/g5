<?php
/******************************************************************************
    Management of table stats.
    
    TODO    Some code of commands/db/init/stats.php should be done in this class.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-01-02 08:30:50+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

use g5\model\DB5;

class Stats{
    
    /**
        Modifies table stats when a new person is added to the database.
        @param  $
    **/
    public static function addPerson(Person $p) {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->query("select * from stats");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        //
        // 1 - Compute new values
        //
        $n = $row['n'] + 1;
        //
        if($p->data['birth']['notime'] === true){
            $n_time = $row['n_time'];
            $n_day = $row['n_day'] + 1;
        }
        else {
            $n_time = $row['n_time'] + 1;
            $n_day = $row['n_day'];
        }
        //
        $n_issues = $row['n_issues'] + count($p->data['issues']);
        //
        $country = $p->data['birth']['place']['cy'];
        $countries = json_decode($row['countries'], true);
        if(!isset($countries[$country])){
            $countries[$country] = 1;
        }
        else {
            $countries[$country]++;
        }
        //
        $years = json_decode($row['years'], true);
        $year = substr($p->birthday(), 0, 4);
        if(!isset($years[$year])){
            $years[$year] = 1;
        }
        else {
            $years[$year]++;
        }
        //
        // 2 - store new values in db
        //
        $stmt = $dblink->prepare("update stats set(
            n,
            n_time,
            n_day,
            n_issues,
            countries,
            years
            )=(?,?,?,?,?,?)");
        $stmt->execute([
            $n,
            $n_time,
            $n_day,
            $n_issues,
            json_encode($countries),
            json_encode($years)
        ]);
    }
    
} // end class
