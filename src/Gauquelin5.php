<?php
/********************************************************************************
    Importation of Gauquelin 5th edition
    Main class, conducts the computation
    
    @license    GPL
    @history    2017-04-27 10:41:02+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5;

use g5\init\Config;

class Gauquelin5{
    
    /**
        Separator used in the generated (csv) files.
        Maybe put in config.
    **/
    const CSV_SEP = ';';
                                                                                                                     
    /** 
        Associations between subjects and packages containing code to handle them.
    **/
    const SUBJECT_PACKAGE = [
        'A' => 'gauquelin5\subjects\cura\serieA',
        'A1' => 'gauquelin5\subjects\cura\serieA',
        'A2' => 'gauquelin5\subjects\cura\serieA',
        'A3' => 'gauquelin5\subjects\cura\serieA',
        'A4' => 'gauquelin5\subjects\cura\serieA',
        'A5' => 'gauquelin5\subjects\cura\serieA',
        'A6' => 'gauquelin5\subjects\cura\serieA',
        'G55' => 'gauquelin5\subjects\g1955',
        'B' => 'gauquelin5\subjects\cura\serieB',
        'B1' => 'gauquelin5\subjects\cura\serieB',
        'B2' => 'gauquelin5\subjects\cura\serieB',
        'B3' => 'gauquelin5\subjects\cura\serieB',
        'B4' => 'gauquelin5\subjects\cura\serieB',
        'B5' => 'gauquelin5\subjects\cura\serieB',
        'B6' => 'gauquelin5\subjects\cura\serieB',
        //
        'D6' => 'gauquelin5\subjects\cura\fileD6',
        'D9a' => '',
        'D9b' => '',
        'D9c' => '',
        'D10' => 'gauquelin5\subjects\cura\fileD10',
        //
        'E1' => 'gauquelin5\subjects\cura\filesE1_E3',
        //
        'E2' => '',
        'E2a' => '',
        'E2b' => '',
        'E2c' => '',
        'E2d' => '',
        'E2e' => '',
        'E2f' => '',
        'E2g' => '',
        //
        'E3' => 'gauquelin5\subjects\cura\filesE1_E3',
        'F1' => '',
        'F2' => '',
    ];
    
    
    //
    /// old constants
    //
    
    /** Associations between series and class names **/
    const SERIES_CLASS = [
        'A1' => 'model\cura\SerieA',
        'A2' => 'model\cura\SerieA',
        'A3' => 'model\cura\SerieA',
        'A4' => 'model\cura\SerieA',
        'A5' => 'model\cura\SerieA',
        'A6' => 'model\cura\SerieA',
        //
        '1955' => 'model\g1955\Serie1955',
        //
        'B1' => 'model\cura\SerieB',
        'B2' => 'model\cura\SerieB',
        'B3' => 'model\cura\SerieB',
        'B4' => 'model\cura\SerieB',
        'B5' => 'model\cura\SerieB',
        'B6' => 'model\cura\SerieB',
        //
        'D6' => 'model\cura\SerieD6',
        'D9a' => '',
        'D9b' => '',
        'D9c' => '',
        'D10' => 'model\cura\SerieD10',
        //
        'E1' => 'model\cura\SerieE1_E3',
        //
        'E2' => '',
        'E2a' => '',
        'E2b' => '',
        'E2c' => '',
        'E2d' => '',
        'E2e' => '',
        'E2f' => '',
        'E2g' => '',
        //
        'E3' => 'model\cura\SerieE1_E3',
        'F1' => '',
        'F2' => '',
    ];
    
    /** 
        Association serie name => available actions for this serie
    **/
    const SERIES_ACTIONS = [
        'A'=> ['raw2csv', 'generateCorrected'],
        'A1'=> ['raw2csv', 'generateCorrected'],
        'A2'=> ['raw2csv', 'generateCorrected'],
        'A3'=> ['raw2csv', 'generateCorrected'],
        'A4'=> ['raw2csv', 'generateCorrected'],
        'A5'=> ['raw2csv', 'generateCorrected'],
        'A6'=> ['raw2csv', 'generateCorrected'],
        //
        '1955'=> ['marked2generated', 'generateOriginal', 'generateCorrected'],
        //
        'B'=> [],
        'B1'=> [],
        'B2'=> [],
        'B3'=> [],
        'B4'=> [],
        'B5'=> [],
        'B6'=> [],
        //
        'D6'=> ['raw2csv', 'computeGeo', 'generateCorrected'],
        'D9a'=> [],
        'D9b'=> [],
        'D9c'=> [],
        'D10'=> ['raw2csv', 'generateCorrected'],
        //
        'E1'=> ['raw2csv', 'generateCorrected'],
        //
        'E2'=> [],
        'E2a'=> [],                                                                       
        'E2b'=> [],
        'E2c'=> [],
        'E2d'=> [],
        'E2e'=> [],
        'E2f'=> [],
        'E2g'=> [],
        //
        'E3'=> ['raw2csv', 'generateCorrected'],
        //
        'F1'=> [],
        'F2'=> [],
    ];

    // ******************************************************
    /** 
        Unique entry point of this package
        Acts as a router to different action methods
        @param      $serie String representing a valid serie (as defined in run-gauquelin5.php)
        @param      $action String representing a valid action (as defined in run-gauquelin5.php)
        @return     string a report
    **/
    public static function action($action, $serie){
        // convert $serie to an array
        switch($serie){
        	case 'A' : 
        	    $series = ['A1', 'A2', 'A3', 'A4', 'A5', 'A6'];
        	break;
        	case 'B' : 
        	    $series = ['B1', 'B2', 'B3', 'B4', 'B5', 'B6'];
        	break;
        	case 'E2' : 
        	    $series = ['E2a', 'E2b', 'E2c', 'E2d', 'E2e', 'E2f', 'E2g'];
        	break;
            default:
                $series = [$serie];
            break;
        }
        $report = '';
        foreach($series as $s){
            $classname = 'gauquelin5\\' . self::SERIES_CLASS[$s];
            $report .= $classname::$action($s);
        }
        return $report;
    }
    
    
}// end class
