<?php
//Configure Script
$server = "http://frontdoor.ctn5.org/"; //include trailing backslash
$channelID = 1; //Cablecast Channel ID
$displayDays = 7;  //Number of Days to Display
date_default_timezone_set('America/New_York');
//End Configure
 
require_once('nusoap.php');
$client = new nusoap_client($server."CablecastWS/CablecastWS.asmx?WSDL", 'wsdl'); // Creates New SOAP client using WSDL file
 
if (isset($_GET['d']) && isset($_GET['m']) && (isset($_GET['y']))) 
{
	$dateString = $_GET['y']."-".$_GET['m']."-".$_GET['d']."T00:00:00";
}

else
{
	$dateString = date("Y-m-d")."T00:00:00";
}

$endDate = date("Y-m-d", strtotime($dateString) + ($displayDays * 24 * 60 * 60))."T12:00:00";

 
$result = $client->call('GetScheduleInformation', array(
    'ChannelID' => $channelID,
    'FromDate' => $dateString,
    'ToDate' => $endDate,
    'restrictToShowID' => 0), '', '', false, true);


 
 
// Check for a fault
 
if ($client->fault) 
{
 
	echo '<h2>Fault</h2><pre>';
 
	print_r($result);
 
	echo '</pre>';
 
} 
else 
{
 
	// Check for errors
 	$err = $client->getError();
 	if ($err)
	{
 		// Display the error
 		echo '<h2>Error</h2><pre>' . $err . '</pre>';
	} 
	else 
	{
 		$resultNumber = count($result['GetScheduleInformationResult']['ScheduleInfo']);
		$count = 0;
 		if($resultNumber == '0')
		{
			echo "Nothing is Currently Scheduled for this day";
		}
 
		if($resultNumber > '1')
		{ 
			$startDay = "";
			echo "<table>\n<tr><th>Time</th><th>Program Title</th></tr>\n";
			while ($count <= ($resultNumber -1))
			{
				$day = date("Y-m-dT12:00:00", strtotime($result['GetScheduleInformationResult']['ScheduleInfo'][$count]['StartTime']));
				if($day != $startDay)
				{
					echo "<tr><th colspan=\"2\">".date("l F n, Y", strtotime($day))."</th></tr>\n";
					$startDay = $day;
				}
 				echo "<tr><td>".date("g:i a", strtotime($result['GetScheduleInformationResult']['ScheduleInfo'][$count]['StartTime']))."</td><td>".$result['GetScheduleInformationResult']['ScheduleInfo'][$count]['ShowTitle']."</td></tr>\n";
 				$count++;
			}
			echo "</table>\n";
		}
	}
}
 
?>