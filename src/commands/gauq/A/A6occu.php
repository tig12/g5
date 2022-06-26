<?php
/********************************************************************************

    php run-g5.php gauq A6 A6occu

    Updates 885 occupations in persons coming from file A6 (11.366 s)
    Occupations are set to "poet" or "novelist"
    Occupation codes are updated on persons stored in database.
    A6 records must have been imported in database before executing this command.
    
    Based on a work exposed on https://newalchemypress.com/gauquelin/research8.php
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-08-15 16:45:27+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\A;

use g5\app\Config;
use tiglib\patterns\Command;
use g5\commands\Newalch;
use g5\commands\gauq\LERRCP;
use g5\model\Person;
use g5\model\Group;

class A6occu implements Command {
    
    /** Directory where FILES are located, relative to LERRCP raw directory. **/
    const DIR = 'a6occu';
    
    /**
        Files used in this command, and related occupations.
    **/
    const FILES = [
        'novelists.dat.zip' => 'novelist',
        'poetse.dat.zip'    => 'poet',
    ];
    
    /**
        @param  $params Array with 2 elements : 'A6' and 'A6occu' (useless here - transmitted by GauqRouter)
    **/
    public static function execute($params=[]): string {
        
        if(count($params) > 2){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        // Normally called with php run-g5.php gauq A6 A6occu
        // Check if it was called for example with php run-g5.php gauq A A6occu
        // In this case, it would execute 6 times
        if($params[0] != 'A6'){
            throw new \Exception('INVALID PARAMETER: ' . $params[0] . "\n"
                . "Must be called with: php run-g5.php gauq A6 A6occu");
            ;
        }
        
        $report = "--- gauq A6 A6occu ---\n";
        
        $t1 = microtime(true);        
        $pattern = '/"(\d+)"/';
        $N = 0;
        foreach(self::FILES as $file => $occu){
            $g = Group::createFromSlug($occu); // group slug = occupation slug
            $g->computeMembers();
            $zipfile = LERRCP::rawDirname() . DS . self::DIR . DS . $file;
            $zip = new \ZipArchive;
            $zip->open($zipfile);
            $content = $zip->getFromName(str_replace('.zip', '', $file));
            $lines = explode("\n", $content);
            foreach($lines as $line){
                if($line == ''){
                    continue;
                }
                preg_match($pattern, $line, $m);
                $p = Person::createFromSourceId('a6', $m[1]);
                $p->addOccus([$occu]);
                $new = ['occus' => $occu];
                $p->addHistory(
                    command: 'gauq A6 occupn',
                    sourceSlug: Newalch::SOURCE_SLUG,
                    newdata: $new,
                    rawdata: $new,
                );
                $p->update(); // DB
                $g->addMember($p->data['id']);
                $N++;
            }
            $g->update(); // DB
            $zip->close();
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $report .= "Updated $N occupations in file A6 ($dt s)\n";
        return $report;
    }
        
} // end class    

