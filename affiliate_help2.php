<?php
/*
*---------------------------------------------------------
*
*	CartET - Open Source Shopping Cart Software
*	http://www.cartet.org
*
*---------------------------------------------------------
*/

require('includes/top.php');

//$osTemplate = new osTemplate;

$osTemplate->assign(array(
			'HTML_PARAMS' => HTML_PARAMS,
			'HREF' => (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG,
			'TITLE' => TITLE));

$osTemplate->assign('help_file', 'help2');

$osTemplate->assign('language', $_SESSION['language']);
$osTemplate->caching = 0;

$osTemplate->display(CURRENT_TEMPLATE . '/module/affiliate_help.html');
?>