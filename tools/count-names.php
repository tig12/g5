<?php
/** 
    Count the names present in 902gdN.html
    
    Used to decide if the names should be taken from this file or from *y.html files
    
    @license    GPL
    @history    2017-04-27 11:16:42+02:00, Thierry Graff : creation
**/

/* 
Result for execution 2017-04-27 12:05:54+02:00
A1 : 2082
A2 : 3637
A3 : 2963
A4 : 2709
A5 : 2398
A6 : 1338
D10 : 1396
D6 : 449
E1 : 2153
E3 : 1539

Decision : use 902gdN.html (contain the same nb of names as in *y.html files)
*/

define('DS', DIRECTORY_SEPARATOR);

require_once '../src/init/init.php';

use gauquelin5\Names;

try{
    $names = Names::parse();
    
    if($names === false){
        die("Unable to parse " . Names::FILENAME . "\n");
    }
    
    ksort($names);
    echo "Number of names in the different files\n";
    foreach($names as $k => $v){
        echo $k . ' : ' . count($v) . "\n";
    }
}
catch(Exception $e){
    echo 'Exception : ' . $e->getMessage() . "\n";
    echo $e->getFile() . ' - line ' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
