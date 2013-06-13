var noquestions = 0;
var nooptions = [];

function addOption(qid)
	//Displays a new option form for the question with id "qid"
	{
	var optiondiv = document.createElement("div");
	optiondiv.innerHTML = '\
		<div id="multipleoptions">Option: <input type="text" size="100" name="answer_'+qid.toString()+'_'+nooptions[qid].toString()+'"></div>';
	optiondiv.setAttribute("id","answercontainer");
	document.getElementById('answercontainer_'+qid.toString()).appendChild(optiondiv);

	nooptions[qid]++
	}

function addQuestion()
	{
	//Displays a new question for the survey
	var questiondiv = document.createElement("div");
	questiondiv.innerHTML = '\
		<div id="qtitle">Question: <input type="text" size="100" name="question_'+noquestions+'"></div>\
		<div id="qtitle">Question type: \
			<select name="type_'+noquestions+'">\
				<option value="radio">Single answer</option>\
				<option value="multi">Multiple answers</option>\
		</select><br><br>\
		<div id="answercontainer_'+noquestions+'">\
			<div id="multipleoptions">Option: <input type="text" size="100" name="answer_'+noquestions+'_1"></div>\
		</div><div id="submit"><button type="button" onclick="addOption('+noquestions+')">Add Option</button></div>\
	';
	questiondiv.setAttribute("id","questioncontainer");
	document.getElementById('surveycreatecontainer').appendChild(questiondiv);
	
	nooptions[noquestions] = 2;
	noquestions++
	}