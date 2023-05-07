<?php
/********************************************************************************

    WARNING Code not used yet

    Handles the notion of "Wiki person" = person built using a file src/model/wiki/templates/person.yml.
    Not a real entity, is converted to a normal person of src/model/Person.php
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-05-07 14:03:07+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\model\wiki;

use g5\G5;
use g5\model\wiki\Wiki;

class WikiPerson {
    
    /** Name of a file containing the description of a person **/
    const TEMPLATE_FILENAME = 'person.yml';
    
    /**
        @return Path to the template file BC.yml - src/model/wiki/templates/person.yml.
    **/
    public static function templateFilePath(){
        return G5::ROOT_DIR . DS . implode(DS, ['model', 'wiki', 'templates', WikiPerson::TEMPLATE_FILENAME]);
    }

    /**
        @return Path to the directory containing persons - by default data/wiki/person.
    **/
    public static function rootDir(){
        return Wiki::rootDir() . DS . 'person';
    }

    /**
        Returns the path of the directory corresponding to a slug.
        Ex: data/wiki/person/1928/03/28/grothendieck-alexandre-1928-03-28
        @param  $personSlug The slug of the person to add ; ex: grothendieck-alexandre-1928-03-28
    **/
    public static function dirPath(string $personSlug) {
        return WikiPerson::rootDir() . DS . Wiki::slug2dir($personSlug);
    }

    /**
        Returns the path of a BC.yml file corresponding to a slug.
        Ex: data/wiki/person/1928/03/28/grothendieck-alexandre-1928-03-28/BC.yml
        @param  $personSlug The slug of the person to add ; ex: grothendieck-alexandre-1928-03-28
    **/
    public static function filePath(string $personSlug) {
        return WikiPerson::dirPath($personSlug) . DS . WikiPerson::TEMPLATE_FILENAME;
    }
    
    /**
        Creates a BC from a file person.yml
    **/
    public static function createFromYamlFile($yamlFile) {
        $person = yaml_parse_file($yamlFile);
        $person['slug'] = basename(dirname($yamlFile));
        return $person;
    }

} // end class
