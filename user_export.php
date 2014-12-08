<?php
include 'config.php';

include 'mysql.class.php';


class user {
	private $source;
	private $home_dic = HOME_DIC;
	function __construct() {
		$this->source = new mysql ();
		$this->source->connect ( DB_HOST, DB_USER, DB_PW, DB_NAME );
	}
	
	public function member(){
		$sql = "SELECT
	lower(email.email) as email,
	'base' AS _website,
	'default' AS _store,
	'' AS confirmation,
	date_added AS created_at,
	'Default Store View' AS created_in,
	'0' AS disable_auto_group_change,
	'' AS dob,
	firs_tname as firstname,
	'' AS gender,
	'1' AS group_id,
last_name as lastname,
	'' AS middlename,
	'' AS password_hash,
	'' AS prefix,
	'' AS reward_update_notification,
	'' AS reward_warning_notification,
	'' AS rp_token,
	'' AS rp_token_created_at,
	'1' AS store_id,
	'' AS suffix,
	'' AS taxvat,
	'' AS website_id,
	'' AS password,
ma.city as _address_city,
'' as _address_company,
ma.country as _address_country_id,
ma.fax as _address_fax,
first_name.firs_tname as _address_firstname,
last_name.last_name as _address_lastname,
'' as _address_middlename,
ma.zip as _address_postcode,
'' as _address_prefix,
ma.state as _address_region,
CONCAT(ma.address_1,\"\r\n\",ma.address_2)  as _address_street,
'' as _address_suffix,
ma.phone as _address_telephone,
'' as _address_vat_id,
ma.billing as _address_default_billing_,
ma.shipping as _address_default_shipping_
FROM
	users u
LEFT JOIN (
	SELECT
		user_id,
		`value` AS email
	FROM
		`user_vars`
	WHERE
		`name` = 'email'
	GROUP BY
		user_id
) email ON email.user_id = u.id
LEFT JOIN (
	SELECT
		user_id,
		`value` AS firs_tname
	FROM
		`user_vars`
	WHERE
		`name` = 'first_name'
	GROUP BY
		user_id
) first_name ON first_name.user_id = u.id
LEFT JOIN (
	SELECT
		user_id,
		`value` AS last_name
	FROM
		`user_vars`
	WHERE
		`name` = 'last_name'
	GROUP BY
		user_id
) last_name ON last_name.user_id = u.id 
LEFT JOIN member_addresses ma ON ma.user_id = u.id
WHERE u.user_group_id = '2'
order by email asc
LIMIT ".LIMIT_VALUE ;
		$arrs = $this->source->get_all_arr($sql);
		
		$fp = fopen ( $this->home_dic . 'member.csv', 'w' );
		
		fputcsv ( $fp, array_keys($arrs[0]) );
		$last_email = '';
		foreach ($arrs as $arr){
			
			if($last_email != $arr['email']){	//如果是新的
				$last_email = $arr['email'];
			}else{
				$arr['email'] = '';
				$arr['_website'] = '';
				$arr['_store'] = '';
				$arr['confirmation'] = '';
				$arr['created_at'] = '';
				$arr['created_in'] = '';
				$arr['disable_auto_group_change'] = '';
				$arr['dob'] = '';
				$arr['firstname'] = '';
				$arr['gender'] = '';
				$arr['group_id'] = '';
				$arr['lastname'] = '';
				$arr['middlename'] = '';
				$arr['password_hash'] = '';
				$arr['prefix'] = '';
				$arr['reward_update_notification'] = '';
				$arr['reward_warning_notification'] = '';
				$arr['rp_token'] = '';
				$arr['rp_token_created_at'] = '';
				$arr['store_id'] = '';
				$arr['suffix'] = '';
				$arr['taxvat'] = '';
				$arr['website_id'] = '';
				$arr['password'] = '';
			}
			
			fputcsv ( $fp, array_values( $arr) );
		}
		
		fclose ( $fp );
	}
}

$user = new user();
$user->member();