<?php

class Database {
    var $sql_string = '';
    var $error_no = 0;
    var $error_msg = '';
    public $conn;
    public $last_query;
    private $magic_quotes_active;
    private $real_escape_string_exists;

    function __construct() {
        $this->open_connection();
        $this->magic_quotes_active = false;
        $this->real_escape_string_exists = function_exists("mysqli_real_escape_string");
    }
    

 public function open_connection() {
        $config = require __DIR__ . '/config.php';
        
        $this->conn = new mysqli(
            $config['db_host'],
            $config['db_user'],
            $config['db_pass'],
            $config['db_name']
        );

        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error . 
                "<br>Using: {$config['db_user']}@{$config['db_host']} with database {$config['db_name']}");
        }
        $this->conn->set_charset("utf8mb4");
    }




    function setQuery($sql='') {
        $this->sql_string=$sql;
    }

    function executeQuery() {
        $this->last_query = $this->sql_string;
        $result = $this->conn->query($this->sql_string); 
        $this->confirm_query($result);
        return $result;
    } 

    private function confirm_query($result) {
        if(!$result){
            $this->error_no = $this->conn->errno;
            $this->error_msg = $this->conn->error;
            die("Erreur de requête SQL: " . $this->error_msg . " (Num: " . $this->error_no . ")<br> Requête: " . $this->last_query);
        }
        return $result;
    } 

    function loadResultList( $key='' ) {
        $cur = $this->executeQuery();

        $array = array();
        while ($row = $cur->fetch_object()) {
            if ($key) {
                $array[$row->$key] = $row;
            } else {
                $array[] = $row;
            }
        }
        $cur->free();
        return $array;
    }

    function loadSingleResult() {
        $cur = $this->executeQuery();

        if ($cur && $cur->num_rows > 0) {
            $row = $cur->fetch_object();
            $cur->free();
            return $row;
        }
        return false;
    }

    function getFieldsOnOneTable($tbl_name) {
        $this->setQuery("DESC ".$tbl_name);
        $rows = $this->loadResultList();

        $f = array();
        for ( $x=0; $x<count($rows); $x++ ) {
            $f[] = $rows[$x]->Field;
        }
        return $f;
    }   

    public function fetch_array($result) {
        return $result->fetch_array();
    }

    public function num_rows($result_set) {
        return $result_set->num_rows;
    }

    public function insert_id() {
        return $this->conn->insert_id;
    }

    public function affected_rows() {
        return $this->conn->affected_rows;
    }

    public function escape_value( $value ) {
        if( $this->real_escape_string_exists ) { 
            if($this->magic_quotes_active) { $value = stripslashes($value); }
            $value = $this->conn->real_escape_string($value);
        } else { 
            if( !$this->magic_quotes_active ) { $value = addslashes($value); }
        }
        return $value;
    }

     public function close_connection() {
        if(isset($this->conn)) {
            $this->conn->close(); 
            unset($this->conn);
        }
    }
}

$mydb = new Database();
