<?php
require_once('config.php');
require_once('db_connect.php');
require_once('funct.php');

define('ALPHA',50.50);

class ElevationProfile
{
    private $StartCoordID;
    private $EndCoordID;
    private $NorthToSouth = true;
    private $StartMile;
    private $EndMile;
    
    function __construct() {
        
    }
    
    private function GetDirection() {
        $this->NorthToSouth = ($this->StartCoordID > $this->EndCoordID);
    }
    
    public function SetLatLng($Source,$Dest) {
        //find closest CoordID for each pair
        $tmp = $this->GetClosestCoordID($Source);
        $this->StartCoordID = $tmp[0];
        $this->StartMile = $tmp[1];
        
        $tmp = $this->GetClosestCoordID($Dest);
        $this->EndCoordID = $tmp[0];
        $this->EndMile = $tmp[1];
        
        $this->GetDirection();
    }
    
    public function SetCoords($Source,$Dest) {
        $this->StartCoordID = $Source;
        $this->EndCoordID = $Dest;
        $this->GetDirection();
        
        $tmp = $this->GetDistanceFromIDs($this->StartCoordID,$this->EndCoordID);
        if($this->NorthToSouth) {
            $this->StartMile = min($tmp);
            $this->EndMile = max($tmp);
        } else {
            $this->StartMile = max($tmp);
            $this->EndMile = min($tmp);
        }
    }
    private function GetDistanceFromIDs($CoordIDA,$CoordIDB)
    {
        $A = @mysql_query("
SELECT
    BanffDistance
FROM
    coords_atomic
WHERE
    CoordID = '" . $CoordIDA . "' OR
    CoordID = '" . $CoordIDB . "'
LIMIT 2
        ");
        if($A && mysql_num_rows($A) == 2) {
            return array(mysql_result($A,0,'BanffDistance'), mysql_result($A,1,'BanffDistance'));
        } else {
            return false;
        }
    }
    
    private function GetClosestCoordID($LatLng) {
        $A = @mysql_query("
   SELECT
    CoordID,BanffDistance,
    SQRT(
     POW(Lat-" . $LatLng[0] . ",2) + POW(Lng-" . $LatLng[1] . ",2)
     ) AS Dist
   FROM
    coords_atomic
   WHERE
    Lat BETWEEN '" . ($LatLng[0]-ALPHA) . "' AND '" . ($LatLng[0]+ALPHA) . "' AND
    Lng BETWEEN '" . ($LatLng[1]-ALPHA) . "' AND '" . ($LatLng[1]+ALPHA) . "'
   ORDER BY
    Dist ASC
   LIMIT 1
   ");
        if($A && mysql_num_rows($A) == 1) {
            return array(mysql_result($A,0,'CoordID'), mysql_result($A,0,'BanffDistance'));
        } else {
            return false;
        }
    }
    
    public function GetSectionStats() {
        $MinCoord = min(array($this->StartCoordID,$this->EndCoordID));
        $MaxCoord = max(array($this->StartCoordID,$this->EndCoordID));
     
        $ElevationResult = @mysql_query("
SELECT
    SUM(TotalAscent) AS GlobalAscent,SUM(TotalDescent) AS GlobalDescent
FROM
    coords_2
WHERE
    CoordID BETWEEN '" . $MinCoord . "' AND '" . $MaxCoord . "'
    ");
    
        if($ElevationResult && @mysql_num_rows($ElevationResult) == 1) {
            $info = @mysql_fetch_assoc($ElevationResult);
            $Distance = abs($this->StartMile-$this->EndMile);
            
            if(!$this->NorthToSouth) {
                $tmp = $info['GlobalAscent'];
                $info['GlobalAscent'] = $info['GlobalDescent'];
                $info['GlobalDescent'] = $tmp;
            }
            
            $Stats = array(
                'StartCoord'		=> $this->StartCoordID,
                'EndCoord'			=> $this->EndCoordID,
                'StartMile'			=> ElevationFormat($this->StartMile,2),
                'EndMile'				=> ElevationFormat($this->EndMile,2),
                'Distance'			=> round($Distance,2),
                'Ascent'				=> ElevationFormat($info['GlobalAscent'],0),
                'Descent'				=> ElevationFormat($info['GlobalDescent'],0),
                'Net'						=> ElevationFormat($info['GlobalAscent'] - $info['GlobalDescent'],0),
                'NorthToSouth'	=> $this->NorthToSouth
            );
            return $Stats;
        } else {
            return false;
        }
    }
    
    public function GetElevationArray() {
        $Stats = $this->GetSectionStats();
        
        $grain = 128;
        if($Stats['Distance'] > 2560)
        {
         $grain = 128;
        }
        else if($Stats['Distance'] > 1280)
        {
         $grain = 64;
        }
        else if($Stats['Distance'] > 640)
        {
         $grain = 32;
        }
        else if($Stats['Distance'] > 320)
        {
         $grain = 16;
        }
        else if($Stats['Distance'] > 160)
        {
         $grain = 8;
        }
        else if($Stats['Distance'] > 80)
        {
         $grain = 4;
        }
        else if($Stats['Distance'] > 40)
        {
         $grain = 2;
        }
        else
        {
         $grain = 2;
        }
        
        $MinCoord = min($this->StartCoordID,$this->EndCoordID);
        $MaxCoord = max($this->StartCoordID,$this->EndCoordID);
        $ElevationQuery = @mysql_query("
    SELECT
    Elevation
    FROM
    coords_atomic,coords_" . $grain . "
    WHERE
    coords_atomic.CoordID = coords_" . $grain . ".CoordID AND
    coords_atomic.CoordID BETWEEN '" . $MinCoord . "' AND '" . $MaxCoord . "'
    ORDER BY
    coords_atomic.CoordID " . (($this->NorthToSouth) ? 'DESC' : 'ASC') . "
     ");
     
        $xArray = array();
        $yArray = array();
        if($ElevationQuery)
        {
            $size = mysql_num_rows($ElevationQuery);
            for($x=0; $x < $size; $x++)
            {
                $yArray[$x] = FEET_PER_METER * mysql_result($ElevationQuery,$x,'Elevation');
                
                if($x == 0)
                {
                 $xArray[$x] = round($this->StartMile,0);
                }
                else if($x == $size-1)
                {
                 $xArray[$x] = round($this->EndMile,0);
                }
                else if($x%15 == 0 && $x < ($size-10))
                {
                 if($this->NorthToSouth)
                  $xArray[$x] = round(($this->StartMile+(($x*$grain)/10)),0);
                 else
                  $xArray[$x] = round(($this->StartMile-(($x*$grain)/10)),0);
                }
                else
                {
                 $xArray[$x] = '';
                }
            }
        }
        
        return array($yArray,$xArray);
    }
}