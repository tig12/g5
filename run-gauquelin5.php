<?php
/********************************************************************************
    CLI (command line interface) management of Gauquelin 5 import
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2017-04-26 12:18:30+02:00, Thierry Graff : creation
********************************************************************************/

/** 
    Association serie name => available actions for this serie
**/
$series_actions = [
    'A'=> ['cura2csv', 'manu2csv'],
    'A1'=> ['cura2csv', 'manu2csv'],
    'A2'=> ['cura2csv', 'manu2csv'],
    'A3'=> ['cura2csv', 'manu2csv'],
    'A4'=> ['cura2csv', 'manu2csv'],
    'A5'=> ['cura2csv', 'manu2csv'],
    'A6'=> ['cura2csv', 'manu2csv'],
    //
    '1955'=> ['cura2csv'],
    //
    'B'=> ['cura2csv'],
    'B1'=> ['cura2csv'],
    'B2'=> ['cura2csv'],
    'B3'=> ['cura2csv'],
    'B4'=> ['cura2csv'],
    'B5'=> ['cura2csv'],
    'B6'=> ['cura2csv'],
    //
    'D6'=> [''],
    'D9a'=> [''],
    'D9b'=> [''],
    'D9c'=> [''],
    'D10'=> [''],
    //
    'E1'=> ['cura2csv'],
    //
    'E2'=> [''],
    'E2a'=> [''],                                                                       
    'E2b'=> [''],
    'E2c'=> [''],
    'E2d'=> [''],
    'E2e'=> [''],
    'E2f'=> [''],
    'E2g'=> [''],
    //
    'E3'=> ['cura2csv'],
    'F1'=> [''],
    'F2'=> [''],
];
$series = array_keys($series_actions);
$series_str = implode("' or '", $series);

$USAGE = <<<USAGE
usage : 
    php {$argv[0]} <serie> <action>
with :
    <serie> = '{$series_str}'
    <action> = 'cura2csv' or 'manu2csv'
Examples :
    php {$argv[0]} A2 cura2csv       # will convert file 902gdA2.html to A2.csv
    php {$argv[0]} A2 manu2csv       # will convert manually edited version of A2.csv to A2.csv
Notes :
    - if serie = A, will compute series A1 to A6
    - if serie = B, will compute series B1 to B6
    - if serie = E2, will compute series E2a to E2g

USAGE;

// check arguments
if(count($argv) != 3){
    die($USAGE);
}

// check serie
$serie = $argv[1];
if(!in_array($serie, $series)){
    echo "!!! INVALID SERIE !!! : '$serie' - possible choices : '$series_str'\n";
    exit;
}

// check action
$action = $argv[2];
if(!in_array($action, $series_actions[$serie])){
    echo "!!! INVALID ACTION FOR SERIE $serie !!! : - possible choices : '" . implode("' or '", $series_actions[$serie]) . "'\n";
    exit;
}


//
// run
//
define('DS', DIRECTORY_SEPARATOR);

require_once __DIR__ . DS . 'src' . DS . 'init' . DS . 'init.php';

use gauquelin5\Gauquelin5;
try{
    echo Gauquelin5::action($action, $serie);
}
catch(Exception $e){
    echo 'Exception : ' . $e->getMessage() . "\n";
    echo $e->getFile() . ' - line ' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

