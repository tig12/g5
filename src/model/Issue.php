<?php
/******************************************************************************
    An issue is a key-value pair.
    Key = type of issue
    Value = description of the issue
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-10-10 02:19:46+02:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

class Issue {
    
    //Standard values for keys
    
    /** Check one of the component of the name **/
    const CHK_NAME = 'chk-name';
    
    /** Check day or time or both **/
    const CHK_DATE = 'chk-date';
    
    const CHK_DAY = 'chk-day';
    
    const CHK_TIME = 'chk-time';
    
    /** Check timezone offset **/
    const CHK_TZO = 'chk-tzo';
    
    /** Check birth place **/
    const CHK_BPLACE = 'chk-bplace';
    
} // end class
