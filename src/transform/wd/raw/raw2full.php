<?php
/********************************************************************************
    
    @license    GPL
    @history    2019-05-16 12:16:35+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\wd\raw;

use g5\init\Config;
use g5\patterns\Command;
use g5\model\Full;
use g5\transform\wd\Wikidata;

class raw2full implements Command{
    
    /** 
        Template for resulting person.
        Guarantees that all records contain the same fields in the same order.
        Contains ony commmon fields.
    **/
    const EMPTY_RES = [
        'name' => '',
        'family-name' => '',
        'given-name' => '',
        'gender' => '',
        'ids' => [],
        'occupations' => [],
        'birth' => [],
        'death' => [],
        'data-sources' => [
            'wikidata' => [
            ],
        ],
    ];
    
    // *****************************************
    /** 
        Store the content of one or several csv file to yaml files of 5-tmp/full
        @param $params array with one element :
            relative path from dirs/1-wd-raw of config.yml to the csv file to import, without .csv extension.
            Ex : if the value dirs/1-wd-raw is data/1-raw/wikidata.org
            and the csv file to import is data/1-raw/wikidata.org/science/maths.csv
            Then the parameter must be "science/maths".
        @return report
    **/
    public static function execute($params=[]): string{
        
        $USAGE = "WRONG USAGE of g5\\transform\\wd\\raw.execute(\$params)\n"
            . "This command needs one parameter expressing one of the following :\n"
            . "- The csv file to import.\n"
            . "    Ex : if the csv file to import is data/1-raw/wikidata.org/science/maths.csv\n"
            . "    then the parameter must be \"science/maths\".\n"
            . "- The directory to import :\n"
            . "    Ex : if the parameter is \"sport\", it will import all files of directory data/1-raw/wikidata.org/sport.\n"
            . "- ALL : this will import all files of all sub-directories of data/1-raw/wikidata.org/.\n";
        if(count($params) != 1){
            return $USAGE;
        }
        $param = $params[0];
        if($param == 'ALL'){
            $dirname = Config::$data['dirs']['1-wd-raw'];
            $files = glob($dirname . DS . '*' . DS . '*.csv');
            foreach($files as $filename){
                self::importOneFile($filename);
            }
            return '';
        }
        else{
            $dirname = Config::$data['dirs']['1-wd-raw'] . DS . $param;
            if(is_dir($dirname)){
                $files = glob($dirname . DS . '*.csv');
                foreach($files as $filename){
                    self::importOneFile($filename);
                }
                return '';
            }
            else{
                $filename = Config::$data['dirs']['1-wd-raw'] . DS . $param . '.csv';
                if(file_exists($filename)){
                    self::importOneFile($filename);
                    return '';
                }
                else{
                    return $USAGE . "\n"
                        . "Nothing corresponds to $param";
                }
            }
        }
    }
    
    // ******************************************************
    /**
        Auxiliary of execute()
    **/
    private static function importOneFile($filename){
        
        echo "Importing $filename ... \n";
        
        $rows = \lib::csvAssociative($filename, Wikidata::RAW_CSV_SEP);
        
        // group rows by wikipedia id, because of doublons
        $assoc = [];
        foreach($rows as $row){
            $id = Wikidata::getId($row['person']);
            if(!isset($assoc[$id])){
                $assoc[$id] = [];
            }
            $assoc[$id][] = $row;
        }
        unset($rows);

        // now each element of $rows contain one person
        $nStored = 0;
        foreach($assoc as $id => $rows){
            $new = self::EMPTY_RES;
            if(count($rows) == 1){
                // one line for a given person
                // convert occupation fields to be coherent with mergeDoublons()
                $row = $rows[0];
                $row['occupation'] = [$row['occupation']];
                $row['occupationLabel'] = [$row['occupationLabel']];
                $row['ambiguities'] = [];
            }
            else{
                $row = self::mergeDoublons($rows);
            }
            // names
            $new['name'] = $row['personLabel'];
            [$new['family-name'], $new['given-name']] = self::computeNames($row['personLabel'], $row['familynameLabel']);
            switch($row['genderLabel']){
            	case 'male': $new['gender'] = 'M'; break;
            	case 'female': $new['gender'] = 'F'; break;
                default: $new['gender'] = $row['genderLabel']; break;
            }
            // ids
            $row['ids'] = [];
            $new['ids']['wikidata'] = $id;
            $new['ids']['isni'] = $row['isni'];
            if($row['macTutor'] != ''){
                $new['ids']['mactutor'] = $row['macTutor'];
            }
            // occupations
            for($i=0; $i < count($row['occupation']); $i++){
                $new['occupations'][] = [
                    'name' => $row['occupationLabel'][$i],
                    'id-wikidata' => Wikidata::getId($row['occupation'][$i]),
                ];
            }
            // birth death
            $new['birth'] = self::birthDeath(
                $row['birthdate'],
                $row['birthplace'],
                $row['birthplaceLabel'],
                $row['birthiso3166'],
                $row['birthgeonamesid'],
                $row['birthcoords']
            );
            $new['death'] = self::birthDeath(
                $row['deathdate'],
                $row['deathplace'],
                $row['deathplaceLabel'],
                $row['deathiso3166'],
                $row['deathgeonamesid'],
                $row['deathcoords']
            );
            if(isset($row['deathcauseLabel'])){
                $new['death']['cause'] = $row['deathcauseLabel'];
            }
            // values specific to wikidata
            $new['data-sources']['wikidata']['id-birthplace'] = Wikidata::getId($row['birthplace']);
            $new['data-sources']['wikidata']['id-deathplace'] = Wikidata::getId($row['deathplace']);
            $new['data-sources']['wikidata']['link-count'] = $row['linkcount'];
            if(count($row['ambiguities']) != 0){
                $new['data-sources']['wikidata']['ambiguities'] = $row['ambiguities'];
            }
            
            // output
            $slug = \lib::slugify($new['name']);
            $dir = Full::getDirectory($new['birth']['date']);
            $filename = $dir . DS . $slug . '.yml';
            if(!is_dir($dir)){
                mkdir($dir, 0755, true);
            }
            $yaml = yaml_emit($new);
            file_put_contents($filename, $yaml);
            $nStored++;
        }
        echo "done - stored $nStored persons\n";
    }
    
    
    // ******************************************************
    /**
        Auxiliary of importOneFile()
    **/
    private static function birthDeath(
        $date,
        $place,
        $placeLabel,
        $iso3166,
        $geonamesid,
        $coords
        
    ){
        $res = [
            'date' => '',
            'place' => [
                'name' => '',
                'country' => '',
                'id-geonames' => '',
                'lg' => '',
                'lat' => '',
            ],
        ];
        $res['date'] = substr($date, 0, 10); // no birth time from wd
        $res['place']['name'] = $placeLabel;
        $res['place']['country'] = $iso3166;
        $res['place']['id-geonames'] = $geonamesid;
        [$res['place']['lg'], $res['place']['lat']] = Wikidata::parseLgLat($coords);
        
        
        return $res;
    }
    
    // ******************************************************
    /**
        Tries to isolate family name and given name.
        Auxiliary of importOneFile()
        @return Array with two elements containing family name and given name
                Contains empty string in case of ambiguity.
    **/
    private static function computeNames($full, $family){
        if(strpos($full, $family) !== false){
            $given = trim(str_replace($family, '', $full));
        }
        else{
            $tmp = explode(' ', $full);
            if(count($tmp) == 2){
                $given = $tmp[0];
                $family = $tmp[1];
            }
            else{
                // don't try clever guess
                // echo "NAME PROBLEM - $full - $family\n"; // TODO log ?
                return ['', ''];
            }
        }
        return [$family, $given];
    }
    
    // ******************************************************
    /**
        Auxiliary of importOneFile()
        @param $rows Rows coming from wikidata - so each row is an associative array without recursion
        @return Assoc array with a scalar value for each field, except for occupation and occupationLabel
                Multiple values for other fields are stored in $res['ambiguities']
        
    **/
    private static function mergeDoublons($rows){
        $res1 = $rows[0];
        for($i=1; $i < count($rows); $i++){
            $res1 = array_merge_recursive($res1, $rows[$i]);
        }
        // each element of $res1 is an array containing all the values of $rows
        $res2 = [];
        foreach($res1 as $k => $v){
            $res2[$k] = array_values(array_unique($v)); // array_values to reindex
        }
        // each element of $res2 is an array containing all the DISTINCT values of $rows
        $res = [];
        $res['ambiguities'] = [];
        foreach($res2 as $k => $v){
            if(count($v) == 1 && $k != 'occupation' && $k != 'occupationLabel'){
                $res[$k] = $v[0];
            }
            else{
                if($k != 'occupation' && $k != 'occupationLabel'){
                    $res[$k] = $v[0]; // arbitrary choice
                    $res['ambiguities'][$k] = $v;
                }
                else{
                    $res[$k] = $v;
                }
            }
        }
        // each element of $res contains a scalar value except for occupation and occupationLabel
        return $res;
    }
    
    
}// end class    
