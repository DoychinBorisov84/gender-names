<?php
require_once 'config.php';

$sql_selectFiltered = "SELECT id, firstName, gender, probability, counter FROM ".DB_FILTERED_FIRSTNAMES;

$selectFiltered = $connection->query($sql_selectFiltered)->fetchAll(PDO::FETCH_ASSOC);

$header = [];
foreach ($selectFiltered as $key => $value) {
    $header[] = array_keys($value);
    break;
}

$filename = 'export.csv';
$outputStream = fopen('php://output', 'w');
header('Content-type: application/csv');
header('Content-Disposition: attachment; filename='.$filename);


fputcsv($outputStream, $header[0]); // the header
foreach ($selectFiltered as $arr) {
    fputcsv($outputStream, $arr);
}

fclose($outputStream);