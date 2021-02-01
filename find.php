<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'config.php';

$request_for_data = $_POST['load_db_data'];
$per_request = $_POST['per_request'];
$offset = $_POST['offset'];

$names_limited = DB_NAMES_LIMIT;
$filtered_names_db = DB_FILTERED_FIRSTNAMES;

// All the records from the test-table with limited records
$sql = "SELECT firstName FROM $names_limited";
$records_db = $connection->query($sql);
// var_dump($records_db->fetchAll()); exit;

// -------------------------------------------------
// TODO: click btn -> limit/offset per request(x10, x20) -> when reached last record from DB - display message for no more data, and don't  process more requests

// $all_records = count($records_db->fetchAll());
// $all_pages = ceil($all_records/$per_request); // all-records / per requested records num

$sql_test = "SELECT firstName FROM $names_limited LIMIT $per_request OFFSET $offset"; 
$request_test = $connection->query($sql_test);
// var_dump($request_test->fetchAll()); exit;

// -------------------------------------------------

$filtered_names = [];
// Filter the db-data(name-row is split into words) vs the api-response for the probability & save to db 
foreach ($request_test as $row) {
  $single_row_names = explode(' ', $row['firstName']);

  // Each db-row as array of chunks send to the http request
    $data = json_decode(curl_req($single_row_names));
    
    foreach ($data as $obj) {
      // Push to data-holder-arr, only high probability records into it
      if($obj->gender != NULL && $obj->probability >= 0.95){
        array_push($filtered_names, array(
          'name' => $obj->name,
          'gender' => $obj->gender,
          'probability' => $obj->probability,
          'count' => $obj->count)
        );
      }
    }
}


if(!empty($filtered_names)){
  // Get the max id record, or use blank(0) as a starting point
  $lastId = $connection->prepare("SELECT MAX(id) AS maxID FROM $filtered_names_db");
  $lastId->execute();
  $lastId_res = $lastId->fetch();
  $max_ID = $lastId_res['maxID'];
  $max_ID = ($max_ID !== NULL ? $max_ID : '0');
    // var_dump($max_ID); //exit;

  $savedToDatabase = saveFilteredToDatabase($filtered_names);

  if($savedToDatabase){
    $sql_persons = "SELECT id, firstName, gender, probability, counter FROM $filtered_names_db WHERE id>$max_ID";
    $sql_result = $connection->query($sql_persons);

    if($request_for_data == 'load_some_data'){
      foreach($sql_result as $arr_ind => $names_arr){
        echo "<tr>
                  <td>".$names_arr['id']."</td>
                  <td>".$names_arr['firstName']."</td>
                  <td>".$names_arr['gender']."</td>
                  <td>".$names_arr['probability']."</td>
                  <td>".$names_arr['counter']."</td>
             </tr>"; 
      }    
    }
    
  }
}

// Send the api request, param is a string chunk, return the response
function curl_req($chunk){
  $api_prepared = ''; // https://api.genderize.io/?name[]=peter&name[]=lois&name[]=stevie
  foreach ($chunk as $name_string) {
    $api_prepared .= 'name[]='.$name_string.'&';
  }
  // var_dump($api_prepared); //exit;
  $api_endpoint = "https://api.genderize.io?$api_prepared"; // SINGLE $api_endpoint = "https://api.genderize.io?name=$api_prepared";
  $curl = curl_init($api_endpoint);
  
  curl_setopt($curl, CURLOPT_HEADER, 0);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  $res_req = curl_exec($curl);
  return $res_req;
}

// Save to the db the filtered records
function saveFilteredToDatabase($filtered_names_arr){
  global $connection;
  global $filtered_names_db;

  foreach ($filtered_names_arr as $key => $value) {
    $name = $value['name'];
    $gender = $value['gender'];
    $probability = $value['probability'];
    $count = $value['count'];

    $sql_filtered_ins = "INSERT INTO $filtered_names_db(firstName, gender, probability, counter, timestamp) VALUES(:name, :gender, :probability, :count, now())";
    
    $request = $connection->prepare($sql_filtered_ins);
    
    $query = $request->execute([':name' => $name, ':gender' => $gender, ':probability' => $probability, ':count' => $count]);
  }

 return $query;
}


