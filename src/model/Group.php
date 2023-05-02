<?php
/******************************************************************************
    Group of persons
    Structure of $this->$data is described in Group.yml
    Fields not in Group.yml:
    - $this->data['members'] array of person ids.
    - $this->data['person-members'] array of Person objects.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
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
        Caches getAllAncestors() computation.
    **/
    private static $allAncestors = null;
    
    public $data = [];
    
    /** Boolean indicating if data['person-members'] have already been computed **/
    public $personMembersComputed;
    
    // ***********************************************************************
    //                          Constructor / factory methods
    // ***********************************************************************
    
    /** 
        Constructor not callable from other classes.
    **/
    private function __construct(){
        $this->personMembersComputed = false;
        $this->data['members'] = [];        // person ids, for table person_groop
        $this->data['person-members'] = []; // array of objects of type Person
    }
    
    /** 
        Creates an empty group, with fields initialized from Group.yml.
        Group members are not computed.
    **/
    public static function createEmpty(): Group {
        $g = new Group();
        $g->data = yaml_parse_file(__DIR__ . DS . 'Group.yml');
        return $g;
    }
    
    /** 
        Creates a group from its definition file.
        Group members are not computed.
        @param  $defFile    Path to a YAML definition file, relative to data/db/group.
        @throws Exception   If $defFile does not correspond to a valid definition file.
    **/
    public static function createFromDefinitionFile(string $defFile): Group {
        $g = Group::createEmpty();
        // Load group data from data/db/group
        $defFile = Config::$data['dirs']['ROOT'] . DS . Config::$data['dirs']['db'] . DS . 'group' . DS . $defFile;
        $yaml = yaml_parse_file($defFile);
        $g->data = array_replace_recursive($g->data, $yaml);
        return $g;
    }
    
    /**
        Creates an object of type Group from storage, using its slug,
        or null if the group doesn't exist.
        Group members are not computed.
    **/
    public static function createFromSlug($slug): ?Group {
        $g = Group::createEmpty();
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('select * from groop where slug=?');
        $stmt->execute([$slug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return null;
        }
        $g->data = array_replace_recursive($g->data, $res);
        $g->data['sources'] = json_decode($res['sources'], true);
        $g->data['parents'] = json_decode($res['parents'], true);
        $g->data['children'] = json_decode($res['children'], true);
        return $g;
    }
    
    /** 
        Creates a group from a sql query selecting members in table person.
        Group members ARE computed.
        If the sql query does not return persons, the returned group is empty, not null.
    **/
    public static function createFromSQL(string $sql): Group {
        $g = Group::createEmpty();
        $dblink = DB5::getDbLink();
        // ATTR_EMULATE_PREPARES was added for commands\db\export\skeptics
        // select * from person where partial_ids::JSONB ? 'cpara' ...
        // Without ATTR_EMULATE_PREPARES, the jsonb ? operator is understood as a prepared variable
        $dblink->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        $stmt = $dblink->prepare($sql);
        $stmt->execute([]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach($rows as $row){
            $g->data['person-members'][] = Person::row2person($row);
            $g->data['members'][] = $row['id'];
            $g->data['n']++;
        }
        $g->personMembersComputed = true;
        return $g;
    }
    
    // ***********************************************************************
    //                           Get several groups
    // ***********************************************************************
    
    /**
        Returns an array of Group objects, retrieved from database.
    **/
    public static function loadAllFromDB() {
        $dblink = DB5::getDbLink();
        $query = 'select * from groop';
        $res = [];
        foreach($dblink->query($query, \PDO::FETCH_ASSOC) as $row){
            $row['parents'] = json_decode($row['parents'], true);
            $row['children'] = json_decode($row['children'], true);
            $row['sources'] = json_decode($row['sources'], true);
            $tmp = Group::createEmpty();
            $tmp->data = $row;
            $res[] = $tmp;
        }
        return $res;
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
        Fills field $this->data['members'] with person ids from table person_groop.
        Also updates field "n".
        Does not affect database.
        @see    computePersonMembers()
    **/
    public function computeMembers(){
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('select id_person from person_groop where id_groop=' . $this->data['id']);
        $stmt->execute([]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
        $stmt = $dblink->prepare('insert into person_groop(id_person,id_groop) values(?,?)');
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
        $dblink->exec('delete from person_groop where id_groop=' . $this->data['id']);
        $stmt = $dblink->prepare('insert into person_groop(id_person,id_groop) values(?,?)');
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
        $dblink->exec('delete from person_groop where id_groop=' . $this->data['id']);
        $this->data['members'] = [];
        $this->data['n'] = 0;
        $this->update(updateMembers:false); // for field n
    }
    
    /** 
        Fills person-members with objects of type Person
        @param $force If true, members computation will be done even if it was already done.
    **/
    public function computePersonMembers(bool $force=false){
        if($force === false && $this->personMembersComputed === true){
            return;
        }
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('select * from person where id in(select id_person from person_groop where id_groop=?)');
        $stmt->execute([$this->data['id']]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->data['members'] = [];
        foreach($rows as $row){
            $this->data['person-members'][] = Person::row2person($row);
            $this->data['members'][] = $row['id'];
        }
        $this->data['n'] = count($this->data['person-members']);
        $this->personMembersComputed = true;
    }
    
    // ******************************************************
    /**
        Adds one person in a group in database.
        Static function, not related to any Group object.
        
        If the person already belongs to parent groups of $groupSlug,
        the associations between parent groups and person are deleted.
        Ex storePersonInGroup('football-player') for a person already belonging to 'sportsperson':
        the association between the person and group 'sportsperson' will be deleted
        
        WARNING: this function doesn't handle the case where the person already belongs to a child group.
        Ex storePersonInGroup('sportsperson') for a person already belonging to 'football-player'.
        
        ==== TODO ===== Update group's field 'n'
        
        @param  $personId   Id of the Person to add (its primary key).
        @param  $groupSlug  Slug of a group already stored in database.
        @throws Exception if insertion failed.
    **/
    public static function storePersonInGroup(int $personId, string $groupSlug) {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('select id from groop where slug=?');
        $stmt->execute([$groupSlug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            throw new \Exception("Group '$groupSlug' not found in database");
        }
        $groupId = $res['id'];
        //
        // Transaction to delete association with parent groups (if any) and insert in current group
        //
        self::computeAllAncestors();
        // This test is necessary because storePersonInGroup() is successively called from
        // db/fill/person(A1.yml) and (D6.yml)
        // With call from A1, self::$allAncestors is cached but d6 group doesn't exist yet
        if(!isset(self::$allAncestors[$groupSlug])){
            self::$allAncestors = null;
            self::computeAllAncestors();
        }
        $ancestors = self::$allAncestors[$groupSlug];
        $dblink->beginTransaction();
        // delete from parent groups
        $stmt_del = $dblink->prepare('delete from person_groop where id_groop=(select id from groop where slug=?) and id_person=?');
        foreach($ancestors as $ancestorSlug){
            $stmt_del->execute([$ancestorSlug, $personId]);
        }
        // insert in current group
        $stmt_ins = $dblink->prepare('insert into person_groop(id_person,id_groop) values(?,?)');
        $stmt_ins->execute([$personId, $groupId]);
        // update field 'n'
        self::update_n($dblink, $groupId);
        $dblink->commit();
    }
    
    /**
        Removes one person from a group in database.
        Does not care about ancestors(useless because if a person is in a group, it is not in the group's encestors)
        ==== TODO ===== Update group's field 'n'
        @param  $personId   Id of the Person to add (its primary key).
        @param  $groupSlug  Slug of a group already stored in database.
        @throws Exception if insertion failed.
    **/
    public static function removePersonFromGroup(int $personId, string $groupSlug) {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('select id from groop where slug=?');
        $stmt->execute([$groupSlug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            throw new \Exception("Group '$groupSlug' not found in database");
        }
        $groupId = $res['id'];
        $stmt_del = $dblink->prepare('delete from person_groop where id_groop=? and id_person=?');
        $stmt_del->execute([$groupId, $personId]);
        // update field 'n'
        self::update_n($dblink, $groupId);
    }
    
    // ******************************************************
    /**
        Fills field 'n of a group with its count(*).
    **/
    private static function update_n($dblink, $groupId) {
        $stmt = $dblink->prepare('select count(*) from person_groop where id_groop=?');
        $stmt->execute([$groupId]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        $count = $res['count'];
        $stmt = $dblink->prepare('update groop set n=? where id=?');
        $stmt->execute([$count,$groupId]);
    }
    
    // ***********************************************************************
    //                       Ancestors / descendants
    // ***********************************************************************
    
    /** 
        Uses vendor/tiglib/dag, direct acyclic graph, were edges go from child to parent - "has parent" relation
    **/
    
    
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
                    $msg = "INCORRECT GROUP DEFINITION - UNEXISTING PARENT\ngroup = '$slug' ; parent = '$parent'";
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
        Generates and stores on disk a csv file (which may be zipped) from its members (of type Person).
        Does not modify the group (in particular, fields 'download', 'n', 'members' are untouched).
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
        @param  $SEP    Separator used in the csv file.
        @return An array with 3 elements :
                - A report.
                - The name of the file where the export is stored, relative to data/output (see config.yml).
                - The number of elements in the group
        
    **/
    public function exportCsv($csvFile, $csvFields, $map=[], $fmap=[], $sort=false, $filters=[], $dozip=false, $SEP=';') {
        
        $csv = implode($SEP, $csvFields) . "\n";
        
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
                    if(is_null($data) && is_null($p->data[$pk])){
                        $msg = "Missing key $pk for person '{$p->data['slug']}'"
                             . " while importing group '{$this->data['slug']}'";
                        throw new \Exception($msg);
                    }

// bug for escalante-edouard-1934-12-03
// marked as A1-1899 in Ertel's file
// but not present in cura files
/* 
if(
    (is_null($data) && !isset($p->data[$pk]))
    || 
    (!is_null($data) && !isset($data[$pk]))
){
    if($pk == 'lerrcp'){
        echo "\np = "; print_r($p); echo "\n";
        echo "\ndata = "; print_r($data); echo "\n";
        die("\n<br>die here " . __FILE__ . ' - line ' . __LINE__ . "\n");
    }
    //echo "pk = $pk\n";
    //throw new \Exception($csvFile);
}
*/

                    $data = is_null($data) ? $p->data[$pk] : $data[$pk];
                }
                $new[$csvKey] = $data;
            }
            // fmap
            foreach($fmap as $csvKey => $function){
                $new[$csvKey] = $function($p);
            }
            $csv .= implode($SEP, $new) . "\n";
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
    
    // ***********************************************************************
    //                                  CRUD
    // ***********************************************************************
    
    /**
        Inserts a group in storage.
        Fills $this->data['id']
        @return The id of the inserted row
        @throws \Exception if trying to insert a duplicate slug
    **/
    public function insert(): int{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('insert into groop(
            slug,
            name,
            n,
            type,
            description,
            download,
            sources,
            parents,
            children
            ) values(?,?,?,?,?,?,?,?,?) returning id');
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
        //
        $this->data['id'] = $res['id'];
        //
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
        $stmt = $dblink->prepare('update groop set
            slug=?,
            name=?,
            n=?,
            type=?,
            description=?,
            download=?,
            sources=?,
            parents=?,
            children=?
            where id=?');
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
    
} // end class
