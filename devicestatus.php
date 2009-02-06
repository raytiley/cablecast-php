<?php

/* This Script will check whats playing on your devices and compare them 
to the schedule.  If something isn't playing that is scheduled, it will
tweet about it, and send you an email with a var_dump.
This script won't work unless you turn off authentication on autopilot.devicestats.aspx (or something like that)

This script won't work with out some heavy modification as its very location dependent.
I'm working to fix that, but don't hold your breath.
*/


//Email Headers  Change this to indicate where the email is coming from.
$headers = 'From: email@server.com' . "\r\n" .
    'Reply-To: noreply' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

$twitter_username	='username'; //twitter username
$twitter_psw		='password';  //twitter password
require_once('nusoap.php');  //nusoap.php is required to make soap calls to web service
require('twitterAPI.php');  //twitterAPI.php sends messages to Twitter if you want to do that.

function stringFromInteger($str) {

    $len = strlen($str);   

    for ($i = 0, $int = ''; $i < $len; $i++) {

        if (is_numeric($str[$i])) {

            $int .= $str[$i];

        } elseif(!is_numeric($str[$i]) && $int) {

            $i = $len;

        }       

    }

    return (int) $int;

}


$cablecast_ip = "192.168.0.100";  //Change to your Cablecast Servers IP Address or DNS name

$device_list ="184,170,171,185";  //In Cablecast Device List Mouse over and get number from status bar.

$client = new nusoap_client("http://$cablecast_ip/CablecastWS/CablecastWS.asmx?WSDL", 'wsdl');  // Creates New SOAP client using WSDL file
$Channel_4 = '';
$requestAddress = "http://$cablecast_ip/Cablecast/Autopilot.ForceEvent.Data.aspx?InitialRequest=false&GetAllDeviceStatus=$device_list&LocationID=22";
$xml_str = file_get_contents($requestAddress,0);
   
//This bit parses the XML from Autopilot.ForceEvent.Data.aspx
$p = xml_parser_create();
xml_parse_into_struct($p, $xml_str, $vals, $index);
xml_parser_free($p);

//Enter all the Server checks here
//You need a block of server code for each device.  I'll eventually make this into an automated loop.


//Begin Server 1 code

	$server_1 = $index['OPERATION'][0];

	echo "<b>Video Server 1 Status</b> ".$vals[$server_1]['value']."<br><br>";
	echo "Extracted Show ID: ".stringFromInteger($vals[$server_1]['value'])." <br><br>";

	if(substr_count($vals[$server_1]['value'], 'Playing'))
		{ 
		echo "Server is playing show id: ".stringFromInteger($vals[$server_1]['value'])."<br><br>";
		$server_playing[] = stringFromInteger($vals[$server_1]['value']);
		}

	else
		{
		echo "Server is not playing<br><br>";
		}

//End Server 1 code

//Begin Server 2 code

	$server_2 = $index['OPERATION'][1];

	echo "<b>Video Server 2 Status</b> ".$vals[$server_2]['value']."<br><br>";
	echo "Extracted Show ID: ".stringFromInteger($vals[$server_2]['value'])." <br><br>";

	if(substr_count($vals[$server_2]['value'], 'Playing'))
		{ 
		echo "Server is playing show id: ".stringFromInteger($vals[$server_2]['value'])."<br><br>";
		$server_playing[] = stringFromInteger($vals[$server_2]['value']);
		}

	else
		{
		echo "Server is not playing<br><br>";
		}

//End Server 2 code

//Begin server 3 code

	$server_3 = $index['OPERATION'][2];

	echo "<b>Video Server 3 Status</b> ".$vals[$server_3]['value']."<br><br>";
	echo "Extracted Show ID: ".stringFromInteger($vals[$server_3]['value'])." <br><br>";

	if(substr_count($vals[$server_3]['value'], 'Playing'))
		{ 
		echo "Server is playing show id: ".stringFromInteger($vals[$server_3]['value'])."<br><br>";
		$server_playing[] = stringFromInteger($vals[$server_3]['value']);
		}

	else
		{
		echo "Server is not playing<br><br>";
		}

//End Server 3 code

//Begin Server 4 code

	$server_4 = $index['OPERATION'][3];

	echo "<b>Video Server 4 Status</b> ".$vals[$server_4]['value']."<br><br>";
	echo "Extracted Show ID: ".stringFromInteger($vals[$server_4]['value'])." <br><br>";

	if(substr_count($vals[$server_4]['value'], 'Playing'))
		{
		echo "Server is playing show id: ".stringFromInteger($vals[$server_4]['value'])."<br><br>";
		$server_playing[] = stringFromInteger($vals[$server_4]['value']);
		}

	else
		{
		echo "Server is not playing<br><br>";
		}

//End Server 4 code



//Do a bunch of silly time calculations
$offset = 0;  //set this if time of your server is different from Cablecast machine.
$day = 60*60*24;  //Calculate # of seconds in a day
$currentDay = date("Y-m-d")."T00:00:00";
$currentDayTime =  date("Y-m-d")."T".date("H:i:s");
$convertedDayTime = strtotime($currentDayTime);
$searchTimestr = $convertedDayTime-$day+($offset * 60 * 60);
$searchTime = date("Y-m-d", $searchTimestr)."T".date("H:i:s", $searchTimestr);


//Enter code for Channel Checking here

//Begin Code for First Channel
$gotShow = '';

	//  Get Todays Schedule
	$result = $client->call('GetScheduleInformation', array(
	'ChannelID'        => 1, // Set This to the Channel ID
	'FromDate'         => $currentDay,
	'ToDate'           => $currentDay,
	'restrictToShowID' => 0), '', '', false, true);




	$resultNumber = count($result['GetScheduleInformationResult']['ScheduleInfo']);


	if($resultNumber > 1)
		{
		$count = 0;
		while ($count <= ($resultNumber - 1))
			{
			$beginingTime = strtotime($result['GetScheduleInformationResult']['ScheduleInfo'][$count]['StartTime']);
			$endingTime = strtotime($result['GetScheduleInformationResult']['ScheduleInfo'][$count]['EndTime']);
			
			//Check To see if a show is scheduled to play right now
			if(($beginingTime <= ($convertedDayTime + ($offset * 60 * 60))) && ($endingTime > ($convertedDayTime + ($offset * 60 * 60))))
				{
				$testNumber = $count;  //sets testNumber to show Id of currently scheduled show
				$gotShow = TRUE;  //Sets a switch to force compairson to video server playback
				}

			$count++;
			}

		if ($gotShow != NULL)  // If a show is currently scheduled, check to see if a device is playing it.

			{
			if(array_search($result['GetScheduleInformationResult']['ScheduleInfo'][$testNumber]['ShowID'], $server_playing) === FALSE)  //If show is not in list do some stuff
				{ 
				echo "Send an alert to twitter"; 
				$twitter_message = "Show ID ".$result['GetScheduleInformationResult']['ScheduleInfo'][$testNumber]['ShowID']. " is not playing correctly on Channel 4.\nVar Dump of Result\n".print_r($result, true)."\n\nVar Dump of Server_playing\n\n".print_r($server_playing, true)."\n\nEnd Report";
				//postToTwitter($twitter_username, $twitter_psw, $twitter_message);  //Send that message to twitter
				mail('ray@ctn4maine.org', 'Problem with Channel', $twitter_message, $headers);  //Email that message
				}
			else 
				{ 
				echo "The show is playing correctly on channel 4"; 
				}

			}
		}
//End Code for First Channel



//Begin Code for Second Channel
$gotShow = '';

	//  Get Todays Schedule
	$result = $client->call('GetScheduleInformation', array(
	'ChannelID'        => 2, // Set This to the Channel ID
	'FromDate'         => $currentDay,
	'ToDate'           => $currentDay,
	'restrictToShowID' => 0), '', '', false, true);




	$resultNumber = count($result['GetScheduleInformationResult']['ScheduleInfo']);


	if($resultNumber > 1)
		{
		$count = 0;
		while ($count <= ($resultNumber - 1))
			{
			$beginingTime = strtotime($result['GetScheduleInformationResult']['ScheduleInfo'][$count]['StartTime']);
			$endingTime = strtotime($result['GetScheduleInformationResult']['ScheduleInfo'][$count]['EndTime']);
		
			//Check To see if a show is scheduled to play right now
			if(($beginingTime <= ($convertedDayTime + ($offset * 60 * 60))) && ($endingTime > ($convertedDayTime + ($offset * 60 * 60))))
				{
				$testNumber = $count;  //sets testNumber to show Id of currently scheduled show
				$gotShow = TRUE;  //Sets a switch to force compairson to video server playback
				}

			$count++;
			}

		if ($gotShow != NULL)  // If a show is currently scheduled, check to see if a device is playing it.

			{

			if(array_search($result['GetScheduleInformationResult']['ScheduleInfo'][$testNumber]['ShowID'], $server_playing) === FALSE)  //If show is not in list do some stuff
				{ 
				echo "Send an alert to twitter";
				$twitter_message = "Show ID ".$result['GetScheduleInformationResult']['ScheduleInfo'][$testNumber]['ShowID']. " is not playing correctly on Channel 2.\nVar Dump of Result\n".print_r($result, true)."\n\nVar Dump of Server_playing\n\n".print_r($server_playing, true)."\n\nEnd Report";
				//postToTwitter($twitter_username, $twitter_psw, $twitter_message);  //Send that message to twitter
				mail('ray@ctn4maine.org', 'Problem with Channel', $twitter_message, $headers);  //Email that message
				}
			else 
				{ 
				echo "<br>The show is playing correctly on channel 2"; 
				}

			}
		}
//End Code for Second Channel

echo "</body></html>";

?>
