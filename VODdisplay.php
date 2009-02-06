<?php
/*  This script prints an html table of all shows that are availalbe for VOD on the server

The shows must have valid event dates that are in the past

*/

require_once('nusoap.php');  //SOAP function library

$client = new nusoap_client("http://YOURSERVER/CablecastWS/CablecastWS.asmx?WSDL", 'wsdl');  // Creates New SOAP client using WSDL file
 
$searchDate = date("Y-m-d")."T12:00:00";

// Search for all shows that have an event date less than now that are available for VOD
$result = $client->call('AdvancedShowSearch', array(    'ChannelID'        => 1,    'searchString'         => '',    'eventDate'           => $searchDate,    'dateComparator'      => '<',    'restrictToCategoryID'  => 0,      'restrictToProducerID'   => 0,     'restrictToProjectID'    =>  0,    'displayStreamingShowsOnly'   =>  1,    'searchOtherSites'     =>   0,), '', '', false, true);


$resultNumber = count($result['AdvancedShowSearchResult']['SiteSearchResult']['Shows']['ShowInfo']);  // of shows for loop	
$count = 0; // Set count varaible


if($resultNumber == '0')
{
//There is probably something wrong if this shows up.
echo "There are now Shows currently available for on demand viewing.";
}
// Prints out a table with time and show title with link to show detial page
if($resultNumber >= '1')
{	echo "<table border ='0' width='100%'><tr>\n
			<th>Program Title</th><th>Link</th></tr>\n";
	while ($count <= ($resultNumber -1))
{

echo "<tr><td>".$result['AdvancedShowSearchResult']['SiteSearchResult']['Shows']['ShowInfo'][$count]['Title']."</td><td><a href='".$result['AdvancedShowSearchResult']['SiteSearchResult']['Shows']['ShowInfo'][$count]['StreamingFileURL']."'>Watch Now</a></td></tr>\n";

$count++;
} 
echo "</table>";


}

?>