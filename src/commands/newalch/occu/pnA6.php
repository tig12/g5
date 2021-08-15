<?php
/********************************************************************************
    Affects occupation "poet" or "novelist" to some records of A6 file.
    "pn" stands for "poets - novelists".
    Based on a work exposed on https://newalchemypress.com/gauquelin/research8.php
    
    A6 records must have been imported in database before executing this command.
    
    @license    GPL
    @history    2021-08-15 16:45:27+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\newalch\occu;

use g5\Config;
use g5\patterns\Command;
use g5\commands\newalch\Newalch;
use g5\commands\gauquelin\LERRCP;
use g5\model\Person;
use g5\model\Group;

class pnA6 implements Command {
    
    /** Directory where FILES are located, relative to Newalch raw directory. **/
    const DIR = '08-writers';
    
    /**
        Files used in this command, and related occupations.
    **/
    const FILES = [
        'novelists.dat.zip' => 'novelist',
        'poetse.dat.zip'    => 'poet',
    ];
    
    /**
        @param  $params Empty array
    **/
    public static function execute($params=[]): string {
        
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $report = "--- newalch occu pnA6 ---\n";
        
        $t1 = microtime(true);        
        $pattern = '/"(\d+)"/';
        $N = 0;
        foreach(self::FILES as $file => $occu){
            $g = Group::getBySlug($occu); // group slug = occupation slug
            $g->computeMembers();
            $zipfile = Newalch::rawDirname() . DS . self::DIR . DS . $file;
            $zip = new \ZipArchive;
            $zip->open($zipfile);
            $content = $zip->getFromName(str_replace('.zip', '', $file));
            $lines = explode("\n", $content);
            foreach($lines as $line){
                if($line == ''){
                    continue;
                }
                preg_match($pattern, $line, $m);
                $p = Person::getBySourceId('a6', $m[1]);
                $p->addOccus([$occu]);
                $new = ['occus' => $occu];
                $p->addHistory('newalch occu pnA6', Newalch::SOURCE_SLUG, $new);
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

