<?php
/******************************************************************************
    Interface definition for Command pattern
    
    @license    GPL
    @history    2019-05-13 15:09:10+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\patterns;

interface Dataset{
    
    /**
        Returns an array containing the possible datafiles processed by the dataset.
        @return Array of strings
    **/
    public static function getDatafiles(): array;
    
    /**
        @return An array of possible actions for this data source.
    **/
    public static function getActions($datafile): array;
        
}// end interface
