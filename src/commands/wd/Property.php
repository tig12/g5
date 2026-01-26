<?php
/******************************************************************************
    
    Constants related to Wikidata properties

    @license    GPL
    @history    2026-01-24 20:52:11+01:00, Thierry Graff : Copied from wd-g5 program
    @history    2025-05-17 20:05:32+02:00, Thierry Graff : Isolate wd properties
    @history    2025-05-03 13:04:49+02:00, Thierry Graff : Creation
********************************************************************************/

declare(strict_types=1);

namespace g5\commands\wd;

class Property {
    
    public const array PROPERTY_NAMES = [
        'P31'       => 'instance of',
        //
        'P279'      => 'subclass of',
        //
        'P734'      => 'family name',
        'P735'      => 'given name',
        'P1448'     => 'official name',
        'P1449'     => 'nickname',
        'P1477'     => 'birth name',
        'P1813'     => 'short name',
        'P2561'     => 'name',
        'P2562'     => 'married name',
        //
        'P569'      => 'date of birth',
        'P19'       => 'place of birth',
        //              
        'P570'      => 'date of death',
        'P20'       => 'place of death',
        //
        'P106'      => 'occupation',
        //
        'P21'       => 'sex or gender',
    ];
    
    public const string INSTANCE_OF                 = 'P31';
    
    public const string SUBCLASS_OF                 = 'P279';
    
    public const string FAMILY_NAME                 = 'P734';
    public const string GIVEN_NAME                  = 'P735';
    public const string OFFICIAL_NAME               = 'P1448';
    public const string NICKNAME                    = 'P1449';
    public const string BIRTH_NAME                  = 'P1477';
    public const string NAME_IN_NATIVE_LANGUAGE     = 'P1559';
    public const string SHORT_NAME                  = 'P1813';
    public const string NAME                        = 'P2561';
    public const string MARRIED_NAME                = 'P2562';
    
    public const string DATE_OF_BIRTH               = 'P569';
    public const string PLACE_OF_BIRTH              = 'P19';
    
    public const string DATE_OF_DEATH               = 'P570';
    public const string PLACE_OF_DEATH              = 'P20';
    
    public const string OCCUPATION                  = 'P106';
    
    public const string SEX_OR_GENDER               = 'P21';

    /** Properties used in a g5 person **/
    public const array USEFUL_PROPERTIES = [
        self::FAMILY_NAME,
        self::GIVEN_NAME,
        self::OFFICIAL_NAME,
        self::NICKNAME,
        self::BIRTH_NAME,
        self::NAME_IN_NATIVE_LANGUAGE,
        self::SHORT_NAME,
        self::NAME,
        self::MARRIED_NAME,
        
        self::DATE_OF_BIRTH,
        self::PLACE_OF_BIRTH,
        
        self::DATE_OF_DEATH,
        self::PLACE_OF_DEATH,
        
        self::OCCUPATION,
        
        self::SEX_OR_GENDER,
    ];
    
} // end class
