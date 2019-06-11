<?php 
/********************************************************************************
    Posgres implementation of {@link DBLink} 
    
    @license    GPL
    @copyright  jetheme.org
    @history    2013-10-02 21:00:34+02:00, Thierry Graff : Creation
    
    @todo         SET client_encoding to 'utf-8';
********************************************************************************/

class DBLink_pg implements DBLink{
    
    /** Connection parameters, in an associative array, always set by the constructor **/
    protected $dbparams = false;
    
    /** Connection object to postgres **/
    protected $dblink = false;
    
    /** Name of the top-level database = pg cluster **/
    protected $tldb = false;
    
    /** Path of the intermediate group hierarchy = pg database **/
    protected $dbgroup = false;
    
    /** db (or terminal db) name = pg schema **/
    protected $dbname = false;
    
    /** May contain a report on execution **/
    protected $report = '';
                                                                
    /** Array of instance variable names with public read access **/
    protected $READABLES = array(
        'tldb',
        'dbgroup',
        'dbname',
        'report',
        'READABLES',
    );
    
    // ******************************************************
    /**
        Creates an object to connect to a database
        @param  $dbparams  assoc array Connection parameters to a postgres db
                required keys :
                - 'dbhost'
                - 'dbport'
                - 'dbuser'
                - 'dbpassword'
                - 'dbpath'
    **/
    public function __construct($dbparams){
        // check params
        if(!isset($dbparams['dbhost'])){
            throw new Exception("Missing parameter \$dbparams['dbhost']");
        }
        if(!isset($dbparams['dbport'])){
            throw new Exception("Missing parameter \$dbparams['dbport']");
        }
        if(!isset($dbparams['dbuser'])){
            throw new Exception("Missing parameter \$dbparams['dbuser']");
        }
        if(!isset($dbparams['dbpassword'])){
            throw new Exception("Missing parameter \$dbparams['dbpassword']");
        }
        if(!isset($dbparams['dbpath'])){
            throw new Exception("Missing parameter \$dbparams['dbpath']");
        }
        // WARNING : rigid behaviour : db path must be at least cluster/database/schema
        $tmp = explode(Storage::SEP, $dbparams['dbpath']);
        if(count($tmp) < 2){
            throw new Exception("Invalid param 'dbpath' : <b>{$dbparams['dbpath']}</b> - postgres need at least 2 components : cluster/database");
        }
        $dbgroup = $tmp[1];       // pg database  = jth db group
        // HERE connect
        try{
            // connect as logged user
            $dsn = "pgsql:host={$dbparams['dbhost']};port={$dbparams['dbport']};user={$dbparams['dbuser']};password={$dbparams['dbpassword']};dbname=$dbgroup";
            $this->dblink = new PDO($dsn);
            $this->dblink->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(Exception $e){
            // connect as default
            $dbparams = Storage::get_dbparams(Users::DEFAULT_USER);
            $dsn = "pgsql:host={$dbparams['dbhost']};port={$dbparams['dbport']};user={$dbparams['dbuser']};password={$dbparams['dbpassword']};dbname=$dbgroup";
            $this->dblink = new PDO($dsn);
            $this->dblink->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        //
        // Here connection ok, fill instance variables
        //
        $this->dbparams = $dbparams;
        $this->tldb = $tmp[0];          // pg cluster   = jth tldb
        $this->dbgroup = $dbgroup;      // pg database  = jth db group
        if(isset($tmp[2])){
            $this->dbname = $tmp[2];    // pg schema    = jth db or terminal db;
        }
        //
        if($this->dbname){
            $this->db_create_if_not_exists($this->dbname);
            $this->db_use($this->dbname);
        }
    }
    
    
    // ******************************************************
    /** 
        Gives read access to {@link $this->READABLES} protected fields
    **/
    public function __get($fieldname){
        if(in_array($fieldname, $this->READABLES)){
            return $this->$fieldname;
        }
    }
    
    
    // ******************************************************
    /** 
        Gives read access to {@link $this->READABLES} protected fields through <code>get*()</code> methods, for ex <code>getReport()</code>
    **/
    public function __call($function, $arguments){
        $candidate = str_replace('get', '', strtolower($function));
        if(in_array($candidate, $this->READABLES)){
            return $this->$candidate;
        }
    }
    
    
    // ******************************************************
    /** See {@link DBLink} **/
    public function query($query){
        return $this->dblink->query($query);
    }
    
    
    // ============================================================================================================
    //                  Transaction management
    // ============================================================================================================
    
    // ******************************************************
    /** See {@link DBLink} **/
    public function beginTransaction(){
        return $this->dblink->beginTransaction();
    }
    
    
    // ******************************************************
    /** See {@link DBLink} **/
    public function commit(){
        return $this->dblink->commit();
    }
    
    
    // ******************************************************
    /** See {@link DBLink} **/
    public function rollback(){
        return $this->dblink->rollBack();
    }
    
    
    // ============================================================================================================
    //                  operations on databases
    // ============================================================================================================
    
    // ******************************************************
    /** See {@link DBLink} ; $path interpreted as a schema name **/
    public function db_exists($path){
        if($path == 'public'){
            return true; // FIX unresolved bug $rst empty for public schema
        }
        $rst = $this->dblink->query("select * from information_schema.schemata where schema_name='" . $path . "'");
        return $rst->rowCount() == 1;
    }

    
    // ******************************************************
    /** See {@link DBLink} ; $path interpreted as a schema name **/
    public function db_use($path){
        $this->dblink->query("set schema '" . $path . "'");
        $this->dbname = $path;
        $this->report .= "\n" . "use terminal db '" . $path . "'";
    }
    
    
    // ******************************************************
    /** See {@link DBLink} ; $path interpreted as a schema name **/
    public function db_create($path){
        $this->dblink->query("create schema " . $path);
        $this->report .= "\n" . 'create schema ' . $path;
    }
    
    
    // ******************************************************
    /** See {@link DBLink} ; $path interpreted as a schema name **/
    public function db_create_if_not_exists($path){
        if($this->db_exists($path)){
            return;
        }
        $this->db_create($path);
    }
    
    
    // ******************************************************
    /** See {@link DBLink} ; $path interpreted as a schema name **/
    public function db_drop($path){
// NOT YET EXECUTED
// @todo implement for a schema and a database
        $this->dblink->query("drop schema cascade " . $path);
        $this->report .= "\n" . 'deleted terminal db ' . $path;
    }
    
    
    // ******************************************************
    /** See {@link DBLink} **/
    public function list_hierarchy($level, $path=''){
        $parts = $path ? explode(Storage::SEP, $path) : [];
//echo "\n<pre>"; print_r($parts); echo "</pre>";
        if(count($parts) != $level - 1){
            throw new Exception("Invalid path : db_list(level=$level, path=$path)");
        }
        switch($level){
        	case '1' :
        	    return $this->list_databases();
            break;
        	case '2' :
        	    // here parts = array(pg db name)
        	    $this->db_use($parts[0]);
        	    return $this->list_schemas();
            break;
        	case '3' :
        	    // here parts = array(pg db name, pg schema name)
        	    $this->db_use($parts[0]);
        	    return $this->list_tables($parts[1]);
        	break;
        }
    }
    
    
    // ******************************************************
    /** Returns a list of databases of the cluster (excepted dbs used by pg for admin)  **/
    protected function list_databases(){
        $rst = $this->dblink->query('SELECT datname FROM pg_database');
        $all = $rst->fetchAll(PDO::FETCH_ASSOC);
        $res = [];
        foreach($all as $db){
            switch($db['datname']){
             	case 'template0' : 
             	case 'template1' : 
             	case 'postgres' : 
                // @todo put these one in DataDBLink
             	case 'autocomplete' : 
             	case 'slugindex' : 
             	case 'timeindex' : 
             	break;
                default:
                    $res[] = $db['datname'];
             } 
        }
        sort($res);
        return $res;
    }
    
    
    // ******************************************************
    /** Returns a list of schemas of the current pg database **/
    private function list_schemas(){
        $rst = $this->dblink->query("select schema_name from information_schema.schemata");
        $all = $rst->fetchAll(PDO::FETCH_ASSOC);
        $res = [];
        foreach($all as $schema){
            $res[] = $schema['schema_name'];
        }
        sort($res);
        return $res;
    }
    
    
    // ******************************************************
    /** Returns a list of tables of the current pg schema **/
    private function list_tables($schema){
        $rst = $this->dblink->query("SELECT table_name FROM information_schema.tables WHERE table_schema='$schema'");
        $all = $rst->fetchAll(PDO::FETCH_ASSOC);
        $res = [];
        foreach($all as $schema){
            // @todo suppress these tables from storage
// @todo remove this line after config suppression
//if($schema['table_name'] == 'infosources' || $schema['table_name'] == 'config') continue;
//
            $res[] = $schema['table_name'];
        }
        sort($res);
        return $res;
    }
    
    
    // ============================================================================================================
    //                  operations on tables
    // ============================================================================================================
    
    
    // ******************************************************
    /** See {@link DBLink} **/
    public function table_exists($table){
        $rst = $this->dblink->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '" . $this->dbname . "' and table_name='" . $table . "'");
        return $rst->rowCount() == 1;
    }
    
    
    // ******************************************************
    /** See {@link DBLink} **/
    public function table_create($table){
        $query = 'create table ' . $this->dbname . '.' . $table . '(';
        $query .= file_get_contents(__DIR__ . DS . 'tabledef' . DS . 'entities');
        $query .= ")";
        $this->dblink->query($query);
        $this->report .= "\n" . 'create table ' . $this->dbname . '.' . $table;
    }
    
    
    // ******************************************************
    /** See {@link DBLink} **/
    public function table_create_if_not_exists($table){
        if($this->table_exists($table)){
            return;
        }
        $this->table_create($table);
    }
    
    
    // ******************************************************
    /** See {@link DBLink} **/
    public function table_drop($table){
        $query = 'drop table ' . $table;
        $this->dblink->query($query);
    }
    
    
    // ******************************************************
    /** See {@link DBLink} **/
    public function table_drop_if_exists($table){
        $query = 'drop table if exists ' . $table;
        $this->dblink->query($query);
    }
    
    
    // ******************************************************
    /** See {@link DBLink} **/
    public function table_count($table, $condition = ''){
        if($condition){
            $where = "where $condition";
        }
        else{
            $where = '';
        }
        $rst = $this->dblink->query("select count(*) from $table $where");
        $count = $rst->fetch(PDO::FETCH_ASSOC);
        return $count['count'];
    }
    
    
    // ============================================================================================================
    //                  operations on data (rows)
    // ============================================================================================================
    
    // ******************************************************
    /**
        See {@link DBLink}
    **/
    public function get($table, $params=[]){
        // put default values to parameters
        foreach(Storage::$SELECT_DEFAULT_PARAMS as $key => $val){
            if(!isset($params[$key])){
                $params[$key] = $val;
            }
        }
        // asc-desc is taken into account only if there is an order by
        if($params['order-by'] != ''){
            if(!isset($params['asc-desc'])){
                $params['asc-desc'] = 'ASC';
            }
            $params['order-by'] .= ' ' . $params['asc-desc'];
        }
        $table_query = str_replace(Storage::SEP, '.', $table);
        $query = 'SELECT ' . $params['fields'] . ' FROM ' . $table_query
            . ($params['where'] != '' ? ' WHERE ' . $params['where'] : '')
            . ($params['group-by'] != '' ? ' GROUP BY ' . $params['group-by'] : '')
            . ($params['order-by'] != '' ? ' ORDER BY ' . $params['order-by'] : '')
            . ($params['limit'] != '' ? ' LIMIT ' . $params['limit'] : '')
            . ($params['offset'] != '' ? ' OFFSET ' . $params['offset'] : '');
//echo "\n<br/>DataDBLink_pg::get() - query = $query";
        if($params['echo-query']){
            echo "\n<br/>$query";
        }
        if($params['log-query']){
            error_log('query = ' . $query);
        }
        // go
        $rst = $this->dblink->prepare($query);
        $rst->execute();
        if($rst->rowCount() == 0){
            return [];
        }
        return $rst->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    // ******************************************************
    /**
        See {@link DBLink}
    **/
    function get_one($table, $params=[]){
        $tmp = $this->get($table, $params);
        return isset($tmp[0]) ? $tmp[0] : [];
    }
    
    
    // ******************************************************
    /**
        See {@link DBLink}
    **/
    public function set($table, $data, $conditions=''){
        // checks if data already exists
        $test = $this->get($table, array('where' => $conditions));
        if(count($test) != 0){
            return $this->update($table, $data, $conditions);
        }
        else{
            return $this->insert($table, $data);
        }
    }
    
    
    // ******************************************************
    /**
        See {@link DBLink}
    **/
    public function update($table, $data, $conditions=''){
        $table = self::table_path2table_name($table);
        $query = 'update ' . $table
            . ' set (' . implode(',', array_keys($data)) . ')'
            . " = (" . implode(',', array_fill(0, count($data), '?')) . ")"
            . ' where ' . $conditions; // @todo possible to put also '?' in $conditions ?
        $sth = $this->dblink->prepare($query);
        $sth->execute(array_values($data));
        return $sth->rowCount();
    }
    
    
    // ******************************************************
    /**
        See {@link DBLink}
    **/
    public function insert($table, $data){
        $table = self::table_path2table_name($table);
        $query = 'insert into ' . $table
            . '(' . implode(',', array_keys($data)) . ')'
            . " values(" . implode(',', array_fill(0, count($data), '?')) . ")";
        $sth = $this->dblink->prepare($query);
        $sth->execute(array_values($data));
        return $sth->rowCount();
    }
    
    
    // ******************************************************
    /**
        See {@link DBLink}
    **/
    public function delete($table, $condition=''){
        if(!$condition){
            throw new Exception('<b>$condition</b> parameter necessary to delete a row - use boolean true to delete all rows');
        }
        $table = self::table_path2table_name($table);
        if($condition === true){
            $query = 'truncate ' . $table;
        }
        else{
            $query = 'delete from ' . $table . ' where ' . $condition;
        }
        $sth = $this->dblink->prepare($query);
        $sth->execute();
        return $sth->rowCount();
    }
    
    
    // ============================================================================================================
    //                              utilities
    // ============================================================================================================
    
    // ******************************************************
    /**
        Converts a table path in jth vocabulary to a table name usable by pg
    **/
    protected static function table_path2table_name(&$table){
        return str_replace(Storage::SEP, '.', $table); // can be "$schema.$table" or "$table"
    }
    
    
}// end class

