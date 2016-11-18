<?php
namespace App\Components;

use Nette\Application\UI,    
    Nette\Mail\SendmailMailer,    
    Nette\Mail\Message,    
    Latte\Engine,    
    Nette\Forms\Form;

class ContactForm extends UI\Control
{ 
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
    /**
     * @var Nette\Application\UI\Form
     */
    public $form;
    /**
     * @var callable 
     */
    public $onSuccess = [];
      
    const DEFAULT_TEMPLATE = '/ContactForm.latte';
    const SAVE_TO_FILE = 'file';
    const LOG_FILENAME = 'ContactFormSubmissions.log';
    
    public function __construct($saveTo, $saveTarget, $mailFrom, $subject, $sendTo, $emailTemplate, $bootstrapRender = true, $submitText = 'Send', $submitOnClick = null){
        parent::__construct();
        $this->saveTo = $saveTo;
        if(!$this->saveTo instanceof \Nette\Database\Context){
            $this->saveTarget = __DIR__. $saveTarget;
        } else {
            $this->saveTarget = $saveTarget;
        }
        $this->mailFrom = $mailFrom;
        $this->subject = $subject;
        $this->sendTo = $sendTo;
        $this->emailTemplate = $emailTemplate;
        $this->bootstrapRender = $bootstrapRender;
        $this->submitText = $submitText;
        $this->submitOnClick = $submitOnClick;
        
        $this->form = new UI\Form();
        if($this->bootstrapRender){
            $renderer = new \Instante\Bootstrap3Renderer\BootstrapRenderer();
            $renderer->setLabelColumns(12, 12)->setRenderMode('vertical');
            $this->form->setRenderer($renderer);
        }
    }
    
    public function render(){
        if($this->templateFilePath == null){
            $this->detectTemplateFile();
        }
        $this->template->render(__DIR__.$this->templateFilePath);
    }
    
    
    /************************** COMMON ********************************/
    private function detectTemplateFile(){
        $this->templateFilePath = (file_exists(__DIR__.'/'.$this->getName().'.latte')) ? '/'.$this->getName().'.latte' : self::DEFAULT_TEMPLATE;
    }
    
    public function setTemplateFile($templatePath){
        $this->templateFilePath = $templatePath;
    }
    
    /****************************** EMAIL ************************************/
    public function setFrom($email){
        $this->mailFrom = $email;
        return $this;
    }
    
    public function setTo($email){
        $this->sendTo = $email;
        return $this;
    }
    
    public function addTo($email){
        if(!$this->isArray($this->sendTo)){
            $this->sendTo = [$this->sendTo];
        }
        $this->sendTo[] = $email;
        return $this;
    }
    
    public function setSubject($subject){
        $this->subject = $subject;
        return $this;
    }
    
    public function setEmailTemplateFile($fileTemplate){
        if(file_exists($fileTemplate)){
            $this->emailTemplate = $fileTemplate;
        } else {
            throw new \Exception('Invalid email template file');
        }
        return $this;
    }
    
    public function addFieldValueReplacement($fieldName, array $replacement){
        $this->fieldValueReplacements[$fieldName] = $replacement;
        return $this;
    }
    
    /***************************** FORM *********************************/
    protected function createComponentContactControlForm(){
        if($this->submitOnClick == null){
            $this->form->addSubmit('send', $this->submitText);
        } else {
            $this->form->addSubmit('send', $this->submitText)->setAttribute('onClick', $this->submitOnClick);
        }
        $this->form->onSuccess[] = [$this, 'contactControlFormSucc'];
        return $this->form;
    }
    
    public function contactControlFormSucc($form, $values){
        $submissionSaved = $this->saveSubmission($values);
        $emailSent = $this->sendEmail($values);
        foreach($this->onSuccess as $callback){
            call_user_func_array($callback, ['form'=>$form, 'values'=>$values, 'saved'=>($submissionSaved!==false), 'sent'=>($emailSent!==false)]);
        }
    }
    
    private function sendEmail($formValues){
        $latte = new Engine();
        \Latte\Macros\CoreMacros::install($latte->getCompiler());
        \Nette\Bridges\ApplicationLatte\UIMacros::install($latte->getCompiler());
        $message = new Message();
        $mailer = new SendmailMailer();
        $params = ['data'=>[]];
        foreach($formValues as $key=>$value){
            $component = $this->form->getComponent($key);
            if($component !== null && $component!== false){
                $label = $component->getLabelPrototype()->getText();
            } else {
                $label = $key;
            }
            $params['data'][] = ['value'=>(key_exists($key, $this->fieldValueReplacements) && in_array($value, $this->fieldValueReplacements[$key])) ? $this->fieldValueReplacements[$key][$value] : $value, 'label'=>$label, 'key'=>$key];
        }
        $body = $latte->renderToString($this->emailTemplate, $params);
        $message->setFrom($this->mailFrom);
        if(is_array($this->sendTo)){
            foreach($this->sendTo as $address){
                $message->addTo($address);
            }
        } else {
            $message->addTo($this->sendTo);
        }
        $message->setSubject($this->subject);
        $message->setHtmlBody($body);
        return $mailer->send($message);
    }
    
    private function saveSubmission($formValues){
        $formValues['form_submitted_date'] = new \DateTime();
        if($this->saveTo instanceof \Nette\Database\Context){
            return $this->saveTo->table($this->saveTarget)->insert($formValues);
        } else {
            if(!is_dir($this->saveTarget)){
                mkdir($this->saveTarget, 0750, true);
            }
            return file_put_contents($this->saveTarget.'/'.self::LOG_FILENAME, json_encode($formValues).PHP_EOL , FILE_APPEND | LOCK_EX);
        }
    }
}

interface IContactFormFactory
{
    /** @return ContactForm */
    function create();
}