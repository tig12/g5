<?php
/******************************************************************************
    str_pad() for multi-byte strings
    Adaptation of code from https://www.php.net/manual/en/function.str-pad.php

    @license    GPL
    @history    2019-10-29 13:06:41+01:00, Thierry Graff : Creation
********************************************************************************/
namespace tiglib\strings;

class mb_str_pad{
    
    /** 
        str_pad() for multi-byte strings
        Same parameters and default values as str_pad() ; see str_pad() doc
    **/
    public static function execute($input, $pad_length, $pad_string=' ', $pad_style=STR_PAD_RIGHT, $encoding="UTF-8") {
        return str_pad($input, strlen($input)-mb_strlen($input,$encoding)+$pad_length, $pad_string, $pad_style);
    } 
    
}// end class
