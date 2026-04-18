<?php

class ContactController extends ContactControllerCore
{
    
    /*
    * module: recaptcha
    * date: 2022-10-13 15:53:58
    * version: 1.2.5
    */
    public function preProcess()
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            self::$smarty->assign('HOOK_CONTACT_FORM_BOTTOM', Module::hookExec('contactFormBottom'));
        }
        if (version_compare(_PS_VERSION_, '1.5', '<') &&
            Tools::isSubmit('submitMessage') && Module::isInstalled('recaptcha')) {
            require_once(_PS_ROOT_DIR_.'/modules/recaptcha/recaptcha.php');
            $recaptcha = new Recaptcha();
            $testText = $recaptcha->validateCaptcha();
            if ($testText and $testText !== true) {
                $this->errors[] = $recaptcha->l('Invalid captcha.');
                unset($_POST['submitMessage']);
            }
        }
        parent::preProcess();
    }
    /*
    * module: recaptcha
    * date: 2022-10-13 15:53:58
    * version: 1.2.5
    */
    public function postProcess2()
    {
        if (Tools::isSubmit('submitMessage') && version_compare(_PS_VERSION_, '1.7', '<')) {
            Hook::exec('contactCaptchaValidate');
        }
        if (empty($this->errors)) {
            parent::postProcess2();
        }
    }
    /*
    * module: recaptcha
    * date: 2022-10-13 15:53:58
    * version: 1.2.5
    */
    public function init()
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $this->context->smarty->assign('HOOK_CONTACT_FORM_BOTTOM', Hook::exec('contactFormBottom'));
        }
        parent::init();
    }
    /*
    * module: recaptcha
    * date: 2022-10-13 15:53:58
    * version: 1.2.5
    */
    public function initContent()
    {
        parent::initContent();
        if (version_compare(_PS_VERSION_, '1.7.0', '<')
                && Module::isInstalled('recaptcha')&&Configuration::get('CAPTCHA_OVERLOAD')==1) {
            if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
                $html = _PS_MODULE_DIR_ . 'recaptcha/views/templates/front/front-contact-form-1-6.tpl';
            } //1.5
            else {
                $html = _PS_MODULE_DIR_ . 'recaptcha/views/templates/front/front-contact-form-1-5.tpl';
            }
            $this->setTemplate($html);
        }
    }
}
