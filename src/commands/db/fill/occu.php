<?php
/******************************************************************************
    
    Fills table occu (occupations) from data/model/occu
    
    @license    GPL
    @history    2021-07-28 20:59:37+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\fill;

class occu implements Command {
    
    /** 
        @param  $params empty array
        @return report.
    **/
    public static function execute($params=[]): string {
        if(count($params) != 0){
            return "USELESS PARAMETER {$params[0]}\n";
        }
        $report = "--- db fill occu ---\n";
        
        $dblink = DB5::getDbLink();
        
        $dblink->exec("delete from search");
        
        $stmt_insert = $dblink->prepare("insert into search(slug,day,name)values(?,?,?)");
        
        $N_person = 0;
        $N_inserted = 0;
        
        $t1 = microtime(true);
        $query = "select slug,name from person";
        $stmt = $dblink->query($query);
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $slug = $row['slug'];
            $json_name = json_decode($row['name'], true);
            $names = Person::computeNames($json_name);
            $bday = substr($slug, -10);
            foreach($names as $name){
                $insertedName = "$name $bday";
                $stmt_insert->execute([$slug, $bday, $insertedName]);
                $N_inserted++;
            }
            $N_person++;
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $report .= "Inserted $N_inserted lines for $N_person persons ($dt s)\n";
        return $report;
    }
    
} // end class
