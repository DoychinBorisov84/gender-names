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
// var_dump($records_db->fetchAll()); exit;
$lastInsertedId = '';

$filtered_names = [];
// Filter the db-data(name-strings are split into words) vs the api-response for the probability & save to db 
foreach ($records_db as $row) {
  $single_row_names = explode(' ', $row['firstName']);
  // var_dump($row); exit;
  // Each db-row as chunk
  // TODO: send all chunks in one cURL, the api can handle up to 10 params as &name[]=
  // foreach ($single_row_names as $chunk) {
    // echo 'start'; $time_start = microtime(true); 

    $data = json_decode(curl_req($single_row_names));
    // echo 'finish'; $time_end = microtime(true);
    // $timeo = $time_end - $time_start; echo $timeo/60;
    // var_dump($data);
    //  exit;
    
     foreach ($data as $obj) {
      //  var_dump($obj); exit;
       # code...
       // Push to the arr, only high probability records
       if($obj->gender != NULL && $obj->probability >= 0.95){
         array_push($filtered_names, array(
           'name' => $obj->name,
           'gender' => $obj->gender,
           'probability' => $obj->probability,
           'count' => $obj->count)
         );
       }
     }
  // }
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


