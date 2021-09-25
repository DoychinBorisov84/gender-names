<?php
require_once 'config.php';

// The request, with the offset for processing DB data -> api -> save DB
$request_for_data = $_POST['load_db_data'];
$per_request = $_POST['per_request'];
$offset = $_POST['offset'];

$filtered_names_db = DB_FILTERED_FIRSTNAMES;

// Records per request with limit/offset
$records_limit_offset_sql = "SELECT id, firstName FROM ".DB_NAMES_LIMIT." LIMIT $per_request OFFSET $offset";
$records_limit_offset = $connection->query($records_limit_offset_sql);

// Source DB last record ID
$source_db_lastId_sql = "SELECT id FROM ".DB_NAMES_LIMIT." ORDER BY id DESC LIMIT 1";
$source_db_last_id = $connection->query($source_db_lastId_sql)->fetch(PDO::FETCH_COLUMN, 0);

// Filtered DB last record ID
$filtered_db_lastID_sql = "SELECT person_id FROM ".DB_FILTERED_FIRSTNAMES." ORDER BY id DESC LIMIT 1";
$filtered_db_lastID = $connection->query($filtered_db_lastID_sql)->fetch(PDO::FETCH_COLUMN, 0);

// Compare source VS filtered databases
if($filtered_db_lastID == $source_db_last_id){
  echo 'last_row';
}else{
    // Set the offset | 0
    if($filtered_db_lastID != false){
      $last_record_position_sql = "SELECT COUNT(id) as counter FROM ".DB_NAMES_LIMIT." WHERE id <= $filtered_db_lastID";
      $last_record_position = $connection->query($last_record_position_sql)->fetch(PDO::FETCH_COLUMN);
    }else{
      $last_record_position = 0;
    }
    
    // The result of the query with offset/limit
    $records_limit_offset_sql = "SELECT id, firstName FROM ".DB_NAMES_LIMIT." LIMIT $per_request OFFSET $last_record_position";
    $records_limit_offset = $connection->query($records_limit_offset_sql)->fetchAll(PDO::FETCH_ASSOC);      
      
  // Filter the db-data(name-row is split into words) vs the api-response for the probability & save to db 
  $filtered_names = [];
  foreach ($records_limit_offset as $row) {
    $single_row_names = explode(' ', $row['firstName']);
  
    // Each db-row as array of chunks send to the api request
      $data = json_decode(curl_req($single_row_names));
      
      foreach ($data as $obj) {
        $existSql = "SELECT firstName FROM $filtered_names_db WHERE firstName LIKE '%$obj->name%' ";
        $exist = $connection->query($existSql);
        
        // Result array of the api response with high probability !existing in the DB
        if($obj->gender != NULL && $obj->probability >= 0.95 && $exist->fetchAll() == null){
          array_push($filtered_names, array(
            'name' => $obj->name,
            'gender' => $obj->gender,
            'probability' => $obj->probability,
            'count' => $obj->count,
            'person_id' => $row['id']
            )
          );
        }
      }
  }
  
  // If we got new records for saving into the DB
  if(!empty($filtered_names)){
    // Get the max id record, or use blank(0) as a starting point
    $lastId = $connection->prepare("SELECT MAX(id) AS maxID FROM $filtered_names_db");
    $lastId->execute();
    $lastId_res = $lastId->fetch();
    $max_ID = $lastId_res['maxID'];
    $max_ID = ($max_ID !== NULL ? $max_ID : '0');
      // var_dump($max_ID); die;
  
    $savedToDatabase = saveFilteredToDatabase($connection, $filtered_names_db, $filtered_names);
    
    // The record is being saved to the DB
    if($savedToDatabase){
      $sql_persons = "SELECT id, firstName, gender, probability, counter FROM $filtered_names_db WHERE id > $max_ID";
      $sql_result = $connection->query($sql_persons);
      
      // Return the html for appending to the table last row
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
}


// Send the api request, param is a string chunk, return the response
function curl_req($chunk){
  $api_prepared = ''; // API DOC: https://api.genderize.io/?name[]=peter&name[]=lois&name[]=stevie
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
function saveFilteredToDatabase($connection, $filtered_names_db, $filtered_names_arr){
  foreach ($filtered_names_arr as $key => $value) {
    $name = $value['name'];
    $person_id = $value['person_id'];
    $gender = $value['gender'];
    $probability = $value['probability'];
    $count = $value['count'];

    $sql_filtered_ins = "INSERT INTO $filtered_names_db(firstName, person_id, gender, probability, counter, timestamp) VALUES(:name, :person_id, :gender, :probability, :count, now())";
    
    $request = $connection->prepare($sql_filtered_ins);
    
    $query = $request->execute([':name' => $name, 'person_id' => $person_id, ':gender' => $gender, ':probability' => $probability, ':count' => $count]);
  }

 return $query;
}


