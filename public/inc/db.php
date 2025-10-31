
<?php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'dragonstone');
define('DB_USER', 'root');
define('DB_PASS', 'Longos@1963');

function db(){
    static $conn = null;
    if ($conn === null){
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) die('DB connection error: '.$conn->connect_error);
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

