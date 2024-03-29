<?php
/******************************************************************************
    
    Code relative to birth certificazte management
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-12-27 04:00:11+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model\wiki;

use g5\model\Person;
use g5\model\Trust;
use g5\model\Occupation;
use g5\model\wiki\Wiki;
use g5\model\wiki\WikiPerson;
use g5\model\DB5;
use g5\G5;

class BC {
    
    /** Name of a file containing the description of a birth certificate **/
    const TEMPLATE_FILENAME = 'BC.yml';
    
    /** Key used in person's field $data['acts'] to store a BC **/
    const PERSON_ACT_KEY = 'birth';
    
    /**
        Variable to cache call to Occupation::getAllSlugNames()
        (useful in command db init wiki)
    **/
    private static $occupationSlugs = null;
    
    /**
        @return Path to the template file BC.yml - src/model/wiki/templates/BC.yml.
    **/
    public static function templateFilePath(){
        return G5::ROOT_DIR . DS . implode(DS, ['model', 'wiki', 'templates', BC::TEMPLATE_FILENAME]);
    }

    /**
        @return Path to the directory containing birth certificates - by default data/wiki/person.
    **/
    public static function rootDir(){
        return WikiPerson::rootDir();
    }

    /**
        Returns the path of the directory corresponding to a slug.
        Ex: data/wiki/person/1811/10/25/galois-evariste-1811-10-25
        @param  $personSlug The slug of the person to add ; ex: galois-evariste-1811-10-25
        @throws Transmits exception thrown by Wiki::slug2dir()
    **/
    public static function dirPath(string $personSlug) {
        return BC::rootDir() . DS . Wiki::slug2dir($personSlug);
    }

    /**
        Returns the path of a BC.yml file corresponding to a slug.
        Ex: data/wiki/person/1811/10/25/galois-evariste-1811-10-25/BC.yml
        @param  $personSlug The slug of the person to add ; ex: galois-evariste-1811-10-25
        @throws Transmits exception thrown by BC::filePath()
    **/
    public static function filePath(string $personSlug) {
        return BC::dirPath($personSlug) . DS . BC::TEMPLATE_FILENAME;
    }
    
    /**
        Creates a BC from a file BC.yml
        @throws Exception if $yamlFile doesn't exist.
    **/
    public static function createFromYamlFile($yamlFile) {
        $BC = yaml_parse_file($yamlFile);
        if($BC === false){
            throw new \Exception("ERROR: unexisting YAML file: $yamlFile");
        }
        $BC['slug'] = basename(dirname($yamlFile));
        return $BC;
    }

    private static function computeOccupationSlugs(){
        if(is_null(self::$occupationSlugs)){
            self::$occupationSlugs = array_keys(Occupation::getAllSlugNames());
        }
    }
    
    /**
        Checks if a BC.yml file contains valid informations
        TODO implement
        @param  $yaml   Array containing information stored in a file BC.yml
        @return         Empty string if ok, error message if problems.
        https://github.com/rjbs/Rx
        https://rjbs.manxome.org/rx/
        https://github.com/romaricdrigon/MetaYaml
    **/
    public static function validate(array $BC): string {
        $msg = '';
        if(isset($BC['extras']['occus'])){
            self::computeOccupationSlugs();
            foreach($BC['extras']['occus'] as $occu){
                if(!in_array($occu, self::$occupationSlugs)){
                    $msg .= "- Occupation '$occu' is not present in database\n";
                }
            }
        }
        // TODO implement the rest...
        return $msg;
    }
    
    /**
        Adds information coming from a file BC.yml to a person.
        Modifies a person, but does not modify anything in database.
        - Fills a person's field $data['acts']['birth'] with the content of a file BC.yml
        - Fills other person's fields from the fields 'transcription' and 'extras' of the act.
        - If, after adding act information, the person doesn't contain $data['name']['given'] and/or $data['name']['family'],
          default values are computed from official name.
        - If BC field 'extras' doesn't contain a field 'trust', field trust of the person is set to Trust::BC
        - field $BC['extras']['occus'] is not handled, must be done by the calling code
        @param  $BC     Associative array containing the information of a file BC.yml
                        $BC is passed by value ; modified inside the function but not modified in the calling code
    **/
    public static function addToPerson(Person $p, array $BC): void {
        //
        // Transfer in $p->data the informations present in the act
        // act is considered more reliable than other sources, so replace existing data.
        // With one exception: if the person has a birth time and the BC has no birth time.
        // In this case, the bith time is not overriden by the BC
        // This exception comes from birth in Paris prior to 1860 : BCs were destroyed and the original acts were lost.
        // but Gauquelin still gives birth hour (the way he obtained them is not known).
        //
        $bdate_orig = $p->data['birth']['date'];
        $bdate_new = '';
        if(isset($BC['transcription']['birth']['date'])){
            $bdate_new = $BC['transcription']['birth']['date'];
        }
        else if(isset($BC['extras']['birth']['date'])){
            // normally, this should never execute as birth date should be in $BC['transcription']
            $bdate_new = $BC['extras']['birth']['date'];
        }
        // occus not handled here because command wiki bc add needs to handle it when updating a person:
        // - to take care of addition of the person in professional groups.
        // - Person::addOccus() must be called instead of array_replace_recursive() to handle duplicates and subgroups.
        unset($BC['extras']['occus']);
        //
        if(isset($p->data, $BC['transcription'])){
            $p->data = array_replace_recursive($p->data, $BC['transcription']);
        }
        if(isset($BC['extras'])){
            $p->data = array_replace_recursive($p->data, $BC['extras']);
        }
        //
        $p->data['slug'] = $BC['slug'];
        if(strlen($bdate_new) == 10 && strlen($bdate_orig) > 10){
            $p->data['birth']['date'] = $bdate_orig;
        }
        //
        $p->data['acts'][BC::PERSON_ACT_KEY] = $BC;
        //
        // Name
        //
        $nameArray = [];
        if(isset($BC['transcription']['name'])){
            $nameArray = $BC['transcription']['name'];
        }
        if(isset($BC['extras']['name'])){
            $nameArray = array_merge_recursive($nameArray, $BC['extras']['name']);
        }
        $p->computeCommonName($nameArray);
        //
        // Trust
        //
        // For BCs, set trust to BC unless specified in extras
        if(isset($BC['extras']['trust']) && $BC['extras']['trust'] != ''){
            $p->data['trust'] = $BC['extras']['trust'];
        }
        else {
            $p->data['trust'] = Trust::BC;
        }
    }
    
} // end class