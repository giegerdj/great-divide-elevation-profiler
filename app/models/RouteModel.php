<?php

class RouteModel {
    function __construct() { }
    
    /**
     * @method getSegmentProfile
     * @param float $start_mile 
     * @param float $end_mile
     * @param int $width 
     * @param int $height 
     * @return array
     */
    function getSegmentProfile($start_mile, $end_mile, $width, $height) {
        
        if( !isset($start_mile, $end_mile) ) {
            throw new Exception('no bounds set');
        }
        
        $start_mile = round($start_mile, 1);
        $end_mile = round($end_mile, 1);
        
        /**
         * check for a cached version of the elevation profile
         */
        $graph_filename = ElevationProfile::getCacheName($start_mile, $end_mile);
        if( is_file(ABS_WEB_CACHE_PATH . $graph_filename) ) {
            return array(
                'filename' => WEB_CACHE_PATH . $graph_filename,
                'cache_hit' => true
            );
        }
        
        /**
         * the elevation profile is not in cache, so lets create it
         */
        $graph_filename = ElevationProfile::createProfile($start_mile, $end_mile, $width, $height);
        
        
        return array(
            'filename' => $graph_filename,
            'cache_hit' => false
        );
    }//end method getGraph
    
    /**
     *
     */
    public function getSegmentStats($start_mile, $end_mile) {
        
        $is_reverse = $start_mile > $end_mile;
        
        $stats = Coordinates::getSegmentStats($start_mile, $end_mile);
        
        return array(
            'ascent' => $is_reverse ? $stats['descent'] : $stats['ascent'],
            'descent' => $is_reverse ? $stats['ascent'] : $stats['descent'],
            'distance' => $stats['distance'],
            'net' => ($is_reverse ? -1 : 1) * $stats['net'],
            'start_mile' => $stats['start_mile'],
            'end_mile' => $stats['end_mile']
        );
    }//end method getSegmentStats
    
    /**
     *
     */
    public function getClosestCoord($lat, $lng) {
        
        $result = Coordinates::getClosestCoord($lat, $lng);
        
        return array(
            'mile' => $result['banff_distance'],
            'coordinate' => array(
                'lat' => $result['lat'],
                'lng' => $result['lng'])
        );
    }
}
