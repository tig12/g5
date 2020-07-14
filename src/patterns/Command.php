<?php
/******************************************************************************
    Interface definition for Command design pattern
    
    @license    GPL
    @history    2019-05-11 17:33:48+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\patterns;

interface Command{
    
    /** 
        Do something
        @return report : string describing the result of execution.
    **/
    //public static function execute($params=[]): string;
    public static function execute($params=[]);
    
} // end interface
