<?php
/******************************************************************************
    
    Builds a hierarchy of occupations
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2026-01-16 07:51:05+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\look;

use tiglib\patterns\Command;
use g5\app\Config;
use g5\model\Person;
use g5\model\Group;
use g5\model\Occupation;
use g5\commands\gauq\LERRCP;
use g5\commands\muller\Muller;

class occutree implements Command {
    
    /** Occupations coming from database **/
    private static array $allOccus = [];
    
    private static array $occuTree = [];
    
    /** 
        @param  $params Empty array.
    **/
    public static function execute($params=[]) {
        if(count($params) > 0){
            return "WRONG USAGE : this command doesn't take any parameter. Useless parameter : '{$params[0]}'\n";
        }
        
        self::$allOccus = Occupation::getAll(slugKey: true);
print_r(self::$allOccus); exit;
        
        $roots = [];
        foreach(self::$allOccus as $slug => $row){
            if(count($row['parents']) == 0) {
                $roots[] = $slug;
            }
        }
        foreach($roots as $root){
            self::$occuTree[$root] = self::buildHierarchy($root);
        }
echo "\n"; print_r(self::$occuTree); echo "\n"; exit;
        echo self::outputHtml();
    }
    
    /**
        Computes the hierarchy of an occupation
        Recursive
        @param  $slug
    **/
    public static function buildHierarchy(string $slug) {
        $res = [];
        foreach(self::$allOccus[$slug]['children'] as $child){
            $res[$child] = self::buildHierarchy($child);    // recursive here
        }
        return $res;
    }
    
    
    const string HTML_TAB = '   ';
    
    private static function outputHtml() {
        $res ='';
        $res .= "<ul>\n";
        foreach(self::$occuTree as $occu => $children){
            $res .= self::outputHtml_node($occu, $children, self::HTML_TAB);
        }
        $res .= "</ul>\n";
        return $res;
    }
    
    /** 
        Auxiliary of self::outputHtml() - builds the list for a single node
        Recursive
    **/
    private static function outputHtml_node($occu, $children, $tab) {
        $res ='';
        $res .= "$tab<li>\n";
        $res .= "$tab$tab$occu\n";
        if(count($children) != 0) {
            $res .= "$tab<ul>\n";
            foreach($children as $occu2 => $children2){
                $res .= self::outputHtml_node($occu2, $children2, $tab . self::HTML_TAB); // recursive here
            }
            $res .= "$tab</ul>\n";
        }
        $res .= "$tab</li>\n";
        return $res;
    }
    
} // end class
