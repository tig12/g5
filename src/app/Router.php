<?php
/******************************************************************************
    Interface definition for dataset command router
    See docs/code-details.html
    
    @license    GPL
    @history    2019-05-13 15:09:10+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\app;

interface Router{
    
    /**
        Returns an array containing the possible datafiles processed by the dataset.
        @return Array of strings
    **/
    public static function getArgs2(): array;
    
    /**
        @return An array of possible commands for this datafile.
    **/
    public static function getArgs3($datafile): array;
    
}// end interface
