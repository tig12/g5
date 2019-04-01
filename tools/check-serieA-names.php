<?php
/********************************************************************************
    Tests if two lists contained in 902gdA*y.html files (ex 902gdA1y.html) contain the same number of elements.
    - First list contains the detailed birth data but not the names.
    - Second list is chronologial order list with names.
    
    Usage : php check-serieA-names.php
    
    Result (execution 2019-03-31) : 
    Serie A1 - nb of elements : list1 : 2087 - list2 : 2082
    Serie A2 - nb of elements : list1 : 3643 - list2 : 3637
    Serie A3 - nb of elements : list1 : 3046 - list2 : 2963
    Serie A4 - nb of elements : list1 : 2720 - list2 : 1469
    Serie A5 - nb of elements : list1 : 2410 - list2 : 1400
    Serie A6 - nb of elements : list1 : 2026 - list2 : 1337
    
    Conclusion : the lists differ.
    
    @license    GPL
    @history    2019-03-31 10:28:58+02:00, Thierry Graff : creation
********************************************************************************/

define('DS', DIRECTORY_SEPARATOR);

require_once '../src/init/init.php';

use gauquelin5\Gauquelin5;

checkAllFiles();

function checkAllFiles(){
    for($i=1; $i <= 6; $i++){
        checkOneFile($i);
    }
}


/** 
    @param  $serieId  Identifier of the serie (1 or 2 ... or 6)
**/
function checkOneFile($serieId){
    $serie = "A$serieId";
    $report =  "Serie $serie - ";
    $raw = Gauquelin5::readHtmlFile($serie);
    //
    // 1 - parse first list (without names)
    //
    $res1 = [];
    preg_match('#<pre>\s*(YEA.*?CITY)\s*(.*?)\s*</pre>#sm', $raw, $m);
    $lines1 = explode("\n", $m[2]);
    foreach($lines1 as $line1){
        $fields = explode(Gauquelin5::HTML_SEP, $line1);
        $day = Gauquelin5::computeDay(['YEA' => $fields[0], 'MON' => $fields[1], 'DAY' => $fields[2]]);
        $res1[] = $day;
    }
    //
    // 2 - Parse chronologial list with names
    //
    $res2 = [];
    preg_match('#CHRONOLOGICAL ORDER \(with names\)</b></font>\s*?<div id="contenu2"><pre>\s*?YEA.*?NAME\s*(.*?)\s*</pre>#smi', $raw, $m);
    $lines2 = explode("\n", $m[1]);
    foreach($lines2 as $line2){
        $fields = explode(Gauquelin5::HTML_SEP, $line2);
        $day = Gauquelin5::computeDay(['YEA' => $fields[0], 'MON' => $fields[1], 'DAY' => $fields[2]]);
        $res2[] = $day;
    }
    //
    // 3 - Compare both lists
    //
    $report .= 'nb of elements : ';
    $report .= 'list1 : ' . count($res1);
    $report .= ' - list2 : ' . count($res2) . "\n";
    //
    // 4 - Report
    //
    echo $report;
}
