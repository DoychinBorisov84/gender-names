<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'config.php';
// include_once 'find.php';

$filtered_names_db = DB_FILTERED_FIRSTNAMES;

$sql_selectFiltered = "SELECT id, firstName, gender, probability, counter FROM $filtered_names_db";

$selectFiltered = $connection->query($sql_selectFiltered);
// echo '<pre>'.print_r($filtered_names, true).'</pre>';

?>
<!-- TODO: make filter button the data from the table and search field, pagination -->
<!-- FLOW
 # index.php list the table data from DB_FILTERED_FIRSTNAMES
 # index.php click the btn -> request to find php
 # find.php ex batch of 10 names (from database with @@@ names) ==> api-endpoint ==> save to db
 # index.php reload with the new records
 # each click return ALL the records(first click 3 rows, second click, 3 + 5 new rows for ex). So we get repeated records. How to get only the newly inserted records?

 #
 -->

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Names and gender</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  </head>
  <body>
  <div class="container">
	
  	<h1 class="d-flex justify-content-center">Filtered names and their gender</h1>

	<p class="lead">
		The table will fill on each button click with more data. The names are being extracted from the DB, send to external api for gender recognition and returned, then the data is being save into the DB and listed here.
	</p>
			
	<div class="row justify-content-center">
		<button type="button" class="btn btn-primary justify-content-center mb-3 mt-1" id="data_loader">Fill the table</button>
	</div>

	<table class="table table-striped table-dark">
		<thead>
			<tr>
			<th scope="col">#</th>
			<th scope="col">Name</th>
			<th scope="col">Gender</th>
			<th scope="col">Probability</th>
			<th scope="col">Counter</th>
			<th scope="col">Dummy Data</th>

			</tr>
		</thead>
		<tbody id="tbody_data">
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
  </div>


  	

<!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script> -->
<script
  src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

<script>
$(document).ready(function() {
	
	$('.container').on('click', '#data_loader', function(){
		
		$.ajax({
			url: 'find.php',
			method: 'POST',
			data: {
				load_db_data: 'load_some_data'
			}, success: function(response){
				console.log(response);
				// alert(response);
				$('#tbody_data').append(response);

			}, complete: function(message){
				console.log(message);
			}, error: function(error){
				console.log(errors);
			}
		});

	}); // on click

}); // document ready
</script>


	</body>
</html>