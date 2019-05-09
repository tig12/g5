<?php
/********************************************************************************
    CLI (command line interface) of Gauquelin5 program
    
    usage : php run.php <action> <subject>
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2017-04-26 12:18:30+02:00, Thierry Graff : creation
********************************************************************************/

define('DS', DIRECTORY_SEPARATOR);

require_once __DIR__ . DS . 'src' . DS . 'init' . DS . 'init.php';

use gauquelin5\Gauquelin5;

// list of possible actions.
// used only for error message, useless for the rest of the code.
$actions = [
    'cura2csv',
    // 'cura2g55', => replace by new2g55
    'cura2geo',
    'g552raw',
    // 'g552original'
    // 'g552corrected'
    'mactutor2new',
    'newalch2csv',
    'newalch2new',
    'wd2csv',
    'wd2new',
];

$actions_str = implode("' or '", $actions);

$USAGE = <<<USAGE
usage : 
    php {$argv[0]} <action> [parameters]
with :
    <action> = '{$actions_str}'
    parameters depends on action
Example :
    php {$argv[0]} cura2csv A2

USAGE;

if(count($argv) < 2){
    echo "WRONG USAGE - need at list one argument\n";
    die($USAGE);
}

$action = $argv[1];
if(!in_array($action, $actions)){
    echo "WRONG USAGE - invalid action : $action\n";
    die($USAGE);
}

$class = action2class($action); // function defined below

if($class === false){
    echo "Invalid action : {$action}\n";
    die($USAGE);
}

//
// run
//
try{
    $params = array_slice($argv, 2);
    $report = $class::$action($params);
    echo "$report\n";
}
catch(Exception $e){
    echo 'Exception : ' . $e->getMessage() . "\n";
    echo $e->getFile() . ' - line ' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}


// ******************************************************
/**
    Analyzes the action and returns the class that must handle it.
    Identification of the class is based on a convention :
    The program is used to perform transformations on data.
    In the name of the action, this transformation is expressed by "2".
    ex : cura2csv : transforms cura data to csv files.
    So in the action string, the part that is before the "2"
    expresses the data which is concerned ("cura" in the example).
    This string must correspond to a sub-package of g5\transform (g5\transform\cura in the example).
    And each package must have a class named Actions.
    This class must have a method named like the action.
    In the example, there must exist class g5\transform\cura\Actions.
    and class Actions must have a method cura2csv()
    @param  $action String Name of the action typed by user, like "cura2csv"
    @return false if computation can't be done.
            Otherwise returns fully qualified name of the class.
    
**/
function action2class($action){
    $pos = strpos($action, '2');
    if($pos === false){
        return false;
    }
    $package = substr($action, 0, $pos);
    return "g5\\transform\\$package\\Actions";
}
