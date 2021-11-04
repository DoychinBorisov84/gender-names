<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/Classes/DB.php';

// DB Class instance
$db = new DB;

// All the filtered DB-records
$filteredRecords = $db->getFilteredRecords();

?>
<!-- Header -->
<?php include_once(__DIR__.'/templates/header.html'); ?>

  <div class="container">
	
  	<h1 class="d-flex justify-content-center" id="projectName"><<< Filter people names based on their gender >>></h1>

	<!-- Sticky navbar -->
	<nav class="navbar navbar-light bg-light d-flex flex-row justify-content-center" id="header">
		<p class="p-2 text-center">
			The table will fill on each button click with more data. Every click takes a bulk of rows from the DB, split the row data to chunk strings, send those strings for correct name validation in the API used here <a href="https://genderize.io/">Gender API</a>. If the string fills the requirement for a legit name, then it is being saved to our new DB table for filtered names and listed below. The DB table used in our example contains `name` cell, which can consist of 2, 3 or more strings ex "John Doe Smith " or single one like "Nicolas"...
		</p>
		<div class="col text-center">
			<button type="button" class="btn btn-primary p-2" id="data_loader" onclick="postRequest(name=false)">Process names from DB source</button>
		</div>
		<div class="col text-center">
			<p> or search a name you want</p>
		</div>
		<div class="col text-center">
			<input type="search" name="search" class="form-control" id="search_input" placeholder="Enter name...">
			<button type="submit" class="btn btn-warning p-2" id="search_btn" onclick="postRequest(name=true)">Search</button>
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
			<?php foreach($filteredRecords as $value) { ?>
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

	<input class="btn btn-info" type="reset" value="Go To Top" id="scrollTop" onclick="goToTop()">
	<form action="export.php" method="POST" id="exportToCSV">
		<input class="btn btn-danger" type="submit" value="Export CSV" id="exportBtn">
	</form>
	

	<!-- Modal with data -->
	<div id="dataModal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-body">
					<div id="input-data">
						<label for="data-name">Name</label>
						<input type="text" readonly style="font:20px Verdana bold" id="data-name" name="data-name"></input><br/>
						<label for="data-gender">Gender</label>
						<input type="text" readonly style="font:20px Verdana bold" id="data-gender"></input><br/>
						<label for="data-probability">Probability</label>
						<input type="text" readonly style="font:20px Verdana bold" id="data-probability"></input><br/>
						<label for="data-count">Counter</label>
						<input type="text" readonly style="font:20px Verdana bold" id="data-count"></input>
					</div>
					<div id="no-data">
						<p>No more record to load from the source database</p>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Loader animation -->
	<div id="container-loader">
		<div class="yellow"></div>
		<div class="red"></div>
		<div class="blue"></div>
		<div class="violet"></div>
	</div>

  </div>

  <!-- Footer -->
  <?php include_once(__DIR__.'/templates/footer.php'); ?>