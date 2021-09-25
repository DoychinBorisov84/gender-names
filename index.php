<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

$sql_selectFiltered = "SELECT id, firstName, gender, probability, counter FROM ".DB_FILTERED_FIRSTNAMES;

$selectFiltered = $connection->query($sql_selectFiltered);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Gender type for people names</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
	<link rel="stylesheet" href="style.css" type="text/css">
  </head>
  <body>
  <div class="container">
	
  	<h1 class="d-flex justify-content-center" id="projectName">Filter people names based on their gender</h1>

	<!-- Sticky navbar -->
	<nav class="navbar sticky-top navbar-light bg-light d-flex flex-row justify-content-center" id="header">
		<p class="p-2 text-center">
			The table will fill on each button click with more data. The names are being extracted from source database table with names, send to external api for gender recognition and returned, then the data is being save into the DB and listed here. The table row contains `name`, which can consist of 2, 3 or more strings ex "John Doe Smith " or single one like "Nicolas"...
		</p>
		<div class="col text-center">
			<button type="button" class="btn btn-primary p-2" id="data_loader">Process names</button>
		</div>
	</nav>

	<!-- The table with the data -->
	<table class="table table-striped table-dark">
		<thead class="sticky-top" id="tableHeader">
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

	<input class="btn btn-info" type="reset" value="Scroll To Top" id="scrollTop">

	<!-- The hidden loader -->
	<div id="container-loader" style="display:none;">
		<div class="yellow"></div>
		<div class="red"></div>
		<div class="blue"></div>
		<div class="violet"></div>
	</div>

	<!-- Modal -->
	<div id="myModal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content" style="background-color: #bfbfbf;">
			<div class="modal-body" style="padding:25px">
				<p style="font:20px Verdana bold">No more record to load from the source database</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal" style="font-family: Verdana;">Close</button>
			</div>
		</div>
	</div>
	</div>

  </div>

  <!-- Javascript -->
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
				if(response == 'last_row'){
					$("#myModal").modal('show');
				}else{
					$('#tbody_data').append(response);
					offset += 10;
				}
				$('#container-loader').hide();	// hide the loader
			}, complete: function(message){},
			 error: function(error){}
		});

	}); // on click

	// TODO: apply for the 1) table thead 2) Scroll To Top btn
	var header = document.getElementById('header');
	var tableHeader = document.getElementById('tableHeader');

	var headerOffsetTop = header.offsetTop;
	var tableHeaderOffsetTop = tableHeader.offsetTop;

	var projectNameH1 = document.getElementById('projectName');
	var projectNameH1OffsetTop = projectNameH1.offsetTop;

	// console.log(headerOffsetTop);
	
	window.onscroll = function(){
		if (window.pageYOffset > headerOffsetTop) {
			// tableHeader.classList.add("sticky");
			header.classList.add("sticky");
		} else {
			// tableHeader.classList.remove("sticky");
			header.classList.remove("sticky");
		}
	}

	// Scroll To Top
	$("#scrollTop").click(function()
	{
		jQuery('html,body').animate({scrollTop:0},1000);
	})

	}); // document ready
</script>

	</body>
</html>