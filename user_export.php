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
	public function prep_tables() {
		{
			$sql_d = "DROP table if EXISTS __email";
			$sql_c = "CREATE TABLE `__email` ( `user_id` MEDIUMINT (9) NOT NULL, `value` VARCHAR (100) DEFAULT NULL, PRIMARY KEY (`user_id`)) ENGINE = INNODB DEFAULT CHARSET = utf8";
			$sql_i = "INSERT INTO __email SELECT user_id, `value` FROM `user_vars` WHERE `name` = 'email'";
			$this->source->query ( $sql_d );
			$this->source->query ( $sql_c );
			$this->source->query ( $sql_i );
		}
		
		{
			$sql_d = "DROP table if EXISTS __first_name";
			$sql_c = "CREATE TABLE `__first_name` ( `user_id` MEDIUMINT (9) NOT NULL, `value` VARCHAR (100) DEFAULT NULL, PRIMARY KEY (`user_id`)) ENGINE = INNODB DEFAULT CHARSET = utf8";
			$sql_i = "INSERT INTO __first_name SELECT user_id, `value` FROM `user_vars` WHERE `name` = 'first_name'";
			$this->source->query ( $sql_d );
			$this->source->query ( $sql_c );
			$this->source->query ( $sql_i );
		}
		{
			$sql_d = "DROP table if EXISTS __last_name";
			$sql_c = "CREATE TABLE `__last_name` ( `user_id` MEDIUMINT (9) NOT NULL, `value` VARCHAR (100) DEFAULT NULL, PRIMARY KEY (`user_id`)) ENGINE = INNODB DEFAULT CHARSET = utf8";
			$sql_i = "INSERT INTO __last_name SELECT user_id, `value` FROM `user_vars` WHERE `name` = 'last_name'";
			$this->source->query ( $sql_d );
			$this->source->query ( $sql_c );
			$this->source->query ( $sql_i );
		}
	}
	public function member() {
		$dc = $this->us_ca ();
		
		$sql = "SELECT
	lower(email.`value`) AS email,
	'base' AS _website,
	'default' AS _store,
	'' AS confirmation,
	date_added AS created_at,
	'Default Store View' AS created_in,
	'0' AS disable_auto_group_change,
	'' AS dob,
	first_name.`value` AS firstname,
	'' AS gender,
	'1' AS group_id,
	last_name.`value` AS lastname,
	'' AS middlename,
	'' AS password_hash,
	'' AS prefix,
	'1' AS reward_update_notification,
	'1' AS reward_warning_notification,
	'' AS rp_token,
	'' AS rp_token_created_at,
	'1' AS store_id,
	'' AS suffix,
	'' AS taxvat,
	'' AS website_id,
	'' AS `password`,
	ma.city AS _address_city,
	'' AS _address_company,
	ma.country AS _address_country_id,
	ma.fax AS _address_fax,
	first_name.`value` AS _address_firstname,
	last_name.`value` AS _address_lastname,
	'' AS _address_middlename,
	ma.zip AS _address_postcode,
	'' AS _address_prefix,
	ma.state AS _address_region,
	CONCAT(
		ma.address_1,
		\"\r\n\",
		ma.address_2
	) AS _address_street,
	'' AS _address_suffix,
	ma.phone AS _address_telephone,
	'' AS _address_vat_id,
	ma.billing AS _address_default_billing_,
	ma.shipping AS _address_default_shipping_
FROM
	users u
LEFT JOIN __email email ON email.user_id = u.id
LEFT JOIN __first_name first_name ON first_name.user_id = u.id
LEFT JOIN __last_name last_name ON last_name.user_id = u.id
LEFT JOIN member_addresses ma ON ma.user_id = u.id
WHERE
	u.user_group_id = '2'
ORDER BY
	email ASC
LIMIT " . LIMIT_VALUE;
		$arrs = $this->source->get_all_arr ( $sql );
		
		$fp = fopen ( $this->home_dic . 'member.csv', 'w' );
		
		fputcsv ( $fp, array_keys ( $arrs [0] ) );
		$last_email = '';
		foreach ( $arrs as $arr ) {
			
			if ($last_email != $arr ['email']) { // 如果是新的
				$last_email = $arr ['email'];
			} else {
				$arr ['email'] = '';
				$arr ['_website'] = '';
				$arr ['_store'] = '';
				$arr ['confirmation'] = '';
				$arr ['created_at'] = '';
				$arr ['created_in'] = '';
				$arr ['disable_auto_group_change'] = '';
				$arr ['dob'] = '';
				$arr ['firstname'] = '';
				$arr ['gender'] = '';
				$arr ['group_id'] = '';
				$arr ['lastname'] = '';
				$arr ['middlename'] = '';
				$arr ['password_hash'] = '';
				$arr ['prefix'] = '';
				$arr ['reward_update_notification'] = '';
				$arr ['reward_warning_notification'] = '';
				$arr ['rp_token'] = '';
				$arr ['rp_token_created_at'] = '';
				$arr ['store_id'] = '';
				$arr ['suffix'] = '';
				$arr ['taxvat'] = '';
				$arr ['website_id'] = '';
				$arr ['password'] = '';
			}
			$arr ['_address_telephone'] = $arr ['_address_telephone'] ? $arr ['_address_telephone'] : '0';
			$arr ['_address_region'] = ($foo = $dc [$arr ['_address_region']]) ? $foo : $arr ['_address_region'];
			fputcsv ( $fp, array_values ( $arr ) );
		}
		
		fclose ( $fp );
	}
	private function us_ca() {
		$dc = array ();
		$ca = file_get_contents ( './ca.txt' );
		foreach ( split ( "\r\n", $ca ) as $r ) {
			list ( $k, $v ) = split ( "\t", $r );
			$dc [$k] = $v;
		}
		;
		
		$us = file_get_contents ( './us.txt' );
		foreach ( split ( "\r\n", $us ) as $r ) {
			list ( $v, $k ) = split ( "\t", $r );
			$dc [$k] = $v;
		}
		;
		
		return $dc;
	}
}

$user = new user ();
$user->prep_tables ();
$user->member();