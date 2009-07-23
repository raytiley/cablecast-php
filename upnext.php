<?php
//Configure Script

$displayUpcomingNum = 4;   // Changes number of upcoming shows to display
$server = "http://frontdoor.ctn4maine.org/";  //include trailing backslash
$channelID = 1;  //Cablecast Channel ID


//End Configure

require_once('nusoap.php'); //Script requires NuSoap library


$server = $server."CablecastWS/CablecastWS.asmx?WSDL";  //Generates Link to WSDL file.


$client = new nusoap_client($server, 'wsdl'); //Creates Web Service Client




$offset = 0; // Use if server is in a different timezone.
$day = 60*60*24;  //Number of seconds in a day.
$currentDay = date("Y-m-d")."T00:00:00";
$currentDayTime = date("Y-m-d")."T".date("H:i:s");
$convertedDayTime = strtotime($currentDayTime);
$searchTimestr = $convertedDayTime+$day+($offset * 60 * 60);
$searchTime = date("Y-m-d", $searchTimestr)."T".date("H:i:s", $searchTimestr);



$result = $client->call('GetScheduleInformation', array(
'ChannelID' => $channelID,
'FromDate' => $currentDay,
'ToDate' => $searchTime,
'restrictToShowID' => 0), '', '', false, true);




$resultNumber = count($result['GetScheduleInformationResult']['ScheduleInfo']);








if($resultNumber > 1)
{
$count = 0;
$startStoring = 0;
$beginingTime;
$endingTime;
$testNumber = 0;
while ($count <= ($resultNumber - 1))
{
$beginingTime = strtotime($result['GetScheduleInformationResult']['ScheduleInfo'][$count]['StartTime']);
$endingTime = strtotime($result['GetScheduleInformationResult']['ScheduleInfo'][$count]['EndTime']);


if(($beginingTime > ($convertedDayTime + ($offset * 60 * 60)))) 
{
$testNumber = $count;
$startStoring = 1;


}

if($startStoring)
{
$comingNextTitle[] = $result['GetScheduleInformationResult']['ScheduleInfo'][$count]['ShowTitle'];

$comingNextTime[] = $result['GetScheduleInformationResult']['ScheduleInfo'][$count]['StartTime'];
}

$count++;
}


}

if(count($comingNextTime) < $displayUpcomingNum)
{
$displayUpcomingNum = count($comingNextTime);
}

$i = 0;
echo "<table>\n";
while ($i < $displayUpcomingNum)
{
echo "<tr><td valign='top'><b>".date("g:ia ", strtotime($comingNextTime[$i]))."</b> - ".
$comingNextTitle[$i]."</td></tr>\n";
$i++;
}
echo "</table>\n\n";



?>