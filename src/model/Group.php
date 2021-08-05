<?php
/******************************************************************************
    Group of persons
    Structure of field $data is described in Group.yml
    Field data['members'] is an array of person ids.
    Field data['person-members'] is an array of Person objects.
    
    @license    GPL
    @history    2019-12-27 23:20:16+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

use g5\Config;                           
use g5\G5;

class Group{
    
    public $data = [];
    
    /** Boolean indicating if data['person-members'] have already been computed **/
    public $personMembersComputed;
    
    
    public function __construct(){
        $this->personMembersComputed = false;
        $this->data = yaml_parse(file_get_contents(__DIR__ . DS . 'Group.yml'));
    }
    
    // *********************** Get ***********************
    
    /**
        Creates an object of type Group from storage, using its slug,
        or null if the group doesn't exist.
        Does not compute the members.
    **/
    public static function getBySlug($slug): ?Group {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from groop where slug=?");
        $stmt->execute([$slug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return null;
        }
        $g = new Group();
        $g->data = $res;
        $g->data['sources'] = json_decode($res['sources'], true);
        $g->data['parents'] = json_decode($res['parents'], true);
        $g->data['members'] = [];
        return $g;
    }
    
    // *********************** CRUD ***********************
    
    /**
        Inserts a group in storage.
        @return The id of the inserted row
        @throws \Exception if trying to insert a duplicate slug
    **/
    public function insert(): int{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("insert into groop(
            slug,
            name,
            n,
            description,
            sources,
            parents
            ) values(?,?,?,?,?,?) returning id");
        $this->data['n'] = count($this->data['members']);
        $stmt->execute([
            $this->data['slug'],
            $this->data['name'],
            $this->data['n'],
            $this->data['description'],
            json_encode($this->data['sources']),
            json_encode($this->data['parents']),
        ]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->data['id'] = $res['id'];
        // members
        $this->insertMembers();
        //
        return $this->data['id'];
    }
    
    /**
        Updates a group in storage.
        @throws \Exception if trying to update an unexisting group
    **/
    public function update() {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("update groop set
            slug=?,
            name=?,
            n=?,
            description=?,
            sources=?,
            parents=?
            where id=?");
        $this->data['n'] = count($this->data['members']);
        $stmt->execute([
            $this->data['slug'],
            $this->data['name'],
            $this->data['n'],
            $this->data['description'],
            json_encode($this->data['sources']),
            json_encode($this->data['parents']),
            $this->data['id'],
        ]);
        $this->updateMembers();
    }
    
    // *********************** Members ***********************
    
    /** 
        Adds a person id in $this->members
        (does not insert in database).
        @param  $entry Person id
    **/
    public function addMember($entry){
        $this->data['members'][] = $entry;
        $this->data['n']++;
    }
    
    /**
        Inserts a the associations between a group and its members in database
        (does not insert the persons).
        If trying to insert a member already associated with the group in database,
        insertion is silently ignored.
    **/
    public function insertMembers() {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("insert into person_groop(id_person,id_groop) values(?,?)");
        $this->data['members'] = array_unique($this->data['members']);
        foreach($this->data['members'] as $pid){
            try{
                $stmt->execute([$pid, $this->data['id']]);
            }
            catch(Exception $e){
                // do nothing
            }
        }
    }
    
    /**
        Updates the associations between the group and its members in database.
        @throws \Exception if trying to update an unexisting group
    **/
    public function updateMembers() {
        $dblink = DB5::getDbLink();
        $dblink->exec("delete from person_groop where id_groop=" . $this->data['id']);
        $stmt = $dblink->prepare("insert into person_groop(id_person,id_groop) values(?,?)");
        $this->data['members'] = array_unique($this->data['members']);
        foreach($this->data['members'] as $pid){
            $stmt->execute([$pid, $this->data['id']]);
        }
    }
    
    /**
        Deletes the associations between group and persons (doesn't delete the persons).
        @throws \Exception if trying to delete members of an unexisting group
    **/
    public function deleteMembers() {
        $dblink = DB5::getDbLink();
        $dblink->exec("delete from person_groop where id_groop=" . $this->data['id']);
        $this->data['members'] = [];
        $this->data['n'] = 0;
    }
    
    // *********************** Export ***********************
    
    /** 
        Fills person-members with objects of type Person
        @param $force If true, members computation will be done even if it was already done.
    **/
    public function computePersonMembers(bool $force=false){
        if($force === false && $this->personMembersComputed === true){
            return;
        }
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from person where id in(select id_person from person_groop where id_groop=?)");
        $stmt->execute([$this->data['id']]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->data['person-members'] = [];
        $this->data['members'] = [];
        foreach($rows as $row){
            $this->data['person-members'][] = Person::row2person($row);
            $this->data['members'][] = $row['id'];
        }
        $this->data['n'] = count($this->data['person-members']);
        $this->personMembersComputed = true;
    }
    
    /** 
        Generates and stores on disk a csv file (which may be zipped) from its members (of type Person).
            First line contains field names.
            Other lines contain data.
        @param $csvFile
            Path to the generated file.
            If $dozip = true, '.zip' is added at the end of $csvFile.
        @param $csvFields
            Names of the fields of the generated csv.
            Are written in this order in the csv.
            ex:
            $csvFields = ['GID', 'FNAME', 'GNAME', 'OCCU', '...', 'GEOID']
        @param $map
            Correspondance between person fields and csv fields
            For person fields, dot (.) is used to express multi-dimensional arrays
            For example, 'name.family' refers to $person['name']['family']
            Same syntax is used for regular arrays : 'occus.0' refers to $person[occus][0]
            ex:
            $map = [
                'name.family' => 'FNAME',
                'name.given' => 'GNAME',
                // ...
                'birth.place.geoid' => 'GEOID',
            ];
        
        @param $fmap Assoc array
                    key = field name in generated csv
                    value = function computing this field's value to write in the csv
                             parameter : a person
                             return : the value of the csv field
                    ex: 
                    $fmap = [
                        'OCCU' => function($p){
                            return implode('+', $p->data['occus']);
                        },
                    ];
        @param $sort    function used to sort the group's members.
                        if $sort = false, members are not sorted.
        @param $filters Regular array of functions returning a boolean.
                        If one of these functions returns false on a group member,
                        export() skips the record.
        @param $dozip   Boolean indicating if hte output should be zipped
        @return Report
        
    **/
    public function exportCsv($csvFile, $csvFields, $map=[], $fmap=[], $sort=false, $filters=[], $dozip=false): string {
        
        $csv = implode(G5::CSV_SEP, $csvFields) . "\n";
        
        $emptyNew = array_fill_keys($csvFields, '');
        
        $this->computePersonMembers();
        
        if($sort !== false){
            usort($this->data['person-members'], $sort);
        }
        
        $report = '';

        $N = 0;
        foreach($this->data['person-members'] as $p){
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
                    $data = is_null($data) ? $p->data[$pk] : $data[$pk];
                }
                $new[$csvKey] = $data;
            }
            // fmap
            foreach($fmap as $csvKey => $function){
                $new[$csvKey] = $function($p);
            }
            $csv .= implode(G5::CSV_SEP, $new) . "\n";
            $N++;
        }
        
        $dir = dirname($csvFile);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
            $report .= "Created directory $dir\n";
        }
        if($dozip){
            $zipFile = $csvFile . '.zip';
            $zip = new \ZipArchive();
            if ($zip->open($zipFile, \ZipArchive::CREATE) !== true) {
                throw new \Exception("Cannot open <$zipFile>");
            }
            $zip->addFromString(basename($csvFile), $csv);
            $zip->close();
            $report .= "Generated $N lines in file $zipFile\n";
        }
        else{
            file_put_contents($csvFile, $csv);
            $report .= "Generated $N lines in file $csvFile\n";
        }
        return $report;
    }
    
}// end class
