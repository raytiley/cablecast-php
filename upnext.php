<?php
//Configure Script
$displayNumber = 4;   // Changes number of upcoming shows to display
$server = "http://frontdoor.ctn5.org/";  //include trailing backslash
$channelID = 1;  //Cablecast Channel ID
date_default_timezone_set('America/New_York');
//End Configure

$server = $server."CablecastWS/CablecastWS.asmx?WSDL";  //Generates Link to WSDL file.
$client = new SoapClient($server); //Creates Web Service Client

$result = $client->GetScheduleInformation(array(
	'ChannelID' => $channelID,
	'FromDate' => date("Y-m-d") . "T00:00:00",
	'ToDate' => date("Y-m-d", time() + 60 * 60 * 24) . "T23:59:59",
	'restrictToShowID' => 0));

if(!isset($result->GetScheduleInformationResult->ScheduleInfo)) {
	$schedule = array();
} else {
	$schedule = is_array($result->GetScheduleInformationResult->ScheduleInfo) ?
		$result->GetScheduleInformationResult->ScheduleInfo :
		array($result->GetScheduleInformationResult->ScheduleInfo);
}
$upnext = array();
if(count($schedule) > 0) {

	foreach($schedule as $run) {
		$beginingTime = strtotime($run->StartTime);
		$endingTime = strtotime($run->EndTime);


		if($beginingTime > time()) {
			$upnext[] = array("ShowTitle" => $run->ShowTitle, "StartTime" => $run->StartTime);
		}
		if(count($upnext) >= $displayNumber) {
			break;
		}
	}
	foreach($upnext as $upcomingRun) {
		// **** ShowTitles could have invalid html characters in them.
		echo date("g:ia ", strtotime($upcomingRun["StartTime"]));
		echo $upcomingRun["ShowTitle"] . "\n";
	}
}