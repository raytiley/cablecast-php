<?php
//Setup Script
$server = "frontdoor.ctn5.org"; //cablecast server address
$dateFormat = "l F jS g:i a";  //search for date() on php.net for info on how to format this string
//Configure the channels below by replacing the "CTN.." with your channels name and the appropriate channelID
//You can add as many channels as you have.
//Remember to leave off the ',' at the end of the last entry.  
$channels = array(
	array("name" => "CTN Channel 5", "id" => 1),
	array("name" => "PPAC Channel 2", "id" => 2)
	);
date_default_timezone_set('America/New_York');
//End Setup

function padWithZeros($s, $n) {
	return sprintf("%0" . $n . "d", $s);
}

$client = new SoapClient("http://$server/CablecastWS/CablecastWS.asmx?WSDL");  
// Creates New SOAP client using WSDL file
$showID = 1;
if(isset($_GET['ShowID'])) { 
	$showID = $_GET['ShowID'];
}
$result = $client->GetShowInformation( array('ShowID' => $showID));	
$searchLength = strtotime(date("Y-m-d")."T".date("H:i:s")) + (60*60*24*35);

echo "<table>\n";
echo "<tr><th>Program Title</th><td>" . $result->GetShowInformationResult->Title . "</td></tr>\n";
echo "<tr><th>Program Length:</th><td>".floor($result->GetShowInformationResult->TotalSeconds / 3600) . ":".padWithZeros(floor(floor($result->GetShowInformationResult->TotalSeconds % 3600) / 60), 2).":".padWithZeros(($result->GetShowInformationResult->TotalSeconds % 60), 2)."</td></tr>\n";

foreach($channels as $channel) {
	
	$scheduleResult = $client->GetScheduleInformation(array(
    	'ChannelID'     => $channel["id"],
		'FromDate'      => date("Y-m-d")."T00:00:00",
		'ToDate'        => date("Y-m-d", $searchLength)."T".date("H:i:s", $searchLength),
		'restrictToShowID' => $showID));
	
	echo "<tr><th>Scheduled on ".$channel['name']."</th>\n";

	if(!isset($scheduleResult->GetScheduleInformationResult->ScheduleInfo) ||
		$scheduleResult->GetScheduleInformationResult->ScheduleInfo == NULL) {
		$schedule = array();
	} else {
		$schedule = is_array($scheduleResult->GetScheduleInformationResult->ScheduleInfo) ?
			$scheduleResult->GetScheduleInformationResult->ScheduleInfo :
			array($scheduleResult->GetScheduleInformationResult->ScheduleInfo);
	}

	if (count($schedule) == 0) {
		echo "<td>This Program is Not Currently Scheduled on ".$channel['name']."</td></tr>\n";
	}
	else {	
		echo "<td>\n";
		foreach($schedule as $run) {
			echo date("$dateFormat",strtotime($run->StartTime))."<br />\n";
		}
		echo "</td></tr>\n";
	}
}
echo "</table>\n";


?>