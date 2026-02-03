<?php
/******************************************************************************
    Arno Müller's 234 famous women
    Code common to afd3
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-05-15 ~22h30+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\m3women;

use g5\app\Config;
use g5\model\Source;
use g5\model\Group;
use tiglib\arrays\csvAssociative;

class M3women {
    
    /**
        Path to the yaml file containing the characteristics of the source describing file
        data/raw/muller/3-women/muller3-234-women.txt
        Relative to directory data/db/source
    **/
    const LIST_SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd3-women-list.yml';

    /** Slug of source muller3-234-women.txt **/
    const LIST_SOURCE_SLUG = 'afd3';
    
    /**
        Path to the yaml file containing the characteristics of Müller's booklet 3 famous women.
        Relative to directory data/db/source
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
        Array built executing look::look_gauquelin()
        Used by tmp2db::execute()
        Format: Müller id => lerrcp id

    **/
    const GQ_MATCH = [
        '1'   => 'A6-6', // ADAM Juliette *LAMBER
        '7'   => 'A6-46', // AUDOUX Marguerite
        '11'  => 'A1-129', // AURIOL Jacqueline *DOUET
        '15'  => 'A5-60', // BARDOT Brigitte
        '18'  => 'A6-72', // BEAUVOIR Simone DE
        '30'  => 'A4-137', // BONHEUR Rosa
        '31'  => 'E3-189', // BOULANGER Nadia Juliette
        '35'  => 'E3-228', // BRUCHOLLERIE Monique DE LA
        '39'  => 'A5-152', // CAROL Martine
        '43'  => 'A5-165', // CHARRAT Janine
        '46'  => 'A6-210', // SIDONIE Gabrielle
        '49'  => 'E3-408', // CRESPIN Regine
        '50'  => 'A5-215', // DARRIEUX Daniele
        '52'  => 'D10-308', // DAY Doris
        '53'  => 'A6-239', // DELARUE-MARDRUS Lucie *DELARUE
        '58'  => 'A5-1084', // DORSCH Rathe
        '62'  => 'A5-879', // DUSE Eleonora
        '71'  => 'A5-327', // FEUILLERE Edwige *CUNATI
        '82'  => 'A6-1098', // GEVERS Marie
        '88'  => 'A5-390', // GRECO Juliette
        '94'  => 'A5-1114', // HAAGEN Margarete
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
   
    // Cases matching one Gauquelin date but different person
    // filled by hand from previous executions
    // Contains Müller ids
    const GQ_NOMATCH = [
        '024', // Soubirous Bernadette
        '091', // Yvette Guilbert
        '124', // Laurencin Marie
        '154', // Michel Louise
        '169', // Nin Anais
        '181', // Rochefort Christiane
        '225', // Weil Simone
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
        
        'WA 02' => 'aircraft-pilot', // 2 persons
        'WA 04' => 'politician', // 7 persons
        'WA 05' => 'religious-leader', // 2 persons
        'WA 06' => 'monarch', // 10 persons
        'WA 08' => 'revolutionary', // 2 persons
        'WA 09' => 'X', // 4 persons - more precise infos in tweaks file
        'WA 10' => 'suffragette', // 7 persons
        'WA 12' => 'partner-of-celebrity', // 8 persons
        
        'SC 01' => 'mathematician', // 1 persons
        'SC 02' => 'X', // 1 persons - Irène Joliot-Curie - more precise infos in tweaks file
        'SC 03' => 'X', // 2 persons - more precise infos in tweaks file
        'SC 04' => 'physician', // 2 persons
        'SC 05' => 'social-scientist', // 8 persons
        'SC 06' => 'historian-of-science', // 1 persons
        'SC 07' => 'romanist', // 1 persons
    ];
    
    /** Conversion to ISO 3166. **/
    public const COUNTRIES = [
        'A'   => 'AT', // Austria
        'B'   => 'BE', // Belgium
        'CH'  => 'CH', // Switzerland
        'D'   => 'DE', // Germany
        'DK'  => 'DK', // Denmark
        'DOP' => 'PL', // Former German regions, now Polish
        'F'   => 'FR', // France
        'GB'  => 'GB', // Great Britain
        'I'   => 'IT', // Italy
        'NL'  => 'NL', // Netherlands
        'S'   => 'SE', // Sweden
        'USA' => 'US', // United States of America
    ];
    
    /** Admin code level 1. **/
    public const C1 = [
        'Baselland'         => 'BL',
        'Basel-Stadt'       => 'BS',
        'Bern'              => 'BE',
        'Ca.'               => 'CA',
        'Emmental, Bern'    => 'BE',
        'Graubünden'        => 'GR',
        'Ill.'              => 'IL',
        'Luzern'            => 'LU',
        'N.H.'              => 'NH',
        'N.J.'              => 'NJ',
        'Minn.'             => 'MN',
        'Nevenburg'         => 'NE',
        'Ohio'              => 'OH',
        'Pa.'               => 'PA',
        'St. Gallen'        => 'SG',
        'Waadt'             => 'VD',
        'Wash.'             => 'WA',
    ];
    
    /**
        Admin code level 2.
        Match not done for AT, DE
    **/
    public const C2 = [
        'Ancona'            => 'AN',
        'Ancona, Rom'       => 'AN',
        'Antwerpen'         => 'VAN',
        'Bologna'           => 'BO',
        'Briissel'          => 'BRU',
        'Calvados'          => '14',
        'Cher'              => '18',
        'Deux-Sévres'       => '79',
        //'Donan'             => '',
        'Dordogne'          => '19',
        'Dresden'           => '',
        //'Elster, Merseburg' => '',
        //'Erzgebirge'        => '',
        //'Fehrbellin, Brandenbg.' => '',
        //'Harz'              => '',
        //'Icking, Oberb.'    => '',
        //'Innsbruck, Tirol'  => '',
        //'Karnten'           => '',
        //'Lavanttal'         => '',
        //'Liitzen'           => '',
        'Lot'               => '46',
        //'Meifen, Sachsen'   => '',
        //'Oder'              => '',
        'Oise'              => '60',
        //'Ostpriegnitz'      => '',
        'Paris'             => '75',
        'Pavia, Lombardei'  => 'PV',
        //'Pegau, Sachsen'    => '',
        //'Rigen'             => '',
        //'Rochlitz, Sachsen' => '',
        'Rom'               => 'RM',
        //'Sachsen'           => '',
        'Sardinien'         => 'NU', // Nuoro
        'Seine, Paris'      => '75',
        //'Steiermark'        => '',
        //'Thüringen'         => '',
        //'Tirol'             => '',
        'Turin'             => 'TO',
        'Vendée'            => '85',
        'Yonne'             => '89',
    ];
    
    // *********************** Source management ***********************
    
    /** Returns a Source object for raw file. **/
// not used - remove after testing a full build of the database
    // public static function getSource(): Source {
        // return Source::getSource(Config::$data['dirs']['db'] . DS . self::SOURCE_DEFINITION);
    // }
    
    // *********************** Group management ***********************
    
    /**
        Returns a Group object for Muller234.
    **/
    public static function getGroup(): Group {
        $g = Group::createEmpty();
        $g->data['slug'] = self::GROUP_SLUG;
        $g->data['name'] = "Müller 234 famous women";
        $g->data['type'] = Group::TYPE_HISTORICAL;
        $g->data['description'] = "234 famous women, gathered by Arno Müller and Edith Lührs";
        $g->data['sources'][] = self::LIST_SOURCE_SLUG;
        return $g;
    }
    
    // *********************** Raw files manipulation ***********************
    
    /**
        @return Path to the raw file, built from scans.
    **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'muller', '3-women', 'muller3-234-women.txt']);
    }
    
    /** Loads 5muller_writers.csv in a regular array **/
    public static function loadRawFile(){
        return file(self::rawFilename(), FILE_IGNORE_NEW_LINES);
    }                                                                                              
                                                                                         
    // *********************** Tmp file manipulation ***********************
    
    /**
        @return Path to the csv file stored in data/tmp/muller/3-women
    **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'muller', '3-women', 'muller3-234-women.csv']);
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
        Returns the name of the "tmp raw file", data/tmp/muller/3-women/muller3-234-women-raw.csv
        (file used to keep trace of the original raw values).
    **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'muller', '3-women', 'muller3-234-women-raw.csv']);
    }
    
    /** Loads the "tmp raw file" in a regular array **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }
    
} // end class
