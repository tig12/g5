<?php
/******************************************************************************
    Fills a csv file to an array of associative arrays.
    
    @license    GPL
    @history    2019-06-11 08:49:25+02:00, Thierry Graff : Creation from old code
********************************************************************************/
namespace tiglib\arrays;


class csvAssociative{
    
    /**
        Fills a csv file to an array of associative arrays.
        The first line of the array is considered as the header, containing the field names.
        All lines are upposed to have the same number of fields (no check is done).
        @param      $filename Absolute path to the csv file
        @param      $delimiter field delimiter (one character only).
        @return     false or associative array
    **/
    public static function compute($filename, $delimiter=';'){
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
    
}// end class
