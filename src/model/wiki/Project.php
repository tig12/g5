<?php
/********************************************************************************
    Handles the notion of Wiki project = set of persons.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-01-21 19:29:36+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\model\wiki;
use g5\app\Config;
use g5\model\DB5;

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
            self::STATUS_ACTIVE,
        ]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $res['id'];
    }
    
} // end class
