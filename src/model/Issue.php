<?php
/******************************************************************************
    
    @license    GPL
    @history    2021-10-10 02:19:46+02:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

//use g5\app\Config;
//use g5\model\DB5;

Issue::init();
class Issue {
    
    //Standard values for keys
    
    /** Check the name **/
    const CHK_NAME = 'chk-name';
    
    /** Check day or time or both **/
    const CHK_DATE = 'chk-date';
    
    const CHK_DAY = 'chk-day';
    
    const CHK_TIME = 'chk-time';
    
    /** Check timezone offset **/
    const CHK_TZO = 'chk-tzo';
    
    /** Check one of the component of the name **/
    const CHK_NAME = 'chk-name';
    
    /** 
        Associative array with the structure defined in Issue.yml
        Values of fields are empty
    **/
    public static $STRCUTURE;
    
    /**
    **/
    public static function init() {
        self::$STRCUTURE = yaml_parse_file(__DIR__ . DS . 'Issue.yml');
    }
    
    
    /**
        @param  $data
                    Associative array with the structure defined in Issue.yml.
                    May be incomplete.
        @return
                    Associative array with the structure defined in Issue.yml.
                    Valid Issue - incomplete fields completed.
    **/
    public static function newTodo($data) {
        $res = array_replace_recursive(self::$STRCUTURE, $data);
        if($res['type'] == ''){
            $res['type'] = $res['key'];
        }
        return $res;
    }
    
} // end class
