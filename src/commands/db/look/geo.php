<?php
/******************************************************************************
    Generates a list of C1 and C2 codes for all countries used in the database.
    
    Output generated to stdout, so usage is
    php run-g5.php db look geo > tmp.html
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2021-12-31 16:45:21+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\commands\db\look;

use tiglib\patterns\Command;
use g5\model\DB5;
use g5\model\Geonames;

class geo implements Command {
    
    /** 
        Association between ISO 3166 2-letter code and country name.
        Put in src/model/Country if useful
        Identical to openg src/model/country.go
    **/
	const COUNTRIES = [
		'AT' =>  'Austria',
		'BE' =>  'Belgium',
		'CH' =>  'Switzerland',
		'CL' =>  'Chile',
		'CZ' =>  'Czech Republic',
		'DE' =>  'Germany',
		'DK' =>  'Denmark',
		'DZ' =>  'Algeria',
		'ES' =>  'Spain',
		'FR' =>  'France',
		'GB' =>  'United Kingdom',
		'GF' =>  'French Guyana',
		'GP' =>  'Guadeloupe',
		'IT' =>  'Italy',
		'LU' =>  'Luxembourg',
		'MA' =>  'Morroco',
		'MC' =>  'Monaco',
		'MQ' =>  'Martinique',
		'NL' =>  'Netherlands',
		'PL' =>  'Poland',
		'RU' =>  'Russia',
		'TN' =>  'Tunisia',
		'SE' =>  'Sweden',
		'US' =>  'United States',
	];
    
    /** 
        @param  $params empty array
        @return Report
    **/
    public static function execute($params=[]): string {
        
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        $res = '';
        
        // Find the countries of g5 database
        
        $g5link = DB5::getDblink();
        $stmt = $g5link->query("select distinct birth->'place'->>'cy' as cy from person order by birth->'place'->>'cy'");
        $cys = [];
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $cys[] = $row['cy'];
        }
        
        // Table of contents
        $geolink = Geonames::compute_dblink();
        $nSplit = floor(count($cys) / 2); // to split toc in 2 columns
        $res .= '<div class="flex-wrap">' . "\n";
        $res .= '    <div class="padding-left2">' . "\n";
        $i = 0;
        foreach($cys as $cy){
            if(!isset(self::COUNTRIES[$cy])){
                return "MISSING COUNTRY NAME FOR CODE $cy\nFix the code before executing this command\n";
            }
            $res .= '        <div><a class="padding-right2" href="#codes-' . $cy . '"><code>' . $cy . '</code> ' . self::COUNTRIES[$cy] . '</a></div>' . "\n";
            $i++;
            if($i == $nSplit){
                $res .= "    </div>\n";
                $res .= '    <div class="padding-left2">' . "\n";
            }
        }
        $res .= "    </div>\n";
        $res .= '</div><!-- class="flex" -->' . "\n";
        
        // Generate one html table per country
        // columns: C1, C1 name, C2, C2 name
        foreach($cys as $cy){
            $res .= "\n" . '<h3 id="codes-' . $cy . '">' . $cy . ' - ' . self::COUNTRIES[$cy] . '</h3>' . "\n";
            $res .= '<table class="wikitable">' . "\n";
            $res .= '<tr><th colspan="2">C1</th><th colspan="2">C2</th></tr>' . "\n";
            $schema = strtolower($cy);
            $query = "select
                a1.admin1_code,
                a1.name         as admin1_name,
                a2.admin2_code,
                a2.name         as admin2_name
                from
                    $schema.admin1 as a1,
                    $schema.admin2 as a2
                where a1.admin1_code = a2.admin1_code
                order by a1.admin1_code, a2.admin2_code
                ";
            $stmt = $geolink->query($query);
            foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
                $res .= "<tr>"
                    . "<td>{$row['admin1_code']}</td>"
                    . "<td>{$row['admin1_name']}</td>"
                    . "<td>{$row['admin2_code']}</td>"
                    . "<td>{$row['admin2_name']}</td>"
                . "</tr>\n";
//echo "\n<pre>"; print_r($row); echo "</pre>\n"; exit;
            }
            $res .= "</table>\n";
//break;
        }
        return $res;
    }
    
} // end class
