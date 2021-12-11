<?php
/********************************************************************************
    Constants that can be used by all parts of the program
    
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
    
    
}// end class
