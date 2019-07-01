<?php
/********************************************************************************
    Generation of the groups published in Gauquelin's book in 1955
    Input data are the files contained in 3-g55-edited
    Outputs files in two directories : 9-g55-original and 9-1955-corrected
    
    @license    GPL
    @history    2017-05-08 23:39:19+02:00, Thierry Graff : creation
    @history    2019-04-08 15:24:04+02:00, Thierry Graff : Start generation of 2 versions : original and corrected
********************************************************************************/
namespace g5\transform\g55;

use g5\Config;
use g5\transform\cura\Cura;
use tiglib\arrays\csvAssociative;

class G55{
    
    // grrrr, libreoffice transformed ; in ,
    // didn't find this fucking setting
    const CSV_SEP_LIBREOFFICE = ',';
    
    /**
        1955 groups ; format : group code => [name, serie]
        serie is 'NONE' for groups that can't be found in cura data
    **/
    const GROUPS = [
        '576MED' => ["576 membres associés et correspondants de l'académie de médecine", 'A2'],
        '508MED' => ['508 autres médecins notables', 'A2'],
        '570SPO' => ['570 sportifs', 'A1'],
        '676MIL' => ['676 militaires', 'A3'],
        '906PEI' => ['906 peintres', 'A4'],
        '361PEI' => ['361 peintres mineurs', 'NONE'],
        '500ACT' => ['500 acteurs', 'A5'],
        '494DEP' => ['494 députés', 'A5'],
        '349SCI' => ["349 membres, associés et correspondants de l'académie des sciences", 'A2'],
        '884PRE' => ['884 prêtres', 'NONE'],
    ];

    /** 
        Possible values for 'datafile' parameter when calling run-g5.php
    **/
    const DATAFILES_POSSIBLES = [
        'all',
        '576MED',
        '508MED',
        '570SPO',
        '676MIL',
        '906PEI',
        '500ACT',
        '494DEP',
        '349SCI',
    ];
    
    /**  Matching between place names used in the edited files and geonames.org corresponding names **/
    const GEONAMES_PLACES = [
        'Alger' => 'Algiers',
    ];
    
    
    // ******************************************************
    /**
        Auxiliary function
        @param      $g55Group   String identifying a G55 group, like "570SPO"
        @return     Regular array containing the edited file in 3-g55-edited/
    **/
    public static function loadG55Edited($g55Group){
        return csvAssociative::compute(Config::$data['dirs']['3-g55-edited'] . DS . $g55Group . '.csv', G55::CSV_SEP_LIBREOFFICE);
    }

        
    // ******************************************************
    /**
        Auxiliary function
        @param      $g55Group   String identifying a G55 group, like "570SPO"
        @return Array with 3 elements :
                - A string identifying the cura file correspônding to the G55 group (like 'A1')
                - An assoc. array containing the G55 data ; keys = cura ids (NUM)
                - An assoc. array containing the cura data ; keys = cura ids (NUM)
    **/
    public static function prepare($g55Group){
        $g55Rows1 = self::loadG55Edited($g55Group);
        
        // find the corresponding cura file
        // For a given G55 group, there is only one cura file
        // It corresponds to the first origin different from 'G55'
        foreach($g55Rows1 as $row){
            if($row['ORIGIN'] != 'G55'){
                $origin = $row['ORIGIN'];
                break;
            }
        }
        
        $g55Rows = [];
        foreach($g55Rows1 as $row){
            if($row['ORIGIN'] != $origin){
                continue;
            }
            $g55Rows[$row['NUM']] = $row;
        }
        
        //$curaRows1 = csvAssociative::compute(Config::$data['dirs']['5-cura-csv'] . DS . $origin . '.csv');
        $curaRows1 = Cura::loadTmpCsv($origin);
        $curaRows = [];
        foreach($curaRows1 as $row){
            if(isset($g55Rows[$row['NUM']])){
                $curaRows[$row['NUM']] = $row;
            }
        }
        return [$origin, $g55Rows, $curaRows];;
    }
    
    
    
}// end class    

