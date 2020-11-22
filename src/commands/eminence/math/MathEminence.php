<?php
/******************************************************************************
    
    @license    GPL
    @history    2020-11-22 09:42:25+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\eminence\math;

// use g5\Config;
// use g5\model\DB5;
// use g5\model\{Source, SourceI, Group};
// use tiglib\time\seconds2HHMMSS;
// use tiglib\arrays\csvAssociative;

class MathEminence {
    
    // TRUST_LEVEL not defined, using value of class Newalch

    
// ==================== WIP ====================
// Following code is useless
// Kept to copy / paste in pdd.php
    
    /** Names of the columns of raw file 5muller_writers.csv **/
    const RAW_FIELDS = [
        'NAME',
        'YEAR',
        'MONTH',
        'DAY',
        'HOUR',
        'MIN',
        'TZO',
        'PLACE',
        'LAT',
        'LG',
    ];
    
    /** Names of the columns of raw file data/tmp/newalch/muller-402-it-writers.csv **/
    const TMP_FIELDS = [
        'MUID',
        'GQID',
        'FNAME',
        'GNAME',
        'SEX',
        'DATE',
        'TZO',
        'LMT',
        'PLACE',
        'CY',
        'C2',
        'LG',
        'LAT',
        'OCCU',
    ];
    
    // *********************** Group management ***********************
    
    /**
        Returns a Group object for Muller402.
    **/
    public static function getGroup(): Group {
        $g = new Group();
        $g->data['slug'] = self::GROUP_SLUG;
        $g->data['name'] = "Müller 402 Italian writers";
        $g->data['description'] = "402 Italian writers, gathered by Arno Müller";
        $g->data['id'] = $g->insert();
        return $g;
    }
    
    // *********************** Tmp file manipulation ***********************
    
    /**
        @return Path to the csv file stored in data/tmp/newalch/
    **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'newalch', 'muller-402-it-writers.csv']);
    }
    
    /**
        Loads the tmp file in a regular array
        @return Regular array ; each element is an assoc array containing the fields
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmpFilename());
    }                                                                                              
    
    /**
        Loads the tmp file in an asssociative array.
            keys = Müller ids (MUID)
            values = assoc array containing the fields
    **/
    public static function loadTmpFile_id(){
        $rows1 = csvAssociative::compute(self::tmpFilename());
        $res = [];
        foreach($rows1 as $row){
            $res[$row['MUID']] = $row;
        }
        return $res;
    }                                                                                              
    
    // *********************** Tmp raw file manipulation ***********************
    
    /**
        Returns the name of a "tmp raw file" : data/tmp/newalch/muller-402-it-writers-raw.csv
        (files used to keep trace of the original raw values).
    **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'newalch', 'muller-402-it-writers-raw.csv']);
    }
    
    /**
        Loads a "tmp raw file" in a regular array
        Each element contains the person fields in an assoc. array
    **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }

}// end class
