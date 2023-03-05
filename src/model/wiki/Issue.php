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
        'person' => null,
        'mark' => '',
        'description' => '',
    ];
    
    /** Check one of the component of the name **/
    const TYPE_NAME = 'chk-name';
    
    /** Check birth day or birth time or both **/
    const TYPE_DATE = 'date';
    
    /** Check birth day**/
    const TYPE_DAY = 'day';
    
    /** Check birth time **/
    const TYPE_TIME = 'time';
    
    /** Check timezone offset **/
    const TYPE_TZO = 'tzo';
    
    /** Check birth place **/
    const TYPE_BPLACE = 'bplace';
    
    /** 
        @param  $p              Person concerned by this issue.
                                $p may not already be stored in database (with id = 0).
        @param  $mark           String identifying the issue for a given person.
                                Must be unique for a given person.
                                Free string, in general constants Issue::TYPE_* are used as mark.
        @param  $description    Description of the issue.
    **/
    public function __construct(Person $p, string $mark, string $description){
        $this->data['person'] = $p;
        $this->data['mark'] = $mark;
        $this->data['description'] = $description;
    }
    
    // ******************************************************
    /**
        Computes the slug of an issue, a string like "abadie-joseph-1873-12-15--chk-date".
    **/
    private function computeSlug(): string {
        return $this->data['person']->data['slug'] . '--' . $this->data['mark'];
    }
    
    // *********************** CRUD *******************************
    
    public function insert(): int{
        if($this->data['person']->data['id'] == 0){
            throw new \Exception("You can't insert an issue related to a person not stored in database (with id = 0)");
        }
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('insert into issue(
            slug,
            description
        )values(?,?) returning id');
        $stmt->execute([
            $this->computeSlug(),
            $this->data['description'],
        ]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->data['id'] = $res['id'];
        //
        $stmt = $dblink->prepare('insert into issue_person(id_issue,id_person) values(?,?)');
        $stmt->execute([$this->data['id'], $this->data['person']->data['id']]);
        //
        return $this->data['id'];
    }

    /** 
        @param  $wp A Wikiproject object.
    **/
    public function linkToWikiproject($wp){
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('insert into issue_wikiproject(id_issue,id_wikiproject) values(?,?)');
        $stmt->execute([$this->data['id'], $wp->data['id']]);
    }        
    
} // end class
