<?php
/******************************************************************************

    CSICOP = Committee for the Scientific Investigation of Claims of the Paranormal
    Class used by source management
                                   
    @license    GPL
    @history    2021-07-20 07:33:13+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop;

class CSICOP {
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory data/model/source
    **/
    const SOURCE_DEFINITION_FILE = 'csicop' . DS . 'csicop.yml';
    
    /** Slug of source  **/
    const SOURCE_SLUG = 'csicop-committee';
    
    /** Slug of the group (all csicop records) **/
    const GROUP_SLUG = 'csicop';

    /** Slug of the group in db (canvas 1) **/
    const GROUP1_SLUG = 'csicop-canvas1';

    /** Slug of the group in db (canvas 2) **/
    const GROUP2_SLUG = 'csicop-canvas2';

    /** Slug of the group in db (canvas 3) **/
    const GROUP3_SLUG = 'csicop-canvas3';

} // end class
