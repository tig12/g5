<?php
/********************************************************************************
    Miscelaneous generic functions
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2007-10-25 21:58, Thierry Graff : Creation
********************************************************************************/
class lib{

    
    // ******************************************************
    /** 
        like sleep() but parameter is a nb of seconds, and it prints a message
    **/
    public static function dosleep($x){
        echo "dosleep($x) ";
        usleep($x * 1000000);
        echo " - end sleep\n";
    }
    
    
    /**
        Fills a csv file to an array of associative arrays
        The first line of the array is considered as the header, containing the field names.
        All lines are upposed to have the same number of fields (no check is done).
        @param      $filename Absolute path to the csv file
        @param      $delimiter field delimiter (one character only).
        @return     false or associative array
    **/
    public static function csvAssociative($filename, $delimiter=';'){
        $res = [];
        if (($handle = fopen($filename, "r")) !== FALSE) {
            $fieldnames = fgetcsv($handle, 0, $delimiter);
            $N = count($fieldnames);
            while (($data = fgetcsv($handle, 0, $delimiter)) !== false){
                if(count($data) == 1 && $data[0] == ''){
                    continue; // skip empty lines
                }
                $tmp = [];
                for ($c=0; $c < $N; $c++) {
                    $tmp[$fieldnames[$c]] = $data[$c];
                }
                $res[] = $tmp;
            }
            fclose($handle);
        }
        return $res;
    }
    
    /* 
    public static function z_csvAssociative_OLD($filename, $delimiter=';'){
        $lines = @file($filename, FILE_IGNORE_NEW_LINES);
        if(!$lines){
            return false;
        }
        $n = count($lines);
        $fields = explode($delimiter, $lines[0]);
        $nfields = count($fields);
        $res = [];
        $cur = [];
        for($i=1; $i < $n; $i++){
            $tmp = explode($delimiter, $lines[$i]);
            for($j=0; $j < $nfields; $j++){
                $cur[$fields[$j]] = $tmp[$j];
            }
            $res[] = $cur;
        }
        return $res;
    }
    */
    
    
    /** Auxiliary variable of sortByKey(), for usort(). */
    private static $sortByKey_keyname;
    
    //***************************************************
    /**
        Sorts a 2 dim array, using one of the key of its elements to sort.
        Param $array must be a regular array composed of associative arrays
        Each of these associative array must have a key named by $keyname
        Ex : 
        $array = [
            0=>['name'=>'toto', 'age'=>45],
            1=>['name'=>'titi', 'age'=>25],
            2=>['name'=>'tata', 'age'=>35]
        ];
        $array2 = jth_sortByKey::sortByKey($array, 'name');
        // then we have :
        $array2 = [
            0=>['name'=>'tata', 'age'=>35],
            1=>['name'=>'titi', 'age'=>25],
            2=>['name'=>'toto', 'age'=>45]
        ];
        $array3 = jth_sortByKey::sortByKey($array, 'age');
        // then we have :
        $array3 = [
            1=>['name'=>'titi', 'age'=>25],
            0=>['name'=>'tata', 'age'=>35],
            2=>['name'=>'toto', 'age'=>45]
        ];
        @param      $array Array to sort
        @param      $keyname Name of the key used to sort
        @return     The sorted array
    **/
    public static function sortByKey($array, $keyname){
        self::$sortByKey_keyname = $keyname;
        usort($array, ['lib', 'sortByKey_aux']);
        return $array;
    }
    
    
    //***************************************************
    /** Auxiliary function of sortByKey(), for usort(). **/
    private static function sortByKey_aux($a, $b){
        if ($a[self::$sortByKey_keyname] == $b[self::$sortByKey_keyname]) return 0;
        return $a[self::$sortByKey_keyname] < $b[self::$sortByKey_keyname] ? -1 : 1;
    }
    
    
    //***************************************************
    /**
        Transforms an ISO-8859-1 or UTF8 string to a string usable in a file name.
        Ex : converts "My string with é" to "my-string-with-e"
        Adaptation from a script found at http://www.phpit.net/code/dirify/, 2005.12.22 02:24
        @param  $str        The string to dirify.
        @param  $replace    String Replacement character (or string) for space characters
        @param  $charset    Can be 'utf8' or 'iso-8859-1'
        @return The transformed string
    **/
    public static function slugify($str, $replace='-', $charset='utf8') {
        if($charset == 'utf8') $str = utf8_decode($str);
        $str = self::convert_high_ascii($str);  ## convert high-ASCII chars to 7bit.
        $str = strtolower($str);                ## lower-case.
        $str = strip_tags($str);                ## remove HTML tags.
        $str = preg_replace('!&#038;[^;\s]+;!','',$str);                 ## remove HTML entities.
        $str = preg_replace('!(_|[^\w\s])!', $replace, $str);            ## remove non-word/space chars, including underscore
        $str = preg_replace('!\s+!', $replace, $str);                    ## change space chars to $replace
        // remove multiple $replace
        while(strPos($str, "{$replace}{$replace}") !== false){
            $str = str_replace("{$replace}{$replace}", $replace, $str);
        }
        // if last character is replace, remove it
        if(substr($str, -1) == $replace){
            $str = substr($str, 0, -1);
        }
        // if first character is replace, remove it
        if(substr($str, 0, 1) == $replace){
            $str = substr($str, 1);
        }
        return $str;        
    }
    
    //***************************************************
    /** Auxiliary function of slugify **/
    private static function convert_high_ascii($s){
             $HighASCII = array(
                     "!\xc0!" => 'A',        # A`
                     "!\xe0!" => 'a',        # a`
                     "!\xc1!" => 'A',        # A'
                     "!\xe1!" => 'a',        # a'
                     "!\xc2!" => 'A',        # A^
                     "!\xe2!" => 'a',        # a^
                     "!\xc4!" => 'A',     # A:
                     "!\xe4!" => 'a',     # a:
                     "!\xc3!" => 'A',        # A~
                     "!\xe3!" => 'a',        # a~
                     "!\xc8!" => 'E',        # E`
                     "!\xe8!" => 'e',        # e`
                     "!\xc9!" => 'E',        # E'
                     "!\xe9!" => 'e',        # e'
                     "!\xca!" => 'E',        # E^
                     "!\xea!" => 'e',        # e^
                     "!\xcb!" => 'E',     # E:
                     "!\xeb!" => 'e',     # e:
                     "!\xcc!" => 'I',        # I`
                     "!\xec!" => 'i',        # i`
                     "!\xcd!" => 'I',        # I'
                     "!\xed!" => 'i',        # i'
                     "!\xce!" => 'I',        # I^
                     "!\xee!" => 'i',        # i^
                     "!\xcf!" => 'I',     # I:
                     "!\xef!" => 'i',     # i:
                     "!\xd2!" => 'O',        # O`
                     "!\xf2!" => 'o',        # o`
                     "!\xd3!" => 'O',        # O'
                     "!\xf3!" => 'o',        # o'
                     "!\xd4!" => 'O',        # O^
                     "!\xf4!" => 'o',        # o^
                     "!\xd6!" => 'O',     # O:
                     "!\xf6!" => 'o',     # o:
                     "!\xd5!" => 'O',        # O~
                     "!\xf5!" => 'o',        # o~
                     "!\xd8!" => 'Oe',     # O/
                     "!\xf8!" => 'oe',     # o/
                     "!\xd9!" => 'U',        # U`
                     "!\xf9!" => 'u',        # u`
                     "!\xda!" => 'U',        # U'
                     "!\xfa!" => 'u',        # u'
                     "!\xdb!" => 'U',        # U^
                     "!\xfb!" => 'u',        # u^
                     "!\xdc!" => 'U',     # U:
                     "!\xfc!" => 'u',     # u:
                     "!\xc7!" => 'C',        # ,C
                     "!\xe7!" => 'c',        # ,c
                     "!\xd1!" => 'N',        # N~
                     "!\xf1!" => 'n',        # n~
                     "!\xdf!" => 'ss'
             );
             $find = array_keys($HighASCII);
             $replace = array_values($HighASCII);
             $s = preg_replace($find,$replace,$s);
             return $s;
    }
    
    
    // ******************************************************
    /**
        Returns the distance between two points identified by their longitudes and latitudes.
        Uses haversine formula.
        Approximate formula
        @todo Possible to better precision using formula of the spheroid for R :
              R = sqrt[ ( a^2 cos(lat))^2 + (b^2 sin(lat))^2 ) / ( (a cos(lat))^2 + (b sin(lat))^2 ) ]
              see https://en.wikipedia.org/wiki/Earth_radius#Geocentric_radius
        @param $lg1, $lat1, $lg2, $lat2 Coordinates expressed in decimal degrees.
    **/
    const R2 = 12745.6; // means 2 * R = 2 * 6372.8
    public static function distance($lg1, $lat1, $lg2, $lat2){
        $lg1 = deg2rad($lg1);
        $lat1 = deg2rad($lat1);
        $lg2 = deg2rad($lg2);
        $lat2 = deg2rad($lat2);
        return self::R2 * asin(
            sqrt(
                pow(sin(($lg2-$lg1)/2), 2)
                + cos($lg1) * cos($lg2) * pow(sin(($lat2-$lat1)/2), 2)
            )
        );
    }

    // ******************************************************
    // from jetheme/model/spacetime/DateUtils
    /**
        Converts a HH:MM string (like 12:34) to the number of minutes it represents
        $str can be preceeded by a - (minus sign)
        The separator between hour and minutes can be any non-numeric character
        @param $str The string to parse
        @return The nb of minutes or false
    **/
    public static function HHMM2minutes($str){
        $pattern = '/([+-]?)(\d{1,2})\D(\d{1,2})/';
        preg_match($pattern, $str, $m);
        if(count($m) != 4){
            return false;
        }
        $res = 60 * $m[2] + $m[3];
        if($m[1] == '-') $res = -$res;
        return $res;
    }
    
    

}// end class
