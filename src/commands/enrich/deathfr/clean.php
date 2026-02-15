<?php
/********************************************************************************
    
    Removes incoherent lines from the sqlite database.
    This step is not part of the g5 process, which was only focused on matching g5 persons / death-fr data.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2026-02-15 13:09:15+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\enrich\deathfr;

use g5\app\Config;
use tiglib\patterns\Command;

class clean implements Command {
    
    /** 
        @param $params empty array
        @return Empty string, echoes its output
    **/
    public static function execute($params=[]) {
        //
        // parameter check
        //
        if(count($params) != 0){
            echo "INVALID CALL - useless parameter: '{$params[0]}' - This command must be called without parameter\n";
            return;
        }
        $sqlite = Deathfr::sqliteConnection();
        $stmt_delete = $sqlite->prepare("delete from person where rowid=:rowid");
        $N = 28917511; // 28 917 511
        $limit = 50000;
//        $offset = 0;
        $offset = 8600000;

        $nbad = 0;
        while($offset - $limit <= $N){
            $stmt = $sqlite->query("select rowid,* from person limit $limit offset $offset");
            foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
                $bdate = date_create($row['bday']);
                if($bdate === false){
                    echo "INVALID BIRTH DATE {$row['bday']} - {$row['fname']} {$row['gname']} - death {$row['dday']} - rowid {$row['rowid']}\n";
                    $stmt_delete->execute([':rowid' => $row['rowid']]);
                    $nbad++;
                    continue;
                }
                //
                $ddate = date_create($row['dday']);
                if($ddate === false){
                    echo "INVALID DEATH DATE {$row['dday']} - {$row['fname']} {$row['gname']} - death {$row['dday']} - rowid {$row['rowid']}\n";
                    $stmt_delete->execute([':rowid' => $row['rowid']]);
                    $nbad++;
                    continue;
                }
                //
                if($row['bday'] > $row['dday']){
                    echo "INCHOERENCE: birh day {$row['bday']} posterior to death date {$row['dday']} - {$row['fname']} {$row['gname']} - death {$row['dday']} - rowid {$row['rowid']}\n";
                    $stmt_delete->execute([':rowid' => $row['rowid']]);
                    $nbad++;
                    continue;
                }
            }
            $offset += $limit;
        }
        echo "Suppressed $nbad rows\n";
        // 77 rows deleted
        // check after execution: select count(*) from person;
        // 28 917 437

    }
    
}// end class    
    