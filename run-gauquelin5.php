<?php
/********************************************************************************
    CLI (command line interface) management of Gauquelin 5 import
    
    usage : php run-gauquelin5.php
            and follow the instructions
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2017-04-26 12:18:30+02:00, Thierry Graff : creation
********************************************************************************/

define('DS', DIRECTORY_SEPARATOR);

require_once __DIR__ . DS . 'src' . DS . 'init' . DS . 'init.php';

use gauquelin5\Gauquelin5;


$series = array_keys(Gauquelin5::SERIES_ACTIONS);
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
if(!in_array($action, Gauquelin5::SERIES_ACTIONS[$serie])){
    echo "!!! INVALID ACTION FOR SERIE $serie !!! : ";
    if(!empty(Gauquelin5::SERIES_ACTIONS[$serie])){
        echo "- possible choices : '" . implode("' or '", Gauquelin5::SERIES_ACTIONS[$serie]) . "'\n";
    }
    else{
        echo "There is no action implemented for this serie\n";
    }
    exit;
}


//
// run
//
try{
    echo Gauquelin5::action($action, $serie); /// here run action ///
}
catch(Exception $e){
    echo 'Exception : ' . $e->getMessage() . "\n";
    echo $e->getFile() . ' - line ' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

