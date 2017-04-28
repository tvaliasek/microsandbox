<?php

namespace App\Components;

use Nette\Application\UI,
    Nette\Mail\SendmailMailer,
    Nette\Mail\Message,
    Latte\Engine;

class ContactFormControl extends UI\Control {

    private $templateFilePath = null;
    private $saveTo;
    private $saveTarget;
    private $mailFrom;
    private $subject;
    private $sendTo;
    private $emailTemplate;
    private $bootstrapRender;
    private $submitText;
    private $submitOnClick = null;
    private $fieldValueReplacements = [];
    private $ajax = false;
    private $successText = false;
    private $errorText = false;

    /**
     * @var Nette\Application\UI\Form
     */
    public $form;

    /**
     * @var callable 
     */
    public $onSuccess = [];

    /**
     * @var callable 
     */
    public $onValidate = [];

    const DEFAULT_TEMPLATE = '/ContactForm.latte';
    const SAVE_TO_FILE = 'file';
    const LOG_FILENAME = 'ContactFormSubmissions.log';

    public function __construct(ContactFormControlSettings $settings) {
        parent::__construct();

        foreach ($settings->getConfig() as $key => $value) {
            $this->$key = $value;
        }
        $this->form = new UI\Form();
        if ($this->bootstrapRender) {
            $renderer = new \Instante\Bootstrap3Renderer\BootstrapRenderer();
            $renderer->setLabelColumns(12, 12);
            $this->form->setRenderer($renderer);
        }
    }

    public function render() {
        if ($this->templateFilePath == null) {
            $this->detectTemplateFile();
        }
        $this->template->render(__DIR__ . $this->templateFilePath);
    }

    /*     * ************************ COMMON ******************************* */

    /**
     * Detects template file by created component name
     */
    private function detectTemplateFile() {
        $this->templateFilePath = (file_exists(__DIR__ . '/' . $this->getName() . '.latte')) ? '/' . $this->getName() . '.latte' : self::DEFAULT_TEMPLATE;
    }
    
    /**
     * Sets template file path
     * @param string $templatePath
     */
    public function setTemplateFile($templatePath) {
        $this->templateFilePath = $templatePath;
    }

    /*     * **************************** EMAIL *********************************** */

    /**
     * Set email sender address
     * @param string $email
     * @return $this
     */
    public function setFrom($email) {
        $this->mailFrom = $email;
        return $this;
    }

    /**
     * Set email recipient address
     * @param string $email
     * @return $this
     */
    public function setTo($email) {
        $this->sendTo = $email;
        return $this;
    }

    /**
     * Add another recipient address
     * @param string $email
     * @return $this
     */
    public function addTo($email) {
        if (!is_array($this->sendTo)) {
            $this->sendTo = [$this->sendTo];
        }
        $this->sendTo[] = $email;
        return $this;
    }

    /**
     * Set email subject
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set email template file
     * @param string $fileTemplate
     * @return $this
     * @throws \Exception
     */
    public function setEmailTemplateFile($fileTemplate) {
        if (file_exists($fileTemplate)) {
            $this->emailTemplate = $fileTemplate;
        } else {
            throw new \Exception('Invalid email template file');
        }
        return $this;
    }

    /**
     * Add field value replacement array for use in email
     * @param string $fieldName (name of form control which value replaces value from replacement)
     * @param array $replacement
     * @return $this
     */
    public function addFieldValueReplacement($fieldName, array $replacement) {
        $this->fieldValueReplacements[$fieldName] = $replacement;
        return $this;
    }

    /*     * *************************** FORM ******************************** */

    /**
     * Factory for main form
     * @return Form
     */
    protected function createComponentContactControlForm() {
        if ($this->submitOnClick == null) {
            $this->form->addSubmit('send', $this->submitText);
        } else {
            $this->form->addSubmit('send', $this->submitText)->setAttribute('onClick', $this->submitOnClick);
        }
        if($this->ajax == true){
            $this->form->getElementPrototype()->addAttributes(['class'=>'ajax']);
        }
        $this->form->onSuccess[] = [$this, 'contactControlFormSucc'];
        $this->form->onValidate[] = [$this, 'contactControlFormValidate'];
        return $this->form;
    }

    /**
     * Validate callbacks
     * @param type $form
     */
    public function contactControlFormValidate($form) {
        foreach ($this->onValidate as $callback) {
            call_user_func_array($callback, ['form' => $form, 'contactFormComponent' => $this]);
        }
    }

    /**
     * Save data, send email and call user callbacks 
     * @param type $form
     * @param type $values
     */
    public function contactControlFormSucc($form, $values) {
        $submissionSaved = $this->saveSubmission($values);
        $emailSent = $this->sendEmail($values);
        if($this->getPresenter()->isAjax()){
            $this->template->message = ($submissionSaved!==false && $emailSent!==false) ? $this->successText : $this->errorText;
            $this->redrawControl('snpcContactForm');
        }
        foreach ($this->onSuccess as $callback) {
            call_user_func_array($callback, ['form' => $form, 'values' => $values, 'saved' => ($submissionSaved !== false), 'sent' => ($emailSent !== false)]);
        }
    }
    
    /**
     * Enable / Disable ajax submission
     * @param bool $state
     * @return $this
     */
    public function enableAjax($state = true){
        $this->ajax = $state;
        return $this;
    }
    
    /**
     * Set ajax success message
     * @param string $text
     * @return $this
     */
    public function setAjaxSuccessText($text){
        $this->successText = $text;
        return $this;
    }
    
    /**
     * Set ajax error message
     * @param string $text
     * @return $this
     */
    public function setAjaxErrorText($text){
        $this->errorText = $text;
        return $this;
    }

    /**
     * Sends email with replaced values etc.
     * @param ArrayHash $formValues
     * @return bool
     */
    private function sendEmail($formValues) {
        $latte = new Engine();
        \Latte\Macros\CoreMacros::install($latte->getCompiler());
        \Nette\Bridges\ApplicationLatte\UIMacros::install($latte->getCompiler());
        $message = new Message();
        $mailer = new SendmailMailer();
        $params = ['data' => []];
        foreach ($formValues as $key => $value) {
            $component = $this->form->getComponent($key);
            if ($component !== null && $component !== false) {
                $label = $component->caption;
            } else {
                $label = $key;
            }
            $tempValue = (key_exists($key, $this->fieldValueReplacements) && key_exists($value, $this->fieldValueReplacements[$key])) ? $this->fieldValueReplacements[$key][$value] : $value;
            $params['data'][] = ['value' => $tempValue, 'label' => $label, 'key' => $key];
        }
        $body = $latte->renderToString(__DIR__ . $this->emailTemplate, $params);
        $message->setFrom($this->mailFrom);
        if (is_array($this->sendTo)) {
            foreach ($this->sendTo as $address) {
                $message->addTo($address);
            }
        } else {
            $message->addTo($this->sendTo);
        }
        
        $message->setSubject($this->subject);
        $message->setHtmlBody($body);
        return $mailer->send($message);
    }

    /**
     * Saves submission to file or database table
     * @param ArrayHash $formValues
     * @return bool
     */
    private function saveSubmission($formValues) {
        $values = clone $formValues;
        $values['form_submitted_date'] = new \DateTime();
        if ($this->saveTo instanceof \Nette\Database\Context) {
            return $this->saveTo->table($this->saveTarget)->insert($values);
        } else {
            if (!is_dir($this->saveTarget)) {
                mkdir($this->saveTarget, 0750, true);
            }
            return file_put_contents($this->saveTarget . '/' . self::LOG_FILENAME, json_encode($values) . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }

}

interface IContactFormControlFactory {

    /**
     * 
     * @return ContactFormControl
     */
    function create();
}
