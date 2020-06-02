<?php
/******************************************************************************
    Regular array containing person paths (strings)
        ex of persons/1864/12/16/machin-pierre
    
    @license    GPL
    @history    2019-12-27 23:20:16+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

use g5\Config;                           
use g5\G5;

class Group{
    
    public $data = [];
    
    // *********************** new *******************************
    /**
        Create an object of type Group from its uid.
        @param $uid     String like group/web/cura/A1
    **/
    public static function new($uid){
        $g = new Group();
        $g->data['uid'] = $uid;
        $g->load();
        return $g;
    }
    
    /** Returns an empty object of type Group. **/
    public static function newEmpty($uid=''){
        $g = new Group();
        if($uid != ''){
            $g->data['uid'] = $uid;
        }
        return $g;
    }
    
    // ************************ id ******************************

    public function uid(){
        return $this->data['uid'];
    }
    
    public function slug(): string {
        $tmp = explode(DB5::SEP, $this->uid());
        return $tmp[count($tmp)-1];
    }
    
    // *********************** fields *******************************
    
    public function add($entry){
        $this->data[] = $entry;
    }
    
    // *********************** file system *******************************
    
    public function file($full=true): string {
        $res = $full ? DB5::$DIR . DB5::SEP : '';
        $res .= $this->uid() . '.txt';
        return str_replace(DB5::SEP, DS, $res);
    }
    
    public function load(){
        $path = $this->file();
        if(!is_file($path)){
            throw new \Exception(
                "IMPOSSIBLE TO LOAD GROUP - file not exist: $path\n"
              . "Execute raw2full() to build the group first\n"
            );
        }
        $this->data = file($path,  FILE_IGNORE_NEW_LINES);
    }
    
    /** 
        Writes a txt file on disk
        with one person slug per line 
    **/
    public function save(){
        $path = $this->file();
        $dir = dirname($path);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        $dump = '';
        // not sorted, keep the order decided by client code 
        foreach($this->data as $elt){
            $dump .= $elt . "\n";
        }
        file_put_contents($path, $dump);
        // echo "___ file_put_contents $path\n"; // @todo log
    }
    
    /** 
        Generates a csv
            first line contains field names
            other lines contain data
        @param $csvFile 
        @param $csvFields
            Names of the fields of the generated csv
            Are written in this order in the csv
            $csvFields = ['GID', 'FNAME', 'GNAME', 'OCCU', '...', 'GEOID']
        @param $map
            $map = [
                'ids.cura' => 'GID',
                'fname' => 'FNAME',
                'gname' => 'GNAME',
                // ...
                'birth.place.geoid' => 'GEOID',
            ];
        
        @param $fmap Assoc array
                    key = field name in generated csv
                    value = function computing this field's value to write in the csv
                             parameter : a person
                             return : the value of the csv field
                    $fmap = [
                        'OCCU' => function($p){
                            return implode('+', $p->data['occus']);
                        },
                    ];
        
    **/
    public function exportCsv($csvFile, $csvFields, $map=[], $fmap=[]){
        
        $csv = implode(G5::CSV_SEP, $csvFields) . "\n";
        
        $emptyNew = array_fill_keys($csvFields, '');
        
        foreach($this->data as $puid){
            $p = Person::new($puid);
            $new = $emptyNew;
            // transfer infos from $p->data to $new
            foreach($map as $personKey => $csvKey){
                $pks = explode('.', $personKey); // @todo put '.' in a constant
                $data = null;
                foreach($pks as $pk){
                    if(!isset($p->data[$pk])){
                        // means an incoherence of data
                    }
                    $data = is_null($data) ? $p->data[$pk] : $data[$pk];
                }
                $new[$csvKey] = $data;
            }
            foreach($fmap as $csvKey => $function){
                $new[$csvKey] = $function($p);
            }
            $csv .= implode(G5::CSV_SEP, $new) . "\n";
        }
        
        file_put_contents($csvFile, $csv);
        // echo "___ file_put_contents $csvFile\n"; // @todo 
    }
    
}// end class
