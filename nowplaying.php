<?php//Configure Script

$server = "http://frontdoor.ctn4maine.org/";  //include trailing backslash
$channelID = 1;  //Cablecast Channel ID
$defualtSource = "Community Bulletin Board";
//End Configure$server = $server."CablecastWS/CablecastWS.asmx?WSDL";
require_once('nusoap.php');$client = new nusoap_client($server, 'wsdl');  //Creates New SOAP client using WSDL file//Some funky Time Calculations$offset = 0;$day = 60*60*24;$currentDay = date("Y-m-d")."T00:00:00";$currentDayTime =  date("Y-m-d")."T".date("H:i:s");$convertedDayTime = strtotime($currentDayTime);$searchTimestr = $convertedDayTime-$day+($offset * 60 * 60);$searchTime = date("Y-m-d", $searchTimestr)."T".date("H:i:s", $searchTimestr);$result = $client->call('GetScheduleInformation', array(    'ChannelID'        => $channelID,    'FromDate'         => $currentDay,    'ToDate'           => $searchTime,
	    'restrictToShowID' => 0), '', '', false, true);

    $resultNumber = count($result['GetScheduleInformationResult']['ScheduleInfo']);

if($resultNumber == 0){echo $defualtSource;}if($resultNumber == 1){echo $defualtSource;}if($resultNumber > 1){$count = 0;$beginingTime;$endingTime;$testNumber = 0;while ($count <= ($resultNumber - 1)){$beginingTime = strtotime($result['GetScheduleInformationResult']['ScheduleInfo'][$count]['StartTime']);$endingTime = strtotime($result['GetScheduleInformationResult']['ScheduleInfo'][$count]['EndTime']);

if(($beginingTime <= ($convertedDayTime + ($offset * 60 * 60))) && ($endingTime > ($convertedDayTime + ($offset * 60 * 60)))){$testNumber = $count;}$count++;}if ($testNumber == '0'){echo $defualtSource;}else{echo $result['GetScheduleInformationResult']['ScheduleInfo'][$testNumber]['ShowTitle'];}}?>