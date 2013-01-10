<?php

class StatsController extends \Tachyon\Controller {
    
    public function get() {
        
        $start_mile = $this->getData('startMile', null);
        $end_mile = $this->getData('endMile', null);
        
        $this->ajax_return = array();
        /*
        try {
            $graph_data = ElevationProfile::getGraph($start_mile, $end_mile);
        } catch(Exception $e) {
            $this->ajax_return['error'] = "We couldn't create the graph. Try again.";
        }*/
        
        $this->render('ajax/json.tpl');
        $this->sendResponse();
    }
}
