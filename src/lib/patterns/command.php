<?php
/******************************************************************************

    @license    GPL
    @history    2019-05-11 17:33:48+02:00, Thierry Graff : Creation
********************************************************************************/

interface command{
    
    public execute($params=[]): string;
    
}// end interface
