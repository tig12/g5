<?php
/******************************************************************************

    LERRCP = Laboratoire d'Étude des Relations entre Rythmes Cosmiques et Psychophysiologiques
    Class used by source management
                                   
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-07-20 07:39:16+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\gauq;

use g5\app\Config;
use g5\commands\gauq\Cura5;
use g5\model\Group;
use g5\model\Source;
use tiglib\arrays\csvAssociative;

class LERRCP {
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'gauq' . DS . 'lerrcp.yml';
    
    /** Slug of source  **/
    const SOURCE_SLUG = 'lerrcp';
    
    /** 
        Informations about the different LERRCP booklets.
        Each line contains:
            - date of publication
            - nb of pages (empty sting when unknown)
            - array of author names
            - title of the booklet (TODO check the original title)
        Source https://web.archive.org/web/20200916073026/http://cura.free.fr/gauq/902gdG.html
        For documentation purpose only.
    **/
    const LERRCP_INFOS = [
        'A1' =>  [
            '1970-04',
            '',
            ['Michel Gauquelin', 'Françoise Gauquelin'],
            '2088 sports champions',
        ],
        'A2' =>  [
            '1970-05',
            150,
            ['Michel Gauquelin', 'Françoise Gauquelin'],
            '3644 scientists and medical doctors',
        ],
        'A3' =>  [
            '1970-07',
            '',
            ['Michel Gauquelin', 'Françoise Gauquelin'],
            '3047 military men',
        ],
        'A4' =>  [
            '1970-11',
            119,
            ['Michel Gauquelin', 'Françoise Gauquelin'],
            '1473 painters and 1249 French musicians',
        ],
        'A5' =>  [
            '1970-12',
            '',
            ['Michel Gauquelin', 'Françoise Gauquelin'],
            '1409 actors and 1003 politicians',
        ],
        'A6' =>  [
            '1971-03',
            123,
            ['Michel Gauquelin', 'Françoise Gauquelin'],
            '2027 writers and journalists',
        ],
        'D6' =>  [
            '1979-09',
            '',
            ['Michel Gauquelin', 'Françoise Gauquelin'],
            '450 New famous European Sports Champions',
        ],
        'D10' => [
            '1982-01',
            '',
            ['Michel Gauquelin'],
            '1398 data of successful Americans',
        ],
        'E1' =>  [
            '1984',
            '',
            ['Michel Gauquelin'],
            '2154 French Physicians, Military Men and Executives',
        ],
        'E3' =>  [
            '1984',
            '',
            ['Michel Gauquelin'],
            '1540 New French Writers, Artists, Actors, Politicians and Journalists',
        ],
    ];
    
    // *********************** Gauquelin unique id ***********************
    
    /**
        Returns a unique Gauquelin id, like "A1-654"
        Unique id of a record among birth dates published by Gauquelin's LERRCP.
        See https://tig12.github.io/gauquelin5/gauquelin.html#gauquelin-unique-id for precise definition.
        @param $datafile    String like 'A1'
        @param $NUM         Value of field NUM of a record within $datafile
    **/
    public static function gauquelinId($datafile, $NUM){
        return "$datafile-$NUM";
    }
    
    /**
        Converts Gauquelin id to an array containing 2 elements: the datafile and the num within this datafile.
        Ex: "A1-654" returns ["A1", 654]
        @param $gqId    String like "A1-654"
    **/
    public static function explodeGauquelinId($gqId){
        return explode('-', $gqId);
    }
        
    // *********************** Source management - data files ***********************
    
    /**
        Computes slug of the source corresponding to a datafile.
        Ex: for datafile 'A6', return 'a6'
    **/
    public static function datafile2sourceSlug($datafile) {
        return strtolower($datafile);
    }
    
    /**
        Returns a Source object for one datafile of cura web site.
        @param  $datafile : string like 'A1'
    **/
    public static function getSourceOfDatafile($datafile): Source {
        $source = new Source();
        $slug = LERRCP::datafile2sourceSlug($datafile);
        $source->data['slug'] = $slug;
        $source->data['name'] = "CURA5 file $datafile";
        $source->data['type'] = 'file';
        $source->data['authors'] = ['Patrice Guinard'];
        $pageName = substr(Cura5::CURA_URLS[$datafile], strrpos(Cura5::CURA_URLS[$datafile], '/') +1);
        $source->data['description'] = "Web page containing part of Gauquelins' data published in LERRCP booklet $datafile."
            . '<br>Original URL : <a href="' . Cura5::CURA_URLS[$datafile] . '">' . $pageName . '</a>.';
        $source->data['parents'][] = LERRCP::datafile2bookletSourceSlug($datafile);
        $source->data['parents'][] = Cura5::SOURCE_SLUG;
        return $source;
    }
    
    // *********************** Source management - booklets ***********************
    
    /**
        Computes slug of the source corresponding to the LERRCP booklet of a datafile.
        Ex: for datafile 'A6', return 'a6-booklet'
    **/
    public static function datafile2bookletSourceSlug($datafile) {
        return strtolower($datafile) . '-booklet';
    }
    
    /**
        Returns a Source object corresponding to original Gauquelin booklet
        for one data file of cura web site.
        @param  $datafile : string like 'A1'
    **/
    public static function getBookletSourceOfDatafile($datafile): Source {
        $source = new Source();      
        $source->data['slug'] = LERRCP::datafile2bookletSourceSlug($datafile);
        $source->data['name'] = "LERRCP $datafile";
        $source->data['type'] = 'booklet';
        $source->data['authors'] = LERRCP::LERRCP_INFOS[$datafile][2];
        $serie = substr($datafile, 0, 1);
        $volume = substr($datafile, 1);
        $source->data['description'] = "LERRCP Serie $serie, vol $volume: "
            . LERRCP::LERRCP_INFOS[$datafile][3]
            . "\n<br>Published in " . LERRCP::LERRCP_INFOS[$datafile][0];
        if(LERRCP::LERRCP_INFOS[$datafile][1] != ''){
            $source->data['description'] .= ' (' . LERRCP::LERRCP_INFOS[$datafile][1] . ' pages)';
        }
        $source->data['parents'] = ['lerrcp'];
        return $source;
    }
    
    // *********************** Group management ***********************
    
    /**
        Computes slug of the group corresponding to a datafile.
        Ex: for datafile 'A6', return 'a6'
    **/
    public static function datafile2groupSlug($datafile) {
        return strtolower($datafile);
    }
    /**
        Returns a Group object for one file of cura web site.
        @param  $datafile : string like 'A1'
    **/
    public static function getGroupOfDatafile($datafile): Group {
        $g = Group::createEmpty(); 
        $g->data['slug'] = LERRCP::datafile2groupSlug($datafile);
        $g->data['name'] = "Gauquelin $datafile";
        $g->data['type'] = Group::TYPE_HISTORICAL;
        $g->data['description'] = "Persons published by Gauquelins' LERRCP booklet "
            . '"' . LERRCP::LERRCP_INFOS[$datafile][3] . '".';
        $g->data['sources'] = [LERRCP::datafile2bookletSourceSlug($datafile)];
        return $g;
    }
    
    // *********************** Raw files manipulation ***********************
    
    /** 
        Computes the name of the directory where raw cura5 files are stored
    **/
    public static function rawDirname(){
        return Config::$data['dirs']['raw'] . DS . 'gauq' . DS . 'lerrcp';
    }
    
    /** 
        Computes the name of a html file downloaded from cura.free.fr
        and locally stored in directory data/raw/gauq/lerrcp
        @param  $datafile : a string like 'A1'
        @return filename, a string like '902gdA1y.html' or '902gdB1.html'
    **/
    public static function rawFilename($datafile){
        return '902gd' . $datafile . (substr($datafile, 0, 1) == 'A' ? 'y' : '') . '.html';
    }
    
    /** 
        Reads a html file downloaded from cura.free.fr
        and locally stored in directory data/raw/gauq/lerrcp
        @param  $datafile : string like 'A1'
        @return The content of the file
    **/
    public static function loadRawFile($datafile){
        $rawFile = self::rawDirname() . DS . self::rawFilename($datafile);
        $tmp = @file_get_contents($rawFile);
        if(!$tmp){
            $msg = "ERROR : Unable to read file $rawFile\n"
                . "Check that config.yml indicates a correct path\n";
            throw new \Exception($msg);
        }
        return utf8_encode($tmp);
    }
    
    // *********************** Tmp files manipulation ***********************
    
    /**
        Returns the name of a tmp file, eg. data/tmp/gauq/lerrcp/A1.csv
        @param  $datafile : a string like 'A1'
    **/
    public static function tmpDirname(){
        return Config::$data['dirs']['tmp'] . DS . 'gauq' . DS . 'lerrcp';
    }
    
    /**
        Returns the name of a tmp file, eg. data/tmp/gauq/lerrcp/A1.csv
        @param  $datafile : a string like 'A1'
    **/
    public static function tmpFilename($datafile){
        return self::tmpDirname() . DS . $datafile . '.csv';
    }
    
    /**
        Loads a tmp file in a regular array
        Each element contains the person fields in an assoc. array
        @param  $datafile : a string like 'A1'
    **/
    public static function loadTmpFile($datafile){
        return csvAssociative::compute(self::tmpFilename($datafile));
    }

    /**
        Loads a tmp file in an asssociative array.
            keys = cura ids (NUM)
            values = assoc array containing the fields
        @param      $datafile : a string like 'A1'
    **/
    public static function loadTmpFile_num($datafile){
        $curaRows1 = self::loadTmpFile($datafile);
        $res = [];
        foreach($curaRows1 as $row){
            $res[$row['NUM']] = $row;
        }
        return $res;
    }
    
    // *********************** Tmp raw files manipulation ***********************
    
    /**
        Returns the name of a "tmp raw file", eg. data/tmp/gauq/lerrcp/A1-raw.csv
        (files used to keep trace of the original raw values).
        @param  $datafile : a string like 'A1'
    **/
    public static function tmpRawFilename($datafile){
        return Config::$data['dirs']['tmp'] . DS . 'gauq' . DS . 'lerrcp' . DS . $datafile . '-raw.csv';
    }
    
    /**
        Loads a "tmp raw file" in a regular array
        Each element contains the person fields in an assoc. array
        @param  $datafile : a string like 'A1'
    **/
    public static function loadTmpRawFile($datafile){
        return csvAssociative::compute(self::tmpRawFilename($datafile));
    }

} // end class
