<?php
/******************************************************************************
    Interface definition for dataset command router
    See docs/code-details.html
    
    @license    GPL
    @history    2019-05-13 15:09:10+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\patterns;

interface Router{
    
    /**
        Returns an array containing the possible datafiles processed by the dataset.
        @return Array of strings
    **/
    public static function getDatafiles(): array;
    
    /**
        @return An array of possible commands for this data source.
    **/
    public static function getCommands($datafile): array;
    
}// end interface
