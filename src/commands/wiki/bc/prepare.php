<?php
/********************************************************************************
    - Creates a subdirectory of data/wiki/persons where a file BC.yml should be stored.
    - Creates an empty BC.yml file

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-12-24 18:21:04+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\bc;

use tiglib\patterns\Command;
use g5\model\wiki\BC;

class prepare implements Command {
    
    /** 
        @param  $params Array containing one element:
                    the slug of the person to add ; ex: galois-evariste-1811-10-25
        @return String report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 1){
            return "INVALID USAGE This commands needs one parameter, the slug of the person.\n"
                . "Ex: php run-g5.php wiki bc prepare baillaud-benjamin-1848-02-14\n";
        }
        
        $slug = $params[0];
        
        $report =  "--- wiki bc prepare $slug ---\n";
        
        try{
            $dir = BC::dirPath($slug);
        }
        catch(\Exception $e){
            return "INVALID SLUG: $slug - the directory was not created\n"
                . "Slug format: slug-name-yyyy-mm-dd";
        }
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
            $report .= "Created directory $dir\n";
        }
        
        $destFile = BC::filePath($slug);;
        # if BC.yml already exists, don't replace it
        if(!is_file($destFile)){
            copy(BC::templateFilePath(), $destFile);
            $report .= "Created file $destFile\n";
        }
        
        return $report;
    }
    
} // end class    

