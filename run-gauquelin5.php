<?php
/********************************************************************************
    CLI (command line interface) management of Gauquelin 5 import
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2017-04-26 12:18:30+02:00, Thierry Graff : creation
********************************************************************************/

$series = [
    'A',
    'A1',
    'A2',
    'A3',
    'A4',
    'A5',
    'A6',
    '1955',
    //
    'B',
    'B1',
    'B2',
    'B3',
    'B4',
    'B5',
    'B6',
    //
    'D6',
    'D9a',
    'D9b',
    'D9c',
    'D10',
    //
    'E1',
    //
    'E2',
    'E2a',
    'E2b',
    'E2c',
    'E2d',
    'E2e',
    'E2f',
    'E2g',
    //
    'E3',
    'F1',
    'F2',
];
$series_str = implode("' or '", $series);

$USAGE = <<<USAGE
usage : 
    php {$argv[0]} <serie>
with :
    <serie> = '{$series_str}'
Examples :
    php {$argv[0]} A2        # will convert file 902gdA2.html to A2.csv
Notes :
    - if serie = A, will compute series A1 to A6
    - if serie = B, will compute series B1 to B6
    - if serie = E2, will compute series E2a to E2g

USAGE;

// check arguments
if(count($argv) < 2){
    die($USAGE);
}

// check serie
$serie = $argv[1];
if(!in_array($serie, $series)){
    echo "!!! INVALID SERIE !!! : '$serie' - possible choices : '$series_str'\n";
    exit;
}

//
// run
//
define('DS', DIRECTORY_SEPARATOR);

require_once __DIR__ . DS . 'src' . DS . 'init' . DS . 'init.php';

use gauquelin5\Gauquelin5;
try{
    echo Gauquelin5::import($serie);
}
catch(Exception $e){
    echo 'Exception : ' . $e->getMessage() . "\n";
    echo $e->getFile() . ' - line ' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

