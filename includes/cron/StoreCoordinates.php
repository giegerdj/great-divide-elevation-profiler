<?php
die();
require_once('../includes/php/config.php');
require_once('../includes/php/db_connect.php');


//for each user id with a SPOT url
//	find out the last coordinate
//	read xml file getting all new xml coordinates
//		(http://share.findmespot.com/messageService/guestlinkservlet?glId={ID}&completeXml=true
//	store xml coordinates
//insert xml coordinates into database

$YearStart = mktime(0,0,0,1,1,date('Y'));
$YearEnd = mktime(0,0,0,1,1,date('Y')+1);

$GetMembers = @mysql_query("
SELECT
	esrgd_ride.RideID, SpotURL, IFNULL(MAX(Time),0) AS LastEntryTime, StartDate
FROM
	esrgd_ride
	LEFT OUTER JOIN
		esrgd_spot_points
	ON
		esrgd_spot_points.RideID = esrgd_ride.RideID
WHERE
	SpotURL != '' AND
	esrgd_ride.TimeDeleted = '0' AND
	TimeCreated > '" . $YearStart . "' AND 
	TimeCreated < '" . $YearEnd . "'
GROUP BY
	esrgd_ride.RideID
");

$InsertSQL = "
INSERT INTO
	esrgd_spot_points (RideID,Time,Lat,Lng)
VALUES ";

$DoInsert = false;
while($Member = mysql_fetch_assoc($GetMembers))
{
	$EarliestTime = max( array($Member['LastEntryTime'],$Member['StartDate']) );
	
	$xml = @simplexml_load_file('http://share.findmespot.com/messageService/guestlinkservlet?glId=' . $Member['SpotURL'] . '&completeXml=true');
	if($xml !== false)
	{
		$Done = false;
		$Count = sizeof($xml->message);
		for($x=0; $x < $Count && !$Done; $x++)
		{
			if($xml->message[$x]->timeInGMTSecond <= $EarliestTime)
			{
				$Done = true;
			}
			else
			{
				$DoInsert = true;
				//echo $Member['RideID'] , ' - ' , $xml->message[$x]->timeInGMTSecond , ' - ' , $xml->message[$x]->latitude , ' - ' , $xml->message[$x]->longitude , '<br />';
				$InsertSQL .= "('" . $Member['RideID'] . "','" . $xml->message[$x]->timeInGMTSecond . "','" . $xml->message[$x]->latitude . "','" . $xml->message[$x]->longitude . "'),";
			}
		}
		unset($xml);
	}
}
if($DoInsert)
{
	$InsertSQL = substr($InsertSQL,0,-1);
	$Insert = @mysql_query($InsertSQL);
	if(!$Insert)
		echo 'failed: ' , mysql_error();
}

/*
<message>
	<esn>0-7421381</esn>
	<esnName>Dave's SPOT</esnName>
	<messageType>TRACK</messageType>
	<timestamp>2011-01-04T02:48:31.000Z</timestamp>
	<timeInGMTSecond>1294109311</timeInGMTSecond>
	<latitude>42.09739</latitude>
	<longitude>-87.83841</longitude>
</message>
*/
