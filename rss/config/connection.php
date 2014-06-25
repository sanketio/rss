<?php

// connection variables
//define('DB_SERVER', 'localhost'); //please enter mysql server name
//define('DB_USERNAME', 'root'); // please enter mysql user name
//define('DB_PASSWORD', ''); // please enter mysql user password
//define('DB_DATABASE', 'rss'); // please enter your database name

define('DB_SERVER', 'mysql8.000webhost.com'); //please enter mysql server name
define('DB_USERNAME', 'a6991009_sanket'); // please enter mysql user name
define('DB_PASSWORD', 'sanketbparmar030210'); // please enter mysql user password
define('DB_DATABASE', 'a6991009_sanket'); // please enter your database name
// connection class to connect with databse with default constructor. so whenever you want to connect to databse, just create one object of this class. as default constructor is created, connection will be made automatically.

class DB_Class {

    function __construct() {
        $connection = mysql_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD) or die('Connection error -> ' . mysql_error());
        mysql_select_db(DB_DATABASE, $connection) or die('Database error -> ' . mysql_error());
    }

}

?>