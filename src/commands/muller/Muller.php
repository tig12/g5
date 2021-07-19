<?php
/********************************************************************************
    Constants and utilities shared by several classes of this package.
    
    @license    GPL
    @history    2021-07-19 15:31:36+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\muller;

class Muller {
    
    /**
        Path to the yaml file containing the characteristics of Müller's Astro-Forschungs-Daten source.
        Relative to directory data/model/source
    **/
    const AFD_SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd.yml';
    
    /**
        Slug of Astro-Forschungs-Daten source.
    **/
    const AFD_SOURCE_SLUG = 'afd';
    
} // end class
