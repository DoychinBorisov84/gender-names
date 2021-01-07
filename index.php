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
<!-- TODO: make filter button the data from the table and search field, pagination -->

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Names and gender</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  </head>
  <body>
  	<h1 class="d-flex justify-content-center">Filtered names and their gender</h1>

	<table class="table table-striped table-dark">
		<thead>
			<tr>
			<th scope="col">#</th>
			<th scope="col">Name</th>
			<th scope="col">Gender</th>
			<th scope="col">Probability</th>
			<th scope="col">Counter</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($selectFiltered as $value) { ?>
				<tr>
				<td><?php echo $value['id']; ?></td>
				<td><?php echo $value['firstName']; ?></td>		    
				<td><?php echo $value['gender']; ?></td>	
				<td><?php echo $value['probability']; ?></td>	
				<td><?php echo $value['counter']; ?></td>	
				</tr>
			<?php } ?>
		</tbody>
	</table>


  	

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
	</body>
</html>