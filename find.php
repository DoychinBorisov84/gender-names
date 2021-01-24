<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'config.php';

$request_for_data = $_POST['load_db_data'];


$names_limited = DB_NAMES_LIMIT;
$filtered_names_db = DB_FILTERED_FIRSTNAMES;

$sql = "SELECT firstName FROM $names_limited";
$records_db = $connection->query($sql);

$lastInsertedId = '';

$filtered_names = [];
// Filter the db-data(name-strings are split into words) vs the api-response for the probability & save to db 
foreach ($records_db as $row) {
  $name_chunks = explode(' ', $row['firstName']);

  // Each db-row as chunk
  foreach ($name_chunks as $chunk) {
    $data = json_decode(curl_req($chunk));
    
    // Push to the arr, only high probability records
    if($data->gender != NULL && $data->probability >= 0.95){
      array_push($filtered_names, array(
        'name' => $data->name,
        'gender' => $data->gender,
        'probability' => $data->probability,
        'count' => $data->count)
      );
    }
  }
}


if(!empty($filtered_names)){
  // Get the max id record, or use blank(0) as a starting points
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
                  <td>".$lastInsertedId."</td>
             </tr>"; 
      }    
    }
    
  }
}

// Send the api request, param is a string chunk, return the response
function curl_req($chunk){
  $api_endpoint = "https://api.genderize.io?name=$chunk";
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


