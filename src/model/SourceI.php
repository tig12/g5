<?php
/******************************************************************************
    
    Interface that classes representing a data source in g5 db must implement
    
    @license    GPL
    @history    2020-05-16 03:14:16+02:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

interface SourceI{
    
    /** 
        Returns a Source object
        @throws Exception if the source cannot be built.
    **/
    public static function source(): Source;
    
}// end class
