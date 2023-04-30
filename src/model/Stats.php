<?php
/******************************************************************************
    Management of table stats.
    
    TODO    Some code of commands/db/init/stats.php should be done in this class.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-01-02 08:30:50+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

use g5\model\DB5;
use g5\model\Trust;

class Stats{
    
    /**
        Modifies table stats when a new person is added to the database.
    **/
    public static function addPerson(Person $p) {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->query("select * from stats");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        //
        // 1 - Compute new values
        //
        // n
        //
        $n = $row['n'] + 1;
        //
        // n_time
        // n_notime
        //
        $birthday = $p->birthday();
        if(strlen($birthday) == 10){
            $n_time = $row['n_time'] + 1;
            $n_notime = $row['n_notime'];
        }
        else {
            $n_time = $row['n_time'];
            $n_notime = $row['n_notime'] + 1;
        }
        //
        // n_checked
        //
        // here problem : n_checked is computed from trust
        // but notime persons may have been checked and have a trust != from BC
        $n_checked = $row['n_checked'];
        if($p->data['trust'] <= Trust::BC){
            $n_checked++;
        }
        //
        // countries
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
        // years
        //
        $years = json_decode($row['years'], true);
        $year = substr($birthday, 0, 4);
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
            n_notime,
            n_checked,
            countries,
            years
            )=(?,?,?,?,?,?)");
        $stmt->execute([
            $n,
            $n_time,
            $n_notime,
            $n_checked,
            json_encode($countries),
            json_encode($years)
        ]);
    }
    
    public static function updatePerson(Person $p_orig, Person $p_new) {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->query("select * from stats");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        //
        // 1 - Compute new values
        //
        // n_time
        // n_notime
        //
        $birthday_orig = $p_orig->birthday();
        $birthday_new = $p_new->birthday();
        $n_time = $row['n_time'];
        $n_notime = $row['n_notime'];
        if(strlen($birthday_orig) == 10 && strlen($birthday_new) > 10){
            $n_time++;
            $n_notime--;
        } // different case should not happen
        //
        // n_checked
        //
        // here problem : n_checked is computed from trust
        // but notime persons may have been checked and have a trust != from BC
        $n_checked = $row['n_checked'];
        $trust_orig = $p_orig->data['trust'];
        $trust_new = $p_new->data['trust'];
        if($trust_orig > Trust::BC && $trust_new <= Trust::BC){
            $n_checked++;
        }
        //
        // countries
        //
        $countries = json_decode($row['countries'], true); // create anyway to update table stats
        $country_orig = $p_orig->data['birth']['place']['cy'];
        $country_new = $p_new->data['birth']['place']['cy'];
        if($country_new != $country_orig){
            // new
            if(!isset($countries[$country_new])){
                $countries[$country_new] = 1;
            }
            else {
                $countries[$country_new]++;
            }
            // orig
            if($countries[$country_orig] == 1){
                unset($countries[$country_orig]);
            }
            else {
                $countries[$country_orig]--;
            }
        }
        //
        // years
        //
        $years = json_decode($row['years'], true); // create anyway to update table stats
        $year_orig = substr($birthday_orig, 0, 4);
        $year_new = substr($birthday_new, 0, 4);
        if($year_new != $year_orig){
            // new
            if(!isset($years[$year_new])){
                $years[$year_new] = 1;
            }
            else {
                $years[$year_new]++;
            }
            // orig
            if($years[$year_orig] == 1){
                unset($countries[$year_orig]);
            }
            else {
                $years[$year_orig]--;
            }
        }
        //
        // 2 - store new values in db
        //
        $stmt = $dblink->prepare("update stats set(
            n_time,
            n_notime,
            n_checked,
            countries,
            years
            )=(?,?,?,?,?)");
        $stmt->execute([
            $n_time,
            $n_notime,
            $n_checked,
            json_encode($countries),
            json_encode($years)
        ]);
    }
    
} // end class
