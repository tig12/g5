<?php
/********************************************************************************
    - Creates a subdirectory of data/wiki/persons where a file person.yml should be stored.
    - Creates an empty person.yml file

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-05-07 10:45:29+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\person;

use tiglib\patterns\Command;
use g5\model\wiki\WikiPerson;

class prepare implements Command {
    
    /** 
        @param  $params Array containing one element:
                    the slug of the person to add ; ex: grothendieck-alexandre-1928-03-28
        @return String report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 1){
            return "INVALID USAGE This commands needs one parameter\n";
        }
        
        $slug = $params[0];
        
        $report =  "--- wiki bc prepare $slug ---\n";
        
        try{
            $dir = WikiPerson::dirPath($slug);
        }
        catch(\Exception $e){
            return "INVALID SLUG: $slug - the directory was not created\n"
                . "Slug format: slug-name-yyyy-mm-dd";
        }
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
            $report .= "Created directory $dir\n";
        }
        
        $destFile = WikiPerson::filePath($slug);;
        # if BC.yml already exists, don't replace it
        if(!is_file($destFile)){
            copy(WikiPerson::templateFilePath(), $destFile);
            $report .= "Created file $destFile\n";
        }
        
        return $report;
    }
    
} // end class    

