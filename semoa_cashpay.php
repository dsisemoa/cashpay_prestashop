<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

require_once _PS_MODULE_DIR_.'semoa_cashpay/classes/CashpayClient.php';
require_once _PS_MODULE_DIR_.'semoa_cashpay/classes/CashpayFileLogger.php';
require_once _PS_MODULE_DIR_.'semoa_cashpay/classes/CashpayPayment.php';
require_once _PS_MODULE_DIR_.'semoa_cashpay/classes/JwtTokenEncoder.php';

use  PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class semoa_cashpay extends PaymentModule{
    
    protected $_html = '';
    
    public function  __construct()
    {   
        $this->name                   = 'semoa_cashpay';
        $this->tab                    = 'payments_gateways';
        $this->version                = '1.0';
        $this->author                 = 'Semoa Togo';
        $this->controllers            = array('payment', 'validation','api');
        $this->currencies             = true;
        $this->currencies_mode        = 'checkbox';
        $this->bootstrap              = true;
        $this->displayName            = 'Cashpay';
        $this->description            = 'Payment module developed by Semoa Togo';
        $this->confirmUninstall       = 'Etes-vous surs de vouloir desinstaller ce module?';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        parent::__construct();
        $this->limited_currencies = array('XOF');

    }
    
    
    /**
     * Install this module and register the following Hooks:
     *
     * @return bool
     */
    public function install()
    {   
        
        
        // Registration order status
        if (!$this->addOrderState()) {
            return false;
        }
        //Create table for cashpay Payments
        Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'cashpay_payment` (
                `id_cashpay_payment` int NOT NULL AUTO_INCREMENT,
                `id_cart` int,
                `id_order` int,
                `order_ref` varchar(50),
                `transaction_id` varchar(50),
                `phone_number` varchar(50),
                `currency` varchar(10),
                `total_paid` float,
                `total_prestashop` float,
                `payment_status` varchar(50),
                `payment_message` text,
                `date_add` datetime NOT NULL,
                `date_upd` datetime,
                PRIMARY KEY (`id_cashpay_payment`)
                )ENGINE=InnoDB' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1'
            );
        
        // Registration order status
        if (!$this->addOrderState()) {
            return false;
        }
        
        if (!(int) Tab::getIdFromClassName('SemoaPayments')) {
            
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = 'SemoaPayments';
            $tab->name = array();
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = 'Semoa Payments';
            }
            $tab->id_parent = 0 ;
            $tab->position = 2 ;
            
            $tab->module = '';
            $tab->add();
        }
        
        
        //Sub menu code
        if (!(int) Tab::getIdFromClassName('adminCashpayPayment')) {
            $parentTabID = Tab::getIdFromClassName('SemoaPayments');
            $parentTab = new Tab($parentTabID);
            
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = "adminCashpayPayment";
            $tab->name = array();
            $tab->icon = "payment";
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $this->l('Cashpay');
            }
            $tab->id_parent = $parentTab->id;
            $tab->module = $this->name;
            $tab->add();
        }
        
        return parent::install()
        && $this->registerHook('paymentOptions')
        && $this->registerHook('paymentReturn');
    }
    
    /**
     * Uninstall this module and remove it from all hooks
     *
     * @return bool
     */
    public function uninstall()
    {   
        $config = [
            'CASHPAY_API_URL',
            'CASHPAY_API_KEY',
            'CASHPAY_API_REFERENCE',
            'CASHPAY_API_LOGIN'
        ];
        
        foreach ($config as $var) {
            Configuration::deleteByName ($var);
        }
        $this->deleteOrderState();
        
        $id_tab = Tab::getIdFromClassName('adminCashpayPayment');
        $tab = new Tab($id_tab);
        try{
            $tab->delete();
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
        if (!parent::uninstall()) {
            return false;
        }
    
        //Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'cashpay_payment`');
        
        return true;
    }
    
    /**
     * Returns a string containing the HTML necessary to
     * generate a configuration screen on the admin
     *
     * @return string
     */
    public function getContent()
    {   

        $this->_html .= $this->postProcess();
        $this->_html .= $this->renderForm();
        
        return $this->_html;
    }
    
    public function hookPaymentOptions($params)
    {
        if (!$this->active) return;
        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int)$currency_id);
        if (in_array($currency->iso_code, $this->limited_currencies ) == false ){
            return false;
        }
        $newOption = new PaymentOption();
        $newOption->setAction($this->context->link->getModuleLink($this->name, 'payment', array(), true))
        ->setLogo($this->context->link->getBaseLink().'modules/'.$this->name.'/views/img/cashpay.png')
        ->setAdditionalInformation($this->l('Paiement par compte mobile money ou par carte bancaire'));
        $payment_options = [
            $newOption,
        ];
        return $payment_options;
    }
    
    public function hookPaymentReturn($params)
    {
        if(!$this->active){
            return;
        }
        return $this->fetch('module:semoa_cashpay/views/templates/hook/payment_return.tpl');
    }
    
    /**
     * Save/Update Credential
     * @return type
     */
    public function postProcess()
    {
        if ( Tools::isSubmit('SubmitPaymentConfiguration'))
        {   
            Configuration::updateValue('CASHPAY_API_URL', Tools::getValue('CASHPAY_API_URL'));
            Configuration::updateValue('CASHPAY_API_KEY', Tools::getValue('CASHPAY_API_KEY'));
            Configuration::updateValue('CASHPAY_API_REFERENCE', Tools::getValue('CASHPAY_API_REFERENCE'));
            Configuration::updateValue('CASHPAY_API_LOGIN', Tools::getValue('CASHPAY_API_LOGIN'));
      
            return $this->displayConfirmation($this->l('Configuration mis à jour avec succès'));
            
        }
    }
    
    
    /**
     * Credentials configuration Form
     */
    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Cashpay Configuration'),
                    'icon' => 'icon-cogs'
                ],
                'description' => $this->l('Cashpay configuration form'),
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Cashpay api url'),
                        'name' => 'CASHPAY_API_URL',
                        'required' => true,
                        'empty_message' => $this->l('Please enter Api url'),

                    ],
                    
                    [
                        'type' => 'text',
                        'label' => $this->l('Cashay api Login'),
                        'name' => 'CASHPAY_API_LOGIN',
                        'required' => true,
                        'empty_message' => $this->l('Please enter your login'),
                    ],
                    
                    [
                        'type' => 'text',
                        'label' => $this->l('Cashay api key'),
                        'name' => 'CASHPAY_API_KEY',
                        'required' => true,
                        'empty_message' => $this->l('Please enter your api key '),
                    ],
                    
                    [
                        'type' => 'text',
                        'label' => $this->l('Cashay api reference'),
                        'name' => 'CASHPAY_API_REFERENCE',
                        'required' => true,
                        'empty_message' => $this->l('Please enter your reference'),
                    ],
                    
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'button btn btn-default pull-right',
                ],
            ],
        ];
        
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->id = 'semoa_cashpay';
        $helper->identifier = 'semoa_caspay';
        $helper->submit_action = 'SubmitPaymentConfiguration';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        ];
        
        return $helper->generateForm(array($fields_form));
    }
    
    /**
     * Create order states
     * @return boolean
     */
    public function addOrderState()
    {  
        $state_exist = false;
        $states = OrderState::getOrderStates((int)$this->context->language->id);
        
        foreach ($states as $state) {
            if (in_array("En attente de paiement par cashpay", $state)) {
                $state_exist = true;
                Configuration::updateValue("PS_OS_CASHPAY_WAITING",$state["id_order_state"]);
                break;
            }
        }
        
        // If the state does not exist, we create it.
        if (!$state_exist) {
            // create new order state
            $order_state = new OrderState();
            $order_state->name = array();            
            $order_state->send_email = false;
            $order_state->color = '#4169E1';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = false;
            $order_state->module_name = $this->name;
            $order_state->template = array();
            
            $languages = Language::getLanguages(false);
            foreach ($languages as $language)
                $order_state->name[ $language['id_lang'] ] = "En attente de paiement par Cashpay";
                $order_state->add();
               /*  if ($order_state->add())    {
                    Configuration::updateValue("PS_OS_CASHPAY_WAITING",$order_state->id);
                    
                    // We copy the module logo in order state logo directory
                    $source = _PS_MODULE_DIR_ . 'semoa_cashpay/logo.png';
                    $destination = _PS_ROOT_DIR_ . '/img/os/' . (int)$order_state->id . '.gif';
                    copy($source, $destination);
                }else return false; */
                
        }
        
      /*   foreach (LanguageCore::getLanguages() as $l) {
            $module_path = dirname(__FILE__).'/views/templates/mails/';
            $application_path = dirname(__FILE__).'/mails/'.$l['iso_code'].'/';
            if (!copy($module_path.'semoa_cashpay.txt', $application_path.'semoa_cashpay.txt')
                || !copy($module_path.'semoa_cashpay.html', $application_path.'semoa_cashpay.html'))
                return false;
        } */
        return true;
        
      
    }
    /**
     * Delete order states
     */
    public function deleteOrderState()
    {
        $states = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($states as $state) {
            if (in_array("En attente de paiement par Cashpay", $state)) {
                (new OrderState((int)$state["id_order_state"],(int)$this->context->language->id))->delete();
                break;
            }
        }
    }
    /**
     * Get Credentials Values
     */
    public function getConfigFieldsValues()
    {
        return [
            'CASHPAY_API_URL' => Tools::getValue('CASHPAY_API_URL', Configuration::get('CASHPAY_API_URL')),
            'CASHPAY_API_LOGIN' => Tools::getValue('CASHPAY_API_LOGIN', Configuration::get('CASHPAY_API_LOGIN')),
            'CASHPAY_API_KEY' => Tools::getValue('CASHPAY_API_KEY', Configuration::get('CASHPAY_API_KEY')),
            'CASHPAY_API_REFERENCE' => Tools::getValue('CASHPAY_API_REFERENCE', Configuration::get('CASHPAY_API_REFERENCE')),

        ];
    }
}

