<?php

/**
 * Handling database connection
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class DbConnect {

    private $conn;

    function __construct() {
        
    }

    /**
     * Establishing database connection
     * @return database connection handler
     */
    function connect() {
        //__FILE__ คือ db_connect.php
        //dirname(__FILE__) คือ ดึงชื่อ Folder ของไฟล์ที่เรียกใช้คำสั่ง 
        include_once dirname(__FILE__) . '/config.php';
        // Connecting to mysql database
        $this->conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

        // Check for database connection error
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        
        /* change character set to utf8 */
        $this->conn->set_charset("utf8");

        // returing connection resource
        return $this->conn;
    }

}

?>
