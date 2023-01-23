<?php
/********************************************************************************
    Recent = table containing the additions in the database
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-01-22 20:56:17+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\model\wiki;
use g5\app\Config;
use g5\model\DB5;
use g5\model\Person;

class Recent {
    
    const STATUS_ACTIVE = 'active';
    const STATUS_ARCHIVED = 'archived';
    
    /**
        Inserts an entry in table wikirecent.
        @param  $datetime   Format YYYY-MM-DD HH:MM:SS
        @return The id in database of the inserted project
    **/
    public static function add(int $idPerson, string $datetime, string $description): void {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('insert into wikirecent(
            id_person,
            dateadd,
            description
            )values(?,?,?)');
        $stmt->execute([
            $idPerson,
            $datetime,
            $description,
        ]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
} // end class
