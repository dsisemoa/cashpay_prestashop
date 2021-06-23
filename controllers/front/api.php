<?php

if (!defined('_PS_VERSION_'))
    exit;

class semoa_cashpayApiModuleFrontController extends ModuleFrontController
{   
    public $ssl = true;
    
    private $cashpayClient;
    private $logger;
    public $errors = array();
    
    public function __construct(){
        parent::__construct();
        $this->cashpayClient = new CashpayClient();
        $this->logger = CashpayFileLogger::getLogger();
        
    }
    
    public function postProcess()
    {   
        $this->logger->logInfo("[Payment][postProcess]: Start");
        $phone = Tools::getValue('tel');
        
        $cart  = $this->context->cart;
        $authorized = false;
        
        //Check if module is active and client infos are valid
        if (!$this->module->active || $cart->id_customer == 0 || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0) {
                Tools::redirect('index.php?controller=order&step=1');
            }
            
        //Check if payment is authorizes
            foreach (Module::getPaymentModules() as $module) {
                if ($module['name'] == 'semoa_cashpay') {
                    $authorized = true;
                    break;
                }
            }
            if (!$authorized) {
                die($this->l('Ce moyen de paiement n est pas activé.'));
            }
            
            /** @var CustomerCore $customer */
            $customer = new Customer($cart->id_customer);
            
            /**
             * Check if this is a vlaid customer account
             */
            if (!Validate::isLoadedObject($customer)) {
                Tools::redirect('index.php?controller=order&step=1');
            }
            
            /** 
             *  Get Customer adress
            */
            $address = new Address((int) ($cart->id_address_invoice));
                        
            /**
             * Create the order with status awaiting payment
             */
            $this->module->validateOrder(
                (int) $this->context->cart->id,
                Configuration::get('PS_OS_CASHPAY_WAITING'), //Etat de la commande
                Tools::ps_round($this->context->cart->getOrderTotal(true), 0),
                $this->module->displayName,
                null,
                null,
                (int) $this->context->currency->id,
                false,
                $customer->secure_key
                );
            
            /**
             *  Get Order
             */
            $order = new Order((int)Order::getOrderByCartId($cart->id));  
            
            //Create cashpay payment Log
            $cashpay = new CashpayPayment();
            $cashpay->id_cart = (int) $cart->id;
            $cashpay->id_order = (int) $order->id;
            $cashpay->currency = $this->context->currency->iso_code;
            $cashpay->total_prestashop = Tools::ps_round($cart->getOrderTotal(true), 0);
            $cashpay->order_ref = $order->reference;
            $cashpay->phone_number = $phone;
            
            /**
             * API remote 
             */       
            
            $clientDetails = [];
            $clientDetails["lastname"] =  $customer->firstname;
            $clientDetails["firstname"] = $customer->lastname;
            $clientDetails["phone"] = $phone;
            
            //$ip = Tools::getRemoteAddr();
      
            $data = array(
                "merchant_reference" =>  $order->reference,
                "amount" => Tools::ps_round($this->context->cart->getOrderTotal(true), 0),
                "notify" => false,
                "callback_url" => $this->context->link->getModuleLink($this->module->name, 'validation',
                    array()),
                "redirect_url" => $this->context->link->getModuleLink($this->module->name, 'confirmation',
                    array("merchant_reference"=>$order->reference)),
                "client" => $clientDetails
            );
            //Send data to cashpay 
            $response = $this->cashpayClient->createOrder($data, $this->getPaymentApiVars());
            if($response['status'] == "error" ){                
                $cashpay->payment_message = strval($response['message']) ;
                $cashpay->payment_status == "error";
                $url = 'index.php?controller=order&step=1';
            }else{
                $cashpay->payment_status == "pending";
                //Redirect to Cashpay payment Page
                $url = $response["data"];
            }
            $cashpay->add();
            
            $this->logger->logInfo("[Payment][postProcess]: End");
            //if success redirect customer to payment page if not to order page with a message error
            Tools::redirect($url);
            
           
     }
      
      
      /**
       * Get API Parameters
       * @return array
       */
      public function getPaymentApiVars()
      {   
            $paramsHeaders =  [
                'baseUrl' => trim(Configuration::get('CASHPAY_API_URL')),
                'login' => trim(Configuration::get('CASHPAY_API_LOGIN')),
                'apikey' => trim(Configuration::get('CASHPAY_API_KEY')),
                'apireference' => trim(Configuration::get('CASHPAY_API_REFERENCE')),  
            ];          
          return $paramsHeaders;
      }
      
}