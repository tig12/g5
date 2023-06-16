<?php
/********************************************************************************
    CLI (command line interface) of Gauquelin5 program
    
    usage : php run-g5.php
    and follow error message
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2017-04-26 12:18:30+02:00, Thierry Graff : creation
********************************************************************************/

define('DS', DIRECTORY_SEPARATOR);

require_once __DIR__ . DS . 'src' . DS . 'app' . DS . 'init.php';

use g5\app\Run;

[$command, $params, $msg] = Run::computeCommandAndParams($argv);

if($command === false){
    die($msg);
}

//
// run
//
try{
    $report = $command::execute($params);
    echo "$report";
}
catch(Exception $e){
    echo 'Exception : ' . $e->getMessage() . "\n";
    echo $e->getFile() . ' - line ' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

exit;


try{
    $params = array_slice($argv, 4);
    [$isRouter, $class] = Run::getCommandClass($arg1, $arg2, $arg3);
    if($isRouter){
        // transmit arg3 and arg2 to the router
        array_unshift($params, $arg3);
        array_unshift($params, $arg2);
    }
    $report = $class::execute($params);
    echo "$report";
}
catch(Exception $e){
    echo 'Exception : ' . $e->getMessage() . "\n";
    echo $e->getFile() . ' - line ' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
