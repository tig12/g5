<?php
/********************************************************************************
    Importation of cura file D6
    450 New famous European Sports Champions
    
    Matches first list and chronological order list
    
    @license    GPL
    @history    2017-04-27 22:04:25+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\D6;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\model\Names;
use g5\model\Names_fr;
use g5\transform\cura\Cura;

class raw2csv implements Command{
    
    /** String written in field PLACE to indicate that a call to geonames webservice failed **/
    const FAILURE_MARK = 'XXX';
    
    /**
        Human corrections for given names.
        Generated by checkNames.php and modified by a human.
        Format : NUM => [family name, given name]
        Notes : corrections marked with // ??? are suppositions that may introduce errors.
    **/
    private static $NAMES_CORRECTIONS = [
        '67' => ['Gordon Hutton', 'Bremner'],
        '113' => ['von Cramm', 'Gottfried'], // Gottfried Alexander Maximilian Walter Kurt Freiherr von Cramm !!!
        '115' => ['Crossalexander', ''], // ???
        '119' => ['Darui', 'Julien'],
        '124' => ['de Haan', 'Johannes'],
        '128' => ['de Michèle', 'Gabriel'],
        '129' => ['de Nadaï', 'Francis'], // Paul-François de Nadaï dit Francis de Nadaï
        '130' => ['den Hartog', 'Arie'],
        '131' => ['den Hartog', 'Fedor'],
        '132' => ['de Shoemmacker', 'Joseph'],
        '133' => ['Desmet', 'Armand'],
        '138' => ['Di Nallo', 'Fleury'],
        '181' => ['Maria Francisca', 'Gommers'], // Maria ("Mia") Francisca Philomena Hoogakker-Gommers
        '189' => ['Grim', 'Joe'], // Joe Grim (born Saverio Giannone)
        '212' => ['Barclay', 'Joyce'], // Joyce Barclay successivement épouse Williams, Hume, Engelfield, Sacerdote et Bennett
        '228' => ['Kemper', 'Franz-Joseph'],
        '229' => ['Koczur', 'Ferenc'], // Ferenc Koczur ou Frans Koczur dit Koczur Ferry
        '247' => ['Le Chenadec', 'Gilbert'],
        '265' => ['Mc Calliog', 'Jim'],
        '266' => ['Mc Cormick', 'John'],
        '267' => ['Mc Creadie', 'Eddie'],
        '268' => ['Mc Govern', 'John'],
        '269' => ['Mc Gowan', 'Walter'],
        '289' => ['Mildenberger', 'Karl'],
        '368' => ['Schnellinger', 'Karl-Heinz'],
        '401' => ['Urtain', 'José Manuel'], // José Manuel Ibar Azpiazu, plus connu comme Urtain
        '403' => ['Van Impe', 'Lucien'],
        '404' => ['Van Linden', 'Rik'],
        '405' => ['Van Tygem', 'Noel'], // ???
        '415' => ['De Vlaeminck', 'Roger'],
        '424' => ['Winkler', 'Hans Günter'],
        '442' => ['Jouve', 'Roger-Louis'], // ???
    ];
    
    // *****************************************
    /** 
        Parses file D6 and stores it in a csv file
        @return report
        @throws Exception if unable to parse
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 2){
            return "INVALID PARAMETER : " . $params[2] . " - raw2csv doesn't need this parameter\n";
        }
        
        $datafile = 'D6';
            
        $report =  "--- Importing $datafile ---\n";
        $raw = Cura::readHtmlFile($datafile);
        // Fix an error on a latitude in cura file
        $raw = str_replace(
            '356	8	1	1925	11	0	0	36N05	00W56	Ruiz Bernardo',
            '356	8	1	1925	11	0	0	38N05	00W56	Ruiz Bernardo',
            $raw);
        $file_serie = Cura::rawFilename($datafile);
        preg_match('#<pre>.*?(NUM.*?NAME)\s*(.*?)\s*</pre>#sm', $raw, $m);
        if(count($m) != 3){
            throw new \Exception("Unable to parse list in " . $file_serie);
        }
        $nb_stored = 0;
        $csv = '';
        $csv = implode(G5::CSV_SEP, D6::FIELDNAMES) . "\n";
        $lines = explode("\n", $m[2]);
        foreach($lines as $line){
            $cur = preg_split('/\t+/', $line);
            $new = [];
            $new['NUM'] = trim($cur[0]);
            [$new['FNAME'], $new['GNAME']] = Names::familyGiven(trim($cur[9]));
            if($new['GNAME'] == ''){
                [$new['FNAME'], $new['GNAME']] = Names_fr::fixJean($new['FNAME']);
            }
            if($new['GNAME'] == '' && isset(self::$NAMES_CORRECTIONS[$new['NUM']])){
                [$new['FNAME'], $new['GNAME']] = self::$NAMES_CORRECTIONS[$new['NUM']];
            }
            $day = Cura::computeDay(['DAY' => $cur[1], 'MON' => $cur[2], 'YEA' => $cur[3]]);
            $hour = Cura::computeHHMM(['H' => $cur[4], 'MN' => $cur[5]]);
            $new['DATE'] = "$day $hour";
            $new['PLACE'] = '';
            $new['CY'] = '';
            $new['C2'] = '';
            $new['GEOID'] = '';
            $new['LG'] = Cura::computeLg($cur[8]);
            $new['LAT'] = Cura::computeLat($cur[7]);
            $csv .= implode(G5::CSV_SEP, $new) . "\n";
            $nb_stored ++;
        }
        $csvfile = Config::$data['dirs']['5-cura-csv'] . DS . $datafile . '.csv';
        file_put_contents($csvfile, $csv);
        $report .= $nb_stored . " lines stored in $csvfile\n";
        return $report;
    }
    
    
}// end class    
