<?php

namespace App\Presenters;

use App\Model;

class WebPresenter extends BasePresenter {

    /**
     * @inject
     * @var \App\Components\IContactFormControlFactory
     */
    public $contactForm;

    public function renderDefault($id = null) {
        $template = $this->template;
        $web = $this->web;
        $template->mainLinks = $web->getNavigation(Model\Web::MAIN_NAVIGATION);
        if ($id !== null) {
            if ($web->pageExists($id)) {
                $template->onHomepage = false;
                $template->setFile($web->getPageTemplate($id));
                $customParams = $web->getCustomParams($id);
                $template->pageData = $web->getPageData($id);
                if (!empty($customParams)) {
                    foreach ($customParams as $variableName => $value) {
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

    /* Example for contact form */

    protected function createComponentSomeContactForm() {
        $cf = $this->contactForm->create();
        $cf->enableAjax();
        $cf->form->addText('name', 'Name and surname')
                ->setRequired('%label is required');
        $cf->form->addEmail('email', 'Email')
                ->setRequired('%label is required');
        $cf->form->addText('phone', 'Phone')
                ->setRequired('%label is required')
                ->addRule(\Nette\Forms\Form::MIN_LENGTH, '%label must have at least 9 characters', 9);
        $cf->form->addSelect('replacement', 'Array replacement example', [0=>'Item 1 on index 0', 1=>'Item 2 on index 1']);
        $cf->addFieldValueReplacement('replacement', [0=>'Item 1 on index 0', 1=>'Item 2 on index 1']);
        $cf->onValidate[] = [$this, 'someContactFormValidate'];
        $cf->onSuccess[] = [$this, 'someContactFormSucc'];
        return $cf;
    }

    public function someContactFormSucc($form, $values, $saved, $sent) {
        // do something? like show flash message with result if Ajax is not enabled
        if(!$this->isAjax()){
            if($saved == true && $sent == true){
                $this->flashMessage('Your submission has been saved', 'success');
            } else {
                $this->flashMessage('Some error occurred, try again later.', 'error');
            }
            $this->redirect('default', ['id'=>$this->getParameter('id')]);
        }
    }

    public function someContactFormValidate($form, $contactFormComponent) {
        // do something? In this example: email recipient is set to filled email.
        $values = $form->getValues();
        $contactFormComponent->setTo($values->email);
    }

}
