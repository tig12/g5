<?php
/********************************************************************************
    Converts raw Gauquelin 1955 files in from data/raw/gauq/g55/
    to temporary csv files in data/tmp/gauq/g55/

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-05-25 23:22:13+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\g55;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
// use g5\commands\gauq\LERRCP;

class raw2tmp implements Command {
    
    // ******************************************************
    /** 
        Parses one file E1 or E3 and stores it in a csv file
        The resulting csv file contains informations of the 2 lists
        @param  $params Array containing 3 elements :
                        - the string "g55" (useless here)
                        - the string "raw2tmp" (useless here)
                        - a string identifying what is processed (ex : '570SPO').
                          Corresponds to a key of G55::GROUPS array
        @return report
    **/
    public static function execute($params=[]): string{
        
        $cmdSignature = 'gauq g55 raw2tmp';
        $report = "--- $cmdSignature ---\n";
        
        $tmp = G55::GROUPS;
        unset($tmp['884PRE']);
        $possibleParams = array_keys($tmp);
        $msg = "Usage : php run-g5.php $cmdSignature <group>\nPossible values for <group>: \n  - " . implode("\n  - ", $possibleParams) . "\n";
        
        if(count($params) != 3){
            return "INVALID CALL: - this command needs exactly one parameter.\n$msg";
        }
        $groupKey = $params[2];
        if(!in_array($groupKey, $possibleParams)){
            return "INVALID PARAMETER: $groupKey\n$msg";
        }
        if(!isset(G55::GROUPS[$groupKey]['raw-file'])){
            return "INVALID GROUP: Group $groupKey does not have raw file.\n$msg";
        }
        
        $outfile = G55::tmpFilename($groupKey);
        $outfile_raw = G55::tmpRawFilename($groupKey);
        
        $N = 0;
        $raw = G55::loadRawFile($groupKey);
        $res = implode(G5::CSV_SEP, G55::TMP_FIELDS) . "\n";
        $res_raw = implode(G5::CSV_SEP, G55::RAW_FIELDS) . "\n";
        $newEmpty = array_fill_keys(G55::TMP_FIELDS, '');
        $trimField = function(&$val, $idx){ $val = trim($val); };
        foreach($raw as $line){
            $N++;
//echo "$line";
//echo "$N\n";
            $fields = explode(G55::RAW_SEP, trim($line));
            if(count($fields) != 4){
                //throw new \Exception("Incorrect format in file $groupKey for line $N:\n$line");
                echo "Incorrect format in file $groupKey for line $N: $line";
                continue;
            }
            array_walk($fields, $trimField);
            $new = $newEmpty;
            $new['NUM'] = $N;
            [$new['FNAME'], $new['GNAME']] = self::computeName($fields[0]);
            $new['DATE'] = self::computeDateTime($fields[1], $fields[2]);
            [$new['PLACE'], $new['C2'], $new['CY']] = self::computePlace($fields[3]);
            $res .= implode(G5::CSV_SEP, $new) . "\n";
            $res_raw .= implode(G5::CSV_SEP, $fields) . "\n";
        }
exit;
        
        file_put_contents($outfile, $csv);
        $report .= "Stored " . self::$n_total . " lines in $outfile\n";
        
        file_put_contents($outfile, $csvRaw);
        $report .= "Stored " . self::$N . " lines in $outfile\n";
        
        return $report;
    }
    
    
    const PATTERN_NAME = '/([A-Z ]+) (.*)/';
    /**
        @return     Array with 3 elements: family name, given name, nobility
    **/
    private static function computeName(string $str): array {
        $res = ['', '', ''];
        preg_match(self::PATTERN_NAME, $str, $m);
        if(count($m) != 3){
            //throw new \Exception("Unable to parse G55 name: $str");
            echo "   Unable to parse G55 name: $str\n";
            return $res;
        }
        $res[0] = ucWords(strtolower($m[1]));
        $res[1] = $m[2];
        return $res;
    }
    
    const PATTERN_DAY = '/(\d+)-(\d+)-(\d+)/';
    const PATTERN_HOUR = '/(\d+) h.( \d+)?/';
    /**
        @return     Date format YYYY-MM-DD HH:MM
    **/
    private static function computeDateTime(string $strDay, string $strHour): string {
        $res = '';
        preg_match(self::PATTERN_DAY, $strDay, $m);
        if(count($m) != 4){
            echo "   Unable to parse G55 day: $strDay\n";
            return $res;
        }
//        str_pad($h , 2, '0', STR_PAD_LEFT);
        return $res;
    }
    
    const PATTERN_PLACE =  '/(.*?) \((.*?)\)\./';
    /**
        @return     Array with 4 elements
                        - 0: place name
                        - 1: admin code level 1
                        - 2: admin code level 2
                        - 3: country iso 3166
    **/
    private static function computePlace(string $str): array {
        $res = ['', '', '', ''];
        preg_match(self::PATTERN_PLACE, $str, $m);
        if(count($m) != 3){
            echo "   Unable to parse G55 place: $str\n";
            return $res;
        }
        $placeName = $m[1];
        if($placeName == 'Saarelouis'){
            return ['Saarlouis', '09', '', 'DE'];
        }
        $res[0] = $placeName;
        $C2 = $m[2];
        if($C2 == 'Algérie'){
            $res[3] = 'DZ';
            return $res;
        }
        $res[3] = 'FR';
        if(!isset(self::DEPTS[$C2])){
            echo "C2 code not handled: $C2\n";
        }
        $res[2] = self::DEPTS[$C2];
        return $res;
    }
    
    
    const DEPTS = [
        'Ain'                   => '01',
        'Aisne'                 => '02',
//        'Algérie'               => '',
        'Allier'                => '03',
        'Alpes-M.'              => '06',
        'Alpes-Marit.'          => '06',
        'Alpes-Maritimes'       => '06',
        'Alsace'                => '',
        'Ardèche'               => '07',
        'Ardennes'              => '08',
        'Ariège'                => '09',
        'Aube'                  => '10',
        'Aude'                  => '11',
        'Aveyr.'                => '12',
        'Aveyron'               => '12',
        'Bas-Rhin'              => '67',
        'Basses-Alpes'          => '04',
        'Basses-Pyrénées'       => '66',
        'B.-du-Rh.'             => '13',
        'Bouches-du-R.'         => '13',
        'Bouches-du-Rh.'        => '13',
        'Bouches-du-Rhône'      => '13',
        'Bretagne'              => '',
        'Calvad.'               => '14',
        'Calvados'              => '14',
        'Cantal'                => '15',
        'Ch.'                   => '',
        'Char.'                 => '16',
        'Charente'              => '16',
        'Charente-Mar.'         => '17',
        'Charente-Maritime'     => '17',
        'Cher'                  => '18',
        'Ch.-Marit.'            => '17',
        'Corrèze'               => '19',
        'Corse'                 => '20',
        'Cote-d’Or'             => '21',
        'Côte-d’Or'             => '21',
        'Côtes-du-N.'           => '22',
        'Côtes-du-Nord'         => '22',
        'Creuse'                => '23',
        'Deux-Sévres'           => '79',
        'Dordogne'              => '24',
        'Doubs'                 => '25',
        'Drôme'                 => '26',
        'E.-et-L.'              => '28',
        'Eure'                  => '27',
        'Eure-et-Loire'         => '28',
        'Finistère'             => '29',
        'Gard'                  => '30',
        'Gers'                  => '32',
        'Gir.'                  => '33',
        'Gironde'               => '33',
        'Haute-Garonne'         => '31',
        'Haute-Loire'           => '43',
        'Haute-M.'              => '52',
        'Haute-Marne'           => '52',
        'Haute-Saône'           => '72',
        'Haute-Savoie'          => '74',
        'Hautes-Pyrénées'       => '65',
        'Haute-Vienne'          => '89',
        'Haut-Rhin'             => '68',
        'Hérault'               => '34',
        'Hte-L.'                => '43',
        'Hte-M.'                => '52',
        'Hte-Marne'             => '52',
        'Hte-Saône'             => '70',
        'Hte-V.'                => '87',
        'Ht-Rh.'                => '68',
        'I.-et-L.'              => '37',
        'I.-et-V.'              => '',
        'Ille-et-V.'            => '',
        'Ille-et-Vil.'          => '',
        'Ille-et-Vilaine'       => '',
        'Indre'                 => '36',
        'Indre-et-Loire'        => '37',
        'Isere'                 => '38',
        'Isére'                 => '38',
        'J.'                    => '',
        'Jura'                  => '39',
        'Landes'                => '40',
        'L.-et-C.'              => '',
        'Loi'                   => '',
        'Loire'                 => '',
        'Loire-Inf.'            => '',
        'Loire-Infér.'          => '',
        'Loire-Inférieure'      => '',
        'Loiret'                => '45',
        'Loir-et-Cher'          => '',
        'Lot'                   => '46',
        'Lot-et-Garonne'        => '47',
        'Loz.'                  => '48',
        'Lozère'                => '48',
        'Maine-et-Loire'        => '',
        'Manche'                => '50',
        'Marne'                 => '51',
        'Mayenne'               => '53',
        'M.-et-M.'              => '54',
        'Meurthe'               => '',
        'Meurthe-et-Mos.'       => '54',
        'Meurthe-et-Moselle'    => '54',
        'Meuse'                 => '55',
        'Morbihan'              => '56',
        'Moselle'               => '57',
        'Nièvre'                => '58',
        'Nord'                  => '59',
        'Oise'                  => '60',
        'Orne'                  => '61',
        'Pas-de-C.'             => '62',
        'Pas-de-Calais'         => '62',
        'P.-de-D.'              => '63',
        'Pr. Mon.'              => '',
        'Puy-de-Dôme'           => '63',
        'Pyrénées-Or.'          => '64',
        'Pyrénées-Orientales'   => '64',
        'Rhône'                 => '69',
        'S.'                    => '',
        'Saône-et-L.'           => '71',
        'Saône-et-Loire'        => '71',
        //'Sarre'                 => '',
        'Sarthe'                => '72',
        'Savoie'                => '73',
        'Seine'                 => '',
        'Seine-et-M.'           => '77',
        'Seine-et-Marne'        => '77',
        'Seine-et-O.'           => '',
        'Seine-et-Oise'         => '',
        'Seine-Infér.'          => '76',
        'Seine-Inférieure'      => '76',
        'S.-et-M.'              => '77',
        'S.-et-O.'              => '',
        'S.-I.'                 => '76',
        'S.-Infér.'             => '76',
        'S.-L.'                 => '71',
        'S.-O.'                 => '',
        'Somme'                 => '80',
        'T.'                    => '',
        'Tarbes'                => '65',
        'Tarn'                  => '81',
        'Tarn-et-Gar.'          => '82',
        'Tarn-et-Garonne'       => '82',
        'Territoire de Belfort' => '90',
        'V.'                    => '',
        'Var'                   => '83',
        'Vaucluse'              => '84',
        'Vend.'                 => '85',
        'Vendée'                => '85',
        'Vienne'                => '86',
        'Vosges'                => '88',
        'Yonne'                 => '89',
    ];
    
} // end class    
