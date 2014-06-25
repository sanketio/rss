<?php

class DataTransaction {

    //========================Constructor for databse connection start=======================
    public function __construct() { // constructor to create the object of the database connection
        $db = new DB_Class(); // creates an object of config file
    }

    //=========================Function Insert Start ======================
    public function insertData($Data, $ViewName) {
        $keystr = "";
        $valstr = "";

        foreach ($Data as $key => $value) {
            $keystr .= "$key,"; // get key string with comma
            $valstr .= "'" . addslashes($value) . "' ,"; // get string of value with comma
        }

        $keys = substr($keystr, 0, -1); // remove last comma from key string
        $vals = substr($valstr, 0, -1); // remove last comma from value string


        $sql = "INSERT INTO " . $ViewName . " (" . $keys . ") VALUES (" . $vals . ")"; // insert values in to
        mysql_set_charset('utf8');
        mysql_query($sql) or die('Error in inserting value');
        return mysql_insert_id();
    }

    //======================Function Insert End =================
    //======================Function Select Start =================
    public function selectdata($ViewName, $Condition) {
        $query = 'SELECT * FROM ' . $ViewName;

        if ($Condition != '') {
            $query .= ' WHERE ' . $Condition;
        }

        mysql_set_charset('utf8');
        $result = mysql_query($query) or die('error');
        $data = array();
        while ($res_array = mysql_fetch_assoc($result)) {
            $data[] = $res_array;
        }
        return $data;
    }

    //==============Function Select End ========================
}
?>