<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Database name
 */
define('DB_NAME', 'people_names');

/**
 * Database user
 */
define('DB_USER', 'root');

/**
 * Database pass 
 */
define('DB_PASSWORD', '');

/**
 *  * Database host
 */
define('DB_HOST', 'localhost');

/**
 *  * Database main table
 */
define('DB_NAMES', 'people');

/**
 *  * Database table of 50 people
 */
define('DB_NAMES_LIMIT', 'people_limit_50');

/**
 *  * Database table for the filtered names
 */
define('DB_FILTERED_FIRSTNAMES', 'filtered_names');

/**
 * 
 * @return array
 * Pretty array print
 */
function print_pr($arr){
 echo '<pre>'.print_r($arr, true).'</pre>';
}

/**
 * Error report on 
 * 
 */ 
function errorReport(){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}


try {
    $connection = new PDO("mysql:host=localhost;dbname=people_names", DB_USER, DB_PASSWORD);
} catch (PDOException $ex) {
    echo $ex->getMessage();
}
