<?php
function randKey($length,$mode = '')
{
	$string = '';
	
	switch($mode)
	{
		case 'all':
			$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_=+[]{},./<>?~';
		break;
		case 'hex':
			$pool = '0123456789ABCDEF';
		break;
		case 'num':
			$pool = '0123456789';
		break;
		case 'alnum':
			$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		break;
		default:
			$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		break;
	}
	$strLen=strlen($pool)-1;
	for($X = 0 ; $X < $length ; $X++)
	{
		$string .= $pool[rand(0,$strLen)];
	}
	
	return $string;
}

function clean_input($string)
{
	$string  = str_replace("\"","",$string);
	$string = str_replace("'","",$string);
	$string = str_replace("\\","",$string);
	$string = mysql_real_escape_string($string);
	return $string;
}


function ValidPassword($password)
{
	return (strlen($password) >= 5);
}

function ValidEmail($email)
{
	if(!preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/', $email))
		return false;
	else
		return true;
}

function ValidURLFormat($url)
{
	if(!preg_match('/^https?:\/\/[a-z0-9-]+(\.[a-z0-9-]+)+/i',$url))
		return false;
	else
		return $url;
}

function ElevationFormat($Number,$NumDecimals)
{
	$tmp = number_format($Number , $NumDecimals ,'.',',');
	if(substr($tmp,0,2) == '-0')
	{
		$tmp = '0';
		if($NumDecimals > 0)
		{
			$tmp .='.';
			for($x=0; $x < $NumDecimals;$x++)
			$tmp.='0';
		}
	}
	return $tmp;
}

function IsNumeric($NumString)
{
	return preg_match("/^(-)?[0-9]+(.[0-9]+)?$/",$NumString);
}

function ValidNameFormat($Name)
{
	return preg_match("/^([a-zA-Z ']+)$/",$Name);
}

function AccountCreated($name,$email)
{
	$EmailBody = array(2);
	$EmailBody['subject'] = 'Welcome to Eat. Sleep. Ride. Great Divide.';
	$EmailBody['message'] = '<p>Hey ' . $name . '</p>';
}

function EscapeXMLTagContents($Body)
{
	return str_replace(
		array('&','<','>','"',"'",'$'),
		array('&amp;','&lt;','&gt;','&quot;',"&#39;",'&#36;'),
		$Body);
}
function encryptPassword($pepper)
{
	$saltValue = array('@Henry@David@Thoreau@',
		'!Ralph!Waldo!Emerson!',
		'~Frederich~Nietzsche~',
		'#Siddhartha#Gautama#',
		'%George%Gordon%Byron%');
	
	$sodiumValue = array('*Robert*Lee*Frost*',
		'&Christopher&Johnson&McCandless&',
		'+Kong+Qiu+',
		'^Gary^Paulsen^');
	
	$salt=0; //create some salt
 	$dinner=$pepper; //add our pepper to our dinner
	$pepper= preg_split('//', $pepper, -1, PREG_SPLIT_NO_EMPTY); //split out the pepper into an array
	foreach($pepper as $key => $value)
	{
		$salt+=ord($value); //get the ascii values of our pepper
	}
	
	$sodium = $salt % 4;
	$salt = $salt % 5; //devide the salt by 5 and return the remainder

	$dinner=md5($saltValue[$salt]) . sha1($dinner) . hash('sha256',$sodiumValue[$sodium]);//add the salt to our dinner and mix it right up
	$dinner=sha1($dinner);//mix our dinner up some more
	return $dinner;
}

function ValidTitle($Title)
{
	$len = strlen(trim($Title));
	if($len == 0 || $len > 200)
	{
		return false;
	}
	else
	{
		return $Title;
		//return htmlspecialchars($Title, ENT_QUOTES, 'UTF-8');
	}
}

function ValidSpotURL($Spot)
{
	$Len = strlen(trim($Spot));
	if($Len == 0 || $Len > 64)
	{
		return false;
	}
	else if(preg_match("/^(0-9a-zA-Z)*$/",$Spot))
	{
		return false;
	}
	else
	{
		return trim($Spot);
	}
}

function ValidRideType($Type)
{
	if($Type == 'Slow Tour' || $Type == 'Tour Divide' || $Type == 'Great Divide Race')
		return true;
	else
		return false;
}

function ValidDirection($Dir)
{
	if($Dir == 'N' || $Dir == 'S')
		return true;
	else
		return false;
}