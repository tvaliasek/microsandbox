<?php
/**
 * Service for passing configuration to ContactForm component
 */

namespace App\Components;

final class ContactFormControlSettings extends \Nette\Object {

    private $config;

    public function __construct($saveTo, $saveTarget, $mailFrom, $subject, $sendTo, $emailTemplate, $bootstrapRender = true, $submitText = 'Send', $submitOnClick = null, $ajax = false, $successText = false, $errorText = false) {

        $this->config['saveTo'] = $saveTo;
        if (!$this->config['saveTo'] instanceof \Nette\Database\Context) {
            $this->config['saveTarget'] = __DIR__ . $saveTarget;
        } else {
            $this->config['saveTarget'] = $saveTarget;
        }
        $this->config['mailFrom'] = $mailFrom;
        $this->config['subject'] = $subject;
        $this->config['sendTo'] = $sendTo;
        $this->config['emailTemplate'] = $emailTemplate;
        $this->config['bootstrapRender'] = $bootstrapRender;
        $this->config['submitText'] = $submitText;
        $this->config['submitOnClick'] = $submitOnClick;
        $this->config['ajax'] = $ajax;
        $this->config['successText'] = $successText;
        $this->config['errorText'] = $errorText;
    }

    public function getConfig() {
        return $this->config;
    }

}
