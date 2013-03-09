<?php

class NotFoundController extends \Tachyon\Controller {
    public function get() {
        $this->response->append("Page not found");
        $this->sendResponse(404);		
    }
}
