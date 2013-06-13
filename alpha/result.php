<html><head>
	
	<link rel=stylesheet href=style.css TYPE="text/css">
	
</head><body>
	<?php 	
	include 'include/filenames.php';
	include $functionsphp; ?>
	
	
	<div id="maincontainer">
	
		<?php 
		if (array_key_exists('id', $_GET))
			printResults($_GET['id']);
		else echo 'Survey ID invalid';
		
		?>
	
	</div>
	
</body></html>