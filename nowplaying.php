<?php

//Configure Script  
date_default_timezone_set('America/New_York'); // Set this to timezone of Cablecast Server
$server = "http://frontdoor.ctn5.org/";  //include trailing backslash
$channelID = 2;  //Cablecast Channel ID
$defualtSource = "Community Bulletin Board";
//End Configure

$server = $server."CablecastWS/CablecastWS.asmx?WSDL";

$client = new SoapClient($server);  //Creates New SOAP client using WSDL file

$result = $client->GetScheduleInformation(array(
    'ChannelID'        => $channelID,
    'FromDate'         => date("Y-m-d") . "T00:00:00",
    'ToDate'           => date("Y-m-d") . "T23:59:59",
    'restrictToShowID' => 0));

if($result->GetScheduleInformationResult->ScheduleInfo == NULL) {
	$schedule = array(); // No results so set results to empty array.	
} else {
	// If the result isn't an array, then its a single result. Put that single
	// result into an array so we can just work with an array below.
	// Its simpler that way.
	$schedule = is_array($result->GetScheduleInformationResult->ScheduleInfo) ?
		$result->GetScheduleInformationResult->ScheduleInfo :
		array($result->GetScheduleInformationResult->ScheduleInfo);
}

$foundRun = FALSE;
foreach($schedule as $run) {
	$beginingTime = strtotime($run->StartTime);
	$endingTime = strtotime($run->EndTime);
	if($beginingTime <= time() && $endingTime > time())
	{
		$foundRun = TRUE;
		echo $run->ShowTitle;
		break;
	}
}

if($foundRun == FALSE) {
	echo $defualtSource;
}