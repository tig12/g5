<?php
/********************************************************************************
    Auxiliary code for run-g5.php, Gauquelin5 CLI frontend.
    Provides a generic implementation for namespaces without Router implementation,
    but which respect the convention described in docs/code-details.html.
    
    @license    GPL
    @history    2017-04-27 10:41:02+02:00, Thierry Graff : creation
    @history    2019-05-10 08:22:01+02:00, Thierry Graff : new version
********************************************************************************/
namespace g5;

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
    public static function getDatasets(){
        return self::getSubdirs(__DIR__ . DS . 'commands');
    }
    
    
    // ******************************************************
    // Simulates implementation of Router 
    /**
        Returns the possible datafiles for a given dataset.
        @todo maybe use reflection if some sub-directories of datasets do not correspond to a datafile sub-package.
    **/
    public static function getDatafiles($dataset){
        // if the dataset has an implementation of interface Router, delegate
        $file = self::datasetRouterFilename($dataset);
        if(file_exists($file)){
            $class = self::datasetRouterClassname($dataset);
            return $class::getDatafiles();
        }
        // else return the directories located in the dataset's class directory
        // as the code is psr4, possible to list php files without using reflection.
        $dir = implode(DS, [__DIR__, 'commands', $dataset]);
        return self::getSubdirs($dir);
    }
    
    
    // ******************************************************
    // Simulates implementation of Router 
    /**
        Returns the possible commands for the datafile of a dataset.
        @return Array of strings containing the possible actions.
    **/
    public static function getCommands($dataset, $datafile){
        // if the dataset has an implementation of interface Router, delegate
        $file = self::datasetRouterFilename($dataset);
        if(file_exists($file)){
            $class = self::datasetRouterClassname($dataset);
            return $class::getCommands($datafile);
        }
        // else return the classes located in the datafile's class directory
        // as the code is psr4, possible to list php files without using reflection.
        $dir = implode(DS, [__DIR__, 'commands', $dataset, $datafile]);
        $tmp = glob($dir . DS . '*.php');
        $res = [];
        foreach($tmp as $file){
            $basename = basename($file, '.php');
            try{
                $class = new \ReflectionClass("g5\\commands\\$dataset\\$datafile\\$basename");
                if($class->implementsInterface("g5\\patterns\\Command")){
                    $res[] = $basename;
                }
            }
            catch(\Exception $e){
                // silently ignore php files present in the directory, but containing errors
                // echo "ERR new \\ReflectionClass(\\"g5\\commands\\$dataset\\$datafile\\$basename\\") \n" . $e->getMessage() . "\n";
            }
        }
        return $res;
    }
    
    // ******************************************************
    /**
        Returns the fully qualified name of a class that will be called for a given triple (dataset, datafile, action).
        Implementation of convention described in docs/code-details.html
        @return Array with 2 elements :
                    - Boolean Indicates if the returned class implements Router or Command
                    - String The class name.
        @throws Exception if the convention is not correctly coded.
        @todo check that the class implements interface Command.
    **/
    public static function getCommandClass($dataset, $datafile, $action){
        // look if class implementing Command exists for the dataset
        $file = self::datasetCommandFilename($dataset);
        if(file_exists($file)){
            $class = self::datasetCommandClassname($dataset);
            return [true, $class];
        }
        // Class for dataset doesn't exist, use default 
        // look if a class corresponding to this action exists
        $class = "g5\\commands\\$dataset\\$datafile\\$action";
        if(class_exists($class)){
            return [false, $class];
        }
        // class not found
        $msg = "BUG : incorrect implementation of command.\n"
            . "  Dataset : $dataset\n"
            . "  Datafile : $datafile\n"
            . "  Action : $action\n";
        throw new \Exception($msg);
    }
    
    
    // ******************************************************
    /** 
        Returns the class name of a dataset's class implementing Router interface.
        Auxiliary of self::getDatafiles() and self::getActionClass().
    **/
    private static function datasetRouterClassname($dataset){
        return implode("\\", ['g5', 'commands', $dataset, ucFirst($dataset) . 'Router']);
    }

    /** 
        Returns the absolute filename of a dataset's class implementing Router interface.
        Auxiliary of self::getDatafiles() and self::getActionClass().
    **/
    private static function datasetRouterFilename($dataset){
        return implode(DS, [__DIR__, 'commands', $dataset, ucFirst($dataset) . 'Router.php']);
    }
    
    
    // ******************************************************
    /** 
        Returns the class name of a dataset's class implementing Command interface.
        Auxiliary of self::getCommandClass().
    **/
    private static function datasetCommandClassname($dataset){
        return implode("\\", ['g5', 'commands', $dataset, ucFirst($dataset) . 'Command']);
    }
    
    /** 
        Returns the absolute path of a dataset's class implementing Command interface.
        Auxiliary of self::getCommandClass().
    **/
    private static function datasetCommandFilename($dataset){
        return implode(DS, [__DIR__, 'commands', $dataset, ucFirst($dataset) . 'Command' . '.php']);
    }
    
    
}// end class
