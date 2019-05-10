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
$datasources = G5::getDatasources();
$datasources_str = implode(", ", $datasources);

$USAGE = <<<USAGE
usage : 
    php {$argv[0]} <datasource> <action> [parameters]
with :
    <datasource> can be : {$datasources_str}
    <action> = action depending on the datasource
    [parameters] optional list of parameters depending on datasource and action
Example :
    php {$argv[0]} cura raw2csv A2

USAGE;

if(count($argv) < 3){
    echo "WRONG USAGE - need at list 2 arguments\n";
    die($USAGE);
}

$datasource = $argv[1];
$action = $argv[2];

// check datasource
if(!in_array($datasource, $datasources)){
    echo "WRONG USAGE - invalid datasource : $datasource\n";
    die($USAGE);
}

// check action
$actions = G5::getActions($datasource);
$actions_str = implode(", ", $actions);

if(!in_array($action, $actions)){
    echo "WRONG USAGE - invalid action : $action\n";
    echo "Possible actions for $datasource : $actions_str\n";
    exit;
}

//
// run
//
try{
    $params = array_slice($argv, 3);
    $class = "g5\\transform\\$datasource\\Actions";
    $report = $class::action($action, $params);
    echo "$report\n";
}
catch(Exception $e){
    echo 'Exception : ' . $e->getMessage() . "\n";
    echo $e->getFile() . ' - line ' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
