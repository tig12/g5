<?php
/********************************************************************************
    Creates a subdirectory of data/wiki/persons where a file BC.yml should be stored.

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-12-24 18:21:04+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\bc;

use tiglib\patterns\Command;
use g5\commands\wiki\Wiki;

class prepare implements Command {
    
    /** 
        @param  $params Array containing one element:
                    the slug of the person to add ; ex: galois-evariste-1811-10-25
        @return String report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 1){
            return "INVALID USAGE This commands needs one parameter\n";
        }
        
        $slug = $params[0];
        
        try{
            $dir = Wiki::slug2dir($slug);
        }
        catch(\Exception $e){
            return "INVALID SLUG: $slug - the directory was not created\n";
        }
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        
        $report =  "Created directory $dir\n";
        return $report;
    }
    
} // end class    

