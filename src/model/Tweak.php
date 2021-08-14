<?php
/********************************************************************************
    Constants that can be used by all parts of the program
    
    @license    GPL
    @history    2019-06-07 01:00:27+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\model;

class TWEAK {
    
    /** 
        Used to identify a tweak.
        WARNING: this does not represent a source slug in the usual way.
            It is used only in 'raw' and 'istory' person field,
            not in 'sources' or 'ids-in-sources' fields.
    **/
    const SOURCE = 'tweak';
    
    /** 
        This key is considered as notes and is not processed by tweak2db command.
    **/
    const BUILD_NOTES = 'BUILD-NOTES';
    
    
}// end class
