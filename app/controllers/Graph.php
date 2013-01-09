<?php

class Graph extends \Tachyon\Controller {
    
    public function get() {
        
        //$start_mile = $this->getData('startMile', '');
        //$end_mile = $this->getData('endMile', '');
        /*
        $post = Post::getPostBySlugAndType($slug, 'recipe');
        
        if( $post == null ) {
            $this->response->append("Page not found");
            $this->sendResponse(404);
        } else {
            $this->recipe = $post;
            
            $this->meta_tags = array(
                'og:title' => htmlspecialchars($post->title, ENT_QUOTES),
                'og:description' => htmlspecialchars($post->content, ENT_QUOTES),
                'og:type' => 'article',
                'og:url' => "http://m.horizondairy.com/recipe/" . $post->slug,
                'og:image' => $post->thumbnail,
            );
            
            $this->renderWithLayout("recipes/show.tpl");
            $this->sendResponse();
        }
        */
        error_log('test');
        $this->render('ajax/json.tpl');
        $this->sendResponse();
    }
}
