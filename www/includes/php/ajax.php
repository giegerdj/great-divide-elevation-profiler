<?php 
header('Content-type: application/json');

require_once('../../../includes/php/config.php');
require_once('../../../includes/php/db_connect.php');
require_once('../../../includes/php/ElevationProfile.class.php');
require_once('../../../includes/php/funct.php');



$Method = isset($_POST['method']) ? $_POST['method'] : '';

switch($Method)
{
	case 'getElevationStats':
		echo GetElevationStats();
	break;
	case 'getCurrentRiders':
		echo GetCurrentRiders();
	break;
	case 'getSingleRiderHistory':
		echo GetSingleRiderHistory();
	break;
	case 'getNavLinks':
		echo GetNavLinks();
	break;
	case 'login':
		echo Login();
	break;
	case 'logout':
		echo Logout();
	break;
	case 'signup':
		echo Signup();
	break;
	case 'saveSpot':
		echo SaveSpot();
	break;
	case 'getSpotInfo':
		echo GetSpotInfo();
	break;
	case 'deleteSpot':
		echo DeleteSpot();
	break;
	case 'getSettings':
		echo GetSettings();
	break;
	case 'saveSettings':
		echo SaveSettings();
	break;
	case 'getTempPassword':
		echo GetTempPassword();
	break;
	default:
		$result = array(
			'success' => false,
			'errors'	=> true,
			'data'		=> 'error: ' . $Method
		);
		echo json_encode($result);
	break;
}

/*
 * 
 * 
 */
function GetElevationStats( $StartCoord = false, $EndCoord = false )
{
	$EP = new ElevationProfile();
	$Coords = array();
	
	if(!$StartCoord || !$EndCoord)
	{
		$Coords = isset($_POST['coords']) ? explode(',',$_POST['coords']) : null;
	}
	else
	{
		$start = explode(',',$StartCoord);
		$end = explode(',',$EndCoord);
		if(sizeof($start) == 2 && sizeof($end) == 2)
		{
			$Coords[0] = $start[0];
			$Coords[1] = $start[1];
			$Coords[2] = $end[0];
			$Coords[3] = $end[1];
		}
	}
	
	if(sizeof($Coords) == 4)
	{
		if(!IsNumeric($Coords[0]) || !IsNumeric($Coords[1]) || !IsNumeric($Coords[2]) || !IsNumeric($Coords[3]))
		{
			$EP->SetCoords(1,126370);
		}
		else
		{
			$EP->SetLatLng(array($Coords[0],$Coords[1]),array($Coords[2],$Coords[3]));
		}
	}
	else
	{
		$EP->SetCoords(1,126370);
	}
	
	$Stats = $EP->GetSectionStats();
	
	if($Stats !== false)
	{
		$result = array(
			'success' => true,
			'errors'	=> false,
			'data'		=> array(
				'startmile'	=> $Stats['StartMile'],
				'endmile'		=> $Stats['EndMile'],
				'distance'	=> $Stats['Distance'] ,
				'direction'	=> ($Stats['NorthToSouth'] ? 'S' : 'N'),
				'ascent'		=> $Stats['Ascent'],
				'descent'		=> $Stats['Descent'],
				'net'				=> $Stats['Net']
			)
		);
		return json_encode($result);
	}
	else
	{
		$errorResult = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> "Something went wrong...?"
		);
		return json_encode($errorResult);
	}
}//end GetElevationStats

/*
 * 
 * 
 */
function getCurrentRiders()
{
	$YearStart = mktime(0,0,0,1,1,date('Y'));
	$YearEnd = mktime(0,0,0,1,1,date('Y')+1);
			
	$GetMarkers = @mysql_query("
SELECT
	Direction,esrgd_ride.RiderID,Title,Links,Direction,RideType,SpotURL, Lat, Lng, Time
FROM
	(
		SELECT
			esrgd_spot_points.RideID, esrgd_spot_points.Lat, esrgd_spot_points.Lng, esrgd_spot_points.Time
		FROM
			esrgd_spot_points
		LEFT OUTER JOIN
			esrgd_spot_points AS t2 ON esrgd_spot_points.RideID = t2.RideID AND 
			esrgd_spot_points.Time < t2.Time
		WHERE t2.RideID IS NULL 
	) LATEST_COORD, esrgd_ride
WHERE
	LATEST_COORD.RideID = esrgd_ride.RideID AND
	TimeCreated > '" . $YearStart . "' AND 
	TimeCreated < '" . $YearEnd . "' AND
	esrgd_ride.TimeDeleted = '0'
	");

	if($GetMarkers)
	{
		$CurrentRiders = Array();
		while($Marker = mysql_fetch_assoc($GetMarkers))
		{
			$Links = unserialize(base64_decode($Marker['Links']));
			
			$CurrentRiders[] = array(
				'RiderID'			=> $Marker['RiderID'],
				'Lat'					=> $Marker['Lat'],
				'Lng'					=> $Marker['Lng'],
				'Direction'		=> (($Marker['Direction'] == 'N') ? 'S' : 'N'),
				'Title'				=> $Marker['Title'],
				'Links'				=> $Links,
				'RideType'		=> $Marker['RideType']
			);
		}//end loop through all current riders
		
		$result = array(
				'success'	=> true,
				'errors'	=> false,
				'data'		=> $CurrentRiders
			);
			return json_encode($result);
	}
	else
	{
		$errorResult = array(
				'success'	=> false,
				'errors'	=> true,
				'data'		=> "Something went wrong trying to get the current riders.  Refresh the page."
			);
		return json_encode($errorResult);
	}
}

function GetSingleRiderHistory( $RiderID = false, $Days = false)
{
	$YearStart = mktime(0,0,0,1,1,date('Y'));
	$YearEnd = mktime(0,0,0,1,1,date('Y')+1);
	
	if($RiderID === false)
		$RiderID = (isset($_POST['id'])) ? mysql_real_escape_string($_POST['id']) : -1;
	
	if($Days === false)
		$Days = (isset($_POST['days'])) ? mysql_real_escape_string($_POST['days']) : 1;
	
	$RiderData = @mysql_query("
SELECT
	esrgd_ride.RideID, Direction,esrgd_ride.RiderID,Title,Links,Direction,RideType,SpotURL, Lat, Lng, Time
FROM
	(
		SELECT
			esrgd_spot_points.RideID, esrgd_spot_points.Lat, esrgd_spot_points.Lng, esrgd_spot_points.Time
		FROM
			esrgd_spot_points
		LEFT OUTER JOIN
			esrgd_spot_points AS t2 ON esrgd_spot_points.RideID = t2.RideID AND 
			esrgd_spot_points.Time < t2.Time
		WHERE t2.RideID IS NULL 
	) LATEST_COORD, esrgd_ride
WHERE
	LATEST_COORD.RideID = esrgd_ride.RideID AND
	esrgd_ride.RiderID = '" . $RiderID . "' AND
	TimeCreated > '" . $YearStart . "' AND 
	TimeCreated < '" . $YearEnd . "' AND
	esrgd_ride.TimeDeleted = '0'
	");
	
	if(!$RiderData || ($RiderData && mysql_num_rows($RiderData) == 0) )
	{
		$errorResult = array(
				'success'	=> false,
				'errors'	=> true,
				'data'		=> "Something went wrong trying to get that rider's history.  Refresh the page."
			);
		return json_encode($errorResult);
	}
	$Links = unserialize( base64_decode( mysql_result($RiderData,0,'Links') ) );
	
	$LastCoordTime = mysql_result($RiderData,0,'Time');
	$EndLat = mysql_result($RiderData,0,'Lat');
	$EndLng = mysql_result($RiderData,0,'Lng');
	$Direction = ((mysql_result($RiderData,0,'Direction') == 'N') ? 'S' : 'N');
	$Title = mysql_result($RiderData,0,'Title');
	$RideID = mysql_result($RiderData,0,'RideID');
	$RideType = mysql_result($RiderData,0,'RideType');
	
	$TargetTime = $LastCoordTime - ($Days*24*60*60);

	
	$GetCoords = @mysql_query("
SELECT
	Lat,Lng,Time
FROM
	esrgd_spot_points
WHERE
	esrgd_spot_points.RideID = '" . $RideID . "' AND
	Time > '" . $TargetTime . "'
ORDER BY
	Time DESC
	");

	if(!$GetCoords)
	{
		$errorResult = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> "Something went wrong trying to get that rider's history.  Refresh the page."
		);
		return json_encode($errorResult);
	}
	else if( ($NumCoords = mysql_num_rows($GetCoords)) > 0 )
	{
		$StartLat = mysql_result($GetCoords,$NumCoords-1,'Lat');
		$StartLng = mysql_result($GetCoords,$NumCoords-1,'Lng');
		
		if($StartLat == $EndLat && $StartLng == $EndLng)
		{
			$errorResult = array(
				'success'	=> false,
				'errors'	=> true,
				'data'		=> "No history to show...they (ie their SPOT) haven't moved recently."
			);
			return json_encode($errorResult);
		}
		
		$CoordArray = Array();
		
		for($x=0; $x < $NumCoords; $x++)
		{
			$time = date("m/d/Y H:i",mysql_result($GetCoords,$x,'Time'));
			$CoordArray[] = array(
				'lat' => mysql_result($GetCoords,$x,'Lat'),
				'lng' => mysql_result($GetCoords,$x,'Lng'),
				'time' => $time
			);
		}
		
		//return 
		$data = array(
			'RiderID'			=> $RiderID,
			'Direction'		=> $Direction,
			'Title'				=> $Title,
			'Links'				=> $Links,
			'Coords'			=> $CoordArray,
			'Lat'					=> $EndLat,
			'Lng'					=> $EndLng,
			'RideType'		=> $RideType
		);
		
		$result = array(
			'success'	=> true,
			'errors'	=> false,
			'data'		=> $data
		);
		return json_encode($result);
	}
	else
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> "No history to show...they (ie their SPOT) haven't moved recently."
		);
		return json_encode($result);
	}
}

function GetNavLinks()
{
	$result = null;

	if( ($LoggedIn = IsLoggedIn()) === true )
	{
		$result = array(
			'success'	=> true,
			'errors'	=> false,
			'data'		=> array(
				array(
					'url'		=>	URL_BASE . '#/riders/edit',
					'title'	=>	'My SPOT Info'),
				array(
					'url'		=>	URL_BASE . '#/riders/settings',
					'title'	=>	'Account Settings'),
				array(
					'url'		=>	URL_BASE . '#/riders/logout',
					'title'	=>	'Log Out')
			)
		);
		
	}
	else
	{
		$result = array(
			'success'	=> true,
			'errors'	=> false,
			'data'		=> array(
				array(
					'url'		=>	URL_BASE . '#/riders/add',
					'title'	=>	'Add My SPOT'),
				array(
					'url'		=>	URL_BASE . '#/riders/login',
					'title'	=>	'Log In')
			)
		);
	}
	return json_encode($result);
}

function Login()
{
	$Email = (isset($_POST['email'])) ? base64_decode($_POST['email']) : false;
	$Password = (isset($_POST['password'])) ? base64_decode($_POST['password']) : false;
	
	if($Email === false || $Email == '' || $Password === false || $Password == '')
	{
		$errorResult = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'Your email and password are required'
		);
		return json_encode($errorResult);
	}
	$Email = mysql_real_escape_string($Email);
	$Password = mysql_real_escape_string($Password);
	
	
	$Password = encryptPassword($Password);

	$GetLogin = @mysql_query("
SELECT
	RiderID,EmailAddress,Name
FROM
	esrgd_rider
WHERE
	EmailAddress = \"" . $Email . "\" AND
	(Password = '" . $Password . "' OR 
	(TemporaryPasswordExpire > '" . time() . "' AND TemporaryPassword = '" . $Password . "'))
LIMIT 1
	");
	if(!$GetLogin)
	{
		$errorResult = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'Something went wrong and we couldn\'t log you in.'
		);
		return json_encode($errorResult);
	}
	
	if($GetLogin && mysql_num_rows($GetLogin) == 1)
	{
		$_SESSION['LoggedIn'] = true;
		$_SESSION['RiderInfo'] = array(
			'ID'		=> mysql_result($GetLogin,0,'RiderID'),
			'Name'	=> mysql_result($GetLogin,0,'Name'),
			'Email'	=> mysql_result($GetLogin,0,'EmailAddress')
		);
			$successResult = array(
				'success'	=> true,
				'errors'	=> false,
				'data'		=> ''
			);
			return json_encode($successResult);
	}
	else
	{
		sleep(1);
		$errorResult = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'Incorrect email address or password. <a href="#/riders/reset">I forgot my password</a>.'
		);
		return json_encode($errorResult);
	}
}

function Logout()
{
	$_SESSION['LoggedIn'] = false;
	$_SESSION['RiderInfo'] = NULL;
	
	$result = array(
		'success'	=> true,
		'errors'	=> false,
		'data'		=> ''
	);
	return json_encode($result);
}


function Signup()
{
	$Error = false;
	$ErrorMessage = '';
	$Email = (isset($_POST['email'])) ? base64_decode($_POST['email']) : false;
	$Password = (isset($_POST['password'])) ? base64_decode($_POST['password']) : false;
	
	if($Email === false || $Password === false)
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'Your email and password are required'
		);
		return json_encode($result);
	}

	$Email = mysql_real_escape_string($Email);
	$Password = mysql_real_escape_string($Password);
	
	if(!ValidEmail($Email))
	{
		$Error = true;
		$ErrorMessage.= 'Please enter a real email address<br />';
	}
	
	if(!ValidPassword($Password))
	{
		$Error = true;
		$ErrorMessage .= 'Please enter a password at least 5 characters<br />';
	}
	if($Error)
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'There were errors in you input: <br /><br />' . $ErrorMessage
		);
		return json_encode($result);
	}
	
	
	$InsertSignup = @mysql_query("
INSERT INTO
	esrgd_rider (EmailAddress,Password,TimeCreated)
VALUES(\"" . $Email . "\",'" . encryptPassword($Password) . "','" . time() . "')
	");
	
	if(!$InsertSignup)
	{
		if(mysql_errno() == 1062)
			$msg = $Email . ' is already registered.  Do you want to <a href="#/riders/login">log in</a> instead?';
		else
			$msg = 'We couldn\'t sign you up right now. Try again.';
		
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> $msg
		);
		return json_encode($result);
	}
	else if(mysql_affected_rows() == 1)
	{
		$_SESSION['LoggedIn'] = true;
		$_SESSION['RiderInfo'] = array(
			'ID'		=> mysql_insert_id(),
			'Name'	=> '',
			'Email'	=> $Email
		);
		
		$result = array(
			'success'	=> true,
			'errors'	=> false,
			'data'		=> ''
		);
		return json_encode($result);
	}
}


function SaveSPOT()
{

	if(!IsLoggedIn())
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'You must be logged in to add your SPOT info.'
		);
		return json_encode($result);
	}
	
	$Error = false;
	$ErrorMessage = '';
	
	$Title = (isset($_POST['title'])) ? mysql_real_escape_string(urldecode($_POST['title'])) : false;
	$SpotURL = (isset($_POST['spot'])) ? mysql_real_escape_string(urldecode($_POST['spot'])) : false;
	$RideDirection = (isset($_POST['dir'])) ? mysql_real_escape_string(urldecode($_POST['dir'])) : false;
	$RideType = (isset($_POST['type'])) ? mysql_real_escape_string(urldecode($_POST['type'])) : false;
	$Date = (isset($_POST['date'])) ? mysql_real_escape_string(urldecode($_POST['date'])) : false;
	
	$BlogInfo64 = (isset($_POST['links'])) ? $_POST['links'] : false;
		
	if($Title === false || $SpotURL === false || $RideDirection === false || $RideType === false)
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'You are missing a required field'
		);
		return json_encode($result);
	}
	
	if($Date === false || $Date == '')
		$StartDate = time();
	else
	{
		$vals = explode('/',$Date);
		$StartDate = mktime(0,0,0,$vals[0],$vals[1],$vals[2]);
	}
	
	//validate input
	if( !($Title = ValidTitle($Title)) )
	{
		$Error = true;
		$ErrorMessage .= 'Please enter a title for your ride between 1 and 100 characters.<br />';
	}
	
	if( !($SpotURL = ValidSpotURL($SpotURL)) )
	{
		$Error = true;
		$ErrorMessage .= 'Please enter your SPOT ID (it\'s the random-looking characters after the = in the URL).<br />';
	}
	if(!ValidDirection($RideDirection))
	{
		$Error = true;
		$ErrorMessage .= 'Please select your direction.<br />';
	}
	if(!ValidRideType($RideType))
	{
		$Error = true;
		$ErrorMessage .= 'Please select your ride type.<br />';
	}
	$LinkAr = array();
	
	$BlogInfo64 = str_replace(' ','+',$BlogInfo64);
	$BlogInfo = base64_decode($BlogInfo64);
	
	$Pairs = explode('|',$BlogInfo);
	$NumLinks = sizeof($Pairs);
	$LinkError = false;
	
	for($x=0; $x < $NumLinks; $x++)
	{
		$Link = explode('~',$Pairs[$x]);
		
		if(sizeof($Link) == 2 && !($Link[0] == 'URL' || $Link[0] == '') )
		{
			$Link[0] = $Link[0];
			$Link[1] = $Link[1];
			
			if( ($Link[0] = ValidURLFormat($Link[0])) )
			{
				if($Link[1] == '' || $Link[1] == 'Title')
				{
					$Link[1] = $Link[0];
				}
				//$LinkAr[] = array($Link[0], htmlspecialchars($Link[1], ENT_QUOTES, 'UTF-8') );
				$LinkAr[] = array( $Link[0], $Link[1] );
			}//end invalid url format
			else
			{
				$LinkError = true;
			}
		}
	}

	if($LinkError)
	{
		$Error = true;
		$ErrorMessage .= 'One or more of your links was not properly formatted.  Please enter them again (don\'t forget the http://).<br />';
	}
	
	if($Error)
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'There were errors in you input: <br /><br />' . $ErrorMessage
		);
		return json_encode($result);
	}
	
	$LinkValues = base64_encode(serialize($LinkAr));

	$YearStart = mktime(0,0,0,1,1,date('Y'));
	$YearEnd = mktime(0,0,0,1,1,date('Y')+1);
	

	$Update = @mysql_query("
UPDATE
	esrgd_ride
SET
	Title = \"" . $Title . "\",
	Direction = '" . $RideDirection . "',
	RideType = '" . $RideType . "',
	Links = '" . $LinkValues . "',
	SpotURL = '" . $SpotURL . "',
	TimeUpdated = '" . time() . "',
	StartDate = '" . $StartDate . "'
WHERE
	RiderID = '" . $_SESSION['RiderInfo']['ID'] . "' AND
	TimeCreated > '" . $YearStart . "' AND 
	TimeCreated < '" . $YearEnd . "' AND
	TimeDeleted = '0'
LIMIT 1
");
	if(!$Update)
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'We couldn\'t save your info right now.  Try again.'
		);
		return json_encode($result);
	}
	else if(mysql_affected_rows() == 1)
	{
		$result = array(
			'success'	=> true,
			'errors'	=> false,
			'data'		=> 'Your SPOT info has been saved.'
		);
		return json_encode($result);
	}
	$Insert = mysql_query("
INSERT INTO
	esrgd_ride (RiderID,Title,Direction,RideType,Links,SpotURL,TimeCreated,StartDate) 
VALUES('" . $_SESSION['RiderInfo']['ID'] . "',\"" . $Title . "\",'" . $RideDirection . "',
	'" . $RideType . "','" . $LinkValues . "','" . $SpotURL . "','" . time() . "','" . $StartDate . "')
");
	
	if(!$Update)
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'We couldn\'t save your info right now.  Try again.'
		);
		return json_encode($result);
	}
	else
	{
		$result = array(
			'success'	=> true,
			'errors'	=> false,
			'data'		=> 'Your SPOT info has been added.'
		);
		return json_encode($result);
	}
	
}


function GetSpotInfo()
{
	if(!IsLoggedIn())
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'You must be logged in to add your SPOT info.'
		);
		return json_encode($result);
	}
	
	$YearStart = mktime(0,0,0,1,1,date('Y'));
	$YearEnd = mktime(0,0,0,1,1,date('Y')+1);
	
	$GetSPOT = @mysql_query("
SELECT
	Direction,Title,RideType,Links,SpotURL,StartDate
FROM
	esrgd_ride
WHERE
	RiderID = '" . $_SESSION['RiderInfo']['ID'] . "' AND 
	TimeCreated > '" . $YearStart . "' AND 
	TimeCreated < '" . $YearEnd . "' AND
	TimeDeleted = '0'
LIMIT 1
");
	if(!$GetSPOT)
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'We couldn\'t get your info right now. Try again.'
		);
		return json_encode($result);
	}
	if(mysql_num_rows($GetSPOT) == 0)
	{
		$result = array(
			'success'	=> true,
			'errors'	=> false,
			'data'		=> false
		);
		return json_encode($result);
	}
	else
	{
		$Title = mysql_result($GetSPOT,0,'Title');
		$Spot = mysql_result($GetSPOT,0,'SpotURL');
		$Dir = mysql_result($GetSPOT,0,'Direction');
		$Type = mysql_result($GetSPOT,0,'RideType');
		$LinkAr = unserialize(base64_decode(mysql_result($GetSPOT,0,'Links')));
		$StartTime = mysql_result($GetSPOT,0,'StartDate');
		$Date = date('m/d/Y',$StartTime);
		$NumLinks = sizeof($LinkAr);
		$Links = '';
		
		
		$Title = htmlspecialchars_decode( $Title, ENT_QUOTES);
		
		for($x=0; $x < $NumLinks; $x++)
		{
			$LinkAr[$x][1] = htmlspecialchars( $LinkAr[$x][1], ENT_QUOTES, 'UTF-8');
		}
		
		$data = array(
			'Title' => $Title,
			'Url' => $Spot,
			'Direction' => $Dir,
			'RideType' => $Type,
			'Links' => $LinkAr,
			'Date'	=> $Date
		);
		
		$result = array(
			'success'	=> true,
			'errors'	=> false,
			'data'		=> $data
		);
		return json_encode($result);
	}
}



function DeleteSPOT()
{
	$YearStart = mktime(0,0,0,1,1,date('Y'));
	$YearEnd = mktime(0,0,0,1,1,date('Y')+1);
	if(IsLoggedIn())
	{
		$Delete = @mysql_query("
UPDATE
	esrgd_ride
SET
	TimeDeleted = '" . time() . "'
WHERE
	RiderID = '" . $_SESSION['RiderInfo']['ID'] . "' AND
	TimeCreated > '" . $YearStart . "' AND 
	TimeCreated < '" . $YearEnd . "' AND
	TimeDeleted = '0'
LIMIT 1
		");
	}
	$result = array(
		'success'	=> true,
		'errors'	=> false,
		'data'		=> ''
	);
	return json_encode($result);
}

function GetSettings()
{
	if(!IsLoggedIn())
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'You must be logged in to be here.'
		);
		return json_encode($result);
	}
	$result = array(
			'success'	=> true,
			'errors'	=> false,
			'data'		=> array(
				'email'	=> $_SESSION['RiderInfo']['Email']
			)
		);
		return json_encode($result);
}

function SaveSettings()
{
	if(!IsLoggedIn())
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'You must be logged in to save your settings.'
		);
		return json_encode($result);
	}
	
	$Error = false;
	$ErrorMessage = '';
	$Email = (isset($_POST['email'])) ? base64_decode($_POST['email']) : false;
	$NewPassword = (isset($_POST['password'])) ? base64_decode($_POST['password']) : false;
	$OldPassword = (isset($_POST['oldpassword'])) ? base64_decode($_POST['oldpassword']) : false;
	
	if($Email === false || $OldPassword === false)
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'Your email and confirmation password are required'
		);
		return json_encode($result);
	}
	$UpdatePassword = false;
	if($NewPassword !== false && $NewPassword != '')
		$UpdatePassword = true;

	$Email = mysql_real_escape_string($Email);
	$NewPassword = mysql_real_escape_string($NewPassword);
	$OldPassword = mysql_real_escape_string($OldPassword);
	
	if(!ValidEmail($Email))
	{
		$Error = true;
		$ErrorMessage.= 'Please enter a real email address<br />';
	}
	
	if($UpdatePassword)
	{
		if(!ValidPassword($NewPassword))
		{
			$Error = true;
			$ErrorMessage .= 'Please enter a new password at least 5 characters<br />';
		}
	}
	
	if($Error)
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'There were errors in you input: <br /><br />' . $ErrorMessage
		);
		return json_encode($result);
	}
	$OldPassword = encryptPassword($OldPassword);
	$NewPassword = encryptPassword($NewPassword);
	
	$Update = @mysql_query("
UPDATE
	esrgd_rider
SET
	EmailAddress = \"" . $Email . "\",
	" . ($UpdatePassword ? "Password = '" . $NewPassword . "'," : '') . "
	TimeUpdated = '" . time() . "'
WHERE
	RiderID = '" . $_SESSION['RiderInfo']['ID'] . "' AND
	(Password = '" . $OldPassword . "' OR 
	(TemporaryPasswordExpire > '" . time() . "' AND TemporaryPassword = '" . $OldPassword . "'))
	");
	
	if(!$Update)
	{
		if(mysql_errno() == 1062)
			$msg = $Email . ' is already being used.';
		else
			$msg = 'We couldn\'t save your settings right now. Try again.';
		
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> $msg
		);
		return json_encode($result);
	}
	else if(mysql_affected_rows() == 1)
	{
		$_SESSION['RiderInfo']['Email'] = $Email;
		
		$result = array(
			'success'	=> true,
			'errors'	=> false,
			'data'		=> 'Your settings have been saved'
		);
		return json_encode($result);
	}
	else
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'Your old password (for confirmation) was incorrect'
		);
		return json_encode($result);
	}
}

function GetTempPassword()
{
$Email = (isset($_POST['email'])) ? mysql_real_escape_string(base64_decode($_POST['email'])) : false;
	
	if($Email === false || $Email == '' || !ValidEmail($Email))
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'Please enter a real email address.'
		);
		return json_encode($result);
	}
	
	$NewPassword = RandKey(8,'hex');
	$UpdateTempPass = @mysql_query("
UPDATE
	esrgd_rider
SET
	TemporaryPassword = '" . encryptPassword($NewPassword) . "',
	TemporaryPasswordExpire = '" . (time()+(60*60*24)) . "'
WHERE
	EmailAddress = '" . $Email . "'
LIMIT 1
	");
	if(!$UpdateTempPass)
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'We couldn\'t send a temp password right now.  Try again.'
		);
		return json_encode($result);
	}
	else if(mysql_affected_rows() == 1)
	{
		$Headers  = 'MIME-Version: 1.0' . "\r\n";
		$Headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$Headers .= 'From: Dave Gieger <dave@eatsleepridegreatdivide.com>' . "\r\n";
		$To = '<' . $Email . '>';
		
		$Message = 'Great Divide Rider,<br />As per your request, we have given you a temporary password to access your account on <a href="http://eatsleepridegreatdivide.com">Eat. Sleep. Ride. Great Divide.</a><br /><br />
		It is: ' . $NewPassword . '<br /><br />
		If you did not request this, don\'t worry.  Your account is still secure and the temporary password will expire in 24 hours.<br /><br />
		Tailwinds and low traffic,<br />Dave';
		
		@mail($To, 'Temporary Password', $Message, $Headers);
		$result = array(
			'success'	=> true,
			'errors'	=> false,
			'data'		=> 'We sent you a temporary password that will expire in 24 hours.  Check your email.'
		);
		return json_encode($result);
	}
	else
	{
		$result = array(
			'success'	=> false,
			'errors'	=> true,
			'data'		=> 'There was no account associated with that email address.'
		);
		return json_encode($result);
	}
}