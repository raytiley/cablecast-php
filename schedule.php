<?php
//Configure Script
$server = "http://frontdoor.ctn5.org/";  //include trailing backslash
$channelID = 1;  //Cablecast Channel ID
//End Configure

require_once('nusoap.php');
$client = new nusoap_client($server."CablecastWS/CablecastWS.asmx?WSDL", 'wsdl');  // Creates New SOAP client using WSDL file
 
if (isset($_GET['m'])) { 
$dateString = $_GET['y']."-".$_GET['m']."-".$_GET['d']."T00:00:00";
$dateString = date("Y-m-d",strtotime($dateString))."T00:00:00";
}
else {
$dateString = date("Y-m-d")."T00:00:00";
$dateString = date("Y-m-d",strtotime($dateString))."T00:00:00";
}
echo date("l F jS",strtotime($dateString));

echo "</th></tr><tr><td valign='top'>";


$result = $client->call('GetScheduleInformation', array(
    'ChannelID'        => $channelID,
    'FromDate'         => $dateString,
    'ToDate'           => $dateString,
    'restrictToShowID' => 0), '', '', false, true);


// Check for a fault

if ($client->fault) {

	echo '<h2>Fault</h2><pre>';

	print_r($result);

	echo '</pre>';

} else {

	// Check for errors

	$err = $client->getError();

	if ($err) {

		// Display the error

		echo '<h2>Error</h2><pre>' . $err . '</pre>';

	} else {

$resultNumber = count($result['GetScheduleInformationResult']['ScheduleInfo']);	
$count = 0;

if($resultNumber == '0')
{
echo "Nothing is Currently Scheduled for this day";
}

if($resultNumber > '1')
{	echo "<table><tr>
			<th>Time</th><th>Program Title</th></tr>";
	while ($count <= ($resultNumber -1))
{

echo "<tr><td>".date("g:i a", strtotime($result['GetScheduleInformationResult']['ScheduleInfo'][$count]['StartTime']))."</td><td>".$result['GetScheduleInformationResult']['ScheduleInfo'][$count]['ShowTitle']."</td></tr>";

$count++;
} 
echo "</table>";
}
}
}

?>