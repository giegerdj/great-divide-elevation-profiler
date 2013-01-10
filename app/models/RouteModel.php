<?php

class RouteModel {
    function __construct() { }
    
    /**
     * @method getGraph 
     * @param numeric $start_mile 
     * @param numeric $end_mile 
     * @param int $width 
     * @param int $height 
     * @return array
     */
    function getSegmentProfile($start_mile, $end_mile, $width, $height) {
        
        if( !isset($start_mile, $end_mile) ) {
            throw new Exception('no distance bounds set');
        }
        
        /**
         * check for a cached version of the elevation profile
         */
        $graph_filename = ElevationProfile::getCacheName($start_mile, $end_mile);
        if( is_file(ABS_WEB_CACHE_PATH . $graph_filename) ) {
            return array(
                'filename' => WEB_CACHE_PATH . $graph_filename
            );
        }
        
        /**
         * the elevation profile is not in cache, so lets create it
         */
        $graph_filename = ElevationProfile::createProfile($start_mile, $end_mile, $width, $height);
        
        
        return array(
            'filename' => $graph_filename
        );
    }//end method getGraph
    
    /**
     *
     */
    public function getSegmentStats($start_mile, $end_mile) {
        
    }//end method getSegmentStats
}
