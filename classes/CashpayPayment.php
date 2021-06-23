<?php

if (!defined('_PS_VERSION_'))
    exit;

/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author 202-ecommerce <tech@202-ecommerce.com>
 *  @copyright 202-ecommerce
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Class CashpayPayment.
 */
class CashpayPayment extends ObjectModel
{   
    /** @var integer Prestashop Cart generated ID */
    public $id_cart;
    /** @var integer Prestashop Order generated ID */
    public $id_order;
    /** @var string order Reference */
    public $order_ref;
    /** @var string Id transaction */
    public $transaction_id;
    
    /** @var string Phone number*/
    public $phone_number;
   
    /** @var string Currency iso code */
    public $currency;
    
    /** @var float Total paid amount by customer */
    public $total_paid;
    
    /** @var string API status */
    public $payment_status;
    
    /** @var string API message */
    public $payment_message;
    
    /** @var float Prestashop order total */
    public $total_prestashop;
      
    /** @var string Object creation date */
    public $date_add;
    
    /** @var string Object last modification date */
    public $date_upd;
    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cashpay_payment',
        'primary' => 'id_cashpay_payment',
        'multilang' => false,
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'order_ref' => array('type' => self::TYPE_STRING),       
            'transaction_id' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'phone_number' => array('type' => self::TYPE_STRING),
            'currency' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'total_paid' => array('type' => self::TYPE_FLOAT, 'size' => 10, 'scale' => 2),
            'payment_status' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'payment_message' => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'total_prestashop' => array('type' => self::TYPE_FLOAT, 'size' => 10, 'scale' => 2),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        )
    );
    
    public function getTransactionReference(){
        return $this->transaction_reference;
    }
    
    /**
     * Get cashpay payment by Order reference 
     * */
    public static function getByOrderReference($reference)
    {   
        $sql = '
          SELECT id_cashpay_payment
            FROM `ps_cashpay_payment` 
            WHERE `order_ref` = \'' . pSQL($reference) . '\'';
        
        $id = (int) Db::getInstance()->getValue($sql);
        return new CashpayPayment($id);
    }
    
    /**
     * Get Id of order by transaction ref
     * @param string $transaction_reference
     * @return integer Order id
     */
    public static function getIdOrderByTransaction($transaction_ref)
    {
        $sql = 'SELECT `id_order`
			FROM `'._DB_PREFIX_.'cashpay_payment`
			WHERE `order_ref` = \''.pSQL($transaction_ref).'\'';
        $result = Db::getInstance()->getRow($sql);
        if ($result != false) {
            return (int) $result['id_order'];
        }
        return 0;
    }
    
   
}
