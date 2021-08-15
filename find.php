<?php
require_once 'config.php';

// The request, with the offset for processing DB data -> api -> save DB
$request_for_data = $_POST['load_db_data'];
$per_request = $_POST['per_request'];
$offset = $_POST['offset'];

$filtered_names_db = DB_FILTERED_FIRSTNAMES;

// All the records from the test-table with for processing(limited records for test)
$all_records_sql = "SELECT firstName FROM ".DB_NAMES_LIMIT;
$all_records = $connection->query($all_records_sql);

// DB records filtered and stored data
$all_records_stored_sql = "SELECT firstName FROM ".DB_FILTERED_FIRSTNAMES;
$all_records_stored = $connection->query($all_records_stored_sql);

// Records with limit,offset from the request passed

$records_limit_offset_sql = "SELECT firstName FROM ".DB_NAMES_LIMIT." LIMIT $per_request OFFSET $offset";

$records_limit_offset = $connection->query($records_limit_offset_sql);
// var_dump($records_limit_offset_sql); die;
// var_dump($records_limit_offset->fetchAll()); die; 


// Filter the db-data(name-row is split into words) vs the api-response for the probability & save to db 
$filtered_names = [];
foreach ($records_limit_offset as $row) {
  $single_row_names = explode(' ', $row['firstName']);

  // Each db-row as array of chunks send to the http request
    $data = json_decode(curl_req($single_row_names));
    
    foreach ($data as $obj) {
      // Push to data-holder-arr, only high probability records into it
      $existSql = "SELECT * FROM $filtered_names_db WHERE firstName LIKE '%$obj->name%' ";
      $exist = $connection->query($existSql);

    // var_dump($exist->fetchAll()); //die;

      if($obj->gender != NULL && $obj->probability >= 0.95 && $exist->fetchAll() == null){
        array_push($filtered_names, array(
          'name' => $obj->name,
          'gender' => $obj->gender,
          'probability' => $obj->probability,
          'count' => $obj->count)
        );
      }
    }
}
// var_dump($filtered_names);

if(!empty($filtered_names)){
  // Get the max id record, or use blank(0) as a starting point
  $lastId = $connection->prepare("SELECT MAX(id) AS maxID FROM $filtered_names_db");
  $lastId->execute();
  $lastId_res = $lastId->fetch();
  $max_ID = $lastId_res['maxID'];
  $max_ID = ($max_ID !== NULL ? $max_ID : '0');
    // var_dump($max_ID); die;

  $savedToDatabase = saveFilteredToDatabase($filtered_names);

  if($savedToDatabase){
    $sql_persons = "SELECT id, firstName, gender, probability, counter FROM $filtered_names_db WHERE id > $max_ID";
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
// var_dump($filtered_names_arr); die;
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


