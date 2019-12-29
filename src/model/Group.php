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
    
    public $uid;
    
    /** Elements of the group. Array of strings - contains data uids **/
    public $data = [];
    
    // *********************** new *******************************
    /**
        Create an object of type Group.
        @param $uid     String like groups/datasets/cura/A1
    **/
    public static function new($uid){
        $g = new Group();
        $g->uid = $uid;
        $g->load();
        return $g;
    }
    
    /** Returns an empty object of type Group. **/
    public static function newEmpty($uid=''){
        $g = new Group();
        if($uid != ''){
            $g->uid = $uid;
        }
        return $g;
    }
    
    // ************************ id ******************************

    public function uid(){
        return $this->uid;
    }
    
    public function slug(): string {
        $tmp = explode(Full::SEP, $this->uid);
        return $tmp[count($tmp)-1];
    }
    
    // *********************** fields *******************************
    
    public function add($entry){
        $this->data[] = $entry;
    }
    
    // *********************** file system *******************************
    
    public function path($full=true): string {
        $res = $full ? Full::$DIR . Full::SEP : '';
        $res .= $this->uid() . '.txt';
        return str_replace(Full::SEP, DS, $res);
    }
    
    public function load(){
        $path = $this->path();
        if(!is_file($path)){
            throw new \Exception(
                "IMPOSSIBLE TO LOAD GROUP - file not exist: $path\n"
              . "Execute raw2full to build the group first\n"
            );
        }
        $this->data = file($path,  FILE_IGNORE_NEW_LINES);
    }
    
    public function save(){
        $path = $this->path();
        $dir = dirname($path);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        $dump = '';
        // not sorted, keep the order decided by client code 
        foreach($this->data as $elt){
            $dump .= $elt . "\n";
        }
        file_put_contents($path, $dump);// echo "___ file_put_contents $path\n";
    }
    
    /** 
         @param $csvFields = ['GID', 'FNAME', 'GNAME', 'OCCU', '...', 'GEOID']
        @param $map = [
                'ids.cura' => 'GID',
                'fname' => 'FNAME',
                'gname' => 'GNAME',
                // ...
                'birth.place.geoid' => 'GEOID',
            ];
        
        $fmap = [
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
        ];
        
    **/
    public function exportCsv($file, $csvFields, $map=[], $fmap=[]){
        
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
//echo "\n<pre>"; print_r($new); echo "</pre>\n";        
            $csv .= implode(G5::CSV_SEP, $new) . "\n";
            
//echo "\n"; print_r($p->data); echo "\n";
//echo "\n"; print_r($new); echo "\n";
//break;
        }
//$file = 'data/9-output/datasets/cura/A1-new.csv';
//echo "$file\n";
//exit;
        file_put_contents($file, $csv);// echo "___ file_put_contents $file\n";
    }
    
}// end class
