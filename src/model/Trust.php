<?php
/******************************************************************************
    Defines constants relative to data reliability
    See docs/check.html
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-12-31 16:36:26+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

class Trust {
    
    /** Hospital certificate **/
    const HC = 1;
    
    /** Birth certificate **/
    const BC = 2;
    
    /** Birth record **/
    const BR = 3;
    
    /** Mainly for royal families ; births without birth certificate, but with hour commonly admitted. **/
    const REPUTATION = 4;
    
    /** Needs to be checked **/
    const CHECK = 5;
    
} // end class
