<?php
/******************************************************************************
    
    Creates all tables of db5
    WARNING : all existing tables are dropped and recreated
    
    @license    GPL
    @history    2020-08-08 17:22:57+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\admin;

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
        $dir_sql = dirname(dirname(dirname(dirname(__DIR__)))) . DS . 'build' . DS . 'db5-create-tables';
        $tables = [
            'person',
            'groop',
            'source',
            'person_groop',
        ];
        $dblink = DB5::getDblink();
        foreach($tables as $table){
            $sql = file_get_contents($dir_sql . DS . $table . '.sql');
            $dblink->exec("drop table if exists $table cascade");
            $dblink->exec($sql);
            $report .= "Create table $table\n";
        }
        return $report;
    }
    
    
}// end class
