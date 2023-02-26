<?php
/******************************************************************************

    @license    GPL
    @history    2023-02-25 01:55:20+01:00, Thierry Graff : Creation
********************************************************************************/
namespace tiglib\time;

class sub{
    
    const PATTERN_OFFSET = '/^([+-])(\d{2}):(\d{2}):?(\d{2})?$/';
    
    // ******************************************************
    /**
        Substracts a duration to a date
        Ex of valid calls:
            add("2023-02-25 14:37", "+01:00")
            add("2023-02-25 14:37", "+01:00:00")
            add("2023-02-25 14:37:35", "-06:55")
            add("2023-02-25 14:37:35", "-06:55:34")
        $str can be preceeded by a - (minus sign)
        The separator between hour and minutes can be any non-numeric character
        @param  $date       YYYY-MM-DD HH:MM[:SS]
                            [:SS] represents optional seconds
        @param  $offset     sHH:MM[:SS]
                            s represents mandatory sign "-" or "+"
        @return             YYYY-MM-DD HH:MM
        @throws Exception if $date or $offset are not correct.
    **/
    public static function execute($date, $offset){
        preg_match(self::PATTERN_OFFSET, $offset, $matches);
        if(count($matches) != 4 && count($matches) != 5){
            throw new \Exception("tiglib\\time\\add - INVALID OFFSET: $offset");
        }
//echo "\n<pre>"; print_r($matches); echo "</pre>\n";
        $sign = $matches[1];
        $h = (int)$matches[2];
        $m = (int)$matches[3];
        $s = count($matches) == 5 ? (int)$matches[4] : 0;
        try{
            $dt = new \DateTime($date);
        }
        catch(\Exception $e){
            throw new \Exception("tiglib\\time\\add - INVALID DATE: $date");
        }
        try{
            $di = new \DateInterval('PT'.$h.'H'.$m.'M'.$s.'S');
        }
        catch(\Exception $e){
            throw new \Exception("tiglib\\time\\add - INVALID OFFSET: $offset");
        }
        if($sign == '-'){
            $di->invert = -1;
        }
//echo "offset = $offset - result = $sign / $h / $m / $s\n";
        $dt->sub($di);
        $result = $dt->format('Y-m-d H:i');
        if(strlen($date) == 19 || strlen($offset) == 9){
            $result .= ':' . $dt->format('s');
        }
        return $result;
    }
    
}// end class
