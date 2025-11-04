<?php
//sets up database
$DB_HOST = "localhost";
$DB_NAME = "wiki";
$DB_USER = "INFX472";
$DB_PASS = "P*ssword";

define("PEPPER", "a9f5c3b1e7_InfX472_wiki");

$mysqli = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$mysqli) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>