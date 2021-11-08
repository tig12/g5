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

use g5\app\Config;                           
use g5\G5;
use tiglib\dag\DAGStringNode;

class Group {
    
    // possible types of groups
    
    const TYPE_OCCU = 'occu';
    
    const TYPE_HISTORICAL = 'history';

    /**
        Associative array: group slug => array of slugs of ancestors.
        Computed by getAllAncestors().
    **/
    private static $allAncestors = null;
    
    public $data = [];
    
    /** Boolean indicating if data['person-members'] have already been computed **/
    public $personMembersComputed;
    
    /** 
        Constructor ; builds an empty group or a group filled from its yaml file definition.
        In all cases, members are not computed.
        @param  $yamlFile Path to a yaml file, relative to data/db/group
    **/
    public function __construct($yamlFile=''){
        $this->personMembersComputed = false;

        // Fill an empty source from its structure
        $this->data = yaml_parse_file(__DIR__ . DS . 'Group.yml');
        if($yamlFile == ''){
            return; // ok, just build an empty group
        }

        // Load group data from data/db/group
        $human = yaml_parse_file($yamlFile);
        $this->data = array_replace_recursive($this->data, $human);
echo "\n<pre>"; print_r($this->data); echo "</pre>\n"; exit;
    }
    
    // ***********************************************************************
    //                                  Get
    // ***********************************************************************
    
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
        $g->data['children'] = json_decode($res['children'], true);
        $g->data['members'] = [];
        return $g;
    }
    
    /**
        Returns an array of Group objects, retrieved from database.
    **/
    public static function loadAllFromDB() {
        $dblink = DB5::getDbLink();
        $query = "select * from groop";
        $res = [];
        foreach($dblink->query($query, \PDO::FETCH_ASSOC) as $row){
            $row['parents'] = json_decode($row['parents'], true);
            $row['children'] = json_decode($row['children'], true);
            $row['sources'] = json_decode($row['sources'], true);
            $tmp = new Group();
            $tmp->data = $row;
            $res[] = $tmp;
        }
        return $res;
    }
    
    // ***********************************************************************
    //                                  CRUD
    // ***********************************************************************
    
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
            type,
            description,
            download,
            sources,
            parents,
            children
            ) values(?,?,?,?,?,?,?,?,?) returning id");
        $this->data['n'] = count($this->data['members']);
        $stmt->execute([
            $this->data['slug'],
            $this->data['name'],
            $this->data['n'],
            $this->data['type'],
            $this->data['description'],
            $this->data['download'],
            json_encode($this->data['sources']),
            json_encode($this->data['parents']),
            json_encode($this->data['children']),
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
    public function update(bool $updateMembers=true) {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("update groop set
            slug=?,
            name=?,
            n=?,
            type=?,
            description=?,
            download=?,
            sources=?,
            parents=?,
            children=?
            where id=?");
        if($updateMembers == true){
            $this->data['n'] = count($this->data['members']);
        }
        $stmt->execute([
            $this->data['slug'],
            $this->data['name'],
            $this->data['n'],
            $this->data['type'],
            $this->data['description'],
            $this->data['download'],
            json_encode($this->data['sources']),
            json_encode($this->data['parents']),
            json_encode($this->data['children']),
            $this->data['id'],
        ]);
        if($updateMembers == true){
            $this->updateMembers();
        }
    }
    
    // ***********************************************************************
    //                                  Members
    // ***********************************************************************
    
    /** 
        Adds a person id in $this->members
        Also updates field "n".
        Does not affect database.
        @param  $pid Person id
    **/
    public function addMember($pid){
        $this->data['members'][] = $pid;
        $this->data['n']++;
    }
    
    /** 
        Fills field $this->members with person ids from table person_groop.
        Also updates field "n".
        Does not affect database.
        @see    computePersonMembers()
    **/
    public function computeMembers(){
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select id_person from person_groop where id_groop=" . $this->data['id']);
        $stmt->execute([]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->data['members'] = [];
        foreach($rows as $row){
            $this->data['members'][] = $row['id_person'];
        }
        $this->data['n'] = count($this->data['members']);
    }
    
    /**
        Inserts in database the associations between a group and its members.
        Does not insert the persons.
        Update in database the group (field 'n' is modified).
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
        $this->data['n'] = count($this->data['members']);
        $this->update(updateMembers:false); // for field n
    }
    
    /**
        Updates in database the associations between the group and its members in database.
        Also updates field 'n' in table groop.
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
        $this->data['n'] = count($this->data['members']);
        $this->update(updateMembers:false); // for field n
    }
    
    /**
        Deletes in database the associations between group and persons (doesn't delete the persons).
        Also updates field 'n' in table groop.
        @throws \Exception if trying to delete members of an unexisting group
    **/
    public function deleteMembers() {
        $dblink = DB5::getDbLink();
        $dblink->exec("delete from person_groop where id_groop=" . $this->data['id']);
        $this->data['members'] = [];
        $this->data['n'] = 0;
        $this->update(updateMembers:false); // for field n
    }
    
    // ***********************************************************************
    //                       Ancestors / descendants
    // ***********************************************************************
    /**
        Returns self::$allAncestors
    **/
    public static function getAllAncestors() {
        self::computeAllAncestors();
        return self::$allAncestors;
    }
    
    /**
        Computes self::$allAncestors
    **/
    private static function computeAllAncestors() {
        if(self::$allAncestors != null){
            return;
        }
        $groupsFromDB = self::loadAllFromDB();
        // 1 - $nodes = assoc array slug - DAGStringNode
        //     $groups = assoc array slug - Group object
        $nodes = [];
        $groups = [];
        foreach($groupsFromDB as $group){
            $slug = $group->data['slug'];
            $nodes[$slug] = new DAGStringNode($slug);
            $groups[$slug] = $group;
        }
        // 2 - add edges from parents
        foreach($groups as $group){
            $slug = $group->data['slug'];
            foreach($group->data['parents'] as $parent){ // $parent is a slug
                if(!isset($nodes[$parent])){
                    $msg = "INCORRECT GROUP DEFINITION - group = '$slug' ; parent = '$parent'";
                    throw new \Exception($msg);
                }
                $nodes[$slug]->addEdge($nodes[$parent]);
            }
        }
        // 3 - result
        self::$allAncestors = [];
        foreach($nodes as $slug => $node){
            self::$allAncestors[$slug] = $node->getReachableAsStrings();
        }
    }
    
    /**
        Returns an array of slugs of all the descendants of an occupation.
        @param  $groupSlug      Group slug for which descendants need to be computed
        @param  $includeSeed    Boolean indicating if $groupSlug should be also returned
    **/
    public static function getDescendants(string $groupSlug, bool $includeSeed) {
        self::computeAllAncestors();
        $res = [];
        foreach(self::$allAncestors as $current => $ancestors){
            if(in_array($groupSlug, $ancestors)){
                $res[] = $current;
            }
        }
        if($includeSeed){
            $res[] = $groupSlug;
        }
        $res = array_unique($res);
        return $res;
    }
    
    // ***********************************************************************
    //                                Export
    // ***********************************************************************
    
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
        Does not modify the group (in particular, fields 'dowload', 'n', 'members' are untouched).
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
        @param $dozip   Boolean indicating if the output should be zipped
        @return An array with 3 elements :
                - A report.
                - The name of the file where the export is stored, relative to data/output (see config.yml).
                - The number of elements in the group
        
    **/
    public function exportCsv($csvFile, $csvFields, $map=[], $fmap=[], $sort=false, $filters=[], $dozip=false) {
        
        $csv = implode(G5::CSV_SEP, $csvFields) . "\n";
        
        $emptyNew = array_fill_keys($csvFields, '');
        
        $this->computePersonMembers();
        
        if($sort !== false){
            usort($this->data['person-members'], $sort);
        }
        
        $report = '';

        $N = 0; // nb of elements in the group
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
            $filename = $csvFile . '.zip';
            $zip = new \ZipArchive();
            if ($zip->open($filename, \ZipArchive::CREATE) !== true) {
                throw new \Exception("Cannot open <$filename>");
            }
            $zip->addFromString(basename($csvFile), $csv);
            $zip->close();
        }
        else{
            $filename = $csvFile;
            file_put_contents($filename, $csv);
        }
        $report .= "Exported group to file $filename ($N lines)\n";
        return [$report, $filename, $N];
    }
    
} // end class
