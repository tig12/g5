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

//
// parameter checking
//
$args1 = Run::getArgs1();
$args1_str = implode(", ", $args1);

$USAGE = <<<USAGE
-------                                                                                               
Usage : 
    php {$argv[0]} <argument1> <argument2> <argument3> [optional arguments]
Example :
    php {$argv[0]} cura A2 raw2csv
-------

USAGE;

// check arg1
if(count($argv) < 2){
    echo "WRONG USAGE - run-g5.php needs at least 3 arguments\n";
    echo $USAGE;
    echo "Possible values for argument1 : $args1_str\n";
    exit;
}
else{
    $arg1 = $argv[1];
    $args1 = Run::getArgs1();
    $args1_str = implode(", ", $args1);
    if(!in_array($arg1, $args1)){
        echo $USAGE;
        echo "WRONG USAGE - INVALID DATASET : $arg1\n";
        echo "Possible argument1 : $args1_str\n";
        exit;
    }
}
// here, arg1 is valid

// check arg2
$arg2s = Run::getArgs2($arg1);
$arg2s_str = implode(", ", $arg2s);
if(count($argv) < 3){
    echo "WRONG USAGE - run-g5.php needs at least 3 arguments\n";
    echo $USAGE;
    echo "\n";
    echo "Possible argument2 for argument1 = $arg1 : $arg2s_str\n";
    echo "\n";
    exit;
}
else{
    $arg2 = $argv[2];
    if(!in_array($arg2, $arg2s)){
        echo $USAGE;
        echo "WRONG USAGE - INVALID DATAFILE : $arg2\n";
        echo "\n";
        echo "Possible argument2 for argument1 = $arg1 : $arg2s_str\n";
        echo "\n";
        exit;
    }
}
// here, arg2 is valid

// check arg3
$arg3s = Run::getArgs3($arg1, $arg2);
$arg3s_str = implode(", ", $arg3s);
if(count($argv) < 4){
    echo "WRONG USAGE - run-g5.php needs at least 3 arguments\n";
    echo $USAGE;
    echo "\n";
    echo "Possible argument3 for $arg1 / $arg2 : $arg3s_str\n";
    echo "\n";
    exit;
}
else{
    $arg3 = $argv[3];
    if(!in_array($arg3, $arg3s)){                  
        echo "WRONG USAGE - INVALID ACTION : $arg3\n";
        echo "\n";
        echo "Possible argument3 for $arg1 / $arg2 : $arg3s_str\n";
        echo "\n";
        exit;
    }
}
// here, arg3 is valid

//
// run
//
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
