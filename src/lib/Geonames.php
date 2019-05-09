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

use g5\init\Config;

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
        Tries to match a place in geonames DB from the slug of a place
        NB : slug = name transformed with no accent, no uppercase and any sprcial character replaced by '-'
        @param $fields The place to match ; associative array containing the fields :
            - slug          required ; string
            - countries     required ; array containing the iso codes of countries where to try the match
            - admin2-code   optional ; string
        @return false if match failed
                or associative array containing these elements :
                    - 'name'        : the name of the place
                    - 'slug'        : slug of place name
                    - 'country'     : ISO country code
                    - 'geoid'       : geoname id of the place
                    - 'admin2_code' : administrative code, level 2 (ex : département in France)
                    - 'lg'          : longitude, decimal degrees
                    - 'lat'         : latitude, decimal degrees
                    - 'timezone'    : textual timezone identifier (ex : "Europe/Paris")
    **/
    public static function matchFromSlug($fields){
        self::compute_dblink();
        if(substr($fields['slug'], 0, 3) == 'st-'){
            $fields['slug'] = 'saint-' . substr($fields['slug'], 3);
        }
        else if(substr($fields['slug'], 0, 4) == 'ste-'){
            $fields['slug'] = 'sainte-' . substr($fields['slug'], 4);
        }
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
                    'timezone'      => $matches[0]['timezone'],
                ];
            }
        }
        return false;
    }
    
    
    // ******************************************************
    /**
        Computes informations about a city using geonames.org web service.
        @param  $username User name used to call geonames web service
        @param  $lg Longitude, in decimal degrees
        @param  $lat Latitude, in decimal degrees
        @param  $need_timezone Boolean indicating if the timezone should also be returned
        @return Associative array containing 2 elements :
            'result' : The result if it could be computed
                In case of success, contains an associative array with the fields :
                - 'name' : name of the city
                - 'geoid' : geonames id of the city
                - 'country' : ISO 3166 country code of the city
                - 'timezone' : Textual timezone identifier (ex : "Europe/Paris") - present only if $need_timezone = true
                In case of failure, 'result' contains an empty array
            'error'  : An error message if the result could not be computed
                or gives incoherent results
                In case of success, 'error' is set to false.
    **/
    public static function cityFromLgLat($username, $lg, $lat, $need_timezone){
        $res = ['result' => [], 'error' => false];
        // first call, to get all infos except timezone
        $url = "http://api.geonames.org/findNearbyPlaceNameJSON?lat=$lat&lng=$lg&username=$username";
        $json = file_get_contents($url);
        $data = json_decode($json);
        if (! empty($data->status)) {
            $res['error'] = $data->status->value.' - '.$data->status->message;
            return $res;
        }
        if(!isset($data->geonames)){
            $res['error'] = "Incoherent result : \n" . print_r($data, true);
            return $res;
        }
        if(!is_array($data->geonames)){
            $res['error'] = "Incoherent result : \n" . print_r($data, true);
            return $res;
        }
        if(count($data->geonames) > 1){
            $res['error'] = "Several possible results : \n" . print_r($data, true);
            return $res;
        }
        // if($data->geonames[0]->fcode != 'PPL'){
        //     $res['error'] = "The result is not a populated place : \n" . print_r($data, true);
        //     return $res;
        // }
        // here, result is ok
        $res['result']['name'] = $data->geonames[0]->name;
        $res['result']['geoid'] = $data->geonames[0]->geonameId;
        $res['result']['country'] = $data->geonames[0]->countryCode;
        
        if(!$need_timezone){
            return $res;
        }
        // second call, for timezone
        $url = "http://api.geonames.org/timezoneJSON?lat=$lat&lng=$lg&username=$username";
        $json = file_get_contents($url);
        $data = json_decode($json);
        if (!empty($data->status)) {
            $res['error'] = 'Unable to compute timezone : ' . $data->status->value.' - '.$data->status->message;
            return $res;
        }
        if(!isset($data->timezoneId)){
            $res['error'] = 'Unable to compute timezone';
            return $res;
        }
        $res['result']['timezone'] = $data->timezoneId;
        return $res;
    }
}// end class

