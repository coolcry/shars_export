<?php
include 'config.php';

include 'mysql.class.php';
class order {
	private $source;
	private $home_dic = HOME_DIC;
	
	private $fp;
	
	private $payment_methods = array('1'=>'CC','2'=>'CH','3'=>'CD','4'=>'PP');
	
	function __construct() {
		$this->source = new mysql ();
		$this->source->connect ( DB_HOST, DB_USER, DB_PW, DB_NAME );
		
		$this->fp = fopen ( $this->home_dic . 'order.csv', 'w' );
	}
	public function prep_tables() {
		
	}
	
	public function order(){
		$sql = "SELECT
	'order' AS type,
	id AS order_num,
	em.`value` AS email,
	date_add,
	'' AS sku,
	'' AS product_name,
	'' AS price,
	'' AS qty,
	'' AS subtotal,
	state_tax AS tax_amount,
	'' AS subtotal_incl_tax,
	shipping_charge + handling_fee AS shipping_amount,
	price AS grand_total,
	'UPS' AS shipping_method,
	tracking_code AS tracking_number,
	payment_type AS payment_method,
	'' AS cc_type,
	'' AS cc_exp_mo,
	'' AS cc_exp_yr,
	'' AS cc_last_four_digit,
	po_number AS po_number,
	'' AS billing_firstname,
	'' AS billing_lastname,
	'' AS billing_middlename,
	'' AS billing_street,
	'' AS billing_city,
	'' AS billing_region,
	'' AS billing_postcode,
	'' AS billing_country,
	'' AS shipping_firstname,
	'' AS shipping_lastname,
	'' AS shipping_middlename,
	'' AS shipping_street,
	'' AS shipping_city,
	'' AS shipping_region,
	'' AS shipping_postcode,
	'' AS shipping_country,
	information
FROM
	orders
LEFT JOIN __email em ON em.user_id = orders.user_id
WHERE
	1 = 1
limit 10000";
		
		$arrs = $this->source->get_all_arr ( $sql );
		$keys = array_keys ( $arrs [0] );
		$_dlk = array_search('information', $keys);
		unset($keys[$_dlk]);
		fputcsv ( $this->fp, $keys );
		foreach ( $arrs as $arr ) {
			$arr['information'] = str_replace('O:16:"SimpleXMLElement":0:{}', 's:16:"SimpleXMLElement";', $arr['information']);
			$info = @unserialize($arr['information']);
			unset($arr['information']);
			if(!$info) echo($arr['order_num']."# order's info unserialize faild.\n");
			$arr['billing_firstname'] = $info['billing_address']['first_name'];
			$arr['billing_lastname'] = $info['billing_address']['last_name'];
			$arr['billing_street'] =  $info['billing_address']['address_1']."\n" . $info['billing_address']['address_2'];
			$arr['billing_city'] = $info['billing_address']['city'];
			$arr['billing_region'] = $info['billing_address']['state'];
			$arr['billing_postcode'] = $info['billing_address']['zip'];
			$arr['billing_country'] = $info['billing_address']['country'];
			//
			list($arr['shipping_firstname'],$arr['shipping_lastname']) = explode(' ',  $info['shipping_address']['contact']);
			$arr['shipping_street'] =  $info['shipping_address']['address_1']."\n" . $info['shipping_address']['address_2'];
			$arr['shipping_city'] =  $info['shipping_address']['city'];
			$arr['shipping_region'] =  $info['shipping_address']['state'];
			$arr['shipping_postcode'] =  $info['shipping_address']['zip'];
			$arr['shipping_country'] =  $info['shipping_address']['country'];
			//
			$arr['payment_method'] = $this->payment_methods[ $arr['payment_method']];
			
			
			//
			if($info){
				foreach ($info['products'] as $id=>$_prod){
					$_item['subtotal'] = $_prod->list_price * $info['quantity'][$id];
					$arr['subtotal'] += $_item['subtotal'];
				}
				
				$arr['subtotal_incl_tax'] = $arr['subtotal'] + $arr['tax_amount'];
				//
				fputcsv ( $this->fp, array_values ( $arr ) );
			}
			
			
			//
			if($info){
				foreach ($info['products'] as $id=>$_prod){
					$_item = array();
					$_item['type'] = 'ITEM';
					$_item['order_number'] = $arr['order_num'];
					$_item['email'] = '';
					$_item['created_at'] = '';
					$_item['sku'] = $_prod->code;
					$_item['product_name'] = html_entity_decode (  $_prod->name, ENT_QUOTES, "UTF-8" );
					$_item['price'] = $_prod->list_price;
					$_item['qty'] = $info['quantity'][$id];
					$_item['subtotal'] = $_prod->list_price * $info['quantity'][$id];
					$arr['subtotal'] += $_item['subtotal'];
					fputcsv ( $this->fp, array_values ( $_item ) );
				}
			}
			
		}
	}
	
	public function __destruct(){
		fclose ( $this->fp );
	}
}

$order = new order();
$order->prep_tables();
$order->order();
