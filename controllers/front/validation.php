<?php


class semoa_cashpayValidationModuleFrontController extends ModuleFrontController
{   
    public function initContent() {
        parent::initContent();
        $this->ajax = true; // enable ajax
    }
    
    
    public function displayAjax(){

        $data = Tools::file_get_contents('php://input');;
        $data = json_decode($data, TRUE);
        PrestaShopLogger::addLog("[semoa_cashpayValidationModuleFrontController][display]: Result Callback" .json_encode($data),1);
    /*Decrypt token and Retrieve data */
        $key = trim(Configuration::get('CASHPAY_API_KEY'));
        $jwt = new JwtTokenEncoder();
        $data_callback = $jwt->decode($data["token"], $key); //Decode Jwt Token
    /* End decrypt Token*/

    /* Start Update cashpay payment log*/
        $cashpay = CashpayPayment::getByOrderReference($data_callback["merchant_reference"]);
        $cashpay->payment_status = $data_callback['state'];
        $payment_message = '<b>Ref. Facture Cashpay</b>: '.strval($data_callback['order_reference']).'<br><b>Description: </b>'.isset($data_callback['message']);
        $cashpay->total_paid = $data_callback['amount'];
        $payment = [];
        if(count($data_callback['payments'])> 0){
            $payment = (array)$data_callback['payments'][0];
            $gateway = (array)$payment['gateway'];
            $cashpay->transaction_id = $payment['thirdparty_reference'];
            PrestaShopLogger::addLog("[semoa_cashpayValidationModuleFrontController][display]: Result Payment" .json_encode($payment),1);

            $payment_message = $payment_message."<br><b>Gateway:</b>".$gateway['libelle'];
        }
        $cashpay->payment_message = $payment_message;
        $cashpay->update();

    /**End Update cashpay payment log **/
    

        $cart = new Cart((int)$cashpay->id_cart);
        $orders = Order::getByReference($data_callback["merchant_reference"]);

    /* Start Update Order State*/
        foreach ($orders as $order) {
            if($order->current_state != Configuration::get('PS_OS_PAYMENT')){
                $message = isset($data_callback['message']);
                switch ($data_callback['state']) {
                    case 'Paid':
                        //Success transaction
                        $extra_vars = array(
                        'transaction_id' => isset($payment['thirdparty_reference']),
                        'payment_type' => isset($data_callback['gateway']),
                        );
                        $orderState = Configuration::get('PS_OS_PAYMENT');
                        $this->updateOrder($order,$orderState,$extra_vars);
                        $this->setTransactionParams($order->id, $extra_vars);
                        $this->addMessage($message, $cart->id, $order);
                        $this->setTransactionParams($order->id, $extra_vars);
                        break;
                        
                    case 'Error'://Error
                        $orderState = Configuration::get('PS_OS_ERROR');
                        $this->updateOrder($order,$orderState);
                        $this->addMessage($message, $cart->id, $order);                        
                        break;
                   case 'Canceled': //Cancel
                        $orderState = Configuration::get('PS_OS_CANCELED');
                        $this->updateOrder($order,$orderState);
                        $this->addMessage($message, $cart->id, $order);
                    default:
                        break;
                }
            } else{
                PrestaShopLogger::addLog("[semoa_cashpayValidationModuleFrontController][display]:Order already Paid",1);          
            }
        }
    /* End Update Order State*/

        return Tools::jsonEncode(array('statut' => "success", "message" => "Success Callback"));  
    }
    
    /**
     * Update order state
     */
    
    public function updateOrder($order,$orderState, array $extra_vars = null){
        
        $new_history = new OrderHistory();
        $new_history->id_order = (int)$order->id;
        $new_history->changeIdOrderState($orderState, $order, true); //change order state
        $new_history->addWithemail(false,$extra_vars);
        $new_history->save();
    }
    
    /**
     * Add message
     * */
    public function addMessage($message, $id_cart, $order){
        $msg = new Message();
        $msg->message = $message;
        $msg->id_cart = (int) $id_cart;
        $msg->id_customer = (int) ($order->id_customer);
        $msg->id_order = (int) $order->id;
        $msg->private = 1;
        $msg->add();
    }
    /**
     * Update order with transaction ID
     *
     */
    public function setTransactionParams($id_order, array $extra_params)
    {
        $order = new Order($id_order);
        $order_payment_collection = $order->getOrderPaymentCollection();
        $order_payment = $order_payment_collection[0];
        $order_payment->transaction_id = $extra_params['transaction_id'];
        $order_payment->update();
    }
   

     
    
}