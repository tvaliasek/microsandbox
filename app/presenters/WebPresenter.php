<?php

namespace App\Presenters;

use App\Model;


class WebPresenter extends BasePresenter {
    
    /*public function beforeRender() {
        parent::beforeRender();
        $template = $this->template;
        $web = $this->web;
    }*/
    
    public function renderDefault($id=null) {
        $template = $this->template;
        $web = $this->web;
        $template->mainLinks = $web->getNavigation(Model\Web::MAIN_NAVIGATION);
        if($id!==null){
            if($web->pageExists($id)){
                $template->onHomepage = false;
                $template->setFile($web->getPageTemplate($id));
                $customParams = $web->getCustomParams($id);
                if(!empty($customParams)){
                    foreach($customParams as $variableName=>$value){
                        $template->$variableName = $value;
                    }
                }
            } else {
                throw new \Nette\Application\BadRequestException;
            }
        } else {
            $template->onHomepage = true;
        }
    }
    
}
