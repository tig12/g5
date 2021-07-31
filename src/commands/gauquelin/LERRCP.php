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
    
    /**
        Returns a unique Gauquelin id, like "A1-654"
        Unique id of a record among birth dates published by Gauquelin's LERRCP.
        See https://tig12.github.io/gauquelin5/cura.html for precise definition.
        @param $datafile    String like 'A1'
        @param $NUM         Value of field NUM of a record within $datafile
    **/
    public static function gauquelinId($datafile, $NUM){
        return "$datafile-$NUM";
    }
    
    
}// end class
