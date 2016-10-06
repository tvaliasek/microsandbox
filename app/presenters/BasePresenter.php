<?php

namespace App\Presenters;

use Nette;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {
    
    /**
     * @inject
     * @var \App\Model\Web 
     */
    public $web;

    public function beforeRender() {
        parent::beforeRender();
        $this->template->version = '?_ver='.$this->web->getVersion();
        $this->template->develMode = \Tracy\Debugger::isEnabled();
        $this->template->siteName = $this->web->getSiteName();
    }

    
}
