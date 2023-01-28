<?php
/********************************************************************************
    Handles the notion of Wiki project = set of persons.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-01-21 19:29:36+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\model\wiki;
use g5\app\Config;
use g5\model\DB5;
use g5\model\Person;

class Project {
    
    const STATUS_ACTIVE = 'active';
    const STATUS_ARCHIVED = 'archived';
    
    /**
        @return Path to the directory containing the yaml files defining the projects.
    **/
    public static function rootDir(){
        return Config::$data['dirs']['wiki'] . DS . 'project';
    }
    
    /**
        Adds one wiki project in database.
        @param  $slug The slug of the project to add ; ex: french-math
        @return The id in database of the inserted project
        @throws Exception if the yaml file defining the project is not present in self::rootDir().
    **/
    public static function addOne(string $slug): int {
        $yamlfile = self::rootDir() . DS . $slug . '.yml';
        if(!is_file($yamlfile)){
            throw new \Exception("WIKI PROJECT DEFINITION FILE IS MISSING: $yamlfile");
        }
        $yaml = @yaml_parse_file($yamlfile);
        if($yaml === false){
            return "FILE DOES NOT EXIST OR IS NOT CORRECTLY FORMATTED: $yamlFile\n";
        }
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('insert into wikiproject(
            slug,
            name,
            description,
            header,
            status
            )values(?,?,?,?,?) returning id');
        $stmt->execute([
            $slug,
            $yaml['name'],
            $yaml['description'],
            json_encode($yaml['header'], JSON_FORCE_OBJECT),
            $yaml['status'],
        ]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $res['id'];
    }
    
    /**
        Adds in database a link between a person and a wiki project.
        @param  $projectSlug    Concerned project
        @param  $p              Person to add to the project
        @pre    Person $p must be present in database (then have a field id).
        @throws Exception if something goes wrong
    **/
    public static function addPersonToProject(string $projectSlug, Person $p): void {
        if(!isset($p->data['id'])){
            throw new \Exception("addPersonToProject() cannot add a person without id");
        }
        $dblink = DB5::getDbLink();
        
        // Compute project id
        $query = "select id from wikiproject where slug='$projectSlug'";
        $stmt = $dblink->prepare($query);
        $stmt->execute([]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            throw new \Exception("Project '$projectSlug' does not exist");
        }
        
        $projectId = $res['id'];
        $personId = $p->data['id'];
        
        // check if person is already associated to the project
        $query = "select * from wikiproject_person where id_person=$personId and id_project=$projectId";
        $stmt = $dblink->prepare($query);
        $stmt->execute([]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            $stmt = $dblink->prepare('insert into wikiproject_person(
                id_project,
                id_person)
                values(?,?)');
            $stmt->execute([
                $projectId,
                $personId,
            ]);
            $stmt->fetch(\PDO::FETCH_ASSOC);
        }
    }
    
} // end class
