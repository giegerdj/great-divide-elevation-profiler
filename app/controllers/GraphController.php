<?php

class GraphController extends \Tachyon\Controller {
    
    public function get() {
        
        $start_mile = $this->getData('startMile', null);
        $end_mile = $this->getData('endMile', null);
        $profile_width = 1000;
        $profile_height = 250;
        
        $this->ajax_return = array();
        
        try {
            $graph_data = RouteModel::getSegmentProfile($start_mile, $end_mile, $profile_width, $profile_height);
            $this->ajax_return['elevation_profile_url'] = $graph_data['filename'];
        } catch(Exception $e) {
            $this->ajax_return['error'] = "We couldn't create the graph. Try again.";
            error_log($e->getMessage());
        }
        
        $this->render('ajax/json.tpl');
        $this->sendResponse();
    }
}
