<?php
/********************************************************************************
    Converts raw Gauquelin 1955 files in from data/raw/gauq/g55/
    to temporary csv files in data/tmp/gauq/g55/

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-05-25 23:22:13+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\g55;

use g5\G5;
use tiglib\patterns\Command;

class raw2tmp implements Command {
    
    // to fix ambiguities
    const TWEAKS = [
        '02-508-physicians' => [
            // MENCIERE Louis, 25-9-1870, 3 h., Saint-Geeil-de-Saintonge (Ch.-Inf.).
            // typo in g55 book
            '355' => [
                'PLACE' => 'Saint-Genis-de-Saintonge',
                'C2' => '17',
            ],
        ],
        //
        //
        //
        '06-361-minor-painters' => [
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
            if(substr($new['FNAME'], 0, 2) == '* '){
                // particular case for 01-576-physicians (means that the person is also member of the academy of science)
                $new['OTHER'] = '*';
                $new['FNAME'] = substr($new['FNAME'], 2);
            }
            $new['DATE'] = self::computeDateTime($fields[1], $fields[2], $line, $N);
            // check date format
            try{
                $dt = new \DateTime($new['DATE']);
            }
            catch(\Exception $e){
                echo "Invalid date format: {$new['DATE']} - line $N   $line\n";
            }
            [$new['PLACE'], $new['C1'], $new['C2'], $new['C3'], $new['CY']] = self::computePlace($fields[3], $line, $N);
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
    
    const PATTERN_NAME = '/(\*? ?[\p{Lu} \'\-]+) (.*)/u';
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
        $res[0] = ucWords(mb_strtolower($m[1]));
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
        @param      $line Only useful to log errors
        @return     Date format YYYY-MM-DD HH:MM
    **/
    private static function computeDateTime(string $strDay, string $strHour, string $line, int $N): string {
        $res = '';
        //
        preg_match(self::PATTERN_DAY, $strDay, $m);
        if(count($m) != 4){
            echo "   Unable to parse G55 day: $strDay -- line $N = $line\n";
            return $res;
        }
        $res = $m[3] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        //
        $tmp = explode('h.', $strHour);
        if(count($tmp) != 2){
            echo "   Unable to parse G55 hour: $strHour -- line $N = $line\n";
            return $res;
        }
        $res .= ' ' . str_pad(trim($tmp[0]), 2, '0', STR_PAD_LEFT) . ':' . str_pad(trim($tmp[1]), 2, '0', STR_PAD_LEFT);
        return $res;
    }
    
    const PATTERN_PLACE =  '/(.*?) \((.*?)\)\./';
    /**
        @param      $line Only useful to log errors
        @param      $num  Only useful to log errors
        @return     Array with 5 elements
                        - 0: place name
                        - 1: admin code level 1
                        - 2: admin code level 2
                        - 3: admin code level 3
                        - 4: country iso 3166
    **/
    private static function computePlace(string $str, string $line, int $N): array {
        $res = ['', '', '', '', ''];
        preg_match(self::PATTERN_PLACE, $str, $m);
        if(count($m) != 3){
            echo "   Unable to parse G55 place: $str -- line $N = $line\n";
            return $res;
        }
        
        $placeName = $m[1];
        if($placeName == 'Saarelouis'){
            return ['Saarlouis', '09', '', '', 'DE'];
        }
        if($placeName == 'Madrid'){
            return ['Madrid', '29', 'M', '', 'ES'];
        }
        if($placeName == 'Genève'){
            return ['Genève', 'GE', '2500', '', 'CH'];
        }
        if($placeName == 'La Haye'){
            return ['La Haye', '05', '0984', '', 'NL'];
        }
        
        $res[0] = $placeName;
        
        $C2 = $m[2];
        if(in_array($C2, [
                'Algérie',
                'Algér.',
                'Alger',
                'Alg.',
                'Oran',
                'Constantine',
        ])){
            $res[4] = 'DZ';
            $res[1] = self::dz_place2admin1($placeName, $line, $N);
            return $res;
        }
        if(in_array($C2, ['Pr. Mon.', 'Principauté de Monaco', 'Monaco'])){
            $res[4] = 'MC';
            return $res;
        }
        if($C2 == 'Ile Maurice'){
            $res[4] = 'MU';
            return $res;
        }
        
        $res[4] = 'FR';
        if(!isset(self::DEPTS[$C2])){
            if(preg_match('/Paris \((\d{1,2})°\) \(Seine\)\./', $str, $m2)){
                return ['Paris', '', '75', $m2[1], 'FR'];
            }
            echo "C2 code not handled: $C2 -- line $N = $line\n";
exit;
        }
        $res[2] = self::DEPTS[$C2];
        if($res[2] == ''){
            $res[2] = self::fr_place2admin2($placeName, $line, $N);
        }
        return $res;
    }
    
    /**
        Auxiliary of computePlace().
        Computes admin1 code for Algeria.
        @param      $line Only useful to log errors
    **/
    private static function dz_place2admin1(string $place, string $line, int $N): string {
        switch($place){
        	case 'Alger':          return '01'; break;
        	case 'Douera':         return '01'; break;
        	case 'Batna':          return '03'; break;
        	case 'Biskra':         return '19'; break;
        	case 'Blidah':         return '20'; break;
        	case 'Blida':          return '20'; break;
        	case 'Bône':           return '18'; break;
        	case 'Clauzel':        return '23'; break; // Houari Boumédiène, wilaya de Guelma
        	case 'Constantine':    return '04'; break;
        	case 'Laferrière':     return '36'; break; // Chaabat El Leham
        	case 'Lavarande':      return '35'; break;
            case 'Mascara':        return '26'; break;
            case 'Médéa':          return '06'; break;
            case 'Mila':           return '48'; break;
            case 'Mirabeau':       return '14'; break;
            case 'Mostaganem':     return '26'; break;
            case 'Mustapha':       return '40'; break; // wilaya de Boumerdès
        	case 'Oran':           return '09'; break;
        	case 'Nemours':        return '15'; break;
        	case 'Philippeville':  return '31'; break; // Skikda
        	case 'Saida':          return '10'; break;
        	case 'Sétif':          return '12'; break;
        	case 'Souk-Ahras':     return '52'; break;
        	case 'Sidi-Bel-Abbès': return '30'; break;
        	case 'Tlemcen':        return '15'; break;
        	case 'Tizi-Ouzou':     return '14'; break;
            default:
                echo "Unable to compute DZ admin1 code for $place -- line $N = $line\n";
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
        'Al.'                   => '03',
        'All.'                  => '03',
        'Allier'                => '03',
        'B.-Alpes'              => '04',
        'Basses-Alpes'          => '04',
        'Hautes-Alpes'          => '05',
        'Alpes-M.'              => '06',
        'Alpes-Mar.'            => '06',
        'Alpes-Marit.'          => '06',
        'Alpes-Maritimes'       => '06',
        'A.-M.'                 => '06',
        'Alsace'                => '', // see fr_place2admin2()
        'Ardèche'               => '07',
        'Ard.'                  => '08',
        'Ardennes'              => '08',
        'Ariège'                => '09',
        'Aube'                  => '10',
        'Aude'                  => '11',
        'Aveyr.'                => '12',
        'Aveyron'               => '12',
        'B.-du-Rh.'             => '13',
        'B.-du-Rhône'           => '13',
        'Bouches-du-R.'         => '13',
        'Bouches-du-Rh.'        => '13',
        'Bouches-du-Rhône'      => '13',
        'Bretagne'              => '', // see fr_place2admin2()
        'Cal.'                  => '14',
        'Calv.'                 => '14',
        'Calvad.'               => '14',
        'Calvados'              => '14',
        'Cantal'                => '15',
        'Ch.'                   => '', // see fr_place2admin2()
        'Char.'                 => '16',
        'Charente'              => '16',
        'C.-M'                  => '17',
        'Ch.-M.'                => '17',
        'Ch.-Mar.'              => '17',
        'Ch.-Marit.'            => '17',
        'Ch.-Maritime'          => '17',
        'Char.-M.'              => '17',
        'Char.-Mar.'            => '17',
        'Charente-M.'           => '17',
        'Charente-Mar.'         => '17',
        'Charente-Marit.'       => '17',
        'Charente-Maritime'     => '17',
        'Ch.-Inf.'              => '17',
        'Charente-Inf.'         => '17',
        'Charente-Inférieure'   => '17',
        'Cher'                  => '18',
        'Cor.'                  => '19',
        'Corrèze'               => '19',
        'Corse'                 => '20',
        'C.-O.'                 => '21',
        'C.-d’O.'               => '21',
        'C.-d’Or'               => '21',
        "Côte-d'Or"             => '21',
        'Cote-d’Or'             => '21',
        'Côte-d’Or'             => '21',
        'Côtes-d’Or'            => '21',
        'C.-du-N.'              => '22',
        'C.-du-Nord'            => '22',
        'Côte-du-Nord'          => '22',
        'Côtes-du-N.'           => '22',
        'Côtes-du-Nord'         => '22',
        'Cr.'                   => '23',
        'Creuse'                => '23',
        'Dord.'                 => '24',
        'Dordogne'              => '24',
        'Doubs'                 => '25',
        'Dr.'                   => '26',
        'Drôme'                 => '26',
        'Eure'                  => '27',
        'E.-L.'                 => '28',
        'E.-et-L.'              => '28',
        'Eure-et-L.'            => '28',
        'Eure-et-Loire'         => '28',
        'Eure-et-Loir'          => '28',
        'Fin.'                  => '29',
        'Finist.'               => '29',
        'Finistère'             => '29',
        'Gard'                  => '30',
        'H.-G.'                 => '31',
        'Hte-G.'                => '31',
        'Hte-Gar.'              => '31',
        'Hte-Garonne'           => '31',
        'Haute-Garonne'         => '31',
        'Gers'                  => '32',
        'Gir.'                  => '33',
        'Gironde'               => '33',
        'Her.'                  => '34',
        'Hér.'                  => '34',
        'Hérault'               => '34',
//        'Ile Maurice'           => '',
        'I.-V.'                 => '35',
        'I.-et-V.'              => '35',
        'Ille-et-V.'            => '35',
        'Ille-et-Vil.'          => '35',
        'Ille-et-Vilaine'       => '35',
        'Indre'                 => '36',
        'I.-L.'                 => '37',
        'I.-et-L.'              => '37',
        'Ind.-et-L.'            => '37',
        'Indre-et-Loire'        => '37',
        'Indre-et-L.'           => '37',
        'Isere'                 => '38',
        'Isére'                 => '38',
        'Isère'                 => '38',
        'J.'                    => '', // see fr_place2admin2()
        'Jura'                  => '39',
        'Landes'                => '40',
        'L.-et-C.'              => '41',
        'Loir-et-Cher'          => '41',
        'Lre'                   => '42',
        'Loire'                 => '42',
        'Hte-L.'                => '43',
        'Haute-Loire'           => '43',
        'Hte-Loire'             => '43',
        'L.-Inf.'               => '44',
        'Loire-Inf.'            => '44',
        'Loire-Infér.'          => '44',
        'Loire-Inférieure'      => '44',
        'Lt'                    => '45',
        'Loiret'                => '45',
        'Lot'                   => '46',
        'L.-et-G.'              => '47',
        'Lot-G.'                => '47',
        'Lot-et-G.'             => '47',
        'Lot-et-Gar.'           => '47',
        'Lot-et-Garonne'        => '47',
        'Loz.'                  => '48',
        'Lozère'                => '48',
        'M.-L.'                 => '49',
        'M.-et-L.'              => '49',
        'Maine-et-L.'           => '49',
        'Maine-et-Loire'        => '49',
        'Manche'                => '50',
        'M.'                    => '', // see fr_place2admin2()
        'Mar.'                  => '51',
        'Marne'                 => '51',
        'H.-Marne'              => '52',
        'Hte-M.'                => '52',
        'Hte-Marne'             => '52',
        'Haute-M.'              => '52',
        'Haute-Marne'           => '52',
        'Haute-Saône'           => '72',
        'May.'                  => '53',
        'Mayenne'               => '53',
        'M.-M.'                 => '54',
        'M.-et-M.'              => '54',
        'Meurthe'               => '', // see fr_place2admin2()
        'Meurthe-et-M.'         => '54',
        'Meurthe-et-Mos.'       => '54',
        'Meurthe-et-Moselle'    => '54',
        'Mthe-et-M.'            => '54',
        'Mthe-et-Moselle'       => '54',
        'Meuse'                 => '55',
        'Morb.'                 => '56',
        'Morbihan'              => '56',
        'Mos.'                  => '57',
        'Moselle'               => '57',
        'Nièvre'                => '58',
        'Nord'                  => '59',
        'Oise'                  => '60',
        'Orne'                  => '61',
        'P.C.'                  => '62',
        'P.-C.'                 => '62',
        'P.-de-C.'              => '62',
        'P.-de-Calais'          => '62',
        'Pas-de-C.'             => '62',
        'Pas-de-Cal.'           => '62',
        'Pas-de-Calais'         => '62',
        'P.-de-D.'              => '63',
        //'Pr. Mon.'              => '',
        'P.-D.'                 => '63',
        'Puy-de-D.'             => '63',
        'Puy-de-Dôme'           => '63',
        'B.-P.'                 => '64',
        'B.-Pyr.'               => '64',
        'B.-Pyrénées'           => '64',
        'Basses-Pyr.'           => '64',
        'Basses-Pyrénées'       => '64',
        'T.'                    => '', // see fr_place2admin2()
        'Tarbes'                => '65',
        'Htes-Pyrénées'         => '65',
        'Hautes-Pyrénées'       => '65',
        'Hautes-Pyr.'           => '65',
        'Haut-Rhin'             => '68',
        'Pyr.-Or.'              => '66',
        'Pyr.-Orient'           => '66',
        'Pyrénées-Orient.'      => '66',
        'Pyr.-Orientales'       => '66',
        'Pyrénées-O.'           => '66',
        'Pyrénées-Or.'          => '66',
        'Pyrénées-Orientales'   => '66',
        'B.-Rh.'                => '67',
        'B.-Rhin'               => '67',
        'Bas-Rhin'              => '67',
        'Ht-Rhin'               => '68',
        'Ht-Rh.'                => '68',
        'Rh.'                   => '69',
        'Rhône'                 => '69',
        'S.'                    => '', // see fr_place2admin2()
        'H.-S.'                 => '70',
        'Hte-Saône'             => '70',
        'S.L.'                  => '71',
        'S.-L.'                 => '71',
        'S.-et-L.'              => '71',
        'Saône-et-L.'           => '71',
        'Saône-et-Loire'        => '71',
        //'Sarre'                 => '', // see fr_place2admin2()
        'Sthe'                  => '72',
        'Sarthe'                => '72',
        'Savoie'                => '73',
        'Hte-Savoie'            => '74',
        'Haute-Savoie'          => '74',
        'Seine'                 => '', // see fr_place2admin2()
        'Seine-et-O.'           => '', // see fr_place2admin2()
        'Seine-et-Oise'         => '', // see fr_place2admin2()
        'S.-I.'                 => '76',
        'S.I.'                  => '76',
        'S.-Inf.'               => '76',
        'S.-Infér.'             => '76',
        'Seine-Inf.'            => '76',
        'Seine-Infér.'          => '76',
        'Seine-Inférieure'      => '76',
        'Seine-et-M.'           => '77',
        'Seine-et-Marne'        => '77',
        'S.-M.'                 => '77',
        'S.-et-M.'              => '77',
        'S.-et-O.'              => '', // see fr_place2admin2()
        'S.-et-Oise'            => '', // see fr_place2admin2()
        'S.O.'                  => '', // see fr_place2admin2()
        'S.-O.'                 => '', // see fr_place2admin2()
        'D.-Sev.'               => '79',
        'Deux-Sev.'             => '79',
        'Deux-Sévres'           => '79',
        'Deux-Sèvres'           => '79',
        'Som.'                  => '80',
        'Somme'                 => '80',
        'Tarn'                  => '81',
        'Tarn-et-Gar.'          => '82',
        'Tarn-et-Garonne'       => '82',
        'V.'                    => '', // see fr_place2admin2()
        'Var'                   => '83',
        'Vau.'                  => '84',
        'Vaucl.'                => '84',
        'Vaucluse'              => '84',
        'Ven.'                  => '85',
        'Vend.'                 => '85',
        'Vendée'                => '85',
        'Vienne'                => '86',
        'H.-V.'                 => '87',
        'Hte-V.'                => '87',
        'Hte-Vienne'            => '87',
        'Haute-V.'              => '87',
        'Vosges'                => '88',
        'Yonne'                 => '89',
        'Yon.'                  => '89',
        'Haute-Vienne'          => '89',
        'Terr. de Belf.'        => '90',
        'Belfort'               => '90',
        'Terr. de Belfort'      => '90',
        'Territoire de Belfort' => '90',
    ];
    
    /**
        Auxiliary of computePlace().
        Computes admin2 code (département) for France for ambiguous cases.
        @param      $line Only useful to log errors
    **/
    private static function fr_place2admin2(string $place, string $line, int $N): string {
        switch($place){
        // Ambiguous cases, handled by self::TWEAKS
        case 'Saint-Germain': return ''; break;
        case 'Saint-Brice': return ''; break;
        case 'Saint-Geeil-de-Saintonge': return ''; break;
        //
        case 'Arcueil': return '94'; break;
        case 'Arcueil-Cachan': return '94'; break;
        case 'Andilly': return '95'; break;
        case 'Angoulême': return '16'; break;
        case 'Anthony': return '92'; break;
        case 'Argenteuil': return '95'; break;
        case 'Asnières': return '92'; break;
        case 'Athis-Mons': return '91'; break;
        case 'Aulnay-sous-Bois': return '93'; break;
        case 'Aubervilliers': return '93'; break;
        case 'Bagnolet': return '93'; break;
        case 'Bassing': return '57'; break;
        case 'Blamont': return '54'; break;
        case 'Boulogne': return '92'; break;
        case 'Boulogne-sur-Seine': return '92'; break;
        case 'Boulogne-Billancourt': return '92'; break;
        case 'Bourg-la-Reine': return '92'; break;
        case 'Boussy-Saint-Antoine': return '91'; break;
        case 'Brunoy': return '91'; break;
        case 'Champigny': return '94'; break; // Champigny-sur-Marne
        case 'Champlon': return '91'; break; // in fact Champlan
        case 'Chatenay': return '77'; break; // Châtenay-sur-Seine
        case 'Chatillon-sous-Bagneux': return '92'; break;
        case 'Chatou': return '78'; break;
        case 'Choisy-le-Roi': return '94'; break;
        case 'Clamart': return '92'; break;
        case 'Clichy': return '92'; break;
        case 'Colombes': return '92'; break;
        case 'Conflans-Ste-Honorine': return '78'; break;
        case 'Corbeil': return '91'; break;
        case 'Corbeil-Essonnes': return '91'; break;
        case 'Coudray-Montceau': return '91'; break;
        case 'Coudray-Montceaux': return '91'; break;
        case 'Courbevoie': return '92'; break;
        case 'Créteil': return '94'; break;
        case 'Draveil': return '91'; break;
        case 'Enghien': return '95'; break;
        case 'Enghien-les-Bains': return '95'; break;
        case 'Epinay-sur-Seine': return '93'; break;
        case 'Ermont': return '95'; break;
        case 'Etampes': return '91'; break;
        case 'Flavigny-sur-Moselle': return '54'; break;
        case 'Fontainebleau': return '77'; break;
        case 'Fontenay-aux-Roses': return '92'; break;
        case 'Fontenay-sous-Bois': return '94'; break;
        case 'Fourqueux': return '78'; break;
        case 'Franconville-la-Garenne': return '95'; break;
        case 'Freneuse': return '78'; break;
        case 'Gagny': return '93'; break;
        case 'Garches': return '92'; break;
        case 'Garges-les-Gonesse': return '95'; break;
        case 'Gazeran': return '78'; break;
        case 'Gennevilliers': return '92'; break;
        case 'Gentilly': return '94'; break;
        case 'Gonesse': return '95'; break;
        case 'Goussainville': return '95'; break;
        case 'Herbeville': return '95'; break;
        case 'Issou': return '78'; break;
        case 'Ivry-sur-Seine': return '94'; break;
        case 'Joinville-le-Pont': return '94'; break;
        case 'Jouy-en-Josas': return '78'; break;
        case 'Kremlin-Bicêtre': return '94'; break;
        case 'Le Pecq': return '78'; break;
        case 'Le Raincy': return '93'; break;
        case 'La Varenne-Saint-Hilaire': return '94'; break;
        case 'Les Mureaux': return '78'; break;
        case 'Levallois-Perret': return '92'; break;
        case 'Le Vésinet': return '78'; break;
        case 'L’Hay-les-Roses': return '78'; break;
        case 'Limay': return '78'; break;
        case 'Limours': return '91'; break;
        case 'Lons-le-Saunier': return '39'; break;
        case 'Luzarches': return '95'; break;
        case 'Luzelburg': return '57'; break;
        case 'Magny-en-Vexin': return '91'; break;
        case 'Mantes-la-Ville': return '78'; break;
        case 'Mantes': return '78'; break;
        case 'Massy': return '91'; break;
        case 'Maisons-Alfort': return '94'; break;
        case 'Maisons-Laffitte': return '78'; break;
        case 'Maisons-Laffite': return '78'; break;
        case 'Malakoff': return '92'; break;
        case 'Mantes-la-Jolie': return '78'; break;
        case 'Mantes-sur-Seine': return '78'; break;
        case 'Massy-Palaiseau': return '91'; break;
        case 'Maule': return '78'; break;
        case 'Meudon': return '92'; break;
        case 'Meulan': return '92'; break;
        case 'Millemont': return '78'; break;
        case 'Milly': return '91'; break;
        case 'Montcontour': return '22'; break;
        case 'Montesson': return '78'; break;
        case 'Montfort-l’Amaury': return '78'; break;
        case 'Montgeron': return '91'; break;
        case 'Montlhéry': return '91'; break;
        case 'Montlignon': return '95'; break;
        case 'Montmorency': return '95'; break;
        case 'Montreuil-sous-Bois': return '93'; break;
        case 'Montrouge': return '92'; break;
        case 'Mouilleron-en-Pareds': return '85'; break;
        case 'Mouthiers': return '16'; break;
        case 'Mureaux': return '78'; break;
        case 'Nesles-la-Vallée': return '95'; break;
        case 'Neuilly-Plaisance': return '93'; break;
        case 'Neuilly-sur-Seine': return '92'; break;
        case 'Nogent-sur-Marne': return '94'; break;
        case 'Noisy-le-Grand': return '93'; break;
        case 'Orgeval': return '78'; break;
        case 'Oudreville': return '54'; break;
        case 'Palaiseau': return '93'; break;
        case 'Parc-Saint-Maur': return '94'; break;
        case 'Pantin': return '93'; break;
        case 'Paris': return '75'; break;
        case 'Pierrefitte': return '93'; break;
        case 'Pierrelaye': return '95'; break;
        case 'Poissy': return '78'; break;
        case 'Pont-à-Mousson': return '54'; break;
        case 'Pontoise': return '95'; break;
        case 'Port-Marly': return '78'; break;
        case 'Pussay': return '91'; break;
        case 'Puteaux': return '92'; break;
        case 'Rambouillet': return '78'; break;
        case 'Ris-Orangis': return '91'; break;
        case 'Rueil': return '92'; break;
        case 'Saint-Benoît de Carmaux': return '81'; break;
        case 'Saint-Christophe-de-Chalais': return '16'; break;
        case 'Saint-Clément': return '54'; break;
        case 'Saint-Cloud': return '92'; break;
        case 'Saint-Cyr-l’Ecole': return '78'; break;
        case 'Saint-Denis': return '93'; break;
        case 'Saint-Didier-les-Bains': return '84'; break;
        case 'Saint-Germain-en-Laye': return '78'; break;
        case 'St-Germain-en-Laye': return '78'; break;
        case 'Saint-Germain-les-Corbeil': return '91'; break;
        case 'Saint-Leu': return '95'; break;
        case 'Saint-Leu-la-Forét': return '95'; break;
        case 'Saint-Mandé': return '94'; break;
        case 'Saint-Maur-des-Fossés': return '94'; break;
        case 'Saint-Ouen-l’Aumone': return '95'; break;
        case 'Saint-Ouen': return '93'; break;
        case 'Saint-Quirin': return '57'; break;
        case 'St-Rémy-les-Chevr': return '78'; break;
        case 'St-Rémy-les-Chevr.': return '78'; break;
        case 'Salonnes': return '57'; break;
        case 'Sannois': return '95'; break;
        case 'Sarcelles': return '95'; break;
        case 'Sarreguemines': return '57'; break;
        case 'Sartrouville': return '78'; break;
        case 'Schlestadt': return '67'; break;
        case 'Sèvres': return '92'; break;
        case 'Suresnes': return '92'; break;
        case 'Taverny': return '95'; break;
        case 'Thiais': return '94'; break;
        case 'Thiverval-Grignon': return '78'; break;
        case 'Toul': return '54'; break;
        case 'Vanves': return '92'; break;
        case 'Vaucresson': return '92'; break;
        case 'Vaujours': return '93'; break;
        case 'Velizy': return '78'; break;
        case 'Verneuil': return '78'; break;
        case 'Vernouillet': return '78'; break;
        case 'Versailles': return '78'; break;
        case 'Vésinet': return '78'; break;
        case 'Villejuif': return '94'; break;
        case 'Villeneuve-le-Roi': return '94'; break;
        case 'Villeneuve-St-Georges': return '94'; break;
        case 'Villeneuve-Saint-Georges': return '94'; break;
        case 'Villemomble': return '93'; break;
        case 'Villennes-sur-Seine': return '78'; break;
        case 'Villepreux': return '78'; break;
        case 'Villiers-sur-Marne': return '94'; break;
        case 'Vincennes': return '94'; break;
        case 'Vitry-sur-Seine': return '94'; break;
        case 'Wissous': return '91'; break;
        case 'Yvry-sur-Seine': return '94'; break;
        default:
            echo "Unable to compute FR admin2 code for $place -- line $N = $line\n";
            return '';
        break;
        }
    }
    
} // end class    
