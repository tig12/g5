<?php
/******************************************************************************
    
    Fills database with information contained in data/wiki
    Executes the actions listed in data/wiki/manage/actions.csv
    Handles addition of acts and issues, addition of wiki project is not done here.
    This is done after tmp2db steps.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-01-22 17:13:29+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\init;

use g5\model\DB5;
use g5\model\wiki\Wiki as ModelWiki;
use g5\commands\wiki\bc\add as CommandBCAdd;
use g5\commands\wiki\bc\update as CommandBCUpdate;
use tiglib\patterns\Command;

class wiki implements Command {
    
    // for report
    private static $report = '';
    private static $report_full = '';
    private static $n_added_bcs = 0;
    private static $n_updated_bcs = 0;
    private static $n_added_persons = 0;
    private static $n_updated_persons = 0;
    
    /** 
        @param  $params array with one element: 'full' or 'small', indicating the kind of report returned by this command.
        @return report.
    **/
    public static function execute($params=[]): string {
        $msg = "INVALID USAGE - This command doesn't needs one parameter:\n"
            . "  - small : echoes a minimal report\n"
            . "  - full : echoes a detailed report\n";
        if(count($params) != 1){
            return $msg;
        }
        if(!in_array($params[0], ['small', 'full'])){
            return "INVALID PARAMETER: {$params[0]}\n$msg";
        }
        //
        self::$report =  "--- db init wiki ---\n";
        self::$report_full = self::$report;
        //
        $actions = ModelWiki::computeAllActions();
        foreach($actions as $action){
            $msg = ModelWiki::check_what($action['what']);
            if($msg != ''){
                throw new \Exception("$msg in actions.csv, line " . implode(ModelWiki::ACTION_SEP, $action));
            }
            $msg = ModelWiki::check_action($action['action']);
            if($msg != ''){
                throw new \Exception("$msg in actions.csv, line " . implode(ModelWiki::ACTION_SEP, $action));
            }
            switch($action['what']){
            	case 'bc': 
                    $RW = 'read';
            	    switch($action['action']){
                    	case ModelWiki::ACTION_ADD: 
                    	    $cmdReport = CommandBCAdd::execute([
                                $action['slug'],
                                'rw=read,action=' . $action['action'],
                            ]);
                            self::$n_added_bcs++;
                            self::$report_full .= "Added BC {$action['slug']}";
                            self::useCmdReport($cmdReport);
                    	break;
                    	case ModelWiki::ACTION_UPDATE: 
                    	    CommandBCUpdate::execute([
                                $action['slug'],
                                'rw=read,action=' . $action['action'],
                            ]);
                            self::$n_updated_bcs++;
                            self::$report_full .= "Updated BC {$action['slug']}";
                            self::useCmdReport($cmdReport);
                    	break;
                    }
            	break;
            	//case 'issue': 
            	//break;
            }
        }
        $strBCs = "BCs     : " . self::$n_added_bcs . " added and " . self::$n_updated_bcs . " updated\n";
        $strPersons = "Persons : " . self::$n_added_persons . " added and " . self::$n_updated_persons . " updated\n";
        self::$report .= $strBCs . $strPersons;
        self::$report_full .= "---\n" . $strBCs . $strPersons;
        return $params[0] == 'small' ? self::$report : self::$report_full;
    }
    
    /**
        Parses the output of command wiki/bc/add or wiki/bc/update.
        Fragile code, as it breaks if the output of these commands are modified.
    **/
    private static function useCmdReport($cmdReport) {
       if(strpos($cmdReport, 'Inserted person') !== false){
           self::$n_added_persons++;
           self::$report_full .= " - person inserted\n";
       }
       else{
           self::$n_updated_persons++;
           self::$report_full .= " - person updated\n";
       }
    }
    
    
} // end class
