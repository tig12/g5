<?php 
/********************************************************************************
    Utilities to build search functionality.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2023-05-08 21:18:39+02:00, Thierry Graff : Creation from existing code.
********************************************************************************/
namespace g5\model;

use g5\app\Config;

Geonames::init();

class Search {
    
    /**
        Computes the different forms of a person name.
        Names used to search a person.
        Family name always before given name.
        @param  $arrNames  Array representing the name, as stored in database. Ex: [
            'given' => Pierre
            'family' => Alard
            'nobl' => ''
            'spouse' => '',
            'official' => [
                'given' => ''
                'family' => ''
            ]
            'fame' => [
                'full' => ''
                'given' => ''
                'family' => ''
            ]
            'alter' => []
        ]
        @return A regular array of possible names
    **/
    public static function computePersonNames(array $arrNames) {
        $res = [];
        // "normal" name
        $fam = $arrNames['family'];
        if($arrNames['nobl'] != ''){
            $fam = $arrNames['nobl'] . $fam;
        }
        if($fam != ''){
            if($arrNames['given'] != ''){
                $res[] = $fam . ' ' . $arrNames['given'];
            }
            else {
                $res[] = $fam;
            }
        }
        // spouse
        if($arrNames['spouse'] != ''){
            if($arrNames['given'] != ''){
                $res[] = $arrNames['spouse'] . ' ' . $arrNames['given'];
            }
            else {
                $res[] = $arrNames['spouse'];
            }
        }
        // fame 
        if($arrNames['fame']['full'] != ''){
            $res[] = $arrNames['fame']['full'];
        }
        if($arrNames['fame']['family'] != '' && $arrNames['fame']['given'] != ''){
            $res[] = $arrNames['fame']['family'] . ' ' . $arrNames['fame']['given'];
        }
        // maybe do not keep alternative names
        if(!empty($arrNames['alter'])){
            foreach($arrNames['alter'] as $alt){
                $res[] = $alt;
            }
        }
        return $res;
    }
    
} // end class
