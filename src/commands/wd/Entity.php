<?php
/******************************************************************************
    
    Constants related to Wikidata entities.
    
    @license    GPL
    @history    2026-01-24 20:51:44+01:00, Thierry Graff : Copied from wd-g5 program
    @history    2025-05-17 20:02:42+02:00, Thierry Graff : Isolate wd entites
    @history    2025-05-03 13:04:49+02:00, Thierry Graff : Creation
********************************************************************************/

declare(strict_types=1);

namespace g5\commands\wd;

class Entity {
    
    /** Base url for wikidata entities **/
    const ENTITY_URL = 'http://www.wikidata.org/entity';
    
    public const array ENTITY_NAMES = [
        'Q5'        => 'human',
        'Q6581072'  => 'female',
        'Q6581097'  => 'male',
    ];
    
    public const string HUMAN       = 'Q5';
    public const string FEMALE      = 'Q6581072';
    public const string MALE        = 'Q6581097';
    
} // end class
