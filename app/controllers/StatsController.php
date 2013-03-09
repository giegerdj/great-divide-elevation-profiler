<?php

class StatsController extends \Tachyon\Controller {
    
    public function post() {
        
        $start_coordinate = $this->getPOST('start_coord', null);
        $end_coordinate = $this->getPOST('end_coord', null);
        
        $profile_width = 1050;
        $profile_height = 200;
        
        $this->ajax_return = array();
        
        try {
            
            $closest_start_coord = RouteModel::getClosestCoord($start_coordinate[0], $start_coordinate[1]);
            $closest_end_coord = RouteModel::getClosestCoord($end_coordinate[0], $end_coordinate[1]);
            
            
            $start_mile = $closest_start_coord['mile'];
            $end_mile = $closest_end_coord['mile'];
            
            
            $graph_data = RouteModel::getSegmentProfile($start_mile, $end_mile, $profile_width, $profile_height);
            $this->ajax_return['elevation_profile_url'] = $graph_data['filename'];
            $this->ajax_return['cache_hit'] = $graph_data['cache_hit'];
            
            $stats = RouteModel::getSegmentStats($start_mile, $end_mile);
            $this->ajax_return['stats'] = array(
                'ascent' => $stats['ascent'],
                'descent' => $stats['descent'],
                'relative_start_mile' => $stats['relative_start_mile'],
                'relative_end_mile' => $stats['relative_end_mile'],
                'absolute_start_mile' => $stats['absolute_end_mile'],
                'absolute_end_mile' => $stats['absolute_end_mile'],
                'distance' => $stats['distance'],
                'start_coordinate' => $closest_start_coord['coordinate'],
                'end_coordinate' => $closest_end_coord['coordinate'],
                'net_elevation' => $stats['net']
            );
        } catch(ESRGD\DatabaseException $e) {
            $this->ajax_return['error'] = "Something went wrong pulling the route stats from the database. Try again?";
        } catch(ESRGD\NotFoundException $e) {
            $this->ajax_return['error'] = "We couldn't find any points for the selected route.  Try expanding the selection.";
        } catch(Exception $e) {
            $this->ajax_return['error'] = "We couldn't create the graph. Try again.";
        }
        
        $this->render('ajax/json.tpl');
        $this->sendResponse();
    }
}
