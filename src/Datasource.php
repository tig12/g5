<?php
/******************************************************************************
    Interface that must be satisfied by classes implementing a data source
    @license    GPL
    @history    2019-05-10 09:30:19+02:00, Thierry Graff : Creation
********************************************************************************/

namespace g5;

interface Datasource{
    
    /** 
        Returns a list of possible actions for this data source.
    **/
    public static function getActions();
    
    /** 
        Executes an action.
    **/
    public static function action($params);
    
}// end class
