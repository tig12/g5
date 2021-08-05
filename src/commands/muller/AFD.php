<?php
/********************************************************************************
    Constants and utilities related to AFD (Müller's Astro-Forschungs-Daten booklets).
    
    @license    GPL
    @history    2021-07-19 15:31:36+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\muller;

class AFD {
    
    /**
        Path to the yaml file containing the characteristics of Müller's Astro-Forschungs-Daten source.
        Relative to directory data/model/source
    **/
    const SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd.yml';
    
    /**
        Slug of Astro-Forschungs-Daten source.
    **/
    const SOURCE_SLUG = 'afd';
    
    /**
        Trust level for data coming from Astro-Forschungs-Daten booklets.
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = 4;
    
    /**
        AFD means Astro-Forschungs-Daten
        Returns a unique Müller id, like "M5-33"
        Unique id of a record among Müller's files.
        5 means volume 5 of AFD (volumes from 1 to 5)
        33 is the id of the record within this volume.
        See https://tig12.github.io/gauquelin5/newalch.html for precise definition
        @param $source      Slug of the source, like 'afd1', 'afd1-100', 'afd2'
        @param $NR          Value of field NR of a record within $source
    **/
    public static function mullerId($source, $NR){
        if(strpos($source, 'afd') === false){
            throw new \Exception("INVALID SOURCE: $source");
        }
        $tmp = str_replace('afd', '', $source);
        return 'M' . $tmp . '-' . $NR;
    }
    
    // *********************** Person ids ***********************
    /**
        Convenience method to find Müller id from Person's $data['ids-in-source'] field.
        If the person is not related to Müller, returns empty string.
        Does not handle ids of persons published in 2 different volumes
        of Astro-Forschungs-Daten (this does not occur).
    **/
    public static function ids_in_sources2mullerId($ids_in_sources){
        foreach($ids_in_sources as $source => $id){
            if($source != 'afd' && str_starts_with($source, 'afd')){
                return AFD::mullerId($source, $id);
            }
        }
        return '';
    }
    
} // end class
