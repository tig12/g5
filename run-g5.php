<?php
/********************************************************************************
    CLI (command line interface) of Gauquelin5 program
    
    Unique entry point to use the program
    
    usage : php run-g5.php
    
    and follow error message
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2017-04-26 12:18:30+02:00, Thierry Graff : creation
********************************************************************************/

define('DS', DIRECTORY_SEPARATOR);

require_once __DIR__ . DS . implode(DS, ['src', 'app' , 'init.php']);

use g5\app\Run;

[$command, $params, $msg] = Run::computeCommandAndParams($argv);

if($command === false){
    die($msg);
}

//
// run
//
try{
    // Command design pattern
    $report = $command::execute($params);
    echo "$report";
}
catch(Exception $e){
    echo 'Exception : ' . $e->getMessage() . "\n";
    echo $e->getFile() . ' - line ' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
