<?php 
// Software released under the General Public License (version 3 or later), available at
// http://www.gnu.org/copyleft/gpl.html
/********************************************************************************
    Façade to use yaml parsing
    
    @license    GPL
    @history    2017-04-27 09:52:21+02:00 Thierry Graff : Creation
********************************************************************************/


class YAML{
    
    // ******************************************************
    /** 
        Pares a YAML file and returns an associative array
        @return array
    **/
    public static function parse($filename){
        if(!class_exists('sfYamlParser')){
            // for calling code without autoload
            require_once 'sfYamlParser.php';
        }    
        $yaml = new sfYamlParser();
        return $yaml->parse(file_get_contents($filename));
    }// end parse
        
}// end class


