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



require_once('nusoap.php');
function padWithZeros($s, $n) 
{
	return sprintf("%0" . $n . "d", $s);
}

$client = new nusoap_client("http://$server/CablecastWS/CablecastWS.asmx?WSDL", 'wsdl');  
// Creates New SOAP client using WSDL file


if(!$_GET['ShowID'])
{ 
	echo "Error!  No Show ID supplied";
}

else
{
	$result = $client->call('GetShowInformation', array('ShowID' => $_GET['ShowID']), '', '', false, true);
		
	$searchLength = strtotime(date("Y-m-d")."T".date("H:i:s")) + (60*60*24*35);

	echo "<table>\n";
	echo "<tr><th>Program Title</th><td>".$result['GetShowInformationResult']['Title']."</td></tr>\n";
	echo "<tr><th>Program Length:</th><td>".floor($result['GetShowInformationResult']['TotalSeconds'] / 3600).":".padWithZeros(floor(floor($result['GetShowInformationResult']['TotalSeconds'] % 3600) / 60), 2).":".padWithZeros(($result['GetShowInformationResult']['TotalSeconds'] % 60), 2)."</td></tr>\n";
	
	
	foreach($channels as $channel)
	{
		
		$schedule = $client->call('GetScheduleInformation', array(
	    	'ChannelID'     => $channel["id"],
			'FromDate'      => date("Y-m-d")."T00:00:00",
			'ToDate'        => date("Y-m-d", $searchLength)."T".date("H:i:s", $searchLength),
			'restrictToShowID' => $_GET['ShowID']), '', '', false, true);
		
		echo "<tr><th>Scheduled on ".$channel['name']."</th>\n";
		
		if (count($schedule['GetScheduleInformationResult']['ScheduleInfo']) == '1')
		{
			echo "<td>This Program is Not Currently Scheduled on ".$channel['name']."</td></tr>\n";
	 		
		}
		else
		{	echo "<td>\n";
			foreach($schedule['GetScheduleInformationResult']['ScheduleInfo'] as $run)
			{
				echo date("$dateFormat",strtotime($run['StartTime']))."<br />\n";
			}
			echo "</td></tr>\n";
		}
	}
	echo "</table>\n";
}


?>