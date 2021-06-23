<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


class CashpayFileLogger 
{
    
    private static $logger;
    
    public static function getLogger()
    {
        if (!self::$logger) {
            self::$logger = new FileLogger();
            
            $logs_dir = _PS_ROOT_DIR_.'/var/logs/';
            if (!file_exists($logs_dir)) {
                $logs_dir = _PS_ROOT_DIR_.'/app/logs/';
                if (!file_exists($logs_dir)) {
                    $logs_dir = _PS_ROOT_DIR_.'/log/';
                }
            }
            
            self::$logger->setFilename($logs_dir.date('Y_m').'_cashpay.log');
        }
        
        return self::$logger;
    }
}

