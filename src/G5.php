<?php
/********************************************************************************
    Auxiliary code for run.php, Gauquelin5 CLI frontend.
    Provides a generic implementation for namespaces without Router implementation,
    but which respect the convention described in docs/code-details.html.
    
    @license    GPL
    @history    2017-04-27 10:41:02+02:00, Thierry Graff : creation
    @history    2019-05-10 08:22:01+02:00, Thierry Graff : new version
********************************************************************************/
namespace g5;

//use g5\patterns\Command;

class G5{
    
    // ******************************************************
    /**
        Returns a list of data sets known by the program
        = list of sub-directories of transform/
    **/
    public static function getDatasets(){
        return array_map('basename', glob(implode(DS, [__DIR__, 'transform', '*']), GLOB_ONLYDIR));
    }
    
    
    // ******************************************************
    // Simulates implementation of Router 
    /**
        Returns the possible datafiles for a given dataset.
        @todo maybe use reflection if some sub-directories of datasets do not correspond to a datafile sub-package.
    **/
    public static function getDatafiles($dataset){
        // if the dataset has an implementation of interface Dataset, delegate
        $class = self::datasetRouterClassname($dataset);
        if(class_exists($class)){
            return $class::getDatafiles();
        }
        // else return the directories located in the dataset's class directory
        // as the code is psr4, possible to list php files without using reflection.
        $dir = implode(DS, [__DIR__, 'transform', $dataset]);
        return glob($dir);
    }
    
    
    // ******************************************************
    // Simulates implementation of Router 
    /**
        Returns the possible commands for the datafile of a dataset.
        @return Array of strings containing the possible actions.
    **/
    public static function getCommands($dataset, $datafile){
        // if class Actions exists, delegate
        $class = self::datasetRouterClassname($dataset);
        if(class_exists($class)){
            return $class::getCommands($datafile);
        }
        // else return the classes located in the datafile's class directory
        // as the code is psr4, possible to list php files without using reflection.
        $dir = implode(DS, [__DIR__, 'transform', $dataset, $datafile]);
        $tmp = glob($dir . DS . '*.php', GLOB_ONLYDIR);
        $res = [];
        foreach($tmp as $file){
            $res[] = basename($file, '.php');
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
        $class = self::datasetCommandClassname($dataset);
        if(class_exists($class)){
            return [true, $class];
        }
        // Class for dataset doesn't exist, use default 
        // look if a class corresponding to this action exists
        $class = "g5\\transform\\$dataset\\$datafile\\$action";
        if(class_exists($class)){
            return [false, $class];
        }
        // class not found
        $msg = "BUG : incorrect implementation of command.\n"
            . "  Dataset : $dataset\n"
            . "  Datafile : $datafile\n"
            . "  Action : $action\n";
        throw new Exception($msg);
    }
    
    
    // ******************************************************
    /** 
        Returns the class name of a dataset's class implementing Dataset interface.
        Uses the convention described in docs/code-details.html.
        Auxiliary of self::getDatafiles() and self::getActionClass().
    **/
    private static function datasetRouterClassname($dataset){
        return implode("\\", ['g5', 'transform', $dataset, ucFirst($dataset) . 'Router']);
    }
    
    
    // ******************************************************
    /** 
        Returns the class name of a dataset's class implementing Command interface.
        Uses the convention described in docs/code-details.html.
        Auxiliary of self::getCommandClass().
    **/
    private static function datasetCommandClassname($dataset){
        return implode("\\", ['g5', 'transform', $dataset, ucFirst($dataset) . 'Command']);
    }
    
    
}// end class
