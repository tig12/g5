<?php
/********************************************************************************
    Constants and utilities shared by several classes of this package.
    
    @license    GPL
    @history    2020-07-13 17:15:37+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\newalch;

use g5\model\DB5;

class Newalch{
    
    /** uid when newalch is used to create a group **/
    const UID_PREFIX_GROUP = 'group' . DB5::SEP . 'datasets' . DB5::SEP . 'newalch';
    
}// end class
