<?php
/******************************************************************************
    
    Creates all tables of db5
    WARNING : all existing tables are dropped and recreated
    
    @license    GPL
    @history    2020-08-08 17:22:57+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\build;

use g5\patterns\Command;
use g5\Config;
use g5\model\DB5;

class dbcreate implements Command {
    
    // *****************************************
    // Implementation of Command
    /** 
        @param  $params empty array
    **/
    public static function execute($params=[]): string {
        $report = '';
        $dir_sql = implode(DS, [Config::$data['dirs']['ROOT'], 'src', 'model', 'db-create-tables']);
        $tables = [
            'person',
            'groop',
            'source',
            'person_groop',
        ];
        $dblink = DB5::getDblink();
        foreach($tables as $table){
            $sql_create = file_get_contents($dir_sql . DS . $table . '.sql');
            $dblink->exec("drop table if exists $table cascade");
            $dblink->exec($sql_create);
            $report .= "Create table $table\n";
            // grant privilege for use with postgrest
            $sql_grant = "grant select on $table to " . Config::$data['db5']['postgrest']['user'];
            $dblink->exec($sql_grant);
            $report .= "$sql_grant\n";
        }
        return $report;
    }
    
    
}// end class
