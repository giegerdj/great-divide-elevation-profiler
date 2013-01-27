<?php

class DatabaseConnection {
    
    public $db_conn;
    
    private static $config = array(
        'username' => DB_USER,
        'password' => DB_PASSWORD,
        'host' => DB_HOST,
        'db' => DB_NAME,
        'errmode' => DB_ERRMODE
    );
    
    public static function getInstance() {
        static $instance = null;
       
        if ($instance === null && !is_null(self::$config) ) {
            $instance = new DatabaseConnection(self::$config);
        }
        return $instance;
    }
    
    private function _connect() {
        
        try {
            $this->db_conn = new PDO(
                'mysql:host='.self::$config['host'].';dbname=' . self::$config['db'] . ';charset=UTF8',
                self::$config['username'],
                self::$config['password']
            );
            $this->db_conn->setAttribute(PDO::ATTR_ERRMODE, self::$config['errmode']);
        } catch (PDOException $e) {
            error_log("couldn't connect to the db");
            error_log( $e->getMessage() );
            throw new ESRGD\DBException();
        }
        
        return true;
    }
    
    private function __construct($db) {
        self::$config = $db;
        $this->_connect();
    }
}