<?php
/********************************************************************************
    CLI (command line interface) of Gauquelin5 program
    
    usage : php run.php
    and follow error message
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2017-04-26 12:18:30+02:00, Thierry Graff : creation
********************************************************************************/

define('DS', DIRECTORY_SEPARATOR);

require_once __DIR__ . DS . 'src' . DS . 'init' . DS . 'init.php';

use g5\G5;

//
// parameter checking
//
$datasets = G5::getDatasets();
$datasets_str = implode(", ", $datasets);

$USAGE = <<<USAGE
Usage : 
    php {$argv[0]} <dataset> <datafile> <action> [parameters]
with :
    <dataset> can be : {$datasets_str}
    <datafile> : the precise file(s) within the dataset.
    <action> = action done on data ; available actions depend on dataset and datafile.
    [parameters] optional list of parameters depending on action.
Example :
    php {$argv[0]} cura A2 raw2csv

USAGE;


if(count($argv) < 4){
    echo "WRONG USAGE - need at least 3 arguments\n";
    die($USAGE);
}

$dataset = $argv[1];
$datafile = $argv[2];
$action = $argv[3];

// check dataset
$datasets = G5::getDatasets();
$datasets_str = implode(", ", $datasets);
if(!in_array($dataset, $datasets)){
    echo "WRONG USAGE - invalid dataset : $dataset\n";
    echo "Possible datasets : $datasets_str\n";
    exit;
}

// check datafile
$datafiles = G5::getDatafiles($dataset);
$datafiles_str = implode(", ", $datafiles);
if(!in_array($datafile, $datafiles)){
    echo "WRONG USAGE - invalid datafile : $datafile\n";
    echo "Possible datafiles for $dataset : $datafiles_str\n";
    exit;
}
// check action
$actions = G5::getCommands($dataset, $datafile);
$actions_str = implode(", ", $actions);
if(!in_array($action, $actions)){
    echo "WRONG USAGE - invalid action : $action\n";
    echo "Possible actions for $dataset, datafile $datafile : $actions_str\n";
    exit;
}

//
// run
//
try{
    $params = array_slice($argv, 4);
    [$isRouter, $class] = G5::getCommandClass($dataset, $datafile, $action);
    if($isRouter){
        // transmit action and datafile to the router
        array_unshift($params, $action);
        array_unshift($params, $datafile);
    }
    $report = $class::execute($params);
    echo "$report\n";
}
catch(Exception $e){
    echo 'Exception : ' . $e->getMessage() . "\n";
    echo $e->getFile() . ' - line ' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
