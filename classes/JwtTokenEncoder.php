<?php

if (!defined('_PS_VERSION_')) {
    exit;
}
use Firebase\JWT\JWT;

class JwtTokenEncoder{

    public function __construct(){
        
    }
    
    public function encode(array $payload, $key)
    {    
        $jwt = JWT::encode($payload, $key);
        return $jwt;
    }

    public function decode($token, $key)
    {   

        $decoded = JWT::decode($token, $key, array('HS256'));
        return (array)$decoded;

    }

    

}