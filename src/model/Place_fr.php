<?php
/******************************************************************************
    Utilities for French places.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2026-02-01 22:14:58+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

class Place_fr{
    
    /**
        Capitalizes the name of a French place, respecting capitalization conventions like "-sur-" and "-et-".
    **/
    public static function ucwords(string $str): string {
        return strtr(ucwords(strtolower($str), "'- \t\r\n\f\v"), [
                '-De-' => '-de-',
                '-Du-' => '-du-',
                '-En-' => '-en-',
                '-Et-' => '-et-',
                '-Sur-' => '-sur-',
                '-La-' => '-la-',
                '-Le-' => '-le-',
                "D'" => "d'",
                "L'" => "l'",
        ]);
    }
    
}// end class
