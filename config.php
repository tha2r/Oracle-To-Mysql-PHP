<?php

// Oracle Database Connection
$oracle_host = 'your_oracle_host';      // Oracle server hostname or IP address
$oracle_username = 'your_oracle_username';  // Oracle database username
$oracle_password = 'your_oracle_password';  // Oracle database password
$oracle_dbname = 'your_oracle_dbname';  // Oracle database name

// MySQL Database Connection
$mysql_host = 'your_mysql_host';        // MySQL server hostname or IP address
$mysql_username = 'your_mysql_username';  // MySQL database username
$mysql_password = 'your_mysql_password';  // MySQL database password
$mysql_dbname = 'your_mysql_dbname';    // MySQL database name

// Connect to Oracle database
$oracle_conn = oci_connect($oracle_username, $oracle_password, $oracle_host.'/'.$oracle_dbname, 'AL32UTF8');

if (!$oracle_conn) {
    $e = oci_error();
    die("Oracle connection failed: " . $e['message']);
}
// Set the Oracle session date format
$oracle_stmt = oci_parse($oracle_conn, "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD'");
oci_execute($oracle_stmt);


// Connect to MySQL database
$mysql_conn = new mysqli($mysql_host, $mysql_username, $mysql_password, $mysql_dbname);

// Check connection
if ($mysql_conn->connect_error) {
    die("MySQL connection failed: " . $mysql_conn->connect_error);
}

// Set character set to UTF-8
if (!$mysql_conn->set_charset("utf8mb4")) {
    die("Error loading character set utf8mb4: " . $mysql_conn->error);
}
