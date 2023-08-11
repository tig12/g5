<?php
/********************************************************************************
    Builds a list of 458 mathematicians ranked by eminence.
    Data source : book
        Éléments d'histoire des Mathématiques
        by Nicolas Bourbaki
        Ed. Springer
        2007 (3rd edition ; reprint from 1984 version, ed. Masson)
        Uses the "INDEX DES NOMS CITÉS", pp 366 - 376 of the book
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-11-26 21:59:17+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\import\math\bourbaki;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use g5\model\{Source, Group};
use tiglib\arrays\sortByKey;


class rank implements Command {
    
    /** 
        @return String report
    **/
    public static function execute($params=[]): string{
        $report = '';
        return $report;
    }
    
}// end class
