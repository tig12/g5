<?php
/******************************************************************************
    
    Fills a source in database
    
    @license    GPL
    @history    2021-07-18 16:45:40+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\fill;

use g5\patterns\Command;
use g5\Config;
use tiglib\filesystem\globRecursive;
use g5\model\Source as ModelSource;

class source implements Command {
    
    
    // *****************************************
    // Implementation of Command
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
        
        $yamlfiles = [];
        $basedir = Config::$data['dirs']['ROOT'] . DS . Config::$data['dirs']['model'] . DS . 'source';
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
        echo "--- db fill source $param ---\n";
        foreach($yamlfiles as $file){
            try{
                $source = ModelSource::getSource($file);
            }
            catch(\Exception $e){
                return "ERROR - invalid YAML file : $file\n"
                    . "Check the syntax and try again\n";
            }
            $slug = $source->data['slug'];
            $test = ModelSource::getBySlug($slug);
            if(is_null($test)){
                $source->insert();
                echo "Inserted source '$slug' in database from $file\n";
            }
            else{
/* 
WARNING - bug here - update is not done becaus sourcee id is not known
ModelSource::getSource() take data from yaml, not from db => doesn't have id
*/
                $source->update();
                echo "Updated source '$slug' in database from $file\n";
            }
        }
        return '';
    }
    
} // end class
