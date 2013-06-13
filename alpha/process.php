<html><head>

</head><body>

	<?php 	
	include 'include/filenames.php';
	include $functionsphp;

	
	if ($_POST['action'] == "0")
		processAnswers();
	elseif ($_POST['action'] == "1")
		processSurveyCreate();
	else
		echo 'No action specified';
	
	
	//foreach ($_POST as $value)
	//	echo $value.'<br>';
	?>

</body></html>