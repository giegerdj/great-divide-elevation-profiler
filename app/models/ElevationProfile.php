<?php

class ElevationProfile {
    
    private function __construct() {}
    
    /**
     *
     */
    public static function getCacheName($start_mile, $end_mile) {
        
        $rand = DEBUG ? StringUtils::randKey(16) : '';
        return 'profile_' . md5($start_mile . '-' . $end_mile . '-' . PROFILE_CACHE_KEY) . $rand . '.png';
    }
    
    /**
     *
     */
    public static function createProfile($start_mile, $end_mile, $width, $height) {
        
        require_once(TP_LIB_PATH . 'jpgraph/jpgraph.php');
        require_once(TP_LIB_PATH . 'jpgraph/jpgraph_line.php');
        require_once(TP_LIB_PATH . 'jpgraph/jpgraph_plotline.php');
        
        $right_margin = 10;
        $left_margin = 40;
        
        /**
         * get the coordinates and values between $start_mile and $end_mile
         */
        $points = Coordinates::getSegmentPoints($start_mile, $end_mile);
        
        $graph = new Graph($width, $height);
        $graph->img->setImgFormat('png', 100);
        $graph->img->SetAntiAliasing();
        $graph->img->SetMargin($left_margin, $right_margin, 10, 25);
        $is_reverse = ($start_mile > $end_mile);
        
        $mile_data = ElevationProfile::getGraphMetadata($start_mile, $end_mile);
        
        
        $graph->SetScale('linlin', null, null, $mile_data['scale_start'], $mile_data['scale_end']);
        
        //http://dejavu-fonts.org/wiki/Main_Page
        $graph->title->SetFont(FF_DV_SANSSERIF, FS_BOLD);
        
        
        $graph->title->Set('Great Divide MTB Route: Miles ' . $mile_data['scale_start_mile'] . ' to ' . $mile_data['scale_end_mile'] .
                           ' ' . ($is_reverse ? 'Northbound' : 'Southbound') );
        
        
        $elevation_line = new LinePlot($points['elevations'], $points['distances']);
        $elevation_line->SetFillGradient('#FFFFFF','#006600');	//gradient under line
        $elevation_line->SetColor('#006600');//top of line color
        $graph->Add($elevation_line);
        
        $filename = ElevationProfile::getCacheName($start_mile, $end_mile);
        $graph->Stroke(ABS_WEB_CACHE_PATH . $filename);
        return WEB_CACHE_PATH . $filename;
    }
    
    /**
     *
     */
    public static function getGraphMetadata($start_mile, $end_mile) {
        
        $is_reverse = $start_mile > $end_mile;
        
        $scale_start_mile = $start_mile;
        $scale_end_mile = $end_mile;
        
        $scale_start = $start_mile;
        $scale_end = $end_mile;
        
        if($is_reverse) {
            $total_distance = round(Coordinates::getRouteDistance(),1);
            
            $scale_start = $end_mile;
            $scale_end = $start_mile;
            
            $scale_start_mile = $total_distance - $start_mile;
            $scale_end_mile = $total_distance - $end_mile;
        }
        
        return array(
            'scale_start' => $scale_start,
            'scale_end' => $scale_end,
            'scale_start_mile' => $scale_start_mile,
            'scale_end_mile' => $scale_end_mile
        );
    }
    
}
