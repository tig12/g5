<?php
/********************************************************************************
    CLI (command line interface) management of Gauquelin 5 import
    
    usage : php run-gauquelin5.php
            and follow the instructions
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2017-04-26 12:18:30+02:00, Thierry Graff : creation
********************************************************************************/

/** 
    Association serie name => available actions for this serie
**/
$series_actions = [
    'A'=> ['raw2exported', 'final'],
    'A1'=> ['raw2exported', 'final'],
    'A2'=> ['raw2exported', 'final'],
    'A3'=> ['raw2exported', 'final'],
    'A4'=> ['raw2exported', 'final'],
    'A5'=> ['raw2exported', 'final'],
    'A6'=> ['raw2exported', 'final'],
    //
    '1955'=> ['modified21955'],
    //
    'B'=> ['raw2exported'],
    'B1'=> ['raw2exported'],
    'B2'=> ['raw2exported'],
    'B3'=> ['raw2exported'],
    'B4'=> ['raw2exported'],
    'B5'=> ['raw2exported'],
    'B6'=> ['raw2exported'],
    //
    'D6'=> [''],
    'D9a'=> [''],
    'D9b'=> [''],
    'D9c'=> [''],
    'D10'=> [''],
    //
    'E1'=> ['raw2exported'],
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
    'E3'=> ['raw2exported'],
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
    php {$argv[0]} A2 raw2exported       # will convert file 1-cura-raw/902gdA2.html to 2-cura-exported/A2.csv
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

