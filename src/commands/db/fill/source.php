<?php
/******************************************************************************
    
    Fills a source in database
    
    @license    GPL
    @history    2021-07-18 16:45:40+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\fill;

use tiglib\patterns\Command;
use g5\app\Config;
use tiglib\filesystem\globRecursive;
use g5\model\Source as ModelSource;

class source implements Command {
    
    /** 
        Inserts or updates a source in database
        @param  $params array containing one element. Can be :
                    - 'all'
                    - the path to the yaml file containing the source definition, relative to data/model/source
        @return Empty string, echoes the report on execution for each source processed.
    **/
    public static function execute($params=[]): string {
        if(count($params) != 1){
            return "INVALID USAGE - This command needs one parameter :\n"
                . "  'all' or path to yaml file containing the source definition, relative to data/model/source\n";
        }
        $param = $params[0];
        
        echo "--- db fill source $param ---\n";
        
        $yamlfiles = [];
        $basedir = ModelSource::$DIR;
        if($param == 'all'){
            $yamlfiles = globRecursive::execute($basedir . '*' . DS . '*.yml');
        }
        else{
            $file = $basedir . DS . $param;
            if(!is_file($file)){
                return "INVALID FILE NAME: $file\n";
            }
            $yamlfiles[] = $file;
        }
        foreach($yamlfiles as $file){
            $relativePath = str_replace($basedir . DS, '', $file);
            try{
                $source = new ModelSource($relativePath);
            }
            catch(\Exception $e){
                return $e->getMessage() . "\n";
            }
            $slug = $source->data['slug'];
            $test = ModelSource::getBySlug($slug);
            if(is_null($test)){
                $source->insert();
                echo "Inserted source '$slug' in database from $relativePath\n";
            }
            else{
                $source->update();
                echo "Updated source '$slug' in database from $relativePath\n";
            }
        }
        return '';
    }
    
} // end class
