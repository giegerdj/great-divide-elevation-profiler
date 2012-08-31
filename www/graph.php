<?php 
require_once('../includes/php/config.php');
require_once('../includes/php/db_connect.php');
require_once('../includes/php/funct.php');

require_once('../includes/php/ElevationProfile.class.php');

require_once('../includes/php/jpgraph/jpgraph.php');
require_once('../includes/php/jpgraph/jpgraph_line.php');

header('content-type: image/png');

$EP = new ElevationProfile();
$Coords = isset($_GET['c']) ? explode(',',$_GET['c']) : null;
$Height = isset($_GET['h']) ? mysql_real_escape_string($_GET['h']) : 200;
$Width = isset($_GET['w']) ? mysql_real_escape_string($_GET['w']) : 1000;

$Width = !IsNumeric($Width) ? 1000 : $Width;
$Height = !IsNumeric($Height) ? 200 : $Height;

if(sizeof($Coords) == 4)
{
	if(!IsNumeric($Coords[0]) || !IsNumeric($Coords[1]) || !IsNumeric($Coords[2]) || !IsNumeric($Coords[3]))
	{
		$EP->SetCoords(126370,1);
	}
	else
	{
		$EP->SetLatLng(array($Coords[0],$Coords[1]),array($Coords[2],$Coords[3]));
	}
}
else
{
	$EP->SetCoords(126370,1);
}

$tmp = $EP->GetElevationArray();
$datay = $tmp[0];
$datax = $tmp[1];
// Setup the graph
$graph = new Graph($Width,$Height);
$graph->SetScale("textlin");

$graph->title->Set('Elevation Profile');

$graph->xgrid->Show(false,false);
$graph->xaxis->SetTickLabels($datax);

// Create the line
$p1 = new LinePlot($datay);
$graph->Add($p1);

$p1->SetFillGradient('#FFFFFF','#006600');	//gradient under line
$p1->SetColor('#006600');//top of line color

// Output line
$graph->Stroke();