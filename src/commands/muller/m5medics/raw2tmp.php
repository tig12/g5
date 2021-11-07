<?php
/********************************************************************************
    Imports data/raw/muller/5-medics/5a_muller-medics-utf8.txt
    to  data/tmp/muller/5-medics/muller5-1083-medics.csv
    and data/tmp/muller/5-medics/muller5-1083-medics-raw.csv
    
    @todo Handle C2 (départements)
        
    @license    GPL
    @history    2019-07-06 12:21:23+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\muller\m5medics;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use g5\model\Names_fr;
use tiglib\arrays\csvAssociative;

class raw2tmp implements Command {

    /** 
        Generated adding in execute() :
        echo $new['C2'] . "\n";
        And executing :
        php run-g5.php muller m5medics raw2csv | sort | uniq
        Then C2 (= département in France) codes were added manually.
        Ambiguous codes are not handled.
    **/
    private static $DEPT_STR = <<<DEPT_STR
01 Ain
02 Aisne
03 Allier
06 Alpes-Maritimes
68 Alsace-Lorraine
67 Alsace-Lorraine [Bas-
67 Alsace-Lorraine [Bas-Rhi
68 Alsace-Lorraine [Haut-R
68 Alsace-Lorraine [Haut-Rhi
57 Alsace-Lorraine [Mosel
57 Alsace-Lorraine [Moselle]
07 Ardèche
08 Ardennes
09 Ariège
10 Aube
11 Aude
12 Aveyron
67 Bas-Rhin
67 Bas-Rhin,
04 Basses-Alpes
64 Basses-Pyrénées
13 Bouches-du-Rhône
14 Calvados
15 Cantal
17 Charente
17 Charente-I
17 Charente-In
17 Charente-Inf.
17 Charente-Inféri
17 Charente-Inférieure
18 Cher
19 Corrèze
20 Corse
21 Côte-d'O
21 Côte-d'Or
22 Côtes-du-Nord
23 Creuse
79 Deux-Sèvres
24 Dordogne
25 Doubs
26 Drôme
27 Eure
28 Eure-et-Loir
29 Finistère
30 Gard
32 Gers
33 Gironde
31 Haute-Garonne
43 Haute-Loire
52 Haute-Marne
05 Hautes-Alpes
71 Haute-Saône
74 Haute-Savoie
65 Hautes-Pyrénées
87 Haute-Vienne
68 Haut-Rhin
90 Haut-Rhin [Territoire-de
34 Hérault
35 Ille-et-Vilaine
36 Indre
37 Indre-et-Loire
38 Isère
39 Jura
40 Landes
42 Loire
44 Loire-Inférie
44 Loire-Inférieure
45 Loiret
41 Loir-et-Cher
46 Lot
47 Lot-et-Garonne
48 Lozère
49 Maine-et-
49 Maine-et-Loir
49 Maine-et-Loire
50 Manche
51 Marne
972 Martinique
53 Mayenne
Meurthe
54 Meurthe-et-Moselle
54 Meurthe [Meurthe-et-Mos
54 Meurthe [Meurthe-et-Mosell
55 Meuse
56 Morbihan
57 Moselle
58 Niévre
58 Nièvre
59 Nord
60 Oise
61 Orne
62 Pas-de-Calais
63 Puy-de-Dôme
Pyrénées
66 Pyrénées-Oriental
66 Pyrénées-Orientales
69 Rhône
71 Saône-et-Loire
72 Sarthe
73 Savoie
Seine
Seine-
Seine-et
Seine-et-Oise
77 Seine-et-M
77 Seine-et-Marne
Seine-et-Oi
Seine-et-Oise
76 Seine-Inf.
76 Seine-Inférieur
76 Seine-Inférieure
80 Somme
81 Tarn
82 Tarn-et-Garonn
82 Tarn-et-Garonne
90 Territoire de Belfort
83 Var
84 Vaucluse
85 Vendée
87 Vienne
88 Vosges
89 Yonne
DEPT_STR;
    
    /** $DEPT_STR in a usable form **/
    private static $depts = [];
    
    /**
        Particular cases to restore C2
        These are cases not handled by self::$depts
        Necessary when old départements do not correspond to present.
    **/
    private static $cities_depts = [
        'Boulogne-sur-Seine'        => '92',
        'Bassing [Moselle]'         => '57',
        'Charenton'                 => '94',
        'Clichy-la-Garenne'         => '92',
        'Conflans-Sainte-Honorine'  => '78',
        'Créteil'                   => '94',
        'Etampes'                   => '91',
        'Fontenay-sous-Bois'        => '94',
        'Fourqueux'                 => '78',
        'le Raincy'                 => '93',
        "l'Ile-Saint-Denis"         => '93',
        'Malakoff'                  => '92',
        'Marly-la-Ville'            => '95',
        'Meudon'                    => '92',
        'Montgeron'                 => '91',
        "Monfort-l'Amaury"          => '78',
        "Montfort-l'Amaury"         => '78',
        'Neuilly-sur-Seine'         => '92',
        'Paris'                     => '75',
        'Pontoise'                  => '95',
        'Rouen'                     => '76',
        'Saint-Cirq-Lalo'           => '46',
        'Saint-Cloud'               => '92',
        'Saint-Denis'               => '93',
        'Saint-Germain-en-Laye'     => '78',
        'Saint-Nicolas-du-Port'     => '54',
        'Saint-Paul-de-Fenouillet'  => '66',
        'Saint-Ouen'                => '93',
        "Saint-Ouen-l'Aumône"       => '95',
        'Savigny-sur-Orge'          => '91',
        'Tourcoing'                 => '59',
        'Trappes'                   => '78',
        'Versailles'                => '78',
        'Villepreux'                => '78',
        'Vincennes'                 => '94',
    ];
    /** 
        = array_keys($cities_depts)
        Initialized in compute_place()
    **/
    private static $cities;
    
    // *****************************************
    /** 
        Imports file raw/muller/5-medics/3a_sports-utf8.csv to tmp/muller/5-medics/
        @param $params empty array
        @return report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 0){
            return "INVALID PARAMETER : " . $params[0] . " - parameter not needed\n";
        }
        
        $filename = M5medics::rawFilename();                  
        if(!is_file($filename)){
            return "ERROR : Missing file $filename\n";
        }
        $raw = file_get_contents($filename);
        $lines = explode("\n", $raw);
        $N = count($lines);
        
        $res = implode(G5::CSV_SEP, M5medics::TMP_FIELDS) . "\n";
        $res_raw = implode(G5::CSV_SEP, array_keys(M5medics::RAW_FIELDS)) . "\n";
        
        $nRecords = 0;
        for($i=5; $i < $N-3; $i++){
            if($i%2 == 1){
                continue;
            }
            $nRecords++;
            
            $line  = trim($lines[$i]);
            $new = array_fill_keys(M5medics::TMP_FIELDS, '');
            $new['NR'] = trim(mb_substr($line, 0, 5));
            $new['SAMPLE'] = trim(mb_substr($line, 5, 11));
            $new['GNR'] = trim(mb_substr($line, 16, 6));
            $new['CODE'] = trim(mb_substr($line, 32, 1));
            // name
            $NAME = trim(mb_substr($line, 34, 51));
            [$new['FNAME'], $new['GNAME']] = self::compute_names($NAME);
            // date
            $GEBDATUM = trim(mb_substr($line, 85, 10));
            $DATE = explode('.', $GEBDATUM);
            $GEBZEIT = mb_substr($line, 101, 5);
            $JAHR = trim(mb_substr($line, 96, 4));
            $new['DATE'] = $DATE[2] . '-' . $DATE[1] . '-' . $DATE[0];
            $new['DATE'] .= ' ' . str_replace('.', ':', $GEBZEIT);
            // place
            $GEBORT = trim(mb_substr($line, 110, 36));
            [$new['PLACE'], $new['C2']] = self::compute_place($GEBORT);
            // lg lat
            $LAENGE = trim(mb_substr($line, 146, 8));
            $BREITE = trim(mb_substr($line, 156, 8));
            $new['LG'] = self::compute_lgLat($LAENGE);
            $new['LAT'] = self::compute_lgLat($BREITE);
            
            $new['MODE'] = trim(mb_substr($line, 168, 3));
            $new['KORR'] = trim(mb_substr($line, 173, 5));
            
            $new['ELECTDAT'] = trim(mb_substr($line, 184, 10));
            $new['STBDATUM'] = trim(mb_substr($line, 204, 10));
            $ELECTAGE = trim(mb_substr($line, 199, 4));
            
            // here are 14 fields present in all lines and not containing spaces, so shorthcut.
            [
                $new['SONNE'],
                $new['MOND'],
                $new['VENUS'],
                $new['MARS'],
                $new['JUPITER'],
                $new['SATURN'],
                $new['SO_'],
                $new['MO_'],
                $new['VE_'],
                $new['MA_'],
                $new['JU_'],
                $new['SA_'],
                $new['PHAS_'],
                $new['AUFAB']
            ] =  preg_split('/\s+/', trim(mb_substr($line, 218, 71)));
            $new['PHAS_'] = str_replace(',', '.', $new['PHAS_']);
            $new['AUFAB'] = str_replace(',', '.', $new['AUFAB']);
            $new['NIENMO'] = trim(mb_substr($line, 295, 1));
            $new['NIENVE'] = trim(mb_substr($line, 302, 1));
            $new['NIENMA'] = trim(mb_substr($line, 309, 1));
            $new['NIENJU'] = trim(mb_substr($line, 316, 1));
            $new['NIENSA'] = trim(mb_substr($line, 323, 1));
            // record with exact raw values
            $new_raw = [
                'NR'        => $new['NR'],
                'SAMPLE'    => $new['SAMPLE'],
                'GNR'       => $new['GNR'],
                'CODE'      => $new['CODE'],
                'NAME'      => $NAME,
                'GEBDATUM'  => $GEBDATUM,
                'JAHR'      => $JAHR,
                'GEBZEIT'   => $GEBZEIT,
                'GEBORT'    => $GEBORT,
                'LAENGE'    => $LAENGE,
                'BREITE'    => $BREITE,
                'MODE'      => $new['MODE'],
                'KORR'      => $new['KORR'],
                'ELECTDAT'  => $new['ELECTDAT'],
                'ELECTAGE'  => $ELECTAGE,
                'STBDATUM'  => $new['STBDATUM'],
                'SONNE'     => $new['SONNE'],
                'MOND'      => $new['MOND'],
                'VENUS'     => $new['VENUS'],
                'MARS'      => $new['MARS'],
                'JUPITER'   => $new['JUPITER'],
                'SATURN'    => $new['SATURN'],
                'SO_'       => $new['SO_'],
                'MO_'       => $new['MO_'],
                'VE_'       => $new['VE_'],
                'MA_'       => $new['MA_'],
                'JU_'       => $new['JU_'],
                'SA_'       => $new['SA_'],
                'PHAS_'     => $new['PHAS_'],
                'AUFAB'     => $new['AUFAB'],
                'NIENMO'    => $new['NIENMO'],
                'NIENVE'    => $new['NIENVE'],
                'NIENMA'    => $new['NIENMA'],
                'NIENJU'    => $new['NIENJU'],
                'NIENSA'    => $new['NIENSA'],
            ];
            
            $res .= implode(G5::CSV_SEP, $new) . "\n";
            $res_raw .= implode(G5::CSV_SEP, $new_raw) . "\n";
        }
        
        $report = "--- muller m5medics raw2tmp ---\n";
        $outfile = M5medics::tmpFilename();
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        file_put_contents($outfile, $res);
        $report .=  "Generated $nRecords records in $outfile\n";
        
        $outfile = M5medics::tmpRawFilename();
        file_put_contents($outfile, $res_raw);
        $report .=  "Generated $nRecords records in $outfile\n";
        
        return $report;
    }
    
    
    // ******************************************************
    /**
        @return Array with 2 elements : family name and given name

    **/
    private static function compute_names($str){
        // NOB (nobiliary-particle) is not handled
        // missing a clear rule for Italy
        $giv = $fam = '';
        $pos = mb_strpos($str, '(');
        // $delimiter param in ucwords adds '-' to default chars
        $fam = ucWords(mb_strtolower(trim(mb_substr($str, 0, $pos-1))), " \t\r\n\f\v-");
        $giv = ucWords(mb_strtolower(mb_substr($str, $pos + 1)), " \t\r\n\f\v-");
        // fix specific typos
        $giv = str_replace('Emi1e', 'Emile', $giv);
        $giv = str_replace('(elie', 'Elie', $giv);
        $giv = str_replace([')', '.'], '', $giv);
        // correct accents
        $parts = explode(' ', $giv);
        $fixed = [];
        foreach($parts as $part){                                         
            $fixed[] = Names_fr::accentGiven($part);
        }
        $giv = implode(' ', $fixed);
        return [$fam, $giv];
    }
    
    // ******************************************************
    /** Auxiliary of raw2csv() **/
    private static function compute_lgLat($str){
        $tmp = explode(' ', $str);
        $res = $tmp[0] + $tmp[2] / 60;
        if($tmp[2] == 'S' || $tmp[2] == 'W'){
            $res = -$res;
        }
        return round($res, 5);
    }
        
    // ******************************************************
    /**
        Problem of place names :
        - In general, the département follows place name, in parenthesis.
        - Département is sometimes missing
        - Département is sometime not complete (no closing parenthesis).
        - The string inside parenthesis sometimes specifies the arrondissement, for Paris
        Note : arrondissement is ignored because erroneous (always 1ER)
        @return Array with 2 elements :
                - place name
                - department code (C2)
    **/
    private static function compute_place($str){
        // prepare self::$depts and self::$cities
        if(count(self::$depts) == 0){
            $lines = explode("\n", self::$DEPT_STR);
            $p = '/(\d{2}) (.*)/';
            foreach($lines as $line){
                preg_match($p, $line, $m);
                if(count($m) == 3){
                    self::$depts[$m[2]] = $m[1];
                }
                else{
                    self::$depts[$line] = $line;
                }
            }
            self::$cities = array_keys(self::$cities_depts);
        }
        $place = $dept = '';
        $pos = mb_strpos($str, '(');
        if($pos === false){
            // happens for 3 cases:
            // Bassing [Moselle] - correct name fixed in tweak2tmp
            // Tourcoing
            // Saint-Cirq-Lalo - correct name fixed in tweak2tmp
            $place = trim($str);
            $dept = self::$cities_depts[$place];
            return [$place, $dept];
        }
        $place = trim(mb_substr($str, 0, $pos)); // could be $pos-1
        $dept = mb_substr($str, $pos + 1);
        $dept = str_replace(')', '', $dept);
        // Obliged to handle Paris here as a particular case
        // because written "Paris (1ER)", then "1ER" is considered as $dept_code
        if($place == 'Paris'){
            $dept_code = 75;
        }
        else if(in_array($place, self::$cities)){
            $dept_code = self::$cities_depts[$place];
        }
        else{
            $dept_code = self::$depts[$dept];
        }
        return [$place, $dept_code];
    }
    
}// end class
