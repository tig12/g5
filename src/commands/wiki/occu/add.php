<?php
/********************************************************************************
    Adds a new occupation in database.
    Creates a corresponding empty group.
    
    This command is not intended to be executed during a generation of the databasse from scratch.
    The new occupation must be included in data/db/occu/all-occus.csv
    and will be added in the next generation of the database.

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-05-14 23:19:45+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\occu;

use tiglib\patterns\Command;
use tiglib\arrays\csvAssociative;
use g5\model\Occupation;

class add implements Command {
    
    /** 
        @param  $params Array containing one element:
                        the slug of the occupation to add, present in data/db/occu/all-occus.csv.
        @return String report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 1){
            return "INVALID USAGE This commands needs one parameter: the slug of the occupation to add\n";
        }
        
        $slug = $params[0];
        
        $report =  "--- wiki occu add $slug ---\n";
        
        $lines = csvAssociative::compute(Occupation::getDefinitionFile());
        $found = false;
        foreach($lines as $line){
            if($line['slug'] != $slug){
                continue; // skip blank lines
            }
            Occupation::insert($line);
            $found = true;
            break;
        }
        if(!$found){
            $report .= "OCCUPATION SLUG NOT FOUND: $slug - Nothing modified in database\n";
        }
        else{
            $report .= "Inserted occupation group $slug in database\n";
        }
        return $report;
    }
    
} // end class    

