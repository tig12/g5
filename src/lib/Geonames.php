<?php 
// Software released under the General Public License (version 3 or later), available at
// http://www.gnu.org/copyleft/gpl.html
/********************************************************************************
    Utilities to match place names to geonames entities
    
    @license    GPL
    @copyright  jetheme.org
    @history    2014-01-21 05:50:27+01:00, Thierry Graff : Creation 
********************************************************************************/

class Geonames{
    
    /** path to the db containing geonames data **/
    const DBNAME = 'jth_collective/geo';
    
    private static $dblink = null;
    
    // ******************************************************
    public static function compute_dblink(){
        if(is_null(self::$dblink)){
            $dbparams = Storage::get_dbparams('default', self::DBNAME);
            self::$dblink = Storage::createDataDBLink('default', $dbparams);
        }
    }
    
    
    // ******************************************************
    /**
        Tries to match a place in geonames DB
        @param $fields The place to match ; associative array containing the fields :
            - slug          required ; string
            - countries     required ; array containing the iso codes of countries where to try the match
            - admin2-code   optional ; string
        @return false if match failed
                or associative array containing these elements :
                    - 'country'     : ISO country code
                    - 'geoid'       : geoname id of the place
                    - 'slug'        : slug of place name
                    - 'admin2_code' : administrative code, level 2 (ex : département in France)
                    - 'lg'          : longitude, decimal degrees
                    - 'lat'         : latitude, decimal degrees
    **/
    public static function match($fields){
        self::compute_dblink();
        $where = "slug='{$fields['slug']}'";
        if(isset($fields['admin2-code']) && $fields['admin2-code']){
            $where .= " and admin2_code='{$fields['admin2-code']}'";
        }
        for($i=0; $i < count($fields['countries']); $i++){
            $schema = strtolower($fields['countries'][$i]);
            try{
                $matches = self::$dblink->get("$schema.cities", array('fields'=>'geoid,name,slug,admin2_code,longitude,latitude,timezone', 'where'=>$where));
            }
            catch(Exception $e){
                // silently pass the fact that a country is not available
                continue;
            }
            if(count($matches) >= 1){ // WARNING, if several places found, takes the first one
                return [
                    'name'          => $matches[0]['name'],
                    'slug'          => $matches[0]['slug'],
                    'country'       => $fields['countries'][$i],
                    'geoid'         => $matches[0]['geoid'],
                    'admin2-code'   => $matches[0]['admin2_code'],
                    'lg'            => $matches[0]['longitude'],
                    'lat'           => $matches[0]['latitude'],
                ];
            }
        }
        return false;
    }
    
    
}// end class

