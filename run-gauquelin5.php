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
    'A'=> ['first', 'final'],
    'A1'=> ['first', 'final'],
    'A2'=> ['first', 'final'],
    'A3'=> ['first', 'final'],
    'A4'=> ['first', 'final'],
    'A5'=> ['first', 'final'],
    'A6'=> ['first', 'final'],
    //
    '1955'=> ['human2final'],
    //
    'B'=> ['first'],
    'B1'=> ['first'],
    'B2'=> ['first'],
    'B3'=> ['first'],
    'B4'=> ['first'],
    'B5'=> ['first'],
    'B6'=> ['first'],
    //
    'D6'=> [''],
    'D9a'=> [''],
    'D9b'=> [''],
    'D9c'=> [''],
    'D10'=> [''],
    //
    'E1'=> ['first'],
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
    'E3'=> ['first'],
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
    <action> depends on serie
Examples :
    php {$argv[0]} A2 first       # will convert file 902gdA2.html to A2.csv
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

