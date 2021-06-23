<?php

require_once _PS_MODULE_DIR_.'semoa_cashpay/classes/CashpayPayment.php';

class adminCashpayPaymentController extends ModuleAdminController 
{
  public function __construct(){
      parent::__construct();
      $this->bootstrap = true; // use Bootstrap CSS
      $this->table = 'cashpay_payment'; // SQL table name, will be prefixed with _DB_PREFIX_
      $this->identifier = 'id_cashpay_payment'; // SQL column to be used as primary key
      $this->className = 'CashpayPayment'; // PHP class name
      $this->allow_export = true; // allow export in CSV, XLS..
      
      $this->_defaultOrderBy = 'a.date_add'; // the table alias is always `a`
      $this->_defaultOrderWay = 'DESC';
      $this->fields_list = [
          'id_cashpay_payment' => ['title' => 'ID','class' => 'fixed-width-xs','remove_onclick' => true],
          'order_ref' => ['title' => 'Order Ref.','callback' => 'printOrderLink','remove_onclick' => true],
          'transaction_id' => ['title' => 'Transaction ID','type'=>'string','remove_onclick' => true],
          'total_prestashop' => ['title' => 'Total Cmd.','remove_onclick' => true],
          'total_paid' => ['title' => 'Total Paye','remove_onclick' => true],
          'phone_number' => ['title' => 'Phone number','type'=>'string','remove_onclick' => true],
          'payment_status' => ['title' => 'Statut','type'=>'string','remove_onclick' => true,'callback'=>'statusColor'],
          'payment_message' => ['title' => 'Message','type'=>'string','remove_onclick' => true,'float' => true],
          'date_add' => ['title' => 'Created','type'=>'datetime','remove_onclick' => true],
          'date_upd' => ['title' => 'Updated','type'=>'datetime','remove_onclick' => true],

      ];
  }
  
  public function printOrderLink($value, $row)
  {
      $link = $this->context->link->getAdminLink('AdminOrders').'&id_order='.(int)$row['id_order'].'&vieworder';
      return '<a href="'.$link.'">'.$value.'</a>';
  }
  
  public function statusColor($value, $row)
  {   if($row['payment_status'] == 'success' || $row['payment_status'] == 'Paid')
    {
          return '<span style="background-color : #5cb85c; color : #ffffff; border-radius : 4px/4px; padding:5px">'. $row['payment_status']. '</span>';
          
    }else if ($row['payment_status'] == 'Error'|| $row['payment_status'] == 'Canceled')
      
      {
          return '<span style="background-color : #d61111; color : #ffffff; border-radius : 4px/4px;  padding:5px">'.$row['payment_status'].'</span>';
          
      }else{
          return '<span style="background-color : #d07e2a; color : #ffffff; border-radius : 4px/4px;  padding:5px">'.$row['payment_status'].'</span>';
          
      }
  
  }
}