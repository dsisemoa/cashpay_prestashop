<?php
if (!defined('_PS_VERSION_'))
    exit;

    
class semoa_cashpayConfirmationModuleFrontController extends ModuleFrontController
{   
    
    public $ssl = true;
    
    public function initContent(){
        parent::initContent();
        
        $cashpay = CashpayPayment::getByOrderReference(Tools::getValue('merchant_reference'));

        $cart = new Cart((int)$cashpay->id_cart);
        $order = new Order((int)Order::getOrderByCartId($cart->id));
        $customer = new Customer($cart->id_customer);
        $gender = new Gender($customer->id_gender);
        $currency = new Currency((int) ($cart->id_currency));
        $amount = $cart->getOrderTotal(true, Cart::BOTH);
        
        $clientInfos = $gender->name[1]." ".$customer->firstname. " ".$customer->lastname;
        
        switch ($order->current_state) {
            case  Configuration::get('PS_OS_PAYMENT'):
                Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
                break;
            
            case  Configuration::get('PS_OS_ERROR'):
            case  Configuration::get('PS_OS_CANCELED'):
                Context::getContext()->smarty->assign(array(
                'amount' => $amount.' '.$currency->iso_code,
                'date_paiement' => $cart->date_add,
                'order_ref' => $order->reference,
                'isCancel'=> true,
                'client' => $clientInfos,
                'state'=> "Echec"
                ));
                $this->setTemplate('module:semoa_cashpay/views/templates/hook/payment_confirmation.tpl');
                break;
            
            default:
                Context::getContext()->smarty->assign(array(
                'amount' => $amount.' '.$currency->iso_code,
                'date_paiement' => $cart->date_add,
                'order_ref' => $order->reference,
                'isCancel'=> false,
                'client' => $clientInfos,
                'state' => "En attente"
                
                ));
                $this->setTemplate('module:semoa_cashpay/views/templates/hook/payment_confirmation.tpl');
                break;
                
        }
    }
}