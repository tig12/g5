<?php
/********************************************************************************
    Contains code common to several D10 classes.
    
    @license    GPL
    @history    2019-05-09 13:54:56+02:00, Thierry Graff : creation from a split of cura2geo
********************************************************************************/
namespace g5\commands\gauq\D10;

class D10{
    
    // TRUST_LEVEL not defined, using value of class Cura

    /** Fields of data/raw/gauq/lerrcp/D10.csv **/
    const RAW_FIELDS = [
        'NUM',
        'NAME',
        'PRO',
        'DAY',
        'MON',
        'YEA',
        'H',
        'TZ',
        'LAT',
        'LON',
        'CICO',
    ];

    /** Fields of data/tmp/gauq/lerrcp/D10.csv **/
    const TMP_FIELDS = [
        'NUM',
        'FNAME',
        'GNAME',
        'DATE',
        'TZO',
        'PLACE',
        'CY',
        'C2',
        'LG',
        'LAT',
        'OCCU',
        'C_APP',
    ];
    
}// end class    
