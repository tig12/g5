<?php
/********************************************************************************
    Auxiliary code for run-g5.php, Gauquelin5 CLI frontend.
    Provides a generic implementation for namespaces without Router implementation,
    but which respects the convention described in docs/code-details.html.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2017-04-27 10:41:02+02:00, Thierry Graff : creation
    @history    2019-05-10 08:22:01+02:00, Thierry Graff : new version
********************************************************************************/
namespace g5\app;

use g5\G5;
use tiglib\filesystem\globRecursive;

Run::init();

class Run {
    
    private static $COMMAND_ROOT_DIR;
    
    public static function init(){
        self::$COMMAND_ROOT_DIR = dirname(__DIR__) . DS . 'commands';
    }
    
    /**
        Computes the command to call and its arguments
        Tries to find the most specific command.
        Ex: a call "php run-g5.php wiki import math bourbaki rank toto titi"
        corresponds to $components = ['wiki', 'import', 'math', 'bourbaki', 'rank', 'toto', 'titi']
        It will succesively try to build the commands
        g5\commands\wiki\import\math\bourbaki\rank\titi\toto
        g5\commands\wiki\import\math\bourbaki\rank\titi
        g5\commands\wiki\import\math\bourbaki\rank
        Here a command is found, and the arguments are ['titi', 'toto']
        @param  $argv, global variable provided by PHP CLI.
        @return array with 3 elements:
            - A php class implementing interface Command, or false if it can't be computed.
            - An array of arguments to use when calling $command.
            - An error message if $command couldn't be computed.
    **/
    public static function computeCommandAndParams($argv) {
        array_shift($argv); // $argv[0] contains "run-g5.php"
        // gauq is the only exception to standard behaviour (see docs/code-details.html)
        if(isset($argv[0]) && $argv[0] == 'gauq'){
            $params = array_slice($argv, 1);
            return ['g5\\commands\\gauq\\GauqCommand', $params, ''];
        }
        $args = [];
        $msg = '';
        $command = false;
        for($i=count($argv)-1; $i >= 0 ; $i--){
            $components = array_slice($argv, 0, $i+1);
            $file = self::$COMMAND_ROOT_DIR . DS . implode(DS, $components) . '.php';
            if(is_file($file)){
                $command = self::getCommandClass($components);
                if($command != false){
                    return [$command, array_reverse($args), $msg];
                }
            }
            $args[] = $argv[$i];
        }
        //
        [$invalidArg, $possibles, $lastOK] = self::computePossibles($argv);
        if(empty($possibles)){
            $msg = "No correspondance\n";
        }
        else{
            $possibles2 = implode("\n    - ", $possibles);
            $lastOK2 = str_replace(self::$COMMAND_ROOT_DIR, '', $lastOK);
            $lastOK2 = trim(str_replace('/', ' ', $lastOK2));
            if($invalidArg != ''){
                $msg .= "INVALID ARGUMENT: '$invalidArg'\n";
            }
            else {
                $msg .= "MISSING ARGUMENT\n";
            }
            $msg .= "Possible values for argument '$lastOK2':\n    - $possibles2\n";
        }
        //
        return [false, [], $msg];
    }

    /**
        Returns the fully qualified name of a class implementing Command,
        or false if $components don't correspond to such a class.
    **/
    private static function getCommandClass($components){
        array_unshift($components, 'commands');
        array_unshift($components, 'g5');
        $classname = implode('\\', $components);
        try{
            $class = new \ReflectionClass($classname);
            if($class->implementsInterface("tiglib\\patterns\\Command")){
                return $classname;
            }
        }
        catch(\Exception $e){
            // silently ignore php files present in the directory, but containing errors
            // echo "ERR new \\ReflectionClass($classname) \n" . $e->getMessage() . "\n";
        }
        return false;
    }
    
    /**
        Proposes a list of possible following arguments.
        Computes the longest path corresponding to a directory from $components.
        Ex: if $components = ['wiki', 'import', 'toto', 'titi']
            The longest path corresponds to src/commands/wiki/import
            It will return all possible following arguments :
            - the php files implementing Command in src/commands/wiki/import
            - the subdirectories contained in src/commands/wiki/import
        @return Array containing 3 elements:
                - $invalidArg : the first argument not corresponding to a valid dir, or '' if $lastOK corresponds to a directory.
                    Ex: if $components = ['wiki', 'import', 'toto', 'titi'] => $invalidArg = 'toto'
                    Ex: if $components = ['wiki', 'import']                 => $invalidArg = ''
                - $possibles: the list of possible following arguments.
                - $lastOK: the longest path corresponding to a directory from $components.
    **/
    private static function computePossibles($components) {
        $lastOK = false;
        $idxLastOK = -1;
        $invalidArg = '';
        for($i=0; $i < count($components); $i++){
            $cur = array_slice($components, 0, $i+1);
            $dir = self::$COMMAND_ROOT_DIR . DS . implode(DS, $cur);
            if(is_dir($dir)){
                $lastOK = $dir;
                $idxLastOK = $i;
            }
        }
        if($lastOK == ''){
            // particular case, the first argument is not valid.
            $lastOK = self::$COMMAND_ROOT_DIR;
        }
        if(isset($components[$idxLastOK + 1])){
            $invalidArg = $components[$idxLastOK + 1];
        }
        $possibles = self::computeSubdirs($lastOK);
        $possibles = array_merge($possibles, self::computeCommandsOfDir($lastOK));
        return [$invalidArg, $possibles, $lastOK];
    }    
    
    /** 
        Returns the sub-directories that can be used to map a parameter in g5 CLI interface.
        Ex : if $dir = src/commands, returns all subdirs of src/commands that don't start with 'z.'
    **/
    private static function computeSubdirs($dir){
        $res = [];
        $subdirs = array_map('basename', glob($dir . DS . '*', GLOB_ONLYDIR));
        foreach($subdirs as $subdir){
            if(G5::isVersioned($subdir)){
                $res[] = $subdir;
            }
        }
        return $res;
    }

    /**
        Returns an array of names of classes implementing Command, defined in .php files located in $dir.
    **/
    private static function computeCommandsOfDir(string $dir): array {
        $files = glob($dir . DS . '*.php');
        //
        $baseClasspath = str_replace(self::$COMMAND_ROOT_DIR, '', $dir);
        $baseClasspath = 'g5\\commands' . str_replace(DS, '\\', $baseClasspath);
        //
        $res = [];
        foreach($files as $file){
            $basename = basename($file, '.php');
            if(!G5::isVersioned($basename)){
                continue;
            }
            try{
                $classpath = $baseClasspath . '\\' . $basename;
                $class = @new \ReflectionClass($classpath); // @ to avoid warning when autoload fails
                if($class->implementsInterface("tiglib\\patterns\\Command")){
                    $res[] = $basename;
                }
            }
            catch(\Exception $e){
                // silently ignore php files present in the directory, but containing errors
                // echo "ERR new \\ReflectionClass($baseClasspath) \n" . $e->getMessage() . "\n";
            }
        }
        return $res;
    }
    
} // end class
