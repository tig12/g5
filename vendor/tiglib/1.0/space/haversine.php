<?php
/******************************************************************************
    Computes the distance between two points identified by their longitudes and latitudes.

    @license    GPL
    @history    2019-06-11 09:36:25+02:00, Thierry Graff : Creation from old code.
********************************************************************************/
namespace tiglib\space;

class haversine{
    
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
    public static function compute($lg1, $lat1, $lg2, $lat2){
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
    
}// end class
