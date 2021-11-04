<!-- JS Bootstrap & jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

<!--JS custom  -->
<script src="js/functions.js" type="text/javascript"> </script>

<!-- JS on-page-ready -->
<script>
	$(document).ready(function() {
		var header = document.getElementById('header');
		var headerOffsetTop = header.offsetTop;

		$('#container-loader').hide();
		
		window.onscroll = function(){
			if (window.pageYOffset > headerOffsetTop) {
				$('#tableHeader').css({'background-color': 'grey', 'opacity': '0.8'});
				$('#scrollTop').insertBefore('table');
				$('#scrollTop').css({'position': 'fixed', 'left': '0px'});
				$('#exportToCSV').insertBefore('table');
				$('#exportToCSV').css({'position': 'fixed', 'right': '0px'});
			} else {
				$('#tableHeader').css({'background-color': '#212529', 'opacity': '1.0'});
				$('#scrollTop').insertAfter('table');
				$('#scrollTop').css({'position': 'relative'});
				$('#exportToCSV').insertAfter('table');
				$('#exportToCSV').css({'position': 'relative'});
			}
		}

	});

</script>

 <!-- Copyright -->
 <footer>
    <div class="text-center p-3" id="footer_div">
        &copy; 2017-<?php echo date("Y"); ?>
        <a class="text-white" href="https://www.linkedin.com/in/doychin-borisov/"> - Doychin Borisov</a>
    </div>
</footer>
  <!-- Copyright -->

	</body>
</html>
