<?php
/********************************************************************************
    Contains code common to several E1 E3 classes.
    
    @license    GPL
    @history    2019-06-07 12:21:18+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\gauq\E1_E3;

class E1_E3{
    
    // TRUST_LEVEL not defined, using value of class Cura
    
    /** Fields of data/raw/cura/E1.csv and E3.csv **/
    const RAW_FIELDS = [
        'NUM',
        'PRO',
        'NAME',
        'NOTE', // This one not in original column names, added for g5
        'DAY',
        'MON',
        'YEA',
        'H',
        'CITY',
        'COD',
    ];
    
    /** Fields of data/tmp/cura/E1.csv and E3.csv **/
    const TMP_FIELDS = [
        'NUM',
        'OCCU',
        'NOTE',
        'FNAME',
        'GNAME',
        'DATE',
        'TZO',
        'PLACE',
        'CY',
        'C2',
        'C3',
        'LG',
        'LAT',
        'GEOID',
        'MO',
        'VE',
        'MA',
        'JU',
        'SA',
    ];
    
    /**
        Associations between Cura profession codes and g5 codes for E1 and E3
    **/
    const PROFESSIONS = [
        'E1' => [
            'PH' => 'physician',
            'MI' => 'military-personnel',
            'EX' => 'executive',
            'PH,EX' => 'physician+executive',
            'MI,PH' => 'military-personnel+physician',
            'MI,EX' => 'military-personnel+executive',
        ],
        'E3' => [
            'PO' => 'politician',
            'JO' => 'journalist',
            'WR' => 'writer',
            'AC' => 'actor',   // [including Pop Singers]
            'PAI' => 'painter', // [including 1 sculptor]
            'MUS' => 'musician',
            'OPE' => 'opera-singer',
            'CAR' => 'cartoonist',
            'DAN' => 'dancer',
            'PHO' => 'photographer',
        ],
    ];
    
}// end class    
