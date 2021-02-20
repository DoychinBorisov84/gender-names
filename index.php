<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
// include_once 'find.php';

$filtered_names_db = DB_FILTERED_FIRSTNAMES;

$sql_selectFiltered = "SELECT id, firstName, gender, probability, counter FROM $filtered_names_db";

$selectFiltered = $connection->query($sql_selectFiltered);
// echo '<pre>'.print_r($filtered_names, true).'</pre>';

?>
<!-- FLOW
 # index.php list the table data from DB_FILTERED_FIRSTNAMES
 # index.php click the btn -> request to find.php
 # find.php ex batch of 10 names (from database with @@@ names) ==> api-endpoint ==> save to db
 # index.php receive html-ajax with the new records
 -->

<!-- TODO: make sort button the data from the table and search field -->
<!-- # TODO: check what num of rows(de-chunked) is good to be passed for requested -> delivered to the FE (25 tested -> 4-5 sec) -->
<!-- # // DONE: spinner/loader till the request succeed -->


<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Names and gender</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
	<link rel="stylesheet" href="style.css" type="text/css">
  </head>
  <body>
  <div class="container">
	
  	<h1 class="d-flex justify-content-center">Filter people names based on their gender</h1>

	<!-- Sticky navbar -->
	<nav class="navbar sticky-top navbar-light bg-light d-flex flex-row justify-content-center header">
		<p class="p-2 text-center">
			The table will fill on each button click with more data. The names are being extracted from the DB, send to external api for gender recognition and returned, then the data is being save into the DB and listed here.
		</p>
		<div class="col text-center">
			<button type="button" class="btn btn-primary p-2" id="data_loader">Process names</button>
		</div>
	</nav>

	<!-- The table with the data -->
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

	<!-- The hidden loader -->
	<div id="container-loader" style="display:none;">
		<div class="yellow"></div>
		<div class="red"></div>
		<div class="blue"></div>
		<div class="violet"></div>
	</div>

  </div>
  	

<script
  src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

<script>
$(document).ready(function() {
	
	offset = 0;
	$('.container').on('click', '#data_loader', function(){
		// Show the loader-adnimation + scroll to top of the div
		$('#container-loader').show();
		var loaderDiv = document.getElementById("container-loader");
		loaderDiv.scrollIntoView();

		$.ajax({
			url: 'find.php',
			method: 'POST',
			data: {
				load_db_data: 'load_some_data',
				per_request: 10,
				offset: offset
			}, success: function(response){
				// console.log(response);				
				// alert(response);
				$('#tbody_data').append(response);
				offset += 10;
				$('#container-loader').hide();	// hide the loader

				// If no more data? 
				// if(response.){}

			}, complete: function(message){
				// console.log(message);
				// console.timeEnd('Clicked');
			}, error: function(error){
				// console.log(errors);
			}
		});

	}); // on click

	var header = document.getElementById('header');
	var headerOffsetTop = header.offsetTop;
	// console.log(sticky);
	
	window.onscroll = function(){
		if (window.pageYOffset > headerOffsetTop) {
			header.classList.add("sticky");
		} else {
			header.classList.remove("sticky");
		}
	}


}); // document ready
</script>


	</body>
</html>