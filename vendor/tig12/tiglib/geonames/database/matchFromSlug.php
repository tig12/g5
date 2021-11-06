<?php
/******************************************************************************
    Tries to match a place in geonames DB from the slug of a place.

    @license    GPL
    @history    2019-06-11 11:22:33+02:00, Thierry Graff : Creation from existing code
********************************************************************************/
namespace tiglib\geonames\database;

class matchFromSlug{
    
    // ******************************************************
    /**                                                                    
        Tries to match a place in geonames DB from the slug of a place.
        NB : slug = name transformed with no accent, no uppercase and any sprcial character replaced by '-'
        
        @param $pdo     A valid PDO link to the database that contains geonames data.
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
                    - 'admin2_code' : administrative code, level 2 (département in France ; state in USA)
                    - 'lg'          : longitude, decimal degrees
                    - 'lat'         : latitude, decimal degrees
                    - 'timezone'    : textual timezone identifier (ex : "Europe/Paris")
    **/
    public static function compute($pdo, $fields){
        
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
                $rst = $pdo->prepare($query);
                $rst->execute();
                if($rst->rowCount() == 0){
                    return [];
                }
                return $rst->fetchAll(\PDO::FETCH_ASSOC);
            }
            catch(\Exception $e){
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
    
}// end class
