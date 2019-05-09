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

}// end class
