<?php
/********************************************************************************
    Constants and utilities related to AFD (Müller's Astro-Forschungs-Daten booklets).
    
    @license    GPL
    @history    2021-07-19 15:31:36+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\muller;

class AFD {
    
    /**
        Path to the yaml file containing the characteristics of Müller's Astro-Forschungs-Daten source.
        Relative to directory data/model/source
    **/
    const SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd.yml';
    
    /**
        Slug of Astro-Forschungs-Daten source.
    **/
    const SOURCE_SLUG = 'afd';
    
    /**
        Trust level for data coming from Astro-Forschungs-Daten booklets.
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = 4;
    
    /**
        AFD means Astro-Forschungs-Daten
        Returns a unique Müller id, like "M5-33"
        Unique id of a record among Müller's files.
        5 means volume 5 of AFD (volumes from 1 to 5)
        33 is the id of the record within this volume.
        See https://tig12.github.io/gauquelin5/newalch.html for precise definition
        @param $source      Slug of the source, like 'afd1', 'afd1-100', 'afd2'
        @param $NR          Value of field NR of a record within $source
    **/
    public static function mullerId($source, $NR){
        if(strpos($source, 'afd') === false){
            throw new \Exception("INVALID SOURCE: $source");
        }
        $tmp = str_replace('afd', '', $source);
        return 'M' . $tmp . '-' . $NR;
    }
    
    // *********************** Person ids ***********************
    /**
        Convenience method to find Müller id from Person's $data['ids-in-source'] field.
        If the person is not related to Müller, returns empty string.
        Does not handle ids of persons published in 2 different volumes
        of Astro-Forschungs-Daten (this does not occur).
    **/
    public static function ids_in_sources2mullerId($ids_in_sources){
        foreach($ids_in_sources as $source => $id){
            if($source != 'afd' && str_starts_with($source, 'afd')){
                return AFD::mullerId($source, $id);
            }
        }
        return '';
    }
    
    // ****************************************************
    // Code shared by several AFD files.    
    // ****************************************************
    
    /** 
        Common to AFD2 (famous men) and AFD3 (famous women).
        @param  $str String like '010 E 19'.
        @return Longitude expressed in decimal degrees.
    **/
    public static function computeLg($str): float {
        // use preg_split instead of explode(' ', $str) because of strings like
        // '005 E  2' (instead of '005 E 02')
        $tmp = preg_split('/\s+/', $str);
        $res = $tmp[0] + $tmp[2] / 60;
        $res = $tmp[1] == 'W' ? -$res : $res;
        return round($res, 2);
    }
    
    /** 
        Common to AFD2 (famous men) and AFD3 (famous women).
        @param  $str String like '50 N 59'.
        @return Latitude expressed in decimal degrees.
    **/
    public static function computeLat($str): float {
        $tmp = explode(' N ', $str);
        $multiply = 1;
        if(count($tmp) != 2){
            $tmp = explode(' S ', $str);
            $multiply = -1;
        }
        return $multiply * round($tmp[0] + $tmp[1] / 60, 2);
    }
    
    /** 
        Common to AFD2 (famous men) and AFD3 (famous women).
        @param  $str String like '21.30'.
        @return String like '21:30'.
    **/
    public static function computeHour($hour): string {
        return str_replace('.', ':', $hour);
    }
    
    /** 
        Common to AFD2 (famous men) and AFD3 (famous women).
        @param  $str String like '23.01.1840'.
        @return String like '1840-01-23'.
    **/
    public static function computeDay($str): string {
        $tmp = explode('.', $str);
        if(count($tmp) != 3){
            echo "ERROR DAY $str\n";
            return $str;
        }
        return implode('-', [$tmp[2], $tmp[1], $tmp[0]]);
    }
    
    /** 
        Common to AFD2 (famous men) and AFD3 (famous women).
        @param  $str String like '-0.83'.
        @return String like '00:50'.
    **/
    public static function computeTimezoneOffset($str): string {
        if($str == ''){
            return '';
        }
        preg_match('/(-?)(\d+)\.(\d+)/', $str, $m);
        array_shift($m);
        [$sign1, $hour1, $min1] = $m;
        // Müller's sign is inverse of ISO 8601
        $sign = $sign1 == '' ? '-' : '+';
        if((int)$hour1 == 0 && (int)$min1 == 0){
            $sign = '+';
        }
        $hour = str_pad($hour1, 2, '0', STR_PAD_LEFT);
        // $min1 is a decimal fraction of hour
        $min = round($min1 * 0.6); // *60 / 100
        $min = str_pad($min, 2, '0', STR_PAD_LEFT);
        $res = "$sign$hour:$min";
        return $res;
    }
    
} // end class
