<?php
@define('GZIP_LEVEL','0');
require('includes/top.php');

@define('YML_NAME', '');
@define('YML_COMPANY', '');
@define('YML_AVAILABLE', 'stock');
@define('YML_DELIVERYINCLUDED', 'false');
@define('YML_AUTH_USER', '');
@define('YML_AUTH_PW', '');
@define('YML_REF_ID', '');
@define('YML_STRIP_TAGS', 'true');
@define('YML_USE_CDATA', 'true');
@define('YML_UT8', '');
@define('YML_VENDOR', 'false');
@define('YML_VENDORCODE', 'true');
@define('YML_USE_CPATH', 'false');
@define('YML_OUTPUT_BUFFER_MAXSIZE', 1024);
@define('YML_OUTPUT_DIRECTORY', 'temp/');
@define('YML_GZIP', 'false');

if (!get_cfg_var('safe_mode') && function_exists('set_time_limit'))
{
	@set_time_limit(0);
}

if (YML_AUTH_USER != "" && YML_AUTH_PW != "")
{
	if (!isset($PHP_AUTH_USER) || $PHP_AUTH_USER != YML_AUTH_USER || $PHP_AUTH_PW != YML_AUTH_PW)
	{
		header('WWW-Authenticate: Basic realm="Realm-Name"');
		header("HTTP/1.0 401 Unauthorized");
		die;
	}
}

//header('Content-Type: text/xml');

$charset = (YML_UTF8 == 'true') ? 'windows-1251' : $_SESSION['language_charset'];

$yml_referer = YML_REF_ID;
$referrer = (YML_REF_ID != '' ? '&'.YML_REF_ID : '');
$referrer .= (!empty($_GET['ref']) ? '&ref='.$_GET['ref'] : '');

if ($_SESSION["language_code"] != DEFAULT_LANGUAGE)
	$language_get = '&language='.$_SESSION["language_code"];

$display_all_categories = (isset($_GET['cats']) && $_GET['cats'] == 'all');

os_yml_out('<?xml version="1.0" encoding="'.$charset .'"?'.'><!DOCTYPE yml_catalog SYSTEM "shops.dtd">');
os_yml_out('<yml_catalog date="'.date('Y-m-d H:i').'">');
os_yml_out('<shop>');
os_yml_out('<name>'.os_yml_clear_string((YML_NAME == '' ? STORE_NAME : YML_NAME)) .'</name>');
os_yml_out('<company>'.os_yml_clear_string((YML_COMPANY == '' ? STORE_OWNER : YML_COMPANY)).'</company>');
os_yml_out('<url>'.HTTP_SERVER.'/</url>');

$current_currency = $_SESSION['currency'];
if ($_SESSION['currency'] == 'RUB')
	$current_currency = 'RUR';

os_yml_out('  <currencies>');
//foreach($osPrice->currencies as $code => $v){
//  if($code == 'RUB') $code = 'RUR';
//  os_yml_out('    <currency id="'.$code.'" rate="'.number_format(1/$v['value'],4).'"/>');
//}
if ($_GET['currency'] == "")
{
	foreach($osPrice->currencies as $code => $v)
	{
		os_yml_out('    <currency id="'.$code.'" rate="'.number_format(1/$v["value"],4).'"/>');
	}
} 
else
{
	$varcurrency = $osPrice->currencies[$_GET['currency']];
	foreach($osPrice->currencies as $code => $v)
	{
		os_yml_out('    <currency id="'.$code.'" rate="'.number_format($varcurrency['value']/$v['value'],4).'"/>');
	}
}
os_yml_out('  </currencies>');

// Категории
os_yml_out('  <categories>');

if ($yml_select === false)
{
	$yml_select = os_db_query('describe '.TABLE_CATEGORIES.' yml_enable');
	$yml_select = ($yml_select > 0) ? ", c.yml_enable, c.yml_bid, c.yml_cbid " : "";
}

$categories_bid = $categories_disable = array();
$categories_query = os_db_query("SELECT c.categories_id, c.parent_id, cd.categories_name".$yml_select." FROM ".TABLE_CATEGORIES." c LEFT JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd ON (c.categories_id = cd.categories_id) WHERE cd.language_id='".(int)$_SESSION['languages_id']."' AND c.categories_status= '1' AND c.yml_enable = '1' ORDER BY c.categories_id");
while ($categories = os_db_fetch_array($categories_query))
{
	if (os_not_null($categories['categories_name']))
	{
		if (!isset($categories["yml_enable"]) || $categories["yml_enable"] == 1)
		{
			$categories_bid[$categories['categories_id']] = (!isset($categories["yml_bid"])) ? 0 : $categories["yml_bid"];
			$categories_cbid[$categories['categories_id']] = (!isset($categories["yml_cbid"])) ? 0 : $categories["yml_cbid"];
			os_yml_out('    <category id="'.$categories['categories_id'].'"'.(($categories['parent_id'] == "0") ? '>' : ' parentId="'.$categories['parent_id'].'">').os_yml_clear_string($categories['categories_name']).'</category>');
		}
		else
			$categories_disable[] = $categories_id;
	}
}
os_yml_out('  </categories>');

// Товар
os_yml_out('  <offers>');
//$products_short_description = os_db_query('describe '.TABLE_PRODUCTS_DESCRIPTION.' products_short_description');
//$yml_select = os_db_query('describe '.TABLE_PRODUCTS.' products_to_xml');

$products_sql = "
	SELECT distinct 
		p.products_id, p2c.categories_id, p.products_model, p.products_quantity, p.products_image, 
		IF(s.status, s.specials_new_products_price, p.products_price) AS products_price, 
		p.products_tax_class_id, p.manufacturers_id, p.products_sort, pd.products_name, m.manufacturers_name, pd.products_description, 
		pd.products_short_description, p.yml_bid, p.yml_cbid, p.yml_available 
	FROM 
		".TABLE_PRODUCTS." p
			LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON (p.products_id = pd.products_id)
			LEFT JOIN ".TABLE_MANUFACTURERS." m ON (p.manufacturers_id = m.manufacturers_id)
			LEFT JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c ON (p.products_id = p2c.products_id)
			LEFT JOIN ".TABLE_SPECIALS." s ON (p.products_id = s.products_id)
	WHERE 
		p.products_status = 1 AND 
		p.products_to_xml = 1 AND 
		pd.language_id = ".(int)$_SESSION['languages_id']."
	GROUP BY 
		p.products_id 
	ORDER BY 
		p.products_id ASC";

$products_query = os_db_query($products_sql);
while ($products = os_db_fetch_array($products_query))
{
	$available = "false";

	//switch(YML_AVAILABLE)
	switch($products['yml_available'])
	{
		case 0:
			$available = 'false';
		break;
		case 1:
			$available = 'true';
		break;
		case 2:
			if ($products['products_quantity'] > 0)
				$available = "true";
			else
				$available = "false";
		break;
	}

	$cbid = $bid = '';
	$products["yml_bid"] = max((!isset($products["yml_bid"]) ? 0 : $products["yml_bid"]), $categories_bid[$products["categories_id"]]);

	if ($products["yml_bid"] > 0)
		$bid = ' bid="'.$products["yml_bid"].'"';

	$products["yml_cbid"] = max((!isset($products["yml_cbid"]) ? 0 : $products["yml_cbid"]), $categories_cbid[$products["categories_id"]]);

	if ($products["yml_cbid"] > 0)
		$cbid = ' cbid="'.$products["yml_cbid"].'"';

	$price = $products['products_price'];
	$price = $osPrice->GetPrice($products['products_id'], false, 1, $products['products_tax_class_id'], $price);
	$url = os_href_link(FILENAME_PRODUCT_INFO, os_product_link($products['products_id'], $products['products_name']).(isset($_GET['ref']) ? '&amp;ref='.$_GET['ref'] : null).$yml_referer, 'NONSSL', false);
	$available = ' available="'.$available.'"';
	os_yml_out('<offer id="'.$products['products_id'].'"'.$available.$bid.$cbid.'>');
	os_yml_out('  <url>'.$url.'</url>');
	os_yml_out('  <price>'.$price['price'].'</price>');
	os_yml_out('  <currencyId>'.$current_currency.'</currencyId>');
	os_yml_out('  <categoryId>'.$products['categories_id'].'</categoryId>');

	if ($display_all_categories)
	{
		$p2c_query = os_db_query("SELECT categories_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id=".(int)$products['products_id']." AND categories_id<>".(int)$products['categories_id']."");
		while($p2c = os_db_fetch_array($p2c_query))
		{
			os_yml_out('  <categoryId>'.$p2c['categories_id'].'</categoryId>');
		}
	}

	if (os_not_null($products['products_image']))
		os_yml_out('  <picture>'. http_path('images_thumbnail').urlencode(basename($products['products_image'])).'</picture>');
	
	if (YML_DELIVERYINCLUDED == "true")
		os_yml_out('  <deliveryIncluded/>');

	os_yml_out('  <name>'.os_yml_clear_string($products['products_name']).'</name>');

	if (YML_VENDOR == "true" && $products['manufacturers_id'] != 0)
	{
		os_yml_out('  <vendor>'.os_yml_clear_string($products['manufacturers_name']).'</vendor>');
	}

	if (YML_VENDORCODE == "true" && os_not_null($products['products_model']))
	{
		os_yml_out('  <vendorCode>'.$products['products_model'].'</vendorCode>');
	}

	os_yml_out('  <description>'.os_yml_clear_string($products['products_short_description']).'</description>');

	if (YML_SALES_NOTES != '')
	{
		os_yml_out('  <sales_notes>'. os_yml_clear_string(YML_SALES_NOTES).'</sales_notes>');
	}

	os_yml_out('</offer>'."\n");
}

os_yml_out('</offers>');
os_yml_out('</shop>');
os_yml_out('</yml_catalog>');

os_yml_out();

function os_yml_out($output='')
{
	if (!empty($output))
	{
		echo $output."\n";
	}
}

function os_yml_clear_string($str)
{
	global $charset;
	//    $str = htmlspecialchars_decode($str, ENT_QUOTES);
	if (YML_STRIP_TAGS == 'true')
	{
		$str = strip_tags($str);
	}

	if (function_exists('iconv'))
	{
		if ($charset != $_SESSION['language_charset'])
		{
			$str = iconv($_SESSION['language_charset'], $charset, $str);
		}
	}
	if (YML_USE_CDATA == 'true')
		$str = '<![CDATA['.$str.']]>';
	else
		$str = htmlspecialchars($str, ENT_QUOTES);

	return $str;
}
?>