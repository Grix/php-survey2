<?php

//Abandon hope all ye who enter here





//These two functions converts potentially hazardous strings to sql or html safe strings
function sqlsecure($str)
{
	$sql = connectMySQL();
	return mysqli_real_escape_string($sql, trim($str));
}

function htmlsecure($str)
{
	return htmlspecialchars(trim($str));
}


function connectMySQL()
{
	//Attempts to set up a connection to the MySQL server defined in settings.php
	//Returns mysql connection handler if successful, an error string otherwise
	
	include 'include/filenames.php';
	include $settingsphp;
	
	$sql = mysqli_connect($server, $un, $pw);
	if (!$sql)
		{
		return mysqli_error($sql);
		}
	$temp = mysqli_select_db($sql, $db);
	if (!$temp)
		{
		return mysqli_error($sql);
		}
	return $sql;
}

function printQuestion($sid, $qid)
{
	//Print a question with survey id $sid and question id $qid
	//Returned bool determines success
	
	$sql = connectMySQL();
	
		$resulttype = mysqli_query($sql, "SELECT type from survey_".sqlsecure($sid)." WHERE qid=".sqlsecure($qid));
		
		$aresulttype = mysqli_fetch_row($resulttype);
		
		if ($aresulttype == NULL)
			{
			return 0;
			}
			
		//if question is radio:
		if ($aresulttype[0] == 'radio')
			{
			$result = mysqli_query($sql, "SELECT text from survey_".sqlsecure($sid)." WHERE qid=".sqlsecure($qid));

			while ($row = mysqli_fetch_array($result, MYSQL_ASSOC)) 
				{
				$aresult[] =  htmlsecure($row['text']);  
				}
			//Finds and prints title and head
			echo '<div id="questioncontainer"> 
			<input type="hidden" name="type_'.htmlsecure($qid).'" value="radio">
			';
			
			$aid = 0;			
			foreach ($aresult as $text)
				{
				//Prints each question
				if ($aid == 0)
					echo '<div id="qtitle">'.$text.'</div><div id="answercontainer">';
				else
					echo '<div id="multipleoptions"><input type="radio" name="'.htmlsecure($qid).'" value='.$aid.'>'.$text.'</div>
					';
				$aid++;
				}
			$aid--;
			echo '<input type="hidden" name="acount_'.htmlsecure($qid).'" value='.$aid.'>';
				
			echo '</div></div>';
			}
		//if question is multiple options (checkbox):
		if ($aresulttype[0] == 'multi')
			{
			$result = mysqli_query($sql, "SELECT text from survey_".sqlsecure($sid)." WHERE qid=".sqlsecure($qid));

			while ($row = mysqli_fetch_array($result, MYSQL_ASSOC)) 
				{
				$aresult[] =  htmlsecure($row['text']);  
				}
			//Finds and prints title and head
			echo '<div id="questioncontainer"> ';
			
			$aid = 0;			
			foreach ($aresult as $text)
				{
				//Prints each question
				if ($aid == 0)
					echo '<div id="qtitle">'.$text.'</div><div id="answercontainer"> 
					<input type="hidden" name="type_'.htmlsecure($qid).'" value="multi">
					';
				else
					echo '<div id="multipleoptions"><input type="checkbox" name="'.htmlsecure($qid).'_'.$aid.'" value=1>'.$text.'</div>
					';
				$aid++;
				}
			$aid--;
			echo '<input type="hidden" name="acount_'.htmlsecure($qid).'" value='.$aid.'>';
				
			echo '</div></div>';
			}
	
	echo mysqli_error($sql);
	
	mysqli_close($sql);
	
	return 1;
		
}

function printSurvey($sid)
{
	include 'include/filenames.php';
	include $settingsphp;
	
	//Prints a survey with id $sid
	//Returned bool determines success
	
	//Check if survey ID is OK
	if (!is_numeric($sid))
		{
		echo 'Survey ID is invalid';
		return 0;
		}
	
	$sql = connectMySQL();
	
	$result = mysqli_query($sql, "SELECT name,text from surveys WHERE id=".sqlsecure($sid));
			
		$aresult = mysqli_fetch_array($result);
		if (!$aresult)
			{
			echo 'Survey ID '.htmlsecure($sid).' not found';
			return 0;
			}
	
	//Prints head of survey
	echo '<div id="surveycontainer">
	<div id="stitle">'.htmlsecure($aresult['name']).'</div>
	<div id="sdescription">'.htmlsecure($aresult['text']).'</div>
	<form method="POST" action="'.$processphp.'">
	<input type="hidden" name="sid" value='.htmlsecure($sid).'>
	<input type="hidden" name="action" value="0">';
	
		$count = 0;
		while (true)
			{
			//Print each question
			if (!printQuestion($sid, $count))
				break;
			$count++;
			}
	
	//Prints legs of survey
	if ($recaptcha_enable)
		{
		require_once($recaptchaphp);
		echo '<div id="submit">'.recaptcha_get_html($recaptcha_pubkey).'</div>';
		}
	
	echo '<div id="submit"><input type="submit" value="Submit"></div>';
	echo '</form></div>';
	
	echo mysqli_error($sql);
	
	mysqli_close($sql);
		
}

function installMySQL()
{
	//Attempts to set up the MySQL database for poll use and create sample surveys
	//Returns 1 if successful, an error string otherwise
	
	$sql = connectMySQL();
	
		mysqli_query($sql, "CREATE TABLE surveys (id INT, name TEXT, text TEXT)");
		mysqli_query($sql, "INSERT INTO surveys VALUES (0, 'Test Survey 1', 'Description of Survey')");
		mysqli_query($sql, "INSERT INTO surveys VALUES (1, 'Test Survey 2', 'Description of Survey')");
		
		mysqli_query($sql, 'CREATE TABLE answers_0 (rid INT, qid INT, aid INT, value BOOL)');
		mysqli_query($sql, "CREATE TABLE survey_0 (qid INT, aid INT, text TEXT, type TEXT)");
		mysqli_query($sql, "INSERT INTO survey_0 VALUES (0, 0, 'Question 1: Radio Buttons', 'radio')");
		mysqli_query($sql, "INSERT INTO survey_0 VALUES (0, 1, 'Answer 1_1', '')");
		mysqli_query($sql, "INSERT INTO survey_0 VALUES (0, 2, 'Answer 1_2', '')");
		mysqli_query($sql, "INSERT INTO survey_0 VALUES (0, 3, 'Answer 1_3', '')");
		mysqli_query($sql, "INSERT INTO survey_0 VALUES (1, 0, 'Question 2: Mulitple Choice', 'multi')");
		mysqli_query($sql, "INSERT INTO survey_0 VALUES (1, 1, 'Answer 2_1', '')");
		mysqli_query($sql, "INSERT INTO survey_0 VALUES (1, 2, 'Answer 2_2', '')");
		mysqli_query($sql, "INSERT INTO survey_0 VALUES (1, 3, 'Answer 2_3', '')");
		
		mysqli_query($sql, 'CREATE TABLE answers_1 (rid INT, qid INT, aid INT, value BOOL)');
		mysqli_query($sql, "CREATE TABLE survey_1 (qid INT, aid INT, text TEXT, type TEXT)");
		mysqli_query($sql, "INSERT INTO survey_1 VALUES (0, 0, 'Question 1: Radio Buttons', 'radio')");
		mysqli_query($sql, "INSERT INTO survey_1 VALUES (0, 1, 'Answer 1_1', '')");
		mysqli_query($sql, "INSERT INTO survey_1 VALUES (0, 2, 'Answer 1_2', '')");
		mysqli_query($sql, "INSERT INTO survey_1 VALUES (0, 3, 'Answer 1_3', '')");
		mysqli_query($sql, "INSERT INTO survey_1 VALUES (1, 0, 'Question 2: Mulitple Choice', 'multi')");
		mysqli_query($sql, "INSERT INTO survey_1 VALUES (1, 1, 'Answer 2_1', '')");
		mysqli_query($sql, "INSERT INTO survey_1 VALUES (1, 2, 'Answer 2_2', '')");
		mysqli_query($sql, "INSERT INTO survey_1 VALUES (1, 3, 'Answer 2_3', '')");
	
	//TODO check if ok and echo error
	
	echo mysqli_error($sql);
	
	mysqli_close($sql);
	
}
	
function createPoll()
{
	//Creates a poll

	
}

function printResults($sid)
{
	//Prints results from a survey with id $sid into super neat graphs and stuff

	//Check if survey ID is OK
	if (!is_numeric($sid))
		{
		echo 'Survey ID is invalid';
		return 0;
		}

	$sql = connectMySQL();

		$result = mysqli_query($sql, "SELECT name,text FROM surveys WHERE id=".sqlsecure($sid));							

		$aresult = mysqli_fetch_array($result);
		if (!$aresult)
			{
			echo 'Survey ID '.htmlsecure($sid).' not found';
			return 0;
			}	
		$name = htmlsecure($aresult['name']);
		$desc = htmlsecure($aresult['text']);
		
		echo '<div id="surveycontainer"><div id="stitle">'.$name.'</div><div id="sdescription">'.$desc.'</div>';
		
		$respresult = mysqli_query($sql, "SELECT MAX(rid) FROM answers_".sqlsecure($sid));
		$resprow = mysqli_fetch_array($respresult);
		$maxrid = $resprow['MAX(rid)'];
		
		echo '<div id="sdescription">Number of Respondents: '.$maxrid.'</div>';
		
		//Print each question
		$inforesult = mysqli_query($sql, "SELECT qid,text,type FROM survey_".sqlsecure($sid)." WHERE aid=0");
		while ($inforow = mysqli_fetch_array($inforesult))
			{
			$qid = $inforow['qid'];
			$result = mysqli_query($sql, "SELECT aid,SUM(value),MAX(rid) FROM answers_".sqlsecure($sid)." WHERE qid=".sqlsecure($qid)." GROUP BY aid");
			
			echo '<div id="questioncontainer"><div id="qtitle">'.htmlsecure($inforow['text']);
			//echo ' ('.$inforow['type'].')';
			echo '</div><div id="answercontainer">';		
			
			while($row = mysqli_fetch_array($result))
				{
				$percentage = htmlsecure(round(intval($row['SUM(value)']) / intval($row['MAX(rid)']) * 100));
				$info2result = mysqli_query($sql, "SELECT text FROM survey_".sqlsecure($sid)." WHERE qid=".sqlsecure($qid)." AND aid=".htmlsecure($row['aid']));
				$info2row = mysqli_fetch_array($info2result);
				echo '<div id="multipleoptions"><div id="texthalf">'.htmlsecure($info2row['text']);
				echo '</div><div id="graphborder"><div id="graph_'.htmlsecure($qid."_".$row['aid']).'"><div id="textpercent">'.$percentage.'%</div></div></div></div>';
				echo '<script>document.getElementById("graph_'.htmlsecure($qid."_".$row['aid']).'").style.width="'.$percentage.'%";</script>';				
				}
				
			echo "</div></div>";
			}
			
		echo '</div>';
		
	//I might have lied about the graphs (TODO)
			
	echo mysqli_error($sql);
	
	mysqli_close($sql);
	
	return 1;
			
}
	
function processAnswers()
{
	//Inserts values from a completed survey into the appropriate answer MySQL table
	//Returned bool determines success
	
	include 'filenames.php';
	include $settingsphp;
	
	if (!array_key_exists('sid',$_POST))
		{
		echo 'No survey ID given';
		return 0;
		}
		
	
	include $settingsphp;	
	if ($recaptcha_enable)
		{
		require_once($recaptchaphp);
		$recaptcha_test = recaptcha_check_answer ($recaptcha_privkey,
									$_SERVER["REMOTE_ADDR"],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]);
		if (!$recaptcha_test->is_valid) 
			{
			echo "You misstyped the captcha, go back and try again. Unless you're a bot, in that case go away.";
			exit();
			}
		}
		
		
		
	$sql = connectMySQL();
		
		//Get unique respondent id, increment from current highest
		$result = mysqli_query($sql, 'SELECT max(rid) from answers_'.$_POST['sid']);
		$aresult = mysqli_fetch_row($result);
		$rid = $aresult[0];
		$rid++;
		
		$thisVariableIsUseless = 'Waste of space';
		
		$qid = 0;
		while (true)
			{	
			//Loop for each question, breaks if question doesn't exist
			if (array_key_exists("type_$qid", $_POST) === FALSE)
				break;
			if ($_POST["type_$qid"] == 'radio')
				{
				$aid = 1;
				while ($aid <= $_POST["acount_$qid"])
					//Loop for each answer if the type of question is radio buttons
					{
					mysqli_query($sql, 'INSERT INTO answers_'.$_POST['sid'].' VALUES ('.$rid.', '.$qid.', '.$aid.', 0)');
					$aid++;
					}
				$temptest= mysqli_query($sql, 'UPDATE answers_'.$_POST['sid'].' SET value=1 WHERE rid='.$rid.' AND qid='.$qid.' AND aid='.$_POST[$qid]);
				if ($temptest === FALSE)
					{
					echo '<br><br>Something went wrong. You probably left a question blank. Go back and try again.';
					exit();
					}
				}
			if ($_POST["type_$qid"] == 'multi')
				{
				$aid = 1;
				while ($aid <= $_POST["acount_$qid"])
					//Loop for each answer if the type of question is multiple options
					{
					if (array_key_exists($qid.'_'.$aid, $_POST))						
						mysqli_query($sql, 'INSERT INTO answers_'.$_POST['sid'].' VALUES ('.$rid.', '.$qid.', '.$aid.', 1)');
					else
						mysqli_query($sql, 'INSERT INTO answers_'.$_POST['sid'].' VALUES ('.$rid.', '.$qid.', '.$aid.', 0)');
					$aid++;
					}
				}

			$qid++;
			}
		
	echo mysqli_error($sql);
	
	mysqli_close($sql);
	
	echo '<p>Survey completed!';
	echo '</p><p>See the results: <a href="'.$baseurl.$resultphp.'?id='.$_POST['sid'].'">'.$baseurl.$resultphp.'?id='.$_POST['sid'].'</a><br>';
	echo 'Back to survey: <a href="'.$baseurl.$surveyphp.'?id='.$_POST['sid'].'">'.$baseurl.$surveyphp.'?id='.$_POST['sid'].'</a></p>';
	
	return 1;
}

function printSurveyCreate()
{
	
	//Prints a form for a user to create a survey
	//Returned bool determies success
	
	//Print form head and first question
	echo '<div id="surveycontainer">
	<form method="POST" action="'.$processphp.'">
	<input type="hidden" name="action" value="1">
	<div id="stitle">Create a Survey</div>
	<div id="sdescription">Survey Name: <input type="text" size="50" name="title"></div>
	<div id="sdescription">Survey Description: <textarea name="desc" rows="3" columns="50"></textarea></div>
	<div id="surveycreatecontainer"></div>
	';
	
	//Print "add more questions" button
	echo '<div id="submit"><button type="button" onclick="addQuestion()">Add Another Question!</button></div>';
	
	//Print form end
	echo '<div id="submit"><input type="submit" value="I\'m Finished, Create My Survey!"></div>';
	echo '</form></div>';
	
		
}

function processSurveyCreate()
{
	//Prints a form for a user to create a survey
	//Returned bool determies success
	
	include 'filenames.php';
	include $settingsphp;
	
	$sql = connectMySQL();

		//create neccessary tables and indexes
		$result = mysqli_query($sql, "SELECT MAX(id) FROM surveys");
		if (!$result)
			echo 'Unable to query mysql database, contact webmaster. Error info: <br><br>'.mysqli_error($sql);
		$aresult = mysqli_fetch_array($result);
		$sid = intval(sqlsecure($aresult['MAX(id)']))+1;
		
		$query = mysqli_query($sql, "INSERT INTO surveys VALUES (".$sid.", '".sqlsecure(htmlsecure($_POST['title']))."', '".sqlsecure(htmlsecure($_POST['desc']))."')");
		if (!$query)
			echo 'Unable to insert into mysql database, contact webmaster. Error info: <br><br>'.mysqli_error($sql);
			
		$query = mysqli_query($sql, 'CREATE TABLE answers_'.$sid.' (rid INT, qid INT, aid INT, value BOOL)');
		if (!$query)
			echo 'Unable to create mysql table, contact webmaster. Error info: <br><br>'.mysqli_error($sql);
			
		$query = mysqli_query($sql, "CREATE TABLE survey_".$sid." (qid INT, aid INT, text TEXT, type TEXT)");
		if (!$query)
			echo 'Unable to create mysql table, contact webmaster. Error info: <br><br>'.mysqli_error($sql);
		
		//insert values into survey table
		foreach ($_POST as $key => $value)
			{
			if (strrpos($key, 'question_') !== FALSE)
				{
				$qid = sqlsecure(htmlsecure(intval(str_replace('question_', '', $key))));
				$title = sqlsecure(htmlsecure($value));
				}
				
			if (strrpos($key, 'type_') !== FALSE)	
				{
				$query = mysqli_query($sql, "INSERT INTO survey_".$sid." VALUES ('".$qid."', 0, '".$title."', '".sqlsecure(htmlsecure($value))."')");
				}
				
			if (strrpos($key, 'answer_') !== FALSE)	
				{
				$qid = intval(substr($key, 7));
				$loop = 2;
				$temp = 0;
				while ($loop)
					{
					if ($key[strlen($key)-$loop] == '_')
						{
							$aid = intval(substr($key, 0-$loop+1));
						$loop = 0;
						}
					else
						$loop++;
						
					if ($loop > 6)
						$loop = 0;
					}
				$query = mysqli_query($sql, "INSERT INTO survey_".$sid." VALUES ('".$qid."', '".$aid."', '".sqlsecure(htmlsecure($value))."', '')");
				}
			
			//mysqli_query($sql, "INSERT INTO survey_".$sid." VALUES (".$qid.", 0, '".sqlsecure(htmlsecure($_POST['question_'.$qid]))."', '".sqlsecure(htmlsecure($_POST['type_'.$qid]))."')");
			//mysqli_query($sql, "INSERT INTO survey_".$sid." VALUES (".$qid.", ".$aid.", '".sqlsecure(htmlsecure($_POST['answer_'.$qid.'_'.$aid]))."', '')");
			}
		
		echo '<p>Survey created, ID: '.$sid.'
		</p><p>Link to survey: <a href="'.$baseurl.$surveyphp.'?id='.$sid.'">'.$baseurl.$surveyphp.'?id='.$sid.'</a><br>
		Link to results: <a href="'.$baseurl.$resultphp.'?id='.$sid.'">'.$baseurl.$resultphp.'?id='.$sid.'</a></p>';
	
	echo mysqli_error($sql);
		
	mysqli_close($sql);
	
	return 1;
}

?>