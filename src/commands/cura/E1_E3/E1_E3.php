<?php
/********************************************************************************
    Contains code common to several E1 E3 classes.
    
    @license    GPL
    @history    2019-06-07 12:21:18+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cura\E1_E3;

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
            'PH' => 'PH',
            'MI' => 'MI',
            'EX' => 'EX',
            'PH,EX' => 'PH+EX',
            'MI,PH' => 'MI+PH',
            'MI,EX' => 'MI+EX',
        ],
        'E3' => [
            'PO' => 'PO',
            'JO' => 'JO',
            'WR' => 'WR',
            'AC' => 'ACT',   // [including Pop Singers]
            'PAI' => 'PAI', // [including 1 sculptor]
            'MUS' => 'MUS',
            'OPE' => 'OPE',
            'CAR' => 'CAR',
            'DAN' => 'DAN',
            'PHO' => 'PHO',
        ],
    ];
    
}// end class    
