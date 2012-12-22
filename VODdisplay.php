<?php
//Configure
$server = "pittsfieldtv.dyndns.org";
date_default_timezone_set('America/New_York');
// End Configure

$client = new SoapClient("http://" . $server . "/CablecastWS/CablecastWS.asmx?WSDL");  // Creates New SOAP client using WSDL file
 
$searchDate = date("Y-m-d")."T12:00:00";

// Search for all shows that have an event date less than now that are available for VOD
$result = $client->AdvancedShowSearch(array(
    'ChannelID'        => 1,
    'searchString'         => '',
    'eventDate'           => date("Y-m-d") . "T00:00:00",
    'dateComparator'      => '<',
    'restrictToCategoryID'  => 0,  
    'restrictToProducerID'   => 0, 
    'restrictToProjectID'    =>  0,
    'displayStreamingShowsOnly'   =>  1,
    'searchOtherSites'     =>   0,));

if(!isset($result->AdvancedShowSearchResult->SiteSearchResult->Shows->ShowInfo)) {
    $vods = array();
} else {
    $vods = is_array($result->AdvancedShowSearchResult->SiteSearchResult->Shows->ShowInfo) ?
        $result->AdvancedShowSearchResult->SiteSearchResult->Shows->ShowInfo :
        array($result->AdvancedShowSearchResult->SiteSearchResult->Shows->ShowInfo);
}

if(count($vods) == '0') {
    //There is probably something wrong if this shows up.
    echo "There are now Shows currently available for on demand viewing.";
} else {
    // Prints out a table with time and show title with link to show detial page
    echo "<table>\n";
    echo "<th>Program Title</th><th>Link</th></tr>\n";
    
    foreach($vods as $vod) {
        echo "<tr>\n";
        echo "<td>" . $vod->Title . "</td>\n";
        echo "<td><a href='" . $vod->StreamingFileURL . "'>Watch Now</a></td>";
        echo "</tr>\n";
    }
    
    echo "</table>";
}