<?php
/********************************************************************************
    Importation of Gauquelin 5th edition ; code specific to series B and E2
    Specific to E2 notice :
    PL : Place of Birth for children (HOS = Saint-Antoine Hospital ; MAT = private Maternity ; H = Home)     
    
    @license    GPL
    @copyright  jetheme.org
    @history    2014-01-11 00:17:29+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura;

use g5\init\Config;
use g5\transform\cura\Cura;

class SerieB{
    
    /** 
        Parses one file of serie B or E2 and store it in database
        The purpose is to merge 2 lists :
        - original list, without names
        - chronological list, with names
        Both lists are supposed to contain the same data, but in practice they don't contain the same number of lines
        So merge is done using birthdate - Merging not complete because of doublons (persons born the same day)
        @param  $params assoc array with the keys :
                    - destination-db : db path containing the table where data of this serie are stored
                    - 'serie'        : string
                    - file-info      : string like '902gdA1y', unique id of the data to parse
                                       used to build the name of the file to parse,                                                            
                                       and the table to build (leading '902' is removed)
                    - description     : string
        @return report
    **/
    public static function import($params){
        $report = '';
        // load and parse raw page
        $raw = Cura::read_raw_file($params['file-info']);
        preg_match('#<pre>\s*(NUM.*?COD)\s*(.*?)</pre>#sm', $raw, $m);
        if(count($m) != 3){
            throw new Exception("Unable to parse file '{$params['file-info']}'");
        }
        $fieldnames = preg_split('/\s+/', $m[1]); // curiously, explode(\t) doesn't work
        $lines = explode("\n", trim($m[2]));
        try{
            //
            // prepare destination table
            //
            $table = Cura::table_name($params['file-info']);
            $table_absolute = Cura::table_name_absolute($params['destination-db'], $table);
//            $this->clean_slugindex(); // done now with data of the original table
            Gauquelin5::$dblink->table_drop_if_exists($table);
            Gauquelin5::$dblink->table_create($table);
            $infosource = Gauquelin5::compute_default_infosource($params['serie'], $params['file-info']);
            $privacy = Gauquelin5::compute_default_privacy();
            Gauquelin5::$dblink->beginTransaction();
            Gauquelin5::$dblink->set($table, $infosource);
            Gauquelin5::$dblink->set($table, $privacy);
            //
            // loop on data and store
            //
            $cur_family = [];
            $prev_sex = false;
            $nb_stored_persons = $nb_stored_families = 0;
            $bugged_8224 = ['902gdB2', '902gdE2c', '902gdE2e']; // to fix a bug in cura.free.fr data : field 8224 has no NUM, in 3 distinct files
            $slugbase = 'gauquelin-' . strtolower(Cura::group_name($params['file-info']));
            $nlines = count($lines);
            for($iline=0; $iline < $nlines; $iline++){
                $line = $lines[$iline];
                $fields = explode(Cura::HTML_SEP, trim($line));
                $cur = [];
                if(in_array($params['file-info'], $bugged_8224) && count($fields) == 12){
                    // executed for 3 lines only (once per bugged file)
                    $cur['NUM'] = 8224;
                    for($i=0; $i < count($fields); $i++){
                        $cur[$fieldnames[$i+1]] = $fields[$i];
                    }
                    $report .= "\nFIXED BUG IN DATA (missing field NUM for record 8224) in file " . $params['file-info'];
                }
                else{
                    // normal case, executed for all lines except 3
                    for($i=0; $i < count($fields); $i++){
                        $cur[$fieldnames[$i]] = $fields[$i]; // ex: $cur['YEA'] = '1817'
                    }
                }
                $new = [];
                // t
                $day = Cura::computeDay($cur);
                $hour = Cura::computeHHMMSS($cur);
                $tz = trim($cur['TZ']) == 0 ? '+00:00' : '-01:00';
                // slug, id
                $num = trim($cur['NUM']);
                $slug = $slugbase . '-' . $num;
                $new['id'] = $slug;
                $new['slug'] = $slug;
                $place = [
                    '', // city unknown (often paris) ; could be found from lg lat
                    'FR',
                    trim($cur['COD']),
                    '', // geonames id => @todo link to geonames
                    Cura::computeLg($cur['LON']),
                    Cura::computeLat($cur['LAT']),
                ];
                $new['s'] = $table_absolute . Storage::SEP . $slug;
                $new['p'] = 'type';
                $new['o'] = Entities::TYPE_PERSON;
                $new['t'] = json_encode($day . ' ' . $hour . $tz);
                $new['l'] = json_encode(implode(Places::STORAGE_SEP, $place));
                // data that will go in field 'free'
                $new['sex'] = ($cur['SEX'] == 'F' || $cur['SEX'] == 'S') ? 'M' : 'F';
                $new['Ci'] = $cur['Ci'];
                // for series E2a and E2b
                if(isset($cur['PL'])){
                    if($cur['PL'] == 'H'){
                        $new['birthplace'] = 'home';
                    }
                    else if($cur['PL'] == 'HOS'){
                        $new['birthplace'] = 'hospital';
                    }
                    else if($cur['PL'] == 'MAT'){
                        $new['birthplace'] = 'maternity';
                    }
                }
                $new['family-position'] = $cur['SEX']; // this field is used to build family, and deleted before being stored
                //
                // families
                //
                $sex = $cur['SEX'];
                if( 
                    ( ($sex == 'F' || $sex == 'M') && ($prev_sex == 'D' || $prev_sex == 'S') )
                 || ($iline == $nlines - 1)
                ){
                if($iline == $nlines - 1){
                    $cur_family[] = $new;
                }
                    // $new is member of a new family, so store $cur_family
                    $nb_stored_families ++;
                    // one row for the family
                    $row = [];
                    $id_family = Cura::$dblink->auto_increment($table);
                    $row['id'] = $id_family;
                    $row['s'] = $table_absolute . Storage::SEP . $id_family;
                    $row['p'] = 'type';
                    $row['o'] = Entities::TYPE_FAMILY;
                    // rows to associate the father and / or the mother to the family
                    Gauquelin5::$dblink->set($table, $row); // HERE store family
                    $children = [];
                    $parents = [];
                    foreach($cur_family as $person){
                        if($person['family-position'] == 'S' || $person['family-position'] == 'D'){
                            $children[] = $person;
                        }
                        else if($person['family-position'] == 'F' || $person['family-position'] == 'M'){
                            $parents[] = $person;
                        }
                    }
                    foreach($parents as $parent){
                        $row = [];
                        $row['id'] = Gauquelin5::$dblink->auto_increment($table);
                        $row['s'] = $table_absolute . Storage::SEP . $id_family;
                        $row['p'] = 'concerns';
                        $row['o'] = $table_absolute . Storage::SEP . $parent['id'];
                        Gauquelin5::$dblink->set($table, $row); // HERE store association between parent and family
                        // store child-of associations
                        foreach($children as $child){
                            $row = [];
                            $row['id'] = Gauquelin5::$dblink->auto_increment($table);
                            $row['s'] = $table_absolute . Storage::SEP . $child['id'];
                            $row['p'] = 'child-of';
                            $row['o'] = $table_absolute . Storage::SEP . $parent['id'];
                            $row['reverse-link'] = ($parent['family-position'] == 'F' ? 'father' : 'mother');
                            Gauquelin5::$dblink->set($table, $row); // HERE store association between parent and child
                            
                        }
                    }
                    //
                    // store persons of the family
                    foreach($cur_family as $person){
                        unset($person['family-position']);
                        $nb_stored_persons += Gauquelin5::$dblink->set($table, $person); // HERE store person
                    }
                    // start a new family
                    $cur_family = [];
                }
                $prev_sex = $sex;
                $cur_family[] = $new;
            }
            $report .= "\nnb stored persons: $nb_stored_persons";
            $report .= "\nnb stored families: $nb_stored_families";
            $report .= Cura::create_group(array_merge($params, ['table' => $table, 'count' => $nb_stored_persons]));
            Gauquelin5::$dblink->commit();
        }
        catch(Exception $e){
            $report .= "\nPROBLEM DURING IMPORT - database was NOT modified";
            $report .= "\n" . $e->getMessage();
            $report .= "\n" .  $e->getFile() . ' - ' . $e->getLine() . "\n";
            $report .= "\n" .  $e->getTraceAsString();
            Gauquelin5::$dblink->rollback();
        }
        // fill slugindex
        Gauquelin5::$dblink->index_entity_table($table);
        //
        return $report;
    }
    
    
}// end class    

