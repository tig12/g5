<?php
/********************************************************************************
    Auxiliary code for run-g5.php, Gauquelin5 CLI frontend.
    Provides a generic implementation for namespaces without Router implementation,
    but which respect the convention described in docs/code-details.html.
    
    @license    GPL
    @history    2017-04-27 10:41:02+02:00, Thierry Graff : creation
    @history    2019-05-10 08:22:01+02:00, Thierry Graff : new version
********************************************************************************/
namespace g5\app;

class Run{
    
    /** 
        Returns the sub-directories that can be used to map a parameter in g5 CLI interface.
        Ex : if $dir = src/commands, returns all subdirs that don't start with 'z.'
    **/
    private static function getSubdirs($dir){
        $res = [];
        $subdirs = array_map('basename', glob($dir . DS . '*', GLOB_ONLYDIR));
        foreach($subdirs as $subdir){
            // files / directories starting by z. are not versioned (draft or obsolete)
            if(strpos($subdir, 'z.') !== 0){
                $res[] = $subdir;
            }
        }
        return $res;
    }
    
    // ******************************************************
    /**
        Returns a list of data sets known by the program
        = list of sub-directories of commands/
    **/
    public static function getArgs1(){
        return self::getSubdirs(dirname(__DIR__) . DS . 'commands');
    }
    
    
    // ******************************************************
    // Simulates implementation of Router 
    /**
        Returns the possible arg2 for a given arg1.
        @todo maybe use reflection if some sub-directories of arg1s do not correspond to a arg2 sub-package.
    **/
    public static function getArgs2($arg1){
        // if the arg1 has an implementation of interface Router, delegate
        $file = self::arg1RouterFilename($arg1);
        if(file_exists($file)){
            $class = self::arg1RouterClassname($arg1);
            return $class::getArgs2();
        }
        // else return the directories located in the arg1's class directory
        // as the code is psr4, possible to list php files without using reflection.
        $dir = implode(DS, [dirname(__DIR__), 'commands', $arg1]);
        return self::getSubdirs($dir);
    }
    
    
    // ******************************************************
    // Simulates implementation of Router 
    /**
        Returns the possible commands for the arg2 of a arg1.
        @return Array of strings containing the possible args3.
    **/
    public static function getArgs3($arg1, $arg2){
        // if the arg1 has an implementation of interface Router, delegate
        $file = self::arg1RouterFilename($arg1);
        if(file_exists($file)){
            $class = self::arg1RouterClassname($arg1);
            return $class::getArgs3($arg2);
        }
        // else return the classes located in the arg2's class directory
        // as the code is psr4, possible to list php files without using reflection.
        $dir = implode(DS, [dirname(__DIR__), 'commands', $arg1, $arg2]);
        $tmp = glob($dir . DS . '*.php');
        $res = [];
        foreach($tmp as $file){
            $basename = basename($file, '.php');
            try{
                $class = new \ReflectionClass("g5\\commands\\$arg1\\$arg2\\$basename");
                if($class->implementsInterface("tiglib\\patterns\\Command")){
                    $res[] = $basename;
                }
            }
            catch(\Exception $e){
                // silently ignore php files present in the directory, but containing errors
                // echo "ERR new \\ReflectionClass(\\"g5\\commands\\$arg1\\$arg2\\$basename\\") \n" . $e->getMessage() . "\n";
            }
        }
        return $res;
    }
    
    // ******************************************************
    /**
        Returns the fully qualified name of a class that will be called for a given triple (arg1, arg2, arg3).
        Implementation of convention described in docs/code-details.html
        @return Array with 2 elements :
                    - Boolean Indicates if the returned class implements Router or Command
                    - String The class name.
        @throws Exception if the convention is not correctly coded.
        @todo check that the class implements interface Command.
    **/
    public static function getCommandClass($arg1, $arg2, $arg3){
        // look if class implementing Command exists for the arg1
        $file = self::arg1CommandFilename($arg1);
        if(file_exists($file)){
            $class = self::arg1CommandClassname($arg1);
            return [true, $class];
        }
        // Class for arg1 doesn't exist, use default 
        // look if a class corresponding to this arg3 exists
        $class = "g5\\commands\\$arg1\\$arg2\\$arg3";
        if(class_exists($class)){
            return [false, $class];
        }
        // class not found
        $msg = "BUG : incorrect implementation of command.\n"
            . "  Dataset : $arg1\n"
            . "  Datafile : $arg2\n"
            . "  Action : $arg3\n";
        throw new \Exception($msg);
    }
    
    
    // ******************************************************
    /** 
        Returns the class name of a arg1's class implementing Router interface.
        Auxiliary of self::getArgs2() and self::getActionClass().
    **/
    private static function arg1RouterClassname($arg1){
        return implode("\\", ['g5', 'commands', $arg1, ucFirst($arg1) . 'Router']);
    }

    /** 
        Returns the absolute filename of a arg1's class implementing Router interface.
        Auxiliary of self::getArgs2() and self::getActionClass().
    **/
    private static function arg1RouterFilename($arg1){
        return implode(DS, [dirname(__DIR__), 'commands', $arg1, ucFirst($arg1) . 'Router.php']);
    }
    
    
    // ******************************************************
    /** 
        Returns the class name of a arg1's class implementing Command interface.
        Auxiliary of self::getCommandClass().
    **/
    private static function arg1CommandClassname($arg1){
        return implode("\\", ['g5', 'commands', $arg1, ucFirst($arg1) . 'Command']);
    }
    
    /** 
        Returns the absolute path of a arg1's class implementing Command interface.
        Auxiliary of self::getCommandClass().
    **/
    private static function arg1CommandFilename($arg1){
        return implode(DS, [dirname(__DIR__), 'commands', $arg1, ucFirst($arg1) . 'Command' . '.php']);
    }
    
    
}// end class
