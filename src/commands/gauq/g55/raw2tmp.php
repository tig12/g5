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
    
    // to fix ambiguities
    const TWEAKS = [
        '361PEI' => [
            // BRUNARD Joseph Brice, 13-1-1812, 14 h., Saint-Brice (Seine-et-Oise).
            // see https://www.saintbrice95.fr/ma-mairie/histoire-de-saint-brice/les-personnalites/ils-ont-habite-ou-sejourne-a-saint-brice/arts-plastiques/joseph-brice-brunard-peintre-miniaturiste-et-imprimeur-864.html
            '76' => [
                'PLACE' => 'Saint-Brice-sous-Forêt',
                'C2' => '95',
            ],
            // FERRET Pierre César, 8-10-1800, 24 h. 15, Saint-Germain (Seine-et-O.).
            '165' => [
                'PLACE' => 'Saint-Germain-en-Laye',
                'C2' => '92',
            ],
            // HAAKMAN Léon André, 24-12-1859, 17 h. 45, Saint-Germain (S.-et-O.).
            '204' => [
                'PLACE' => 'Saint-Germain-en-Laye',
                'C2' => '92',
            ],
        ],
    ];
    
    
    // ******************************************************
    /** 
        Parses one g55 file and stores it in a csv file
        @param  $params Array containing 3 elements :
                        - the string "g55" (useless here, used by GauqCommand).
                        - the string "raw2tmp" (useless here, used by GauqCommand).
                        - a string identifying what is processed (ex : '570SPO').
                          Corresponds to a key of G55::GROUPS array
        @return report
    **/
    public static function execute($params=[]): string{
        
        $cmdSignature = 'gauq g55 raw2tmp';
        
        $possibleParams = G55::getPossibleGroupKeys();
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
        
        $report = "--- $cmdSignature $groupKey ---\n";
        
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
            $fields = explode(G55::RAW_SEP, trim($line));
            if(count($fields) != 4){
                //throw new \Exception("Incorrect format in file $groupKey for line $N:\n$line");
                echo "Incorrect format in file $groupKey for line $N: $line";
                continue;
            }
            array_walk($fields, $trimField);
            $new = $newEmpty;
            $new['NUM'] = $N;
            [$new['FNAME'], $new['GNAME'], $new['NOB']] = self::computeName($fields[0]);
            $new['DATE'] = self::computeDateTime($fields[1], $fields[2]);
            [$new['PLACE'], $new['C1'], $new['C2'], $new['CY']] = self::computePlace($fields[3]);
            if(isset(self::TWEAKS[$groupKey][$N])){
                $new = array_replace($new, self::TWEAKS[$groupKey][$N]);
            }
            // check useful if fr_place2admin2() returned '' for ambiguous cases
            // and case not handled by self::TWEAKS
            if($new['CY'] == 'FR' && $new['C2'] == ''){
                echo "C2 not handled for $groupKey $N: $line\n";
            }
            $new['OCCU'] = G55::GROUPS[$groupKey]['occupation'];
            $res .= implode(G5::CSV_SEP, $new) . "\n";
            $res_raw .= implode(G5::CSV_SEP, $fields) . "\n";
        }
        
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($outfile, $res);
        $report .= "Stored " . $N . " lines in $outfile\n";
        
        file_put_contents($outfile_raw, $res_raw);
        $report .= "Stored " . $N . " lines in $outfile_raw\n";
        
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
            //echo "   Unable to parse G55 name: $str\n";
            $res[0] = $str;
            return $res;
        }
        $res[0] = ucWords(strtolower($m[1]));
        // nobility
        $nob = '';
        $pos1 = strpos($m[2], '(d');
        if($pos1 !== false){
            $pos2 = strpos($m[2], ')');
            $nob = substr($m[2], $pos1+1, $pos2-$pos1-1);
            if($pos1 == 0){
                $gname = trim(substr($m[2], $pos2+1));
            }
            else{
                $gname = trim(substr($m[2], 0, $pos1-1));
            }
        }
        else{
            $gname = $m[2];
        }
        $res[1] = $gname;
        $res[2] = $nob;
        return $res;
    }
    
    const PATTERN_DAY = '/(\d+)-(\d+)-(\d+)/';
    /**
        @return     Date format YYYY-MM-DD HH:MM
    **/
    private static function computeDateTime(string $strDay, string $strHour): string {
        $res = '';
        //
        preg_match(self::PATTERN_DAY, $strDay, $m);
        if(count($m) != 4){
            echo "   Unable to parse G55 day: $strDay\n";
            return $res;
        }
        $res = $m[3] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        //
        $tmp = explode('h.', $strHour);
        if(count($tmp) != 2){
            echo "   Unable to parse G55 hour: $strHour\n";
            return $res;
        }
        $res .= ' ' . str_pad(trim($tmp[0]), 2, '0', STR_PAD_LEFT) . ':' . str_pad(trim($tmp[1]), 2, '0', STR_PAD_LEFT);
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
            $res[1] = self::dz_place2admin1($placeName);
            return $res;
        }
        if($C2 == 'Pr. Mon.'){
            $res[3] = 'MC';
            return $res;
        }
        
        $res[3] = 'FR';
        if(!isset(self::DEPTS[$C2])){
            echo "C2 code not handled: $C2\n";
        }
        $res[2] = self::DEPTS[$C2];
        if($res[2] == ''){
            $res[2] = self::fr_place2admin2($placeName);
        }
        return $res;
    }
    
    /**
        Auxiliary of computePlace().
        Computes admin1 code for Algeria.
    **/
    private static function dz_place2admin1(string $place): string {
        switch($place){
        	case 'Alger': return '01'; break;
        	case 'Blidah': return '20'; break;
        	case 'Constantine': return '04'; break;
            default:
                echo "Unable to compute DZ admin1 code for $place\n";
                return '';
            break;
        }
    }
    
    /** 
        Association name of département as written in Gauquelin book => code
    **/
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
        'I.-et-V.'              => '37',
        'Ille-et-V.'            => '35',
        'Ille-et-Vil.'          => '35',
        'Ille-et-Vilaine'       => '35',
        'Indre'                 => '36',
        'Indre-et-Loire'        => '37',
        'Isere'                 => '38',
        'Isére'                 => '38',
        'J.'                    => '',
        'Jura'                  => '39',
        'Landes'                => '40',
        'L.-et-C.'              => '41',
        'Loire'                 => '42',
        'Loire-Inf.'            => '44',
        'Loire-Infér.'          => '44',
        'Loire-Inférieure'      => '44',
        'Loiret'                => '45',
        'Loir-et-Cher'          => '41',
        'Lot'                   => '46',
        'Lot-et-Garonne'        => '47',
        'Loz.'                  => '48',
        'Lozère'                => '48',
        'Maine-et-Loire'        => '49',
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
        //'Pr. Mon.'              => '',
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
    
    /**
        Auxiliary of computePlace().
        Computes admin2 code (département) for France for ambiguous cases.
    **/
    private static function fr_place2admin2(string $place): string {
        switch($place){
        // Ambiguous cases, handled by self::TWEAKS
        case 'Saint-Germain': return ''; break;
        case 'Saint-Brice': return ''; break;
        //
        case 'Schlestadt': return '67'; break;
        case 'Montcontour': return '22'; break;
        case 'Saint-Christophe-de-Chalais': return '16'; break;
        case 'Lons-le-Saunier': return '39'; break;
        case 'Saint-Quirin': return '57'; break;
        case 'Blamont': return '54'; break;
        case 'Salonnes': return '57'; break;
        case 'Saint-Benoît de Carmaux': return '81'; break;
        case 'Saint-Didier-les-Bains': return '84'; break;
        // Seine
        case 'Chatillon-sous-Bagneux': return '92'; break;
        case 'Boulogne-sur-Seine': return '92'; break;
        case 'Courbevoie': return '92'; break;
        case 'Créteil': return '94'; break;
        case 'Fontenay-aux-Roses': return '92'; break;
        case 'Gennevilliers': return '92'; break;
        case 'Gentilly': return '94'; break;
        case 'Joinville-le-Pont': return '94'; break;
        case 'Montrouge': return '92'; break;
        case 'Neuilly-sur-Seine': return '92'; break;
        case 'Nogent-sur-Marne': return '94'; break;
        case 'Pantin': return '93'; break;
        case 'Puteaux': return '92'; break;
        case 'Saint-Mandé': return '94'; break;
        case 'Saint-Ouen': return '93'; break;
        case 'Vitry-sur-Seine': return '94'; break;
        case 'Yvry-sur-Seine': return '94'; break;
        // Seine-et-Oise
        case 'Montgeron': return '91'; break;
        case 'Saint-Cyr-l’Ecole': return '78'; break;
        case 'Saint-Cloud': return '92'; break;
        case 'Andilly': return '95'; break;
        case 'Coudray-Montceau': return '91'; break;
        case 'Le Vésinet': return '78'; break;
        case 'Limay': return '78'; break;
        case 'Magny-en-Vexin': return '91'; break;
        case 'Meudon': return '92'; break;
        case 'Montesson': return '78'; break;
        case 'Montfort-l’Amaury': return '78'; break;
        case 'Mureaux': return '78'; break;
        case 'Pontoise': return '95'; break;
        case 'Saint-Leu': return '95'; break;
        case 'Sèvres': return '92'; break;
        case 'Taverny': return '95'; break;
        case 'Verneuil': return '78'; break;
        case 'Versailles': return '78'; break;
        case 'Villepreux': return '78'; break;
        case 'Mantes-la-Jolie': return '78'; break;
        case 'Argenteuil': return '95'; break;
        case 'Thiverval-Grignon': return '78'; break;
        case 'Maisons-Laffitte': return '78'; break;
        case 'Saint-Germain-les-Corbeil': return '91'; break;
        case 'Saint-Germain-en-Laye': return '78'; break;
        default:
            echo "Unable to compute FR admin2 code for $place\n";
            return '';
        break;
        }
    }
    
} // end class    
