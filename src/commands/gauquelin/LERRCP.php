<?php
/******************************************************************************

    LERRCP = Laboratoire d'Étude des Relations entre Rythmes Cosmiques et Psychophysiologiques
    Class used by source management
                                   
    @license    GPL
    @history    2021-07-20 07:39:16+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\gauquelin;

class LERRCP {
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory data/model/source
    **/
    const SOURCE_DEFINITION_FILE = 'gauquelin' . DS . 'lerrcp.yml';
    
    /** Slug of source  **/
    const SOURCE_SLUG = 'lerrcp';
    
}// end class
