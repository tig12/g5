<?php
/******************************************************************************
    Computes informations about a city using geonames.org web service.

    @license    GPL
    @history    2019-06-11 09:58:55+02:00, Thierry Graff : Creation from existing code.
********************************************************************************/
namespace tiglib\geonames\webservice;

class cityFromLgLat{
    
    /** URL of geonames.org web service **/
    const URL = 'http://api.geonames.org/findNearbyPlaceNameJSON';
    
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
    public static function compute($username, $lg, $lat, $need_timezone){
        
        $res = [
            'result' => [],
            'error' => false
        ];
        
        // first call, to get all infos except timezone
        $url = self::URL . "?lat=$lat&lng=$lg&username=$username";
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
        $url = self::URL . "?lat=$lat&lng=$lg&username=$username";
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
