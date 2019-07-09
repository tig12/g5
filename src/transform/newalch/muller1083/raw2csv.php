<?php
/********************************************************************************
    Imports 1-newalch-raw/05-muller-medics/5a_muller-medics-utf8.txt to 5-newalch-csv/1083MED.csv
        
    @license    GPL
    @history    2019-07-06 12:21:23+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\newalch\muller1083;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use tiglib\arrays\csvAssociative;

class raw2csv implements Command{
    
    public static $depts;
    
    // *****************************************
    /** 
        Parses file 1-raw/newalchemypress.com/3a_sports-utf8.csv
        and stores it to 5-tmp/newalch/4391SPO.csv
        @param $params empty array
        @return report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 0){
            return "INVALID PARAMETER : " . $params[0] . " - don't need this parameter\n";
        }
        
        $raw = file_get_contents(Muller1083::raw_filename());
        $lines = explode("\n", $raw);
        $N = count($lines);
        
        $res = implode(G5::CSV_SEP, Muller1083::TMP_CSV_COLUMNS) . "\n";
        $nRecords = 0;
        
//        for($i=5; $i < 100; $i++){
        for($i=5; $i < $N-3; $i++){
            if($i%2 == 1){
                continue;
            }
            $nRecords++;
            
            $line  = trim($lines[$i]);
            $new = array_fill_keys(Muller1083::TMP_CSV_COLUMNS, '');
            $new['NR'] = trim(mb_substr($line, 0, 5));
            $new['SAMPLE'] = trim(mb_substr($line, 5, 11));
            $new['GNR'] = trim(mb_substr($line, 16, 6));
            $new['CODE'] = trim(mb_substr($line, 32, 1));
            [$new['FNAME'], $new['GNAME']] = self::compute_names(trim(mb_substr($line, 34, 51)));
            $tmp = explode('.', trim(mb_substr($line, 85, 10)));
            $new['DATE'] = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
            $new['DATE'] .= ' ' . str_replace('.', ':', mb_substr($line, 101, 5));
            // place
            [$new['PLACE'], $new['C2'], $new['NOTES']]
                = self::compute_place(trim(mb_substr($line, 110, 36)));
            
            $new['LG'] = self::compute_lgLat(trim(mb_substr($line, 146, 8)));
            $new['LAT'] = self::compute_lgLat(trim(mb_substr($line, 156, 7)));
            
            $new['MODE'] = trim(mb_substr($line, 168, 3));
            $new['KORR'] = trim(mb_substr($line, 173, 5));
            
            $new['ELECTDAT'] = trim(mb_substr($line, 184, 10));
            $new['STBDATUM'] = trim(mb_substr($line, 204, 10));
            // ELECTAGE not done (duplicate information, can be recomputed)
            
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
            // GEBJAHR not done (duplicate information)
            // GEBMONAT not done (duplicate information)
            // GEBTAG not done (duplicate information)
            $res .= implode(G5::CSV_SEP, $new) . "\n";
        }
            
        $outfile = Config::$data['dirs']['5-newalch-csv'] . DS . Muller1083::TMP_CSV_FILE;
        file_put_contents($outfile, $res);
        
        return "Importing Müller 183 - $nRecords records\n$outfile generated\n";
    }
    
    
    // ******************************************************
    /**
        @return Array with 2 elements : family name and given name

    **/
    public static function compute_names($str){
        $giv = $fam = '';
        $pos = mb_strpos($str, '(');
        $fam = ucWords(mb_strtolower(trim(mb_substr($str, 0, $pos-1))));
        $giv = ucWords(mb_strtolower(mb_substr($str, $pos + 1)));
        // should also uppercase letters following '-'
        $giv = str_replace([')', '.'], '', $giv);
        return [$fam, $giv];
    }
    
    
    
    // ******************************************************
    /**
        Problem of place names :
        - In general, the département follows place name, in parenthesis.
        - Département is sometimes missing
        - Département is sometime not complete (no closing parenthesis).
        - The string inside parenthesis sometimes specifies the arrondissement, for Paris
        @return Array with 3 elements :
                - place name
                - department code (C2)
                - a string to add to field NOTE
    **/
    public static function compute_place($str){
        $place = $dept = $note = '';
        $pos = mb_strpos($str, '(');
        if($pos === false){
            $place = trim($str);
            return [$place, $dept, $note];
        }
        $place = trim(mb_substr($str, 0, $pos)); // could be $pos-1
        $dept = mb_substr($str, $pos + 1);
        $dept = str_replace(')', '', $dept);
        if($place == 'Paris'){
            $note = 'Paris ' . mb_strtolower($dept);
            $dept = 'Paris';
        }
        return [$place, $dept, $note];
    }
    
    // ******************************************************
    /**
        Auxiliary of raw2csv()
    **/
    private static function compute_lgLat($str){
        $tmp = explode(' ', $str);
        $res = $tmp[0] + $tmp[2] / 60;
        if($tmp[2] == 'S' || $tmp[2] == 'W'){
            $res = -$res;
        }
        return round($res, 5);
    }
    
    
/*                                                         
1    MUER_NUR                   1 ABADIE (BERNARD).                                  23.02.1817 1817 02.00    Mazerolles (Hautes-Pyrénées)        000 E 05   43 N 14    LMT             31.01.1888     71,0 18.10.1888    30   22    26   33       1     30   1   0   1   0   2   1 37,200   8,80                    0      2      2    1817        2     23

2    MUERGAUQ   SA22            3 ABADIE (JEAN BAPTISTE MARIE JULES).                12.08.1876 1876 03.00    Blaye (Gironde)                     000 W 34   44 N 50    LMT             26.05.1936     59,8 10.08.1953    26    2    30   25      19      6   1   2   1   0   1   1 60,200 -10,20      2      2      0      2      0    1876        8     12
478  MUERGAUQ   SA21049         3 HERMANN (HENRI XAVIER).                            19.12.1892 1892 20.00    Lunéville (Meurthe-et-Moselle)      006 E 12   48 N 42         -0.16      16.11.1943     50,9   .  .        23   23    26   13      11     30   0   0   1   1   2   1  0,200  -1,50                    0      2      2    1892       12     19
14   MUERGAUQ   ND129           3 ARDOIN (FRANÇOIS GUSTAVE EDMOND).                  03.11.1897 1897 10.00    Bourges (Cher)                      002 E 23   47 N 05         -0.16      28.05.1957     59,6   .  .         6   31     9    6      11      3   1   1   2   1   2   2 64,700  10,40             2      0      2      2    1897       11      3
*/
    
/* 
Generated with echo dept
php run-g5.php newalch muller1083 raw2csv | sort | uniq 
    private static $tmp_depts = <<<DEPTS
01 Ain
02 Aisne
03 Allier
06 Alpes-Maritimes
Alsace-Lorraine
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
Haut-Rhin [Territoire-de
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
DEPTS;
*/
}// end class
