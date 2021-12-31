<?php
/******************************************************************************
    Defines constants relative to data reliability
    See docs/check.html
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-12-31 16:36:26+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

class Trust {
    
    const HC = 1;
    const BC = 2;
    const BC_CHECK = 2.5;
    const BR = 3;
    const CHECK = 4;
    const REST = 5;
    
} // end class
