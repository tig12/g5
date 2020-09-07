<?php
/********************************************************************************
    Code used to test features of cura A files.
    Used to take decisions during g5 development
    
    Informative only - does not perform any transformation on files
    
    @license    GPL
    @history    2020-09-07 15:08:07+02:00, Thierry Graff : Creation to store code previously in gauquelin5/build
********************************************************************************/
namespace g5\commands\cura\A;

use g5\patterns\Command;
use g5\commands\cura\Cura;
use g5\commands\cura\CuraNames;

class build implements Command {
    
    /** 
        Possible values of the command
    **/
    const POSSIBLE_PARAMS = [
        'lists',
        'names',
    ];
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by       : php run-g5.php build A lists
        @param $params  array with 3 elements :
                        - the datafile to process (ex A1) 
                        - name of this command (useless here) 
                        - the action to perform
        @return         String containing the html tables with the comparisons
        @history    2019-03-31 10:28:58+02:00, Thierry Graff : Creation
        @history    2020-09-07, Thierry Graff : Integration to g5\commands
    **/
    public static function execute($params=[]): string {
        if(count($params) > 3){
            return "INVALID PARAMETER : " . $params[3] . " - this command doesn't need this parameter\n";
        }
        $possibleParams_str = implode(', ', self::POSSIBLE_PARAMS);
        if(count($params) != 3){
            return "PARAMETER MISSING\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        $param = $params[2];
        if(!in_array($param, self::POSSIBLE_PARAMS)){
            return "INVALID PARAMETER\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        
        $datafile = $params[0];
        $method = 'build_' . $param;
        return self::$method($datafile);
    }
    
    /**
        Tests if two lists contained in 902gdA*y.html files (ex 902gdA1y.html) contain the same number of elements.
        - First list contains the detailed birth data but not the names.
        - Second list is chronologial order list with names.
        
        Result (execution 2019-03-31) : 
        Serie A1 - nb of elements : list1 : 2087 - list2 : 2082
        Serie A2 - nb of elements : list1 : 3643 - list2 : 3637
        Serie A3 - nb of elements : list1 : 3046 - list2 : 2963
        Serie A4 - nb of elements : list1 : 2720 - list2 : 1469
        Serie A5 - nb of elements : list1 : 2410 - list2 : 1400
        Serie A6 - nb of elements : list1 : 2026 - list2 : 1337
        
        Conclusion : the lists differ.
    **/
    private static function build_lists($datafile){
        $report =  "Comparing the two lists in file $datafile - ";
        $raw = Cura::loadRawFile($datafile);
        //
        // 1 - parse first list (without names)
        //
        $res1 = [];
        preg_match('#<pre>\s*(YEA.*?CITY)\s*(.*?)\s*</pre>#sm', $raw, $m);
        $lines1 = explode("\n", $m[2]);
        foreach($lines1 as $line1){
            $fields = explode(Cura::HTML_SEP, $line1);
            $day = Cura::computeDay(['YEA' => $fields[0], 'MON' => $fields[1], 'DAY' => $fields[2]]);
            $res1[] = $day;
        }
        //
        // 2 - Parse chronologial list with names
        //
        $res2 = [];
        preg_match('#CHRONOLOGICAL ORDER \(with names\)</b></font>\s*?<div id="contenu2"><pre>\s*?YEA.*?NAME\s*(.*?)\s*</pre>#smi', $raw, $m);
        $lines2 = explode("\n", $m[1]);
        foreach($lines2 as $line2){
            $fields = explode(Cura::HTML_SEP, $line2);
            $day = Cura::computeDay(['YEA' => $fields[0], 'MON' => $fields[1], 'DAY' => $fields[2]]);
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
        return $report;
    }
    
    /** 
        Count the names present in 902gdN.html
        Result of execution 2017-04-27 12:05:54+02:00
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
        because it permits to write only one parsing for all files.
        @param $datafile Useless here
        @history    2017-04-27 11:16:42+02:00, Thierry Graff : creation   
        @history    2020-09-07, Thierry Graff : Integration to g5\commands
        
    **/
    private static function build_names($datafile){
        $report = '';
        $names = CuraNames::parse();
        ksort($names);
        $report .= "Number of names in the different files\n";                                                                        
        foreach($names as $k => $v){
            $report .= $k . ' : ' . count($v) . "\n";
        }
        return $report;
    }
    
    
}// end class
