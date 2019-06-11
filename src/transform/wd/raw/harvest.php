<?php
/********************************************************************************
    
    @license    GPL
    @history    2019-05-16 12:16:35+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\wd\raw;

use g5\Config;
use g5\patterns\Command;
use g5\model\Full;
use g5\transform\wd\Wikidata;
use tiglib\arrays\csvAssociative;

class harvest implements Command{
    
    // *****************************************
    /** 
        Store the content of one or several csv file to yaml files of 5-tmp/full
        @param $params array with one element :
            relative path from dirs/1-wd-raw of config.yml to the csv file to import, without .csv extension.
            Ex : if the value dirs/1-wd-raw is data/1-raw/wikidata.org
            and the csv file to import is data/1-raw/wikidata.org/science/maths.csv
            Then the parameter must be "science/maths".
        @return report
    **/
    public static function execute($params=[]): string{
    }
        
}// end class    
