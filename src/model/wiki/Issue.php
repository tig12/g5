<?php
/******************************************************************************

    An issue is a problem identified on a person.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-03-04 04:55:45+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model\wiki;

use g5\model\DB5;
use g5\model\Person;

class Issue {
    
    /**
        The structure of an issue is defined by this array
    **/
    public $data = [
        'id' => 0,
        'slug' => '',       // string like "abadie-joseph-1873-12-15--date"
        'type' => '',       // in general, one of the TYPE_* constants
        'person' => null,
        'description' => '',
    ];
    
    /** Check one of the component of the name **/
    const TYPE_NAME = 'name';
    
    /** Check nobiliary particle **/
    const TYPE_NOB = 'nob';
    
    /** Check birth day or birth time or both **/
    const TYPE_DATE = 'date';
    
    /** Check field DATE-UT **/
    const TYPE_DATE_UT = 'date-ut';
    
    /** Check timezone offset **/
    const TYPE_TZO = 'tzo';
    
    /** Check birth place **/
    const TYPE_BPLACE = 'bplace';
    
    // *********************** Instance methods *******************************
    
    /** 
        Creates a new issue, not already stored in database. 
        @param  $p              Person concerned by this issue.
                                $p may not already be stored in database (with id = 0).
        @param  $type           Type of the issue
                                In general, $type is a constant Issue::TYPE_*.
                                But can be a free string if needed.
        @param  $description    Description of the issue.
    **/
    public function __construct(Person $p, string $type, string $description=''){
        $this->data['person'] = $p;
        $this->data['type'] = $type;
        $this->data['description'] = $description;
        if(isset($p->data['slug'])){
            $this->data['slug'] = Issue::computeSlugFromPersonSlugAndType($p->data['slug'], $type);
        }
    }
    
    /**
        Returns an object of type Issue from database, using its slug, or null if doesn't exist.
        WARNING: The Person object of the issue is an empty person.
    **/
    public static function createFromSlug(string $issueSlug): ?Issue{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('select * from issue where slug=?');
        $stmt->execute([$issueSlug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return null;
        }
        $p = new Person();
        $issue = new Issue($p, $res['type'], $res['description']);
        $issue->data['id'] = $res['id'];
        $issue->data['slug'] = $issueSlug;
        return $issue;
    }
    
    /**
        Resolves an issue stored in database.
    **/
    public static function resolvePersonIssue(Person $p, string $issueType): void {
        $issueSlug = Issue::computeSlugFromPersonSlugAndType($p-data['slug'], $issueType);
        $issue = Issue::createFromSlug($issueSlug);
        $issue->resolve();
    }
    
    /**
        Computes an issue slug from the person slug and the issue type.
    **/
    private static function computeSlugFromPersonSlugAndType(string $personSlug, string $issueType): string {
        return $personSlug . '--' . $issueType;
    }
    
    // ***********************************************************************
    //                                  INSTANCE
    // ***********************************************************************
    
    /**
        Resolution of an issue is currently done through its deletion.
    **/
    public function resolve() {
        $this->delete();
    }
    
    // *********************** CRUD *******************************
    
    /** 
        Returns the id of the inserted person,
        or -1 if the insertion couldn't be done.
    **/
    public function insert(): int {
        if($this->data['person']->data['id'] == 0){
            throw new \Exception("You can't insert an issue related to a person not stored in database (with id = 0)");
        }
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('insert into issue(
            id_person,
            slug,
            type,
            description
        )values(?,?,?,?) returning id');
        // Here, try / catch to handle issues on persons belonging to several groups during the construction of the initial database.
        // Their issue would be inserted twice with the same slug, violating the unique constraint on table issue.
        // ex: sebert-hippolyte-1839-01-30 belongs to two G55 groups
        try{
            $stmt->execute([
                ($this->data['person'])->data['id'],
                $this->data['slug'],
                $this->data['type'],
                $this->data['description'],
            ]);
            $res = $stmt->fetch(\PDO::FETCH_ASSOC);
            $this->data['id'] = $res['id'];
            return $this->data['id'];
        }
        catch(\Exception $e){
            return -1;
        }
    }

    /** 
        @param  $wp A Wikiproject object.
    **/
    public function linkToWikiproject($wp){
        if($wp->data['id'] == null || $wp->data['id'] == 0){
            throw new \Exception('Trying to link an issue to a null wiki project $wp = ' . print_r($wp, true));
        }
        $dblink = DB5::getDbLink();
        // when an issue is solved, it is removed from all its projects
        $stmt = $dblink->prepare('insert into wikiproject_issue(id_issue,id_project) values(?,?)');
        $stmt->execute([$this->data['id'], $wp->data['id']]);
    }        
    
    /**
    **/
    public function delete() {
        $dblink = DB5::getDbLink();
        //
        $stmt = $dblink->prepare('delete from wikiproject_issue where id_issue=?');
        $stmt->execute([$this->data['id']]);
        //
        $stmt = $dblink->prepare('delete from issue where id=?');
        $stmt->execute([$this->data['id']]);
    }
    
} // end class
