<?php
/********************************************************************************
    Constants and utilities that can be used by all parts of the program
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-06-07 01:00:27+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5;

class G5{
    
    /**
        Path to the yaml file containing the characteristics of g5 program,
        when seen as an information source.
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'g5.yml';
    
    /** 
        Separator for all csv files of data/tmp/ and data/db/init/
    **/
    const CSV_SEP = ';';
    
    /** 
        Used in all files containing human tweaks in data/db/init
        This key is considered as notes and is not processed by tweak2tmp commands.
    **/
    const TWEAK_BUILD_NOTES = 'build-notes';
    
    const ROOT_DIR = __DIR__;
    
    // ******************************************************
    /**
        Command helper which permits to express a set of parameter names and their values.
        Useful when a command has optional parameters.
        Ex:
            dozip=true,export=toto
        is converted to
            ['dozip' => true, 'export' => 'toto']
        Parameters are separated by a comma.
        Parameter name and parameter value are separated by = sign.
        NOTE: this generic mechanism is used for exports, see g5\commands\db\export\Export
        @param  $str String to parse, containing the parameters and their values.
    **/
    public static function parseOptionalParameters(string $str): array {
        $res = [];
        $tmp1 = explode(',', $str);
        foreach($tmp1 as $tmp2){
            $tmp3 = explode('=', $tmp2);
            if(count($tmp3) != 2){
                throw new \Exception("INVALID PARAMETER STRING: $str");
            }
            $res[$tmp3[0]] = $tmp3[1];
        }
        return $res;
    }
    
    /**
        Does the opposite of parseOptionalParameters() : converts an array of optional parameters to a string expressing these parameters
        Ex:
            ['dozip' => true, 'export' => 'toto']
        is converted to
            dozip=true,export=toto
    **/
    public static function computeOptionalParametersString(array $options): string {
        $tmp = [];
        foreach($options as $k => $v){
            $tmp[] = "$k=$v";
        }
        return implode(',', $tmp);
    }
    
    
}// end class
