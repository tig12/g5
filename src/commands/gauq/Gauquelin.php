<?php
/********************************************************************************
    Constants and utilities related to AFD (Müller's Astro-Forschungs-Daten booklets).
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-07-19 15:31:36+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq;

class Gauquelin {
    
    /**
        Path to the yaml file containing the characteristics of Arno Müller.
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'gauquelin/gauquelin.yml';
    
    /**
        Slug of this source.
    **/
    const SOURCE_SLUG = 'gauquelin';
    
    /**
        Trust level for data coming from this source
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = 4;
    
    
    
} // end class
