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
    
    public function __construct(){
        $this->data = yaml_parse(file_get_contents(__DIR__ . DS . 'Group.yml'));
    }
    
    // *********************** Storage *******************************
    
    /** Creates an object of type Group from storage, using its id. **/
    public static function get($id): Group{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from groop where id=?");
        $stmt->execute([$id]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return new Group();
        }
        $res['sources'] = json_decode($res['sources'], true);
        $g = new Group();
        $g->data = $res;
        return $g;
    }
    
    /** Creates an object of type Group from storage, using its slug. **/
    public static function getBySlug($slug): Group{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from groop where slug=?");
        $stmt->execute([$slug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return new Group();
        }
        $res['sources'] = json_decode($res['sources'], true);
        $g = new Group();
        $g->data = $res;
        return $g;
    }
    
    /**
        Inserts a new group in storage.
        @return The id of the inserted row
        @throws \Exception if trying to insert a duplicate slug
    **/
    public static function insert(Group $g): int{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("insert into groop(slug,name,description,sources) values(?,?,?,?) returning id");
        $stmt->execute([
            $g->data['slug'],
            $g->data['name'],
            $g->data['description'],
            json_encode($g->data['sources']),
        ]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        $g->data['id'] = $res['id'];
        // members                                                                                     
        $stmt = $dblink->prepare("insert into person_group(id_person,id_group) values(?,?)");
        foreach($g->data['members'] as $pid){
            $stmt->execute([$pid, $g->data['id']]);
        }
        return $g->data['id'];
    }
    
    /**
        Updates a group in storage.
        @throws \Exception if trying to update an unexisting id
    **/
    public static function update(Group $g) {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("update groop set slug=?,name=?,description=?,sources=? where id=?");
        $stmt->execute([
            $g->data['slug'],
            $g->data['name'],
            $g->data['description'],
            json_encode($g->data['sources']),
            $g->data['id'],
        ]);
        // members                                                                                     
        $dblink->exec("delete from person_group where id_group='" . $dblink->quote($g->data['id']) . "'");
        $stmt = $dblink->prepare("insert into person_group(id_person,id_group) values(?,?)");
        foreach($g->data['members'] as $pid){
            $stmt->execute([$pid, $g->data['id']]);
        }
    }
    
    // *********************** Fields *******************************
    
    public function addMember($entry){
        $this->data['members'][] = $entry;
    }
    
    /** 
        Loads a group from storage and fills members with objects of type Person
        @param $slug
    **/
    public static function loadWithMembers($slug){
        $g = Group::getBySlug($slug);
        $gid = $g->data['id'];
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from person where id in(select id_person from person_group where id_group=?)");
        $stmt->execute([$gid]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $res = [];
        foreach($rows as $row){
            $res[] = Person::row2person($row);
        }
        return $res;
    }
    
    // *********************** Export *******************************
    
    /** 
        Generates a csv from its members
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
        @param $filters Regular array of functions returning a boolean
                        If one of these functions returns false on a group member,
                        export() skips the record.
        @return Report
        
    **/
    public function exportCsv($csvFile, $csvFields, $map=[], $fmap=[], $filters=[]): string {
        
        $csv = implode(G5::CSV_SEP, $csvFields) . "\n";
        
        $emptyNew = array_fill_keys($csvFields, '');
        
        $members = self::loadWithMembers($this->data['slug']);
        
        foreach($members as $p){
            // filters
            foreach($filters as $function){
                if(!$function($p)){
                    continue 2;
                }
            }
            $new = $emptyNew;
            // map
            foreach($map as $personKey => $csvKey){
                $pks = explode('.', $personKey);
                $data = null;
                foreach($pks as $pk){
                    if(!isset($p->data[$pk])){
                        // means an incoherence of data
                    }
                    $data = is_null($data) ? $p->data[$pk] : $data[$pk];
                }
                $new[$csvKey] = $data;
            }
            // fmap
            foreach($fmap as $csvKey => $function){
                $new[$csvKey] = $function($p);
            }
            $csv .= implode(G5::CSV_SEP, $new) . "\n";
break;
        }
        
        $report = '';
        $dir = dirname($csvFile);
        if(!is_dir($dir)){
            mkdir($dir, 0777, true);
            $report .= "Created directory $dir\n";
        }
        file_put_contents($csvFile, $csv);
        $report .= "Generated file $csvFile \n";
        return $report;
    }
    
}// end class
