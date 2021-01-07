<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'config.php';
include_once 'find.php';

$filtered_names_db = DB_FILTERED_FIRSTNAMES;

$sql_selectFiltered = "SELECT id, firstName, gender, probability, counter FROM $filtered_names_db";

$selectFiltered = $connection->query($sql_selectFiltered);

// echo '<pre>'.print_r($filtered_names, true).'</pre>';

?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Names and gender</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  </head>
  <body>
  	<h1>Filtered names and their gender</h1>

  	<table style="width:100%;border:2px solid black;">
		<tr>
		<th>ID</th>		    
		<th>Name</th>
		<th>Gender</th>
		<th>Probability</th>
		<th>Counter</th>
		</tr>
		<?php foreach($selectFiltered as $value) {
		?>
			<tr>
			<td><?php echo $value['id']; ?></td>
			<td><?php echo $value['firstName']; ?></td>		    
			<td><?php echo $value['gender']; ?></td>	
			<td><?php echo $value['probability']; ?></td>	
			<td><?php echo $value['counter']; ?></td>	
			</tr>
			
		<?php 
			}
		?>
	</table>

	</body>
</html>