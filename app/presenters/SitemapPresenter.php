<?php

namespace App\Presenters;

use Nette;

class SitemapPresenter extends BasePresenter {
    
    /**
     * @inject
     * @var \App\Model\Web
     */
    public $web;
    
    public function renderDefault() {
        $this->template->pages = $this->web->getPageIds();
    }
    
    public function renderRobots(){
        
    }
}
