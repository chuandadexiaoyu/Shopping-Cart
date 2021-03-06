<?php
/*
	Plugin Name: Категории
	Plugin URI: http://osc-cms.com/extend/plugins
	Version: 1.1
	Description: Плагин выводит дерево категорий
	Author: CartET
	Author URI: http://osc-cms.com
	Plugin Group: Products
*/

add_action('box',				'box_categories_func');
add_filter('head_array_detail',	'box_categories_js');

function box_categories_js($value)
{
	if (get_option('menuJSType') == 'accordion')
	{
		add_style(plugurl().'js/menu_accordion.css', $value, 'categories');
		add_js(plugurl().'js/menu_accordion.js', $value, 'categories');
	}

	return $value;
}

function box_categories_func()
{
	global $osTemplate;
	$box = new osTemplate;

	// Build Tree
	function build_tree($cats, $parent_id, $level)
	{
		if(is_array($cats) && count($cats[$parent_id]) > 0)
		{
			$aTree = array();
			foreach($cats[$parent_id] as $cat)
			{
				$category_path = explode('_',$GLOBALS['cPath']);
				$in_path = in_array($cat['cID'], $category_path);
				$this_category = array_pop($category_path);

				$active = '';
				$current = '';
				if ($this_category == $cat['cID'])
				{
					$active = 'active';
					$current = 'current';
				}
				elseif ($in_path)
				{
					$active = 'current-parent';
					$current = 'current';
				}

				if ($level != get_option('maxSubCategories'))
				{
					$aTree[] = array(
						'id' => $cat['cID'],
						'link' => os_href_link(FILENAME_DEFAULT, os_category_link($cat['cID'], $cat['categories_name'])),
						'name' => $cat['categories_name'],
						'image' => (get_option('showCatImages') == 'true') ? $cat['categories_image'] : '',
						'counts' => (get_option('countProducts') == 'true') ? os_count_products_in_category($cat['cID']) : '',
						'level' => $level,
						'active' => $active,
						'current' => $current,
						'childs' => (get_option('subCategories') == 'true') ? build_tree($cats, $cat['cID'], $level+1) : '',
					);
				}
			}
		}
		else
			return null;

		return $aTree;
	}

	$group_check = (GROUP_CHECK == 'true') ? "AND c.group_permission_".$_SESSION['customers_status']['customers_status_id']." = 1 " : '';

	$categories_query = osDBquery("
		SELECT 
			c.categories_id as cID, cd.categories_name, c.parent_id, c.categories_image 
		FROM 
			".TABLE_CATEGORIES." c, ".TABLE_CATEGORIES_DESCRIPTION . " cd 
		WHERE 
			c.categories_status = '1' ".$group_check." AND c.categories_id = cd.categories_id AND cd.language_id='".(int)$_SESSION['languages_id']."' AND c.menu = '1' 
		ORDER BY 
			sort_order, cd.categories_name
	");

	$cats = array();
	while($cat = os_db_fetch_array($categories_query, true))
	{
		if (is_file(dir_path('images').'categories/'.$cat['categories_image']))
			$cat['categories_image'] = http_path('images').'categories/'.$cat['categories_image'];

		$cats[$cat['parent_id']][] = $cat;
	}

	$box->assign('aCategories', build_tree($cats, 0, 0));
	$box->assign('imageWidth', get_option('cImgWidth'));
	$box->assign('imageHeight', get_option('cImgHeight'));
	$box->assign('aCategories', build_tree($cats, 0, 0));
	$box->assign('plugDir', dirname(__FILE__).'/themes');
	$box->assign('language', $_SESSION['language']);
	$box->template_dir = plugdir();

	if (!CacheCheck())
	{
		$box->caching = 0;
		$_box_value = $box->fetch(dirname(__FILE__).'/themes/categories.html');
	}
	else
	{
		$box->caching = 1;
		$box->cache_lifetime = CACHE_LIFETIME;
		$box->cache_modified_check = CACHE_CHECK;
		$cache_id = $_SESSION['language'];
		$_box_value = $box->fetch(dirname(__FILE__).'/themes/categories.html', $cache_id);
	}

	$osTemplate->assign('box_NEW_CATEGORIES', $_box_value);
}

function box_categories_install()
{
	add_option('countProducts',		'false', 'radio', "array('true', 'false')");
	add_option('subCategories',		'true', 'radio', "array('true', 'false')");
	add_option('maxSubCategories',	'5', 'input');
	add_option('showCatImages',		'false', 'radio', "array('true', 'false')");
	add_option('cImgWidth',			'30', 'input');
	add_option('cImgHeight',		'30', 'input');
	add_option('menuJSType',		'none', 'radio', "array('none', 'accordion')");
}
?>