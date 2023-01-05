<?php
/********************************************************************************
    Constants and utilities related to Wiki management.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-12-24 15:54:45+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\model
;
use g5\app\Config;

class Wiki {
    
    /**
        Slug of wiki information source.
    **/
    const SOURCE_SLUG = 'wiki';
    
    // *********************** File manipulation ***********************
    
    /**
        @return Path to the directory containing informations related to persons.
    **/
    public static function personsRootDir(){
        return implode(DS, [Config::$data['dirs']['wiki'], 'persons']);
    }
    
    /**
        Computes the directory where person informations are stored.
        @param  $slug The slug of the person to add ; ex: galois-evariste-1811-10-25
        @return The directory path
        @throws Exception if the slug is incoherent.
    **/
    public static function slug2dir(string $slug) {
        $p = '/(.*?)\-(\d+)\-(\d{2})\-(\d{2})/';
        preg_match($p, $slug, $m);
        if(count($m) != 5){
            throw new \Exception("Invalid slug: " . $slug);
        }
        $path = [
            self::personsRootDir(),
            $m[2],
            $m[3],
            $m[4],
            $slug,
        ];
        return implode(DS, $path);
    }
    
} // end class