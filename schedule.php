<?php
//Configure Script
$server = "http://frontdoor.ctn5.org/"; //include trailing backslash
$channelID = 1; //Cablecast Channel ID
$displayDays = 7;  //Number of Days to Display
date_default_timezone_set('America/New_York');
//End Configure
 

$client = new SoapClient($server."CablecastWS/CablecastWS.asmx?WSDL"); // Creates New SOAP client using WSDL file
 
// Get year month and day from individual query string parameters if exist.
// There must of been some reason I did it this way. Probably some crappy calendar
// I was using at the time. Feel free to change to a better calendar.
if (isset($_GET['d']) && isset($_GET['m']) && (isset($_GET['y']))) {
	$dateString = $_GET['y'] . "-" . $_GET['m'] . "-" . $_GET['d'];
} else {
	//default to today's date
	$dateString = date("Y-m-d");
}
//Calculate the end date
$endDate = date("Y-m-d", strtotime($dateString) + ($displayDays * 24 * 60 * 60))."T23:59:59";

//Get the schedule from the web service
$result = $client->GetScheduleInformation(array(
    'ChannelID' => $channelID,
    'FromDate' => $dateString . "T00:00:00",
    'ToDate' => $endDate,
    'restrictToShowID' => 0));

// if there are no results or a single result make an array anyway
// so we can deal with it all the same way below
if($result->GetScheduleInformationResult->ScheduleInfo == NULL) {
	$scheduleItems = array();
} else {
	$scheduleItems = is_array($result->GetScheduleInformationResult->ScheduleInfo) ?
		$result->GetScheduleInformationResult->ScheduleInfo :
		array($result->GetScheduleInformationResult->ScheduleInfo);
}

// Print it all out in a table
if(count($scheduleItems) == '0') {
	echo "Nothing is Currently Scheduled for this channel";
} else {
	$startDay = "";
	echo "<table>\n<tr><th>Time</th><th>Program Title</th></tr>\n";
	foreach($scheduleItems as $run) {
		$day = date("Y-m-d", strtotime($run->StartTime));
		if($day != $startDay)
		{
			echo "<tr><th colspan=\"2\">".date("l F jS, Y", strtotime($day))."</th></tr>\n";
			$startDay = $day;
		}
			echo "<tr><td>" . date("g:i a", strtotime($run->StartTime)) . "</td><td>" . $run->ShowTitle . "</td></tr>\n";
	}
	echo "</table>\n";
}
 
?>