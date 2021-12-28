<?php
/******************************************************************************
    
    Builds charts from the database
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-12-27 23:51:08+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\export;

use g5\app\Config;
use g5\model\DB5;
use tiglib\patterns\Command;
use tigdraw\bar;

class chart implements Command {
    
    const POSSIBLE_PARAMS = [
        'year' => "Generates a svg repartition by year",
    ];
    
    /** 
        @param $param Array containing one element (a string)
                      Must be one of self::POSSIBLE_PARAMS
        @return Report.
    **/
    public static function execute($params=[]): string {
        $possibleParams_str = '';
        foreach(self::POSSIBLE_PARAMS as $k => $v){
            $possibleParams_str .= "  '$k' : $v\n";
        }
        if(count($params) == 0){
            return "PARAMETER MISSING\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        if(count($params) > 1){
            return "USELESS PARAMETER : {$params[1]}\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        $param = $params[0];
        if(!in_array($param, array_keys(self::POSSIBLE_PARAMS))){
            return "INVALID PARAMETER\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        
        $method = 'exec_' . $param;
        return self::$method();
    }
    
    // ******************************************************
    /**
        @param  $
    **/
    private static function exec_year() {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->query('select years from stats');
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        $years = json_decode($res['years'], true);
        $mean = array_sum($years) / count($years);
        $svg = bar::svg(
            data:           $years,
            svg_separate:   true,
            title:          'Number of persons per year',
            barW:           4,
            xlegends:       ['min', 'max'],
            ylegends:       ['min', 'max', 'mean'],
            stats:          ['mean' => $mean],
            meanLine:       true,
            meanLineStyle: 'stroke:black; stroke-dasharray:5,15;'
        )[1];
        $outfile = implode(DS, [Config::$data['dirs']['opengauquelin.org'], 'src', 'static', 'tmp', 'years.svg']);
        file_put_contents($outfile, $svg);
        return "Generated $outfile\n";
    }
    
} // end class
