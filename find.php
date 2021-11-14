<?php
require_once __DIR__.'/Classes/Config.php';
require_once __DIR__.'/Classes/DB.php';

// DB Class instance
$db = new DB;

// Config class instance
$config = new Config;

// The request, with the offset for processing DB data -> api -> save DB
$requestType = $_POST['requestType'];

if($requestType != 'database'){
  $arr = explode(' ', $requestType);
  $result = $config->apiCall($arr);
  
  $res2 = json_decode($result)[0];
  echo json_encode($res2); 
}else{
  // DB Object
  $db = new DB;

  // Source DB last record ID
  $sourceDBLastID = $db->getSourceLastRecordId();

  // Filtered DB last record ID
  $lastFilteredPersonID = $db->getFilteredLastRecordId();
  
  // Compare source VS filtered databases
  if($lastFilteredPersonID == $sourceDBLastID){
    echo 'LAST_ROW';
  }else{
    // Get the records for the table
    $dataDB = $db->getSourceData();

    // Filter the db-data(name-row is split into words) vs the api-response for the probability & save to db 
    $filtered_names = [];
    foreach ($dataDB as $row) {
      $single_row_names = explode(' ', $row['firstName']);
    
      // Each db-row as array of chunks send to the api request
        $data = json_decode($config->apiCall($single_row_names));
        
        foreach ($data as $obj) {
          // Check if string-name exists in already processed names
          $exist = $db->checkFilterdExists($obj->name);
          
          // Result array of the api response with high probability !existing in the DB
          if($obj->gender != NULL && $obj->probability >= 0.95 && $exist == null){
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
      // The record is being saved to the DB
      $savedToDatabase = $db->saveFilteredResults($filtered_names);
      
      if($savedToDatabase){
        $newFilteredRecords = $db->getFilteredRecords($lastFilteredPersonID);
        
        // Return the html for appending to the table last row
        if($requestType == 'database'){
          foreach($newFilteredRecords as $arr_ind => $names_arr){
            echo "<tr>
                      <td>"; $config->sanitizeOutput($names_arr['id'], 'string'); echo 
                      "</td>
                      <td>"; $config->sanitizeOutput($names_arr['firstName'], 'string'); echo "</td>
                      <td>"; $config->sanitizeOutput($names_arr['gender'], 'string'); echo "</td>
                      <td>"; $config->sanitizeOutput($names_arr['probability'], 'string'); echo "</td>
                      <td>"; $config->sanitizeOutput($names_arr['counter'], 'string'); echo "</td>
                 </tr>"; 
          }    
        }
      }
    }
  }
}  



