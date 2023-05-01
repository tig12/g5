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
        $report =  "--- db init wiki ---\n";
        $report_full = $report;
        //
        $actions = ModelWiki::computeAllActions();
        $n_added = 0;
        $n_updated = 0;
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
                    	    CommandBCAdd::execute([
                                $action['slug'],
                                'rw=read,action=' . $action['action'],
                            ]);
                            $n_added++;
                            $report_full .= "Add BC {$action['slug']}\n";
                    	break;
                    	case ModelWiki::ACTION_UPDATE: 
                    	    CommandBCUpdate::execute([
                                $action['slug'],
                                'rw=read,action=' . $action['action'],
                            ]);
                            $n_updated++;
                            $report_full .= "Update BC {$action['slug']}\n";
                    	break;
                    }
            	break;
            	//case 'issue': 
            	//break;
            }
        }
        $report .= "Added $n_added and updated $n_updated BCs\n";
        $report_full .= "---\nAdded $n_added and updated $n_updated BCs\n";
        return $params[0] == 'small' ? $report : $report_full;
    }
    
} // end class
