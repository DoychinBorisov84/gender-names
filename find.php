<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'config.php';

$names_limited = DB_NAMES_LIMIT;
$filtered_names_db = DB_FILTERED_FIRSTNAMES;

$sql = "SELECT firstName FROM $names_limited";
$result = $connection->query($sql);

$api_endpoint = 'https://api.genderize.io?name=';
$filtered_names = [];
foreach ($result as $row) {
  $name_chunks = explode(' ', $row['firstName']);
  // var_dump($name_chunks); exit;
  foreach ($name_chunks as $chunk) {
    # code...
    $data = json_decode(file_get_contents($api_endpoint. urlencode($chunk)));
    // var_dump($data); exit;

    if($data->gender != NULL && $data->probability >= 0.95){
      // $filtered_names[] = $row['firstName'];
      array_push($filtered_names, array(
        'name' => $data->name,
        'gender' => $data->gender,
        'probability' => $data->probability,
        'count' => $data->count)
      );
    }

  }
}

foreach ($filtered_names as $key => $value) {
  $name = $value['name'];
  $gender = $value['gender'];
  $probability = $value['probability'];
  $count = $value['count'];

  $sql_filtered_ins = "INSERT INTO $filtered_names_db(firstName, gender, probability, counter) VALUES(:name, :gender, :probability, :count)";

  $request = $connection->prepare($sql_filtered_ins);

  $query = $request->execute([':name' => $name, ':gender' => $gender, ':probability' => $probability, ':count' => $count]);
  
}

return $query;

// echo '<pre>'.print_r($filtered_names, true).'</pre>';


