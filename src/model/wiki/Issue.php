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
    
    /** The structure of an issue is defined by this array **/
    public $data = [
        'id' => 0,
        'type' => '',       // one of the TYPE_* constants
        'person' => null,
        'mark' => '',
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
        @param  $type           Type of the issue ; must be a constant Issue::TYPE_*.
        @param  $mark           String identifying the issue for a given person.
                                Must be unique for a given person.
                                Free string, in general, the type of the issue is used as mark.
        @param  $description    Description of the issue.
    **/
    public function __construct(Person $p, string $type, string $mark, string $description){
        $this->data['person'] = $p;
        $this->data['mark'] = $mark;
        $this->data['type'] = $type;
        $this->data['description'] = $description;
    }
    
    /**
        Returns an object of type Issue from storage, using its slug,
        or null if doesn't exist.
        The Person object of the issue is not computed.
    **/
    public static function createFromSlug($slug): ?Issue{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('select * from issue where slug=?');
        $stmt->execute([$slug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return null;
        }
        $issue = new Issue(null, $res['type'], $res['type'], $res['description']);
        $issue->data['id'] = $res['id'];
        return $issue;
    }
    
    /**
        Resolution of an issue is currently done through its deletion.
    **/
    public static function resolveIssue(string $slug) {
        $issue = self::createFromSlug($slug);
        if(is_null($issue)){
            return; // throw exception ?
        }
        $issue->delete();
    }
    
    // *********************** Slug manipulation *******************************
    
    /**
        Computes the slug of an issue, a string like "abadie-joseph-1873-12-15--date".
    **/
    private function computeSlug(): string {
        return $this->data['person']->data['slug'] . '--' . $this->data['mark'];
    }
    
    /**
        Computes an issue slug from the person slug and the issue type.
    **/
    public static function computeSlugFromPersonAndType(string $personSlug, string $issueType): string {
        return $personSlug . '--' . $issueType;
    }
    
    // *********************** CRUD *******************************
    
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
        $stmt->execute([
            ($this->data['person'])->data['id'],
            $this->computeSlug(),
            $this->data['type'],
            $this->data['description'],
        ]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->data['id'] = $res['id'];
        return $this->data['id'];
    }

    /** 
        @param  $wp A Wikiproject object.
    **/
    public function linkToWikiproject($wp){
        if($wp->data['id'] == null || $wp->data['id'] == 0){
            throw new \Exception('Trying to insert a null wiki project $wp = ' . print_r($wp, true));
        }
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('insert into wikiproject_issue(id_issue,id_project) values(?,?)');
        $stmt->execute([$this->data['id'], $wp->data['id']]);
    }        
    
    /**
        @param  $
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
