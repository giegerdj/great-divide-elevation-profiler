<?php

class ElevationProfile {
    
    private function __construct() {}
    
    /**
     *
     */
    public static function getCacheName($start_mile, $end_mile) {
        
        return 'profile_' . md5($start_mile . '-' . $end_mile) . '.png';
    }
    
    /**
     *
     */
    public static function createProfile($start_mile, $end_mile, $width, $height) {
        
        require_once(TP_LIB_PATH . 'jpgraph/jpgraph.php');
        require_once(TP_LIB_PATH . 'jpgraph/jpgraph_line.php');
        /**
         * get the coordinates and values between $start_mile and $end_mile
         */
        
        
        $graph = new Graph($width, $height);
        $graph->img->setImgFormat('png', 100);
        $graph->SetScale('textlin');
        
        $graph->title->Set('Elevation Profile');
        
        $graph->xgrid->Show(false,false);
        //$graph->xaxis->SetTickLabels($datax);
        
        // Create the line
        $p1 = new LinePlot(array(1,2,3,4,5,6));
        $graph->Add($p1);
        
        $p1->SetFillGradient('#FFFFFF','#006600');	//gradient under line
        $p1->SetColor('#006600');//top of line color
        
        // Output line
        $filename = ElevationProfile::getCacheName($start_mile, $end_mile);
        $graph->Stroke(ABS_WEB_CACHE_PATH . $filename);
        
        return WEB_CACHE_PATH . $filename;
    }
    
    
}
