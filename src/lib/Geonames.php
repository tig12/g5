<?php 
// Software released under the General Public License (version 3 or later), available at
// http://www.gnu.org/copyleft/gpl.html
/********************************************************************************
    Utilities to match place names to geonames entities
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2014-01-21 05:50:27+01:00, Thierry Graff : Creation 
    @history    2017-05-04 10:31:00+02:00, Thierry Graff : Adaptation for autonom use 
********************************************************************************/

use gauquelin5\init\Config;

class Geonames{
    
    /** path to the db containing geonames data **/
    const DBNAME = 'jth_collective/geo';
    
    private static $dblink = null;
    
    // ******************************************************
    public static function compute_dblink(){
        if(is_null(self::$dblink)){
            $host = Config::$data['postgresql']['dbhost'];
            $port = Config::$data['postgresql']['dbport'];
            $user = Config::$data['postgresql']['dbuser'];
            $password = Config::$data['postgresql']['dbpassword'];
            $dbname = Config::$data['postgresql']['dbname'];
            $dsn = "pgsql:host=$host;port=$port;user=$user;password=$password;dbname=$dbname";
            self::$dblink = new PDO($dsn);
            self::$dblink->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
                $query = "select geoid,name,slug,admin2_code,longitude,latitude,timezone from $schema.cities where $where";
                $rst = self::$dblink->prepare($query);
                $rst->execute();
                if($rst->rowCount() == 0){
                    return [];
                }
                return $rst->fetchAll(PDO::FETCH_ASSOC);
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

