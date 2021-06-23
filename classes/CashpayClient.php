
<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

define('CREATE_ORDER_ENDPOINT', '/orders');

class CashpayClient
{
    private $login;
    private $apikey;
    private $apireference;
    private $baseUrl;
    private $url;
    private $curl;
    private $headers;
    private $logger;
    
    
    public function __construct(){
        $this->logger = CashpayFileLogger::getLogger();
        
    }

    public function createOrder($data, array $params = null)
    {   
        $this->logger->logInfo("[CashpayClient][createOrder]: Start");
        $response = [];
        $result = $this->post(CREATE_ORDER_ENDPOINT,$data,$params);
        if($result['status_code'] !== 200 && $result['status_code'] !== 201 ){                
            $data = json_decode($result["data"],true);
            $response = array('status' => 'error', 'message' => strval($data['message']));
        }else{
            //Redirect
            $data = json_decode($result["data"],true);
            $response = array('status' => 'success', 'message' => strval($data['message']), 'data'=>$data['bill_url']);
        }
        $this->logger->logInfo("[CashpayClient][createOrder]: End");

        return $response;
    }
    
    public function post($endPoint, $data, array $params = null)
    {
        
        $this->initCurl();
        
        if($params){
            $this->setParams($params);
        }
        $this->createRequestHeaders();
        $this->logger->logInfo("[CashpayClient][Post]: Data to send". json_encode($data));
        PrestaShopLogger::addLog("[CashpayClient][post]: Data to send: ".json_encode($data),1);
        
        $curlOptions = [
            CURLOPT_URL => $params['baseUrl'].$endPoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $this->headers
        ];
        curl_setopt_array($this->curl, $curlOptions);
        $result = curl_exec($this->curl);
        $err = curl_error($this->curl);
        $info = curl_getinfo($this->curl);
        curl_close($this->curl);
        
        if ($err) {
            $response = array(
                'status_code' => (int)$info["http_code"],
                'data' => $err
            );
            $this->logger->logError("[CashpayClient][Post]: response of request ".json_encode($response));
            PrestaShopLogger::addLog("[CashpayClient][post]: response of request ".json_encode($response),3);
            
            
        } else {
            $response = array(
                'status_code' => (int)$info["http_code"],
                'data' => $result
            );
            $this->logger->logInfo("[CashpayClient][Post]: response of request ".json_encode($response));
            PrestaShopLogger::addLog("[CashpayClient][post]: response of request ".json_encode($response),1);
            
            
        }
        
        return $response;
    }
    
    
    public function initCurl()
    {
        $this->curl = curl_init();
    }
    
    public function setParams(array $params)
    {
        if( isset($params['baseUrl']) ){
            $this->baseUrl = $params['baseUrl'];
        }
        if( isset($params['apikey']) ){
            $this->apikey = $params['apikey'];
        }
        if( isset($params['login']) ){
            $this->login = $params['login'];
        }
        
        if( isset($params['apireference']) ){
            $this->apireference = $params['apireference'];
        }
        
    }

    
    public function createRequestHeaders()
    {
            
        $salt = time();  
        $this->logger->logInfo("createRequestHeaders before hash: ". json_encode(array(
            "login"=>$this->login,
            "salt"=>$salt,
            "apikey" => $this->apikey,
            "len" => strlen($this->apikey)
        )));
        $apisecure = hash('sha256', $this->login.$this->apikey.$salt);
        
        $this->logger->logInfo("CreateRequestHeaders after hash: ". json_encode(array(
            "apisecure"=>$apisecure
        )));
        $headers = array(
            "login: " .$this->login,
            "apireference: " .$this->apireference,
            "apisecure: " .$apisecure,
            "cache-control: no-cache",
            "content-type: application/json",
            "salt: ".$salt
        );
        $this->headers = $headers;
    }
    
    
    /*transaction Encryptage */
    public function cryptage($min, $max) {
        $range = $max - $min;
        if ($range < 0)
            return $min; // not so random...
            $log = log ( $range, 2 );
            $bytes = ( int ) ($log / 8) + 1; // length in bytes
            $bits = ( int ) $log + 1; // length in bits
            $filter = ( int ) (1 << $bits) - 1; // set all lower bits to 1
            do {
                $rnd = hexdec ( bin2hex ( openssl_random_pseudo_bytes ( $bytes ) ) );
                $rnd = $rnd & $filter; // discard irrelevant bits
            } while ( $rnd >= $range );
            return $min + $rnd;
    }
    
    public function getCode($length) {
        $token = "";
        $code = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $code .= "abcdefghijklmnopqrstuvwxyz";
        $code .= "0123456789";
        for($i = 0; $i < $length; $i ++) {
            $token .= $code [self::cryptage ( 0, strlen ( $code ) )];
        }
        $current_time = time();
        return $current_time."-".$token;
    }
    
    
}

