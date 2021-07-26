<?php
/******************************************************************************
    Utilities for french names.
    
    @license    GPL
    @history    2019-06-06 22:53:04+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

class Names_fr{
    
    /** Used by accentGiven() **/
    private static $accentGiven = [
        'Aime'      => 'Aimé',
        'Arsene'    => 'Arsène',
        'Amedee'    => 'Amédée',
        'Andre'     => 'André',
        'Benoit'    => 'Benoît',
        'Celestin'  => 'Célestin',
        'Cesar'     => 'César',
        'Clement'   => 'Clément',
        'Desire'    => 'Désiré',           
        'Dieudonne' => 'Dieudonné',
        'Eugene'    => 'Eugène',
        'Eusebe'    => 'Eusèbe',
        'Felix'     => 'Félix',
        'Francois'  => 'François',
        'Frederic'  => 'Frédéric',
        'Gerard'    => 'Gérard',
        'Gisele'    => 'Gisèle',
        'Honore'    => 'Honoré',
        'Jérome'    => 'Jérôme',
        'Joel'      => 'Joël',
        'Jose'      => 'José',
        'Leon'      => 'Léon',
        'Noel'      => 'Noël',
        'Raphael'   => 'Raphaël',
        'Raphaél'   => 'Raphaël',
        'Regis'     => 'Régis',
        'Remi'      => 'Rémi',
        'Remy'      => 'Rémy',
        'Rene'      => 'René',
        'Seraphin'  => 'Séraphin',
        'Stephane'  => 'Stéphane',
        'Theo'      => 'Théo',
        'Valere'    => 'Valère',
    ];
    // ******************************************************
    /**
        Fix accents in common given names; 
        @return     Corrected string
    **/
    public static function accentGiven($str): string{
        foreach(self::$accentGiven as $k => $v){
            if($str == $k){
                $str = $v;
                break;
            }
        }
        return $str;
    }
    
    
    /** Used by fixJean() **/
    private static $fixJean = [
        'claude'    => 'Claude',
        'françois'  => 'François',
        'jacques'   => 'Jacques',
        'joseph'    => 'Joseph',
        'louis'     => 'Louis',
        'marie'     => 'Marie',
        'michel'    => 'Michel',
        'noel'      => 'Noël',
        'paul'      => 'Paul',
        'pierre'    => 'Pierre',
    ];
    // ******************************************************
    /**
        @param $str A string that may contain a name with composed given name starting by Jean
                    Ex : "Augert Jean Noel"
        @return Array with 2 elements : family name and given name.
                If given name could not be computed,
                - family name contains $str
                - given name is empty
    **/
    public static function fixJean($str){
        $str2 = strtolower($str);
        $parts = explode(' ', $str2);
        if(count($parts) != 3){
            return [$str, '']; // do nothing
        }
        if($parts[1] != 'jean'){
            return [$str, '']; // do nothing
        }
        if(in_array($parts[2], array_keys(self::$fixJean))){
            return [
                ucFirst($parts[0]),
                'Jean-' . self::$fixJean[$parts[2]]
            ];
        }
        else{
            echo "Possible candidate for Names_fr.fixJean() : $str - NOT FIXED - modify \$fixJean to fix it.\n";
        }
        return [$str, ''];
    }
    
    // ******************************************************
    /**
        Computes a family name taking the nobiliary particle into account.
    **/
    public static function computeFamilyName($fname, $nob){
        if($nob == ''){
            return $fname;
        }
        if($nob == "d'"){
            return $nob . $fname;
        }
        return $nob . ' ' . $fname;
    }
    
} // end class
