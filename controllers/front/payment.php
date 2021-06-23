<?php

if (!defined('_PS_VERSION_'))
    exit;

class semoa_cashpayPaymentModuleFrontController extends ModuleFrontController
{   
    public $ssl = true;    

    public function postProcess()
    {           
        //LOAD OBJECTS
        $cart = $this->context->cart;
        $currency = new Currency((int) ($cart->id_currency));
        $customer = new Customer((int) ($cart->id_customer));
        $gender = new Gender($customer->id_gender);
        $client = $gender->name[1]." ".$customer->firstname. " ".$customer->lastname;
  
        $action_url = $this->context->link->getModuleLink('semoa_cashpay', 'api', array(), true);
        $this->context->smarty->assign('client_fullname', $client);
        $this->context->smarty->assign('action_url', $action_url);
        $this->context->smarty->assign('amount', $cart->getOrderTotal(true)." ".$currency->iso_code);
        
        $this->context->smarty->assign('moduleDir', _MODULE_DIR_.$this->module->name);
        $this->setTemplate('module:semoa_cashpay/views/templates/hook/payment_options.tpl');
        
           
     }
      
}