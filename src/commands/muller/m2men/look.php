<?php
/********************************************************************************
    Code to examine data/tmp/muller/2-men/muller2-612-men.csv
    (not the raw file, the intermediate file generated by raw2tmp.php).
    Not part of any build process - only to try to understand.
    
    To add a new function : 
        - add <entry> in POSSIBLE_PARAMS
        - implement a method named "look_<entry>"
    
    @license    GPL
    @history    2021-09-05 04:45:48+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\m2men;

use tiglib\patterns\Command;
use g5\model\DB5;
use tiglib\arrays\sortByKey;
use g5\commands\gauq\LERRCP;

class look implements Command {
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php muller m2 look gauquelin
    **/
    const POSSIBLE_PARAMS = [
        'source',
        'gauquelin',
        'check',
        'occu',
    ];
    
    // *****************************************
    /** 
        Routes to the different actions, based on $param
        @param $params Array
                       First element indicates the method to execute ; must be one of self::POSSIBLE_PARAMS
                       Other elements are transmitted to the called method.
                       (Called methods are responsible to handle their params).
    **/
    public static function execute($params=[]): string{
        $possibleParams_str = implode(', ', self::POSSIBLE_PARAMS);
        if(count($params) == 0){
            return "PARAMETER MISSING\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        $param = $params[0];
        if(!in_array($param, self::POSSIBLE_PARAMS)){
            return "INVALID PARAMETER\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        
        $method = 'look_' . $param;
        
        if(count($params) > 1){
            array_shift($params);
            return self::$method($params);
        }
        
        return self::$method();
    }
    
    
    /**
        Checks columns SOURCE and GQ.
        SOURCE contains "primary source" and "secondary source".
    **/
    private static function look_source(){
        $report = '';
        $data = M2men::loadTmpFile();
        $N = count($data);
        $NG = 0; // nb of record marked G in GQ column
        $source1 = array_fill_keys(['S', 'F', 'M'], 0); // primary source
        $source2 = array_fill_keys(['E', 'B', 'A', 'G'], 0); // secondary source
        $NStrange = 0;
        $reportStrange = '';
        foreach($data as $line){
            $s1 = substr($line['SOURCE'], 0, 1);
            $s2 = substr($line['SOURCE'], 1);
            $source1[$s1]++;
            $source2[$s2]++;
            $GQ = $line['GQ'];
            if($GQ == 'G'){
                $NG++;
            }
            if($GQ != 'G' && $s2 == 'G'){
                $reportStrange .=  "  {$line['SOURCE']} {$line['GQ']} {$line['MUID']}"
                    . " {$line['FNAME']} {$line['GNAME']}\t{$line['DATE']} {$line['OCCU']} \n";
                $NStrange++;
            }
        }
        $report .= "field SOURCE - Primary source:\n";
        foreach($source1 as $k => $v){
            $report .= "  $k: $v\n";
        }
        $report .= "field SOURCE - Secondary source:\n";
        foreach($source2 as $k => $v){
            $report .= "  $k: $v\n";
        }
        $report .= "$NStrange strange lines: secondary source contains 'G' but not marked 'G'\n";
        $report .= $reportStrange;
        $report .= "field GQ - $NG lines marked 'G' (field GQ)\n";
        $NNoG = $N - $NG;
        $report .= "field GQ - $NNoG lines not marked 'G'\n";
        return $report;
    }
        
    /**
        Builds an array of matches Müller id => Gauquelin id.
        Match between Müller and Gauquelin is only done by birth day.
        Function written in an iterative process, to build variables $yesMatch and $incoherent.
        @pre    Gauquelin data must have been previously loaded in g5 database.
    **/
    private static function look_gauquelin(){
        // Cases declared as Gauquelin by Müller (field GQ = G)
        // Ambiguous cases fixed by hand from previous executions
        // Müller id => lerrcp id
        $yesMatch = [
            '15'    => 'A6-29',     // Apollinaire Guillaume
            '19'    => 'A6-50',     // Ayme Marcel
            '28'    => 'A5-67',     // Barrault Jean-Louis
            '34'    => 'A2-1646',   // Bastian Adolph
            '41'    => 'A3-1758',   // Beck Ludwig
            '49'    => 'A6-89',     // Bernanos Georges
            '63'    => 'A4-139',    // Bonnard Pierre
            '74'    => 'A5-1861',   // Brandt Willy
            '80'    => 'A6-148',    // Breton André
            '81'    => 'E3-212',    // Briand Aristide
            '82'    => 'A2-2604',   // Broglie Louis
            '100'   => 'D10-185',   // Button Richard
            '102'   => 'D10-190',   // John Cage
            '103'   => 'A6-168',    // Camus Albert
            '104'   => 'A3-1800',   // Canaris Wilhelm
            '106'   => 'A2-3240',   // Carnap Rudolf
            '121'   => 'A4-237',    // Cocteau Jean
            '123'   => 'A1-924',    // Coppi Fausto
            '129'   => 'A5-1636',   // Croce Benedetto
            '140'   => 'A4-281',    // Degas Edgar
            '145'   => 'A4-300',    // Derain André
            '157'   => 'A6-276',    // Duchamp Marcel
            '158'   => 'A2-239',    // Duhamel Georges
            '174'   => 'A5-1885',   // Erhard Ludwig
            '182'   => 'A4-1860',   // Gabriel Fauré
            '194'   => 'A2-2689',   // Foch Ferdinand
            '213'   => 'E3-682',    // Gambetta Leon
            '227'   => 'A6-372',    // Giraudoux Jean
            '233'   => 'A4-1444',   // Gogh Vincent Van
            '239'   => 'A5-1112',   // Gründgens Gustav
            '244'   => 'A2-3304',   // Hahn Otto
            '257'   => 'D10-582',   // Herman Woody
            '264'   => 'A5-1931',   // Himmler, Heinrich
            '290'   => 'A5-1149',   // Jürgens Curd
            '307'   => 'A4-1324',   // Kirchner Ernst
            '327'   => 'A2-3328',   // Laue Max
            '347'   => 'A2-3606',   // Lorentz Hendrik Antoon
            '350'   => 'A5-1977',   // Lübke Heinrich
            '358'   => 'A6-909',    // Malaparte Curzio
            '365'   => 'A5-521',    // Marais Jean
            '367'   => 'A2-3071',   // Marconi Guglielmo
            '370'   => 'A6-548',    // Martin du Gard Roger
            '374'   => 'A4-742',    // Matisse Henri
            '385'   => 'A4-2130',   // Messiaen Olivier
            '392'   => 'A5-1204',   // Minetti Bernhard
            '394'   => 'A4-1212',   // Modigliani Amedeo
            '396'   => 'A4-1456',   // Mondrian Piet
            '397'   => 'A6-585',    // Montherlant Henri
            '399'   => 'A5-1742',   // Moro Aldo
            '410'   => 'A5-1751',   // Nenni Pietro
            '426'   => 'A5-2006',   // Papen Franz
            '433'   => 'D10-1006',  // Peck Gregory
            '438'   => 'A5-627',    // Philippe Gérard
            '441'   => 'A2-3343',   // Piccard Auguste
            '449'   => 'A5-1221',   // Ponto Erich
            '450'   => 'A2-3346',   // Prandtl Ludwig
            '453'   => 'A6-658',    // Proust Marcel
            '456'   => 'A5-1225',   // Quadflieg, Will
            '458'   => 'A6-665',    // Queneau Raymond
            '467'   => 'A4-2218',   // Ravel Maurice
            '479'   => 'A6-689',    // Raimbaud Arthur
            '487'   => 'A6-697',    // Rolland Romain
            '488'   => 'A6-700',    // Romains Jules
            '489'   => 'A3-2098',   // Rommel Erwin
            '494'   => 'A4-979',    // Rousseau Henri
            '498'   => 'A1-223',    // de Saint-Exupery Antoine
            '500'   => 'A6-727',    // Sartre Jean-Paul
            '501'   => 'A2-2110',   // Sauerbruch Ernst
            '518'   => 'E3-1384',   // Schuman Robert
            '524'   => 'A4-1030',   // Seurat Georges
            '526'   => 'A5-868',    // Sica Vittorio
            '550'   => 'A6-758',    // Sully-Prud'Homme Armand
            '554'   => 'A2-2898',   // Teilhard De Chardin Pierre
            '559'   => 'A4-1359',   // Thoma Hans
            '563'   => 'A5-1829',   // Togliatti Palmiro
            '585'   => 'D6-420',    // Walter Fritz
            '597'   => 'A2-3392',   // Wieland Heinrich
            '600'   => 'D6-424',    // Winkler Hans Günter
            '607'   => 'A2-3396',   // Ziegler Karl
            '611'   => 'A6-813',    // Zola Emile
        ];
        
        // Cases declared as Gauquelin by Müller (field GQ = Y)
        // Birth date correspond to one Gauquelin record
        // but function check() shows that the association is wrong
        // Contains Müller ids
        $yesButNoMatch = [
            '113', // Clair René
            '292', // Junkers Hugo
        ];
        
        // Cases added by hand from previous executions
        // Cases declared as NOT Gauquelin by Müller (field GQ = N)
        // but in fact present in Gauquelin data
        // Müller id => lerrcp id
        $noButYesMatch = [
            '16'    => 'A6-36',     // Arp Hans
            '23'    => 'A4-1144',   // Balla Giacomo
            '25'    => 'A6-53',     // Balzac Honoré
            '536'   => 'A2-3379',   // Staudinger Hermann
            '595'   => 'A2-3391',   // Weyl Hermann
            '199'   => 'A4-1883',   // Franck César
        ];
        
        $report = '';
        $data = M2men::loadTmpFile();
        $dblink = DB5::getDbLink();
        $query = "select name,ids_in_sources,birth from person where birth->>'date-ut' like ? or birth->>'date' like ?";
        $stmt = $dblink->prepare($query);
        
        $N_total = 0; // total number of lines in Müller's file
        $N_GQ_G = 0; // total number of lines supposed to be in Gauquelin data (field GQ = G)
        $N_GQ_N = 0; // total number of lines not supposed to be in Gauquelin data (field GQ = N)
        
        $res_match = ''; // code to copy in class M2men
        $N_match = 0; // nb of records matching Gauquelin
        $N_nomatch = 0; // nb of records not matching Gauquelin
        
        // Cases declared as Gauquelin by Müller (field GQ = G)
        // but not found in Gauquelin data
        $report_yesButNo = '';
        $recap_yesButNo = ''; // same as $report_yesButNo, but more compact
        $N_yesButNo = 0;
        
        // Cases declared as NOT Gauquelin by Müller (field GQ = N)
        // but found in Gauquelin data
        $report_check_no = ''; // records with field GQ = N, but possibly in Gauquelin data
        $report_noButYes = ''; // just repeats $noButYesMatch
        $N_noButYes = 0; // only come from $noButYesMatch
        
        foreach($data as $line){
            $N_total++;
            $GQ = $line['GQ'];
            if($GQ == 'N'){
                $N_GQ_N++;
            }
            else{
                $N_GQ_G++;
            }

            $MUID = $line['MUID'];
            
            if(in_array($MUID, $yesButNoMatch)){
                $N_nomatch++;
                continue;
            }
            if(isset($yesMatch[$MUID])){
                // case manually fixed in $yesMatch (from previous executions)
                $N_match++;
                $res_match .= "        '$MUID' => '{$yesMatch[$MUID]}', // {$line['FNAME']} {$line['GNAME']} {$line['FAME']}\n";
                continue;
            }
            if(isset($noButYesMatch[$MUID])){
                // case manually added in $noButYesMatch (from previous executions)
                $N_match++;
                $N_noButYes++;
                $report_noButYes .= "'$MUID' => '{$noButYesMatch[$MUID]}', // {$line['FNAME']} {$line['GNAME']} {$line['FAME']}\n";
                continue;
            }
            
            // HERE query g5 database
            $param = substr($line['DATE'], 0, 10) . '%';
            $stmt->execute([$param, $param]);
            $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // handle cases with GQ = G
            if($GQ == 'G'){
                if(count($res) == 1){
                    $ids_in_sources = json_decode($res[0]['ids_in_sources'], true);
                    if(isset($ids_in_sources[LERRCP::SOURCE_SLUG])){
                        $N_match++;
                        $res_match .= "        '$MUID' => '{$ids_in_sources[LERRCP::SOURCE_SLUG]}', // {$line['FNAME']} {$line['GNAME']} {$line['FAME']}\n";
                    }
                    else{
                        // happens for one case with same birth date, from Muller 1083 medics
                        $N_nomatch++;
                        $report_yesButNo .= "\nDate found but no LERRCP id: " . self::report_muller($line);
                        $recap_yesButNo .= self::report_muller($line);
                    }
                }
                else{
                    // Zero match or several matches not handled by $yesMatch
                    $N_nomatch++;
                    $report_yesButNo .= "\n0 or several possible match: " . self::report_muller($line);
                    $recap_yesButNo .= self::report_muller($line);
                    foreach($res as $candidate){
                        $report_yesButNo .= self::report_gauquelin($candidate);
                    }
                }
            }
            // handle cases with GQ = N
            else{
                $N_nomatch++;
                if(count($res) != 0){
                    $report_check_no .= "\n" . self::report_muller($line);
                    foreach($res as $candidate){
                        $report_check_no .= self::report_gauquelin($candidate);
                    }
                }
            }
        }
        //
        $report .= "\n=== matches to copy in class M2men: ===\n";
        $tmp = explode("\n", $res_match);
        usort($tmp, 'strnatcmp');
        $report .= "    const MU_GQ = ["; // code to copy in class M2men
        $report .= implode("\n", $tmp) . "\n";
        $report .= "    ];\n";
        //
        $report .= "\n=== Supposed to match but no match:\n";
        $report .= $report_yesButNo;
        //
        $report .= "\n=== Not supposed to match but to check:\n";
        $report .= $report_check_no;
        //
        $report .= "\n=== Supposed to match but no match:\n";
        $report .= $recap_yesButNo;
        $report .= "\n--- According to Müller:\n";
        $report .= "N not Gauquelin = $N_GQ_N\n";
        $report .= "N Gauquelin     = $N_GQ_G\n";
        $report .= "--- Found by g5:\n";
        $report .= "N Gauquelin match         = $N_match\n";
        $report .= "N Gauquelin no match      = $N_nomatch\n";
        $report .= "---\n";
        $N_new = $N_total - $N_match;
        $report .= "N Gauquelin     = $N_match\n";
        $report .= "N new           = $N_new\n";
        $report .= "N total         = $N_total\n";
        return $report;
    }
    
    /**
        Auxiliairy of look_gauquelin()
    **/
    private static function report_muller($line) {
        return 'MU: '
        . str_pad($line['MUID'], 9)
        . str_pad("{$line['FNAME']} {$line['GNAME']} {$line['FAME']}", 40)
        . str_pad($line['DATE'], 20)
        . str_pad($line['PLACE'], 29) . $line['CY']
        . "\n";
    }
    
    /**
        Auxiliairy of look_gauquelin()
    **/
    private static function report_gauquelin($line) {
        $name = json_decode($line['name'], true);
        $birth = json_decode($line['birth'], true);
        $ids_in_sources = json_decode($line['ids_in_sources'], true);
        $date = $birth['date-ut'] ?? $birth['date'];
        // for 3 records, Müller data matches with a record not coming from Gauquelin
        // so ids_in_sources without LERRCP::SOURCE_SLUG
        $id = isset($ids_in_sources[LERRCP::SOURCE_SLUG]) 
            ? $ids_in_sources[LERRCP::SOURCE_SLUG]
            : '';
        return 'GQ: '
            . str_pad($id, 9)
            . str_pad("{$name['family']} {$name['given']}", 40)
            . str_pad($date, 20)
            . str_pad($birth['place']['name'], 29)
            . $birth['place']['cy']
            . "\n";

    }
    
    /**
        Command used to visually check the coherence of look_gauquelin()
        Prints Gauquelin and Müller records
        Constant M2men::MU_GQ to retrieve the data fro g5 database.
    **/
    public static function look_check() {
        $report = '';
        $data = M2men::loadTmpFile_muid();
        $dblink = DB5::getDbLink();
        $query = "select name,ids_in_sources,birth from person where ids_in_sources->>'" . LERRCP::SOURCE_SLUG . "'=?";
        $stmt = $dblink->prepare($query);
        
        foreach(M2men::MU_GQ as $MUID => $GQID){
            $report .= "\n" . self::report_muller($data[$MUID]);
            $stmt->execute([$GQID]);
            $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if(count($res) > 1){
                throw new \Exception("Unexpected result for " . self::report_muller($data[$MUID]));
            }
            $report .= self::report_gauquelin($res[0]);
        }
        return $report;
    }
    
    /**
        Echoes a list of occupation codes and the nb of associated persons
    **/
    public static function look_occu() {
        $data = M2men::loadTmpFile();

        $occus = [];
        foreach($data as $line){
            $occu = $line['OCCU'];
            if(!isset($occus[$occu])){
                $occus[$occu] = 0;
            }
            $occus[$occu]++;
        }
        ksort($occus);
        $res = '';
        foreach($occus as $code => $nb){
            // convert 'AR00' to 'AR 00'
            $code = substr($code, 0, 2) . ' ' . substr($code, 2);
            $res .= "        '$code' => '', // $nb persons\n";
        }
        return $res;
    }
    
}// end class
