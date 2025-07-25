<?php
/*
* File: /vintage-photo-blog/includes/db.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file handles the database connection using PDO.
* It creates a reusable database connection object.
*/

require_once 'config.php';

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    
    private $dbh;
    private $error;
    private $stmt;
    
    /**
     * Constructor - Creates a new database connection
     */
    public function __construct() {
        // Set DSN (Data Source Name)
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        
        // Set PDO options
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        );
        
        // Create a new PDO instance
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            echo 'Connection Error: ' . $this->error;
        }
    }
    
    /**
     * Prepare statement with query
     * @param string $query - The SQL query to prepare
     */
    public function query($query) {
        $this->stmt = $this->dbh->prepare($query);
    }
    
    /**
     * Bind values to prepared statement using named parameters
     * @param string $param - Parameter name
     * @param mixed $value - Parameter value
     * @param mixed $type - Parameter type if explicit type binding is needed
     */
    public function bind($param, $value, $type = null) {
        if(is_null($type)) {
            switch(true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }
    
    /**
     * Execute the prepared statement
     * @return boolean - Returns true on success or false on failure
     */
    public function execute() {
        return $this->stmt->execute();
    }
    
    /**
     * Get result set as array of objects
     * @return array - Returns an array containing all of the result set rows
     */
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }
    
    /**
     * Get single record as object
     * @return object - Returns a single result row
     */
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }
    
    /**
     * Get row count
     * @return int - Returns the number of rows affected by the last SQL statement
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    /**
     * Get last inserted ID
     * @return string - Returns the last inserted ID
     */
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
    
    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }
    
    /**
     * End a transaction and commit
     */
    public function endTransaction() {
        return $this->dbh->commit();
    }
    
    /**
     * Cancel a transaction and roll back
     */
    public function cancelTransaction() {
        return $this->dbh->rollBack();
    }
}
?>
