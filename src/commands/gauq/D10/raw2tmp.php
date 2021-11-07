<?php
/********************************************************************************
    Imports file data/raw/gauq/lerrcp/902gdD10.html to data/tmp/gauq/lerrcp/D10.csv
    1398 Data of Successful Americans
    
    @license    GPL                  
    @history    2019-04-04 14:23:10+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\D10;

use g5\G5;
use tiglib\patterns\Command;
use g5\commands\gauq\LERRCP;
use g5\commands\gauq\Cura;

class raw2tmp implements Command {
    
    /** ISO 3166 code (all data share the same country) **/
    const COUNTRY = 'US';
    
    /**
        Associations between Cura profession codes and g5 occupation slugs
    **/
    const PROFESSIONS = [
        'SP' => 'sportsperson',
        'MI' => 'military-personnel',
        'AC' => 'actor',
        'PO' => 'politician',
        'EX' => 'executive',
        'WR' => 'writer',
        'SC' => 'scientist',
        'AR' => 'artist',
        'X'  => 'miscelaneous', 
    ];
    
    // *****************************************
    /** 
        Parses file D10 and stores it in a csv file
        @param  $params Useless here - ['D10', 'raw2tmp']
        @return report
        @throws Exception if unable to parse
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 2){
            return "INVALID PARAMETER : " . $params[2] . " - raw2tmp doesn't need this parameter\n";
        }
        
        $report =  "--- gauq D10 raw2tmp ---\n";
        $raw = LERRCP::loadRawFile('D10');
        preg_match('#<pre>\s*(NUM.*?CICO)\s*(.*?)\s*</pre>#sm', $raw, $matches);
        if(count($matches) != 3){
            throw new \Exception("Unable to parse list in " . LERRCP::rawFilename('D10'));
        }
        // $matches[2] contains all interesting lines
        // Fix problems of whitespace + missing lg lat (NUM 493) in cura html page
        $matches[2] = preg_replace(
            "/112.*?Hardin County,\s+TN/",
            "112\tBlanton Leonard\tPO\t10\t4\t1930\t21:30\t6h\t35N11\t88W10\tHardin County, TN",     
            $matches[2]
        );
        $matches[2] = preg_replace(
            "/192.*?Bourneville,\s+OH/",
            "192\tCaldwell Philip\tEX\t27\t1\t1920\t08:00\t5h\t39N16\t83W09\tBourneville, OH",
            $matches[2]
        );
        $matches[2] = preg_replace(
            "/409.*?Sweatwater,\s+TX/",
            "409\tFaver Dudley\tMI\t17\t8\t1916\t15:00\t6h\t32N28\t100W24\tSweatwater, TX",
            $matches[2]                                                               
        );
        $matches[2] = preg_replace( // here add lg lat
            "/493.*?Grosby,\s+MN/",
            "493\tGood Robert\tAC\t21\t5\t1922\t06:00\t6h\t46N27\t94W10\tGrosby, MN",
            $matches[2]
        );
        $matches[2] = preg_replace(
            "/568.*?Rayville,\s+LA/",
            "568\tHayes Elvin\tSP\t11\t11\t1945\t22:00\t6h\t32N28\t91W45\tRayville, LA",
            $matches[2]
        );
        $matches[2] = preg_replace(
            "/658.*?Union,\s+SC/",
            "658\tJeter Robert\tSP\t9\t5\t1937\t08:00\t5h\t34N43\t81W37\tUnion, SC",
            $matches[2]
        );
        $matches[2] = preg_replace(
            "/817.*?Gary,\s+IN/",
            "817!\tMalden Karl\tAC\t22\t3\t1912\t17:00\t6h\t41N35\t87W20\tGary, IN",
            $matches[2]
        );
        $matches[2] = preg_replace(
            "/935.*?Appleton,\s+WI/",
            "935\tMurphy William\tEX\t17\t6\t1907\t10:00\t6h\t44N15\t88W24\tAppleton, WI",
            $matches[2]
        );
        $matches[2] = preg_replace(
            "/945.*?Packard,\s+KY/",
            "945\tNeal Patricia\tAC\t20\t1\t1926\t04:30\t6h\t36N40\t84W03\tPackard, KY",
            $matches[2]
        );
        $matches[2] = preg_replace(
            "/1033.*?Field,\s+CA/",
            "1033\tPitts William\tMI\t27\t11\t1919\t06:00\t8h\t33N54\t117W15\tMarch Field, CA",
            $matches[2]
        );
        $matches[2] = preg_replace(
            "/1058.*?Somerset,\s+KY/",
            "1058\tRamsey Lloyd\tMI\t29\t5\t1918\t07:00\t5h\t37N05\t84W36\tSomerset, KY",
            $matches[2]
        );
        $matches[2] = preg_replace(
            "/1066.*?Glasgow,\s+MT/",
            "1066!\tReeves Steve\tAC\t21\t1\t1926\t08:00\t7h\t48N11\t106W38\tGlasgow, MT",
            $matches[2]
        );
        $matches[2] = preg_replace(
            "/1158.*?Harding,\s+KS/",
            "1158\tShaffer Raymond\tEX\t6\t4\t1912\t22:30\t6h\t37N59\t94W49\tHarding, KS",
            $matches[2]
        );
        $matches[2] = preg_replace(
            "/1218.*?Chase,\s+WI/",
            "1218\tStaiger John\tEX\t20\t3\t1910\t19:00\t6h\t44N43\t88W09\tChase, WI",
            $matches[2]
        );
        $matches[2] = preg_replace(
            "/1250.*?Cohasset,\s+MA/",
            "1250\tSweeney Walter\tSP\t18\t4\t1941\t09:20\t5h\t42N14\t70W48\tCohasset, MA",
            $matches[2]
        );
        
        // fields in the resulting csv
        $csv = implode(G5::CSV_SEP, D10::TMP_FIELDS) . "\n";
        $emptyNew = array_fill_keys(D10::TMP_FIELDS, '');
        
        $csvRaw = implode(G5::CSV_SEP, D10::RAW_FIELDS) . "\n";
        $emptyNewRaw = array_fill_keys(D10::RAW_FIELDS, '');
        
        $nb_stored = 0;
        $lines = explode("\n", $matches[2]);
        foreach($lines as $line){
            $cur = preg_split('/\t+/', $line);
            $new = $emptyNew;
            [$new['NUM'], $new['C_APP']] = self::compute_corr_app(trim($cur[0]));
            [$new['FNAME'], $new['GNAME']] = self::compute_name(trim($cur[1]));
            // date time
            $day = Cura::computeDay(['DAY' => $cur[3], 'MON' => $cur[4], 'YEA' => $cur[5]]);
            if($cur[6] == '-----'){
                // Concerns one line without birth time
                // 1098!	Rose Peter		SP	14	4	1941	-----	5h	39N6	84W31	Cincinnati, OH
                $hour = '     ';
            }
            else{
                $hour = $cur[6];
            }
            $new['DATE'] = "$day $hour";
            // timezone
            $tmp = explode('h', trim($cur[7]));
            $h =  str_pad($tmp[0], 2, '0', STR_PAD_LEFT);
            $m =  str_pad ($tmp[1], 2, '0');
            $new['TZO'] = '-' . $h . ':' . $m;
            // place
            $tmp = explode(',', $cur[10]);
            $new['PLACE'] = trim($tmp[0]);
            $new['CY'] = self::COUNTRY;
            $new['C2'] = trim($tmp[1]);
            $new['LG'] = Cura::computeLg($cur[9]);
            $new['LAT'] = Cura::computeLat($cur[8]);
            // @todo link to geonames
            $new['OCCU'] = self::compute_profession($cur[2]);
            $csv .= implode(G5::CSV_SEP, $new) . "\n";
            // $raw, to keep an exact trace of original values
            $newRaw = $emptyNewRaw;
            $newRaw['NUM'] = $cur[0];
            $newRaw['NAME'] = $cur[1];
            $newRaw['PRO'] = $cur[2];
            $newRaw['DAY'] = $cur[3];
            $newRaw['MON'] = $cur[4];
            $newRaw['YEA'] = $cur[5];
            $newRaw['H'] = $cur[6];
            $newRaw['TZ'] = $cur[7];
            if($new['NUM'] != '493'){
                $newRaw['LAT'] = $cur[8];
                $newRaw['LON'] = $cur[9];
            }
            else{
                $newRaw['LAT'] = '';
                $newRaw['LON'] = '';
            }
            $newRaw['CICO'] = $cur[10];
            $csvRaw .= implode(G5::CSV_SEP, $newRaw) . "\n";
            //
            $nb_stored ++;
        }
        $csvfile = LERRCP::tmpFilename('D10');
        file_put_contents($csvfile, $csv);
        $report .= $nb_stored . " lines stored in $csvfile\n";
        
        $csvfile = LERRCP::tmpRawFilename('D10');
        file_put_contents($csvfile, $csvRaw);
        $report .= $nb_stored . " lines stored in $csvfile\n";
        
        return $report;
    }
    
    
    // ******************************************************
    /**
        If the last character of NUM is '!', it means that the record was
        modified in the review "Astro-Psychological Problems" ; C_APP is 'Y'.
        Otherwise C_APP is ''
        @return An array with two elements : [NUM, C_APP]
    **/
    private static function compute_corr_app($num){
        if(strpos($num, '!') === false){
            return [$num, ''];
        }
        return [substr($num, 0, -1), 'Y'];
    }
    
    // ******************************************************
    /**
        Compute family name and given name.
        The names containing only 1 white space are composed 
        of family name followed by given name.
        All the names containing 2 white spaces are regular
        (the 2 first strings = family name, last string = given name),
        except for 594 Hill L Gordon.
        @return An array with two elements : [family name, given name]
    **/
    private static function compute_name($name){
        if($name == 'Hill L Gordon'){
            return ['Lucius', 'Gordon Hill']; // Lucius comes from https://www.kayakero.net/per/gen/brief_record/br_7.html
        }
        $tmp = explode(' ', $name);
        if(count($tmp) == 2){
            return [$tmp[0], $tmp[1]];
        }
        return [$tmp[0] . ' ' . $tmp[1], $tmp[2]];
    }
    
    // ******************************************************
    /** 
        Compute profession labels(s) from profession code(s)
        Auxiliary of import()
    **/
    private static function compute_profession($pro){
        $codes = explode(',', trim($pro));
        $labels = [];
        foreach($codes as $code){
            $labels[] = self::PROFESSIONS[$code];
        }
        return implode('+', $labels);
    }

    
}// end class    
