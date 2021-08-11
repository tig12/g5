<?php
/******************************************************************************
    Arno Müller's 234 famous women
    Code common to afd3
    
    @license    GPL
    @history    2020-05-15 ~22h30+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\afd3women;

use g5\Config;
use g5\model\DB5;
use g5\model\{Source, Group};
//use tiglib\time\seconds2HHMMSS;
use tiglib\arrays\csvAssociative;

class AFD3 {
    
    /**
        Trust level for data
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = 4;
    
    /**
        Path to the yaml file containing the characteristics of the source describing file
        data/raw/muller/afd3-women/muller-afd3-women.txt
        Relative to directory data/model/source
    **/
    const LIST_SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd3-women-list.yml';

    /** Slug of source muller-afd3-women.txt **/
    const LIST_SOURCE_SLUG = 'afd3';
    
    /**
        Path to the yaml file containing the characteristics of Müller's booklet AFD3.
        Relative to directory data/model/source
    **/
    const BOOKLET_SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd3-women-booklet.yml';
    
    /** Slug of source Astro-Forschungs-Daten vol 3 **/
    const BOOKLET_SOURCE_SLUG = 'afd3-booklet';
    
    /** Slug of the group in db **/
    const GROUP_SLUG = 'muller-afd3-women';

    /**
        Limit of fields in the raw fields ; example for beginning of first line:
        001 ADAM, Juliette *LAMBER                      04.10.1836 23.00       LMT  F   Verberie (Oise)
        |   |                                           |                      |
        0   4                                           48                     59
    **/
    const RAW_LIMITS = [
        0,
        4,
        48,
        59,
        65,
        71,
        76,
        80,
        112,
        120,
        129,
        135,
        144,
        147,
        149,
    ];
    
    /** Names of the columns of raw file **/
    const RAW_FIELDS = [
        'MUID',
        'NAME',
        'DATE',
        'TIME',
        'TZO',
        'TIMOD', // time mode
        'CY',
        'PLACE',
        'LAT',
        'LG',
        'OCCU',
        'BOOKS',
        'SOURCE',
        'GQ',
    ];
    
    /** Names of the columns of tmp csv file **/
    const TMP_FIELDS = [
        'MUID',
        'FNAME',
        'GNAME',
        'ONAME1', // other component of the name 1 - part between parentheses
        'ONAME2', // other component of the name 2 - for names with 2 comas
        'ONAME3', // other component of the name 3 - part after the *
        'DATE',
        'TZO',
        'TIMOD', // time mode
        'PLACE',
        'CY',
        'C1',
        'C2',
        'LAT',
        'LG',
        'OCCU',
        'BOOKS',
        'SOURCE',
        'GQ',
    ];
    
    /** 
        Match between Müller and Cura ids.
        Array built by look::look_gauquelin()
        Used by tmp2db::execute()
    **/
    const MU_GQ = [
        '001' => 'A6-6', // ADAM Juliette *LAMBER
        '007' => 'A6-46', // AUDOUX Marguerite
        '011' => 'A1-129', // AURIOL Jacqueline *DOUET
        '015' => 'A5-60', // BARDOT Brigitte
        '018' => 'A6-72', // BEAUVOIR Simone DE
        '030' => 'A4-137', // BONHEUR Rosa
        '031' => 'E3-189', // BOULANGER Nadia Juliette
        '035' => 'E3-228', // BRUCHOLLERIE Monique DE LA
        '039' => 'A5-152', // CAROL Martine
        '043' => 'A5-165', // CHARRAT Janine
        '046' => 'A6-210', // SIDONIE Gabrielle
        '049' => 'E3-408', // CRESPIN Regine
        '050' => 'A5-215', // DARRIEUX Daniele
        '052' => 'D10-308', // DAY Doris
        '053' => 'A6-239', // DELARUE-MARDRUS Lucie *DELARUE
        '058' => 'A5-1084', // DORSCH Rathe
        '062' => 'A5-879', // DUSE Eleonora
        '071' => 'A5-327', // FEUILLERE Edwige *CUNATI
        '082' => 'A6-1098', // GEVERS Marie
        '088' => 'A5-390', // GRECO Juliette
        '094' => 'A5-1114', // HAAGEN Margarete
        '109' => 'E3-834', // JOLIOT-CURIE Irene *CURIE
        '117' => 'A5-1157', // KNEF Hildegard
        '128' => 'A6-492', // LENERU Marie
        '131' => 'A5-1182', // LEUWERIK Ruth
        '135' => 'A2-1974', // LINDEN Maria GFN VON
        '151' => 'D10-853', // MCCARTHY Mary Therese
        '155' => 'A5-552', // BOURGEOIS Joanne-Marie
        '160' => 'E3-1107', // MOREAU Jeanne
        '161' => 'A5-568', // MORGAN Michele
        '162' => 'A4-793', // MORISOT Berthe
        '170' => 'A6-600', // NOAILLES Anne-Elisabeth
        '171' => 'A6-601', // NOEL Marie
        '174' => 'A5-628', // PIAF Edith
        '177' => 'A6-668', // RACHILDE *EYMERY Marguerite Vallette
        '183' => 'A5-690', // ROSAY Francoise
        '187' => 'A6-716', // SAGAN Francoise
        '189' => 'A6-719', // SAINTE-SOLINE Claire
        '210' => 'E3-1439', // TAILLEFERRE Germaine
        '217' => 'A4-1092', // VALADON Suzanne
        '218' => 'D10-1300', // VAUGBAN Sarah Lois
        '221' => 'A5-1289', // WALDOFF Claire
        '232' => 'A6-810', // YOURCENAR Marguerite
    ];
    
    /** 
        Associations Müller's Berufsgruppe / Tätigkeitsfeld => g5 occupation code
        Partly built by look::look_occus().
        Note: sometimes doesn't follow Müller, after checking on wikipedia.
        X means useless because handled by tweaks file.
    **/
    const OCCUS = [
        'AR 01' => 'fictional-writer', // 85 persons
        'AR 02' => 'factual-writer', // 12 persons
        'AR 03' => 'actor', // 43 persons
        'AR 04' => 'composer', // 1 persons
        'AR 06' => 'singer', // 21 persons
        'AR 07' => 'musician', // 3 persons
        'AR 08' => 'X', // 11 persons - more precise infos in tweaks file
        'SC 01' => 'mathematician', // 1 persons
        'SC 02' => 'X', // 1 persons - Irène Joliot-Curie - more precise infos in tweaks file
        'SC 03' => 'X', // 2 persons - more precise infos in tweaks file
        'SC 04' => 'physician', // 2 persons
        'SC 05' => 'social-scientist', // 8 persons
        'SC 06' => 'historian-of-science', // 1 persons
        'SC 07' => 'romanist', // 1 persons
        'WA 02' => 'aircraft-pilot', // 2 persons
        'WA 04' => 'politician', // 7 persons
        'WA 05' => 'religious-leader', // 2 persons
        'WA 06' => 'monarch', // 10 persons
        'WA 08' => 'revolutionary', // 2 persons
        'WA 09' => 'X', // 4 persons - more precise infos in tweaks file
        'WA 10' => 'suffragette', // 7 persons
        'WA 12' => 'partner-of-celebrity', // 8 persons
    ];
    
    // *********************** Source management ***********************
    
    /** Returns a Source object for raw file. **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['model'] . DS . self::SOURCE_DEFINITION);
    }
    
    // *********************** Group management ***********************
    
    /**
        Returns a Group object for Muller234.
    **/
    public static function getGroup(): Group {
        $g = new Group();
        $g->data['slug'] = self::GROUP_SLUG;
        $g->data['name'] = "Müller 234 famous women";
        $g->data['type'] = Group::TYPE_HISTORICAL;
        $g->data['description'] = "234 famous women, gathered by Arno Müller";
        $g->data['sources'][] = self::LIST_SOURCE_SLUG;
        return $g;
    }
    
    // *********************** Raw files manipulation ***********************
    
    /**
        @return Path to the raw file 5muller_writers.csv coming from newalch
    **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'muller', 'afd3-women', 'muller-afd3-women.txt']);
    }
    
    /** Loads 5muller_writers.csv in a regular array **/
    public static function loadRawFile(){
        return file(self::rawFilename(), FILE_IGNORE_NEW_LINES);
    }                                                                                              
                                                                                         
    // *********************** Tmp file manipulation ***********************
    
    /**
        @return Path to the csv file stored in data/tmp/newalch/
    **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'muller', 'afd3-women', 'muller-afd3-women.csv']);
    }
    
    /**
        Loads the tmp file in a regular array
        @return Regular array ; each element is an assoc array containing the fields
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmpFilename());
    }                                                                                              
    
    /**
        Loads the file temporary file in an asssociative array ; keys = MUID
    **/
    public static function loadTmpFile_muid(){
        $rows1 = self::loadTmpFile();
        $res = [];              
        foreach($rows1 as $row){
            $res[$row['MUID']] = $row;
        }
        return $res;
    }
    
    // *********************** Tmp raw files manipulation ***********************
    
    /**
        Returns the name of the "tmp raw file", eg. data/tmp/newalch/1083MED-raw.csv
        (file used to keep trace of the original raw values).
    **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'muller', 'afd3-women', 'muller-afd3-women-raw.csv']);
    }
    
    /** Loads the "tmp raw file" in a regular array **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }
    
} // end class
