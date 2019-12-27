<?php
/********************************************************************************
    Constants that can be used by all parts of the program
    
    @license    GPL
    @history    2019-06-07 01:00:27+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5;

class G5{
    
    /** 
        Separator for all csv files of data/5-tmp/ and data/3-edited/
    **/
    const CSV_SEP = ';';
    
    /** 
        Used in all files containing human tweaks in 3-edited/
        This key is considered as notes and is not processed by raw2csv steps.
    **/
    const TWEAK_BUILD_NOTES = 'build-notes';
    
    
}// end class
