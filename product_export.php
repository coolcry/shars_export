<?php
include 'config.php';

include 'mysql.class.php';

/**
 * 过滤出图片
 *
 * @param unknown $var        	
 * @return number
 */
function filter_pic($var) {
	return preg_match ( '{^[0-9]}', $var );
}

/**
 * 图片排序
 *
 * @param string $a        	
 * @param string $b        	
 * @return number
 */
function sort_pic($a, $b) {
	$a = preg_replace ( '{\..+$}', '', preg_replace ( '{^[0-9\-]+}', '', $a ) );
	$b = preg_replace ( '{\..+$}', '', preg_replace ( '{^[0-9\-]+}', '', $b ) );
	
	if ($a == 'Main') {
		return - 1;
	} elseif ($b == 'Main') {
		return 1;
	}
	
	$k = 1;
	$r1 = 0;
	for($i = strlen ( $a ) - 1; $i >= 0; $i --) {
		$r1 += ord ( $a {$i} ) * $k;
		$k = $k * 100;
	}
	
	$k = 1;
	$r2 = 0;
	for($i = strlen ( $b ) - 1; $i >= 0; $i --) {
		$r2 += ord ( $b {$i} ) * $k;
		$k = $k * 100;
	}
	
	return ($r1 < $r2) ? - 1 : 1;
}
class product {
	private $source;
	private $home_dic = HOME_DIC;
	function __construct() {
		$this->source = new mysql ();
		$this->source->connect ( DB_HOST, DB_USER, DB_PW, DB_NAME );
	}
	public function product_categories() {
	}
	/**
	 * sku的attr ID
	 *
	 * @return array
	 */
	private function _sku_s_attr() {
		$product_filter_list = file_get_contents ( $this->home_dic . 'product filter list.txt' );
		$rows = explode ( NEW_LINE, $product_filter_list );
		$sku_cat = array ();
		foreach ( $rows as $row ) {
			if ($row) {
				$rs = explode ( "\t", $row );
				if ($rs [0] == 'product code') {
				} else {
					$sku_cat [$rs [0]] = substr ( $rs [1], 0, 4 );
				}
			}
		}
		return $sku_cat;
	}
	
	/**
	 * sku的catgory ID
	 *
	 * @return array
	 */
	private function _sku_s_cat() {
		$product_filter_list = file_get_contents ( $this->home_dic . 'product filter list.txt' );
		$rows = explode ( NEW_LINE, $product_filter_list );
		$sku_cat = array ();
		foreach ( $rows as $row ) {
			if ($row) {
				$rs = explode ( "\t", $row );
				if ($rs [0] == 'product code') {
				} else {
					$sku_cat [$rs [0]] = $rs [1];
				}
			}
		}
		return $sku_cat;
	}
	
	/**
	 *
	 * @return array
	 */
	private function _cat_s_name() {
		$product_filter_list = file_get_contents ( $this->home_dic . 'product category list.txt' );
		$rows = explode ( NEW_LINE, $product_filter_list );
		$rt = array ();
		foreach ( $rows as $row ) {
			if ($row) {
				$rs = explode ( "\t", $row );
				// if (strlen ( $rs [2] ) == 4) {
				$rt [$rs [2]] = strtolower ( trim ( $rs [3], '"' ) );
				// }
			}
		}
		return $rt;
	}
	/**
	 * key为小写的类别名称，value为类别名称和path
	 *
	 * @return multitype:multitype:unknown string
	 */
	private function _cat_s_url_path() {
		$product_filter_list = file_get_contents ( $this->home_dic . 'category_export - category_export.csv' );
		$rows = explode ( NEW_LINE, $product_filter_list );
		unset ( $rows [0] );
		$rt = array ();
		foreach ( $rows as $row ) {
			if ($row) {
				$rs = str_getcsv ( $row );
				$rs [0] = trim ( $rs [0], '"' );
				
				$rt [strtolower ( $rs [0] )] = array (
						$rs [0],
						$rs [3] 
				);
			}
		}
		return $rt;
	}
	
	/**
	 * 导出商品
	 */
	public function products() {
		$fp = fopen ( $this->home_dic . 'Product_Import-updated.csv', 'w' );
		
		$sku_attr = $this->_sku_s_attr ();
		$sku_cat = $this->_sku_s_attr ();
		$cat_s_name = $this->_cat_s_name ();
		$cat_s_url_path = $this->_cat_s_url_path ();
		
		$this->_product_filter_list ( $filters, $sku_filters );
		
		$titles = array (
				'name',
				'sku',
				'product.attribute_set',
				'product.type',
				'status',
				'visibility',
				'description',
				'short_description',
				'price',
				'special_price',
				'special_from_date',
				'special_to_date',
				'tax_class_id',
				'weight',
				'product.has_options',
				'product.required_options',
				'stock.is_in_stock',
				'stock.qty',
				'meta_title',
				'meta_keyword',
				'meta_description',
				'image',
				'image_label',
				'small_image',
				'small_image_label',
				'thumbnail',
				'thumbnail_label',
				'image_ribbon',
				'news_from_date',
				'news_to_date',
				'url_key',
				'product.websites',
				'category.path',
				'featured_product',
				'ships_freight',
				'flat_rate_shipping_box',
				'units_sold_per_product',
				'product_badges',
				'user_manual_name',
				'user_manual_file',
				'catalog_page',
				'product_includes',
				'product_spotlight',
				'num_pieces',
				'package_width',
				'package_height',
				'package_length',
				'package_quantity',
				'manufacturer',
				'vendor_sku' 
		);
		
		$_titles = $titles;
		
		foreach ( $filters as $filter ) {
			$_titles [] = trim ( preg_replace ( '{[^0-9a-zA-Z]+}', '_', $filter ), '_' );
		}
		
		fputcsv ( $fp, $_titles );
		
		$products = $this->source->get_all_arr ( "
SELECT
	p.*, p. CODE AS sku,
	p.product_description AS description,
	pc.`name` AS product_category_name,
	'simple' AS `product.type`,
	'Catalog, Search' AS visibility,
	'' AS short_description,
	list_price AS price,
	sell_price AS special_price,
	sell_price_from_date AS special_from_date,
	sell_price_till_date AS special_to_date,
	'Taxable Goods' AS tax_class_id,
	weight,
	'No' AS `product.has_options`,
	'No' AS `product.required_options`,
	image,
	'base' AS `product.websites`,
	catalog_page,
	user_manual_file,
	application,
	feature,
	product_includes,
	inventory_id,
	truck_shipment AS `ships_freight`,
	manufacturer,
	vendor_number AS `vendor_sku`
FROM
	products p
LEFT JOIN product_categories pc ON pc.id = p.product_category_id
LEFT JOIN (
	SELECT
		product_id,
		`value` AS weight
	FROM
		`product_vars`
	WHERE
		`name` = 'weight'
	GROUP BY
		product_id
) weight ON weight.product_id = p.id
LEFT JOIN (
	SELECT
		product_id,
		`value` AS catalog_page
	FROM
		`product_vars`
	WHERE
		`name` = 'catalog_page'
	GROUP BY
		product_id
) catalog_page ON catalog_page.product_id = p.id
LEFT JOIN (
	SELECT
		product_id,
		`value` AS user_manual_file
	FROM
		`product_vars`
	WHERE
		`name` = 'manual'
	GROUP BY
		product_id
) user_manual_file ON user_manual_file.product_id = p.id
LEFT JOIN (
	SELECT
		product_id,
		`value` AS application
	FROM
		`product_vars`
	WHERE
		`name` = 'application'
	GROUP BY
		product_id
) application ON application.product_id = p.id
LEFT JOIN (
	SELECT
		product_id,
		`value` AS feature
	FROM
		`product_vars`
	WHERE
		`name` = 'feature'
	GROUP BY
		product_id
) feature ON feature.product_id = p.id
LEFT JOIN (
	SELECT
		product_id,
		`value` AS product_includes
	FROM
		`product_vars`
	WHERE
		`name` = 'includes'
	GROUP BY
		product_id
) product_includes ON product_includes.product_id = p.id
LEFT JOIN (
	SELECT
		product_id,
		`value` AS inventory_id
	FROM
		`product_vars`
	WHERE
		`name` = 'inventory_id'
	GROUP BY
		product_id
) inventory_id ON inventory_id.product_id = p.id
LEFT JOIN (
	SELECT
		product_id,
		`value` AS `truck_shipment`
	FROM
		`product_vars`
	WHERE
		`name` = 'truck_shipment'
	GROUP BY
		product_id
) truck_shipment ON truck_shipment.product_id = p.id
LEFT JOIN (
	SELECT
		product_id,
		`value` AS `vendor_id`,
		vendors.company AS manufacturer
	FROM
		`product_vars`
	LEFT JOIN vendors ON vendors.id = product_vars.
	VALUE

	WHERE
		`name` = 'vendor_id'
	GROUP BY
		product_id
) vendor_id ON vendor_id.product_id = p.id
LEFT JOIN (
	SELECT
		product_id,
		`value` AS `vendor_number`
	FROM
		`product_vars`
	WHERE
		`name` = 'vendor_number'
	GROUP BY
		product_id
) vendor_number ON vendor_number.product_id = p.id
LIMIT " . LIMIT_VALUE );
		foreach ( $products as $product ) {
			
			$product ['application'] = str_replace ( '<p>&nbsp;</p>', '', $product ['application'] );
			$product ['product_includes'] = str_replace ( '<p>&nbsp;</p>', '', $product ['product_includes'] );
			
			$product ['name'] = html_entity_decode ( $product ['name'], ENT_QUOTES, "UTF-8" );
			$product ['status'] = $product ['active'] == '1' ? 'Enabled' : 'Disabled';
			$product ['url_key'] = preg_replace ( '{[^0-9a-zA-Z]+}', '-', $product ['name'] );
			$product ['category.path'] = '';
			$product ['product_spotlight'] = ($product ['application'] && $product ['feature']) ? $product ['application'] . '<br />' . $product ['feature'] : $product ['application'] . $product ['feature'];
			$product ['stock.is_in_stock'] = ($product ['inventory_id'] == '1' || $product ['inventory_id'] == null) ? 'in stock' : 'Out of Stock';
			$product ['stock.qty'] = $product ['stock.is_in_stock'] == 'in stock' ? 500 : 0;
			$product ['description'] = html_entity_decode ( html_entity_decode ( $product ['description'], ENT_QUOTES, "UTF-8" ), ENT_QUOTES, "UTF-8" );
			$product ['featured_product'] = $product ['featured_item'] ? 'yes' : 'no';
			
			if (! $sku_attr [$product ['code']]) { // 根据货号在procut filter.xls中查找分类ID
				$product ['product.attribute_set'] = null; // 如果没有查到
			} else {
				if (sizeof ( $sku_filters [$product ['code']] ) == 0) { // 该sku是否有过滤器值
					$product ['product.attribute_set'] = 'Default'; // 没有
				} else {
					$product ['product.attribute_set'] = isset ( $cat_s_url_path [$cat_s_name [$sku_attr [$product ['code']]]] ) ? $cat_s_url_path [$cat_s_name [$sku_attr [$product ['code']]]] [0] : null;
				}
			}
			
			$product ['category.path'] = isset ( $cat_s_url_path [$cat_s_name [$sku_cat [$product ['code']]]] ) ? $cat_s_url_path [$cat_s_name [$sku_cat [$product ['code']]]] [1] : null;
			$product ['product_includes'] = html_entity_decode ( html_entity_decode ( $product ['product_includes'], ENT_QUOTES, "UTF-8" ), ENT_QUOTES, "UTF-8" );
			$product ['product_spotlight'] = html_entity_decode ( html_entity_decode ( $product ['product_spotlight'], ENT_QUOTES, "UTF-8" ), ENT_QUOTES, "UTF-8" );
			
			$product_img_code = $this->_get_product_img_code ( $product ['code'] );
			
			if ($product_img_code) {
				$product ['image'] = 'products/' . $product_img_code . '/' . $product_img_code . 'Main.jpg';
				$product ['small_image'] = $product ['thumbnail'] = 'products/' . $product_img_code . '/Thumbnails/' . $product_img_code . 'Main.jpg';
			}else{
				$product ['image'] = $product ['small_image'] = $product ['thumbnail'] = null;
			}
			
			if ($product ['special_price'] == 0) {
				$product ['special_price'] = $product ['special_from_date'] = $product ['special_to_date'] = null;
			}
			//
			$nr = array ();
			foreach ( $titles as $key => $title ) {
				$nr [] = isset ( $product [$title] ) ? $product [$title] : null;
			}
			
			foreach ( $filters as $key => $title ) {
				$nr [] = isset ( $sku_filters [$product ['code']] [$title] ) ? $sku_filters [$product ['code']] [$title] : null;
			}
			
			fputcsv ( $fp, $nr );
		}
		
		fclose ( $fp );
	}
	private function _get_product_img_code($code) {
		$darr = scandir ( IMG_PATH . '/' . $code );
		
		if ($darr) {
			foreach ( $darr as $da ) {
				
				if (preg_match ( "{(.+)Main.jpg$}i", $da, $matches )) {
					
					return $matches [1];
				}
			}
		}
	}
	private function _product_filter_list(&$filters, &$skus) {
		$product_filter_list = file_get_contents ( $this->home_dic . 'product filter list.txt' );
		$rows = explode ( NEW_LINE, $product_filter_list );
		$titles = array ();
		foreach ( $rows as $row ) {
			$rs = explode ( "\t", $row );
			if ($rs [0] == 'product code') {
				unset ( $rs [0], $rs [1] );
				$titles = $rs;
				foreach ( $rs as $r ) {
					if ($r) {
						$filters [] = $r;
					}
				}
			} else {
				
				foreach ( $titles as $k => $t ) {
					if ($t) {
						$skus [$rs [0]] [$t] = trim ( $rs [$k], "\"" );
					}
				}
			}
		}
		
		$filters = array_unique ( $filters );
	}
	public function ccp() {
		$fp = fopen ( $this->home_dic . 'Product_Extra_Import-CCP.csv', 'w' );
		
		fputcsv ( $fp, array (
				'##CCP',
				'url_path',
				'sku',
				'position' 
		) );
		
		$sku_cat = $this->_sku_s_cat ();
		$cat_s_name = $this->_cat_s_name ();
		$cat_s_url_path = $this->_cat_s_url_path ();
		
		$products = $this->source->get_all_arr ( "
SELECT
	p.*
FROM
	products p
LIMIT " . LIMIT_VALUE );
		foreach ( $products as $product ) {
			
			//
			$nr = array (
					'CCP',
					isset ( $cat_s_url_path [$cat_s_name [$sku_cat [$product ['code']]]] ) ? $cat_s_url_path [$cat_s_name [$sku_cat [$product ['code']]]] [1] : null,
					$product ['code'],
					0 
			);
			
			fputcsv ( $fp, $nr );
		}
		
		fclose ( $fp );
	}
	public function cpri() {
		$idscode = $this->source->get_array_keyvalue ( "select id,code from products" );
		$product_relations = $this->source->get_all_arr ( "SELECT * FROM `product_relations`" );
		
		$fp = fopen ( $this->home_dic . 'Product_Extra_Import-CPRI.csv', 'w' );
		fputcsv ( $fp, array (
				'##CPRI',
				'sku',
				'linked_sku',
				'position',
				'qty' 
		) );
		
		foreach ( $product_relations as $_row ) {
			$i = 0;
			foreach ( explode ( ',', $_row ['relation'] ) as $item ) {
				
				if ($idscode [$item]) {
					fputcsv ( $fp, array (
							'CPRI',
							$idscode [$_row ['id']],
							$idscode [$item],
							$i ++,
							null 
					) );
				}
			}
		}
		
		fclose ( $fp );
	}
	public function cpxi() {
		$fp = fopen ( $this->home_dic . 'Product_Extra_Import-CPXI.csv', 'w' );
		fputcsv ( $fp, array (
				'##CPXI',
				'sku',
				'linked_sku',
				'position',
				'qty' 
		) );
		
		$products = $this->source->get_all_arr ( "SELECT p.* FROM products p LIMIT " . LIMIT_VALUE );
		foreach ( $products as $p ) {
			$i = 0;
			$cpxis = $this->source->get_all_arr ( "SELECT product_id,code,count(product_id) as count from order_products left join products p on p.id = order_products.product_id where order_id in (SELECT order_id FROM `order_products` WHERE `product_id` = '{$p['id']}')  and product_id != '{$p['id']}' GROUP BY product_id order by count desc limit 3" );
			foreach ( $cpxis as $cpxi ) {
				fputcsv ( $fp, array (
						'CPXI',
						$p ['code'],
						$cpxi ['code'],
						$i ++,
						null 
				) );
			}
		}
		
		fclose ( $fp );
	}
	
	/**
	 * 产生图片数据
	 */
	public function gen_pics() {
		// for($i = 100; $i < 900; $i ++) {
		// for($j = 1000; $j < 1030; $j ++) {
		// $sku = $i . '-' . $j;
		// mkdir ( IMG_PATH . '/' . $sku );
		// for($c = ord ( 'A' ); $c < ord ( 'E' ); $c ++) {
		// touch ( IMG_PATH . '/' . $sku . '/' . $sku . chr ( $c ) . '.jpg' );
		// }
		// }
		// }
	}
	public function cpi() {
		$fp = fopen ( $this->home_dic . 'Product_Extra_Import-CPI.csv', 'w' );
		if (! $fp) {
			exit ();
		}
		fputcsv ( $fp, array (
				'##CPI',
				'sku',
				'image_url',
				'label',
				'position',
				'disabled' 
		) );
		
		if ($handle = opendir ( IMG_PATH )) {
			
			while ( false !== ($file = readdir ( $handle )) ) {
				
				if ($file != '.' && $file != '..') {
					
					$darr = scandir ( IMG_PATH . '/' . $file );
					$darr = array_filter ( $darr, 'filter_pic' );
					usort ( $darr, 'sort_pic' );
					
					foreach ( $darr as $key => $file_jpg ) {
						fputcsv ( $fp, array (
								'CPI',
								$file,
								'products/' . $file . '/' . $file_jpg,
								null,
								$key + 1,
								0 
						) );
					}
				}
			}
			
			closedir ( $handle );
		} else {
			echo 'error:目录打开失败。';
		}
		
		fclose ( $fp );
	}
}

$product = new product ();
$product->products ();
// $product->ccp ();
// $product->cpri ();
// $product->cpxi ();
// $product->cpi ();
// $product->gen_pics ();




