<?php
/*
*---------------------------------------------------------
*
*	CartET - Open Source Shopping Cart Software
*	http://www.cartet.org
*
*---------------------------------------------------------
*/

define('AJAX_APPLICATION_RUNNING', true);

#require('includes/classes/JsHttpRequest.php');

#unset($JsHttpRequest);
#$JsHttpRequest = new JsHttpRequest('');
require('includes/top.php');
#$JsHttpRequest->setEncoding($_SESSION['language_charset']);

$axhandler = ($_GET['ajax_page']) ? $_GET['ajax_page'] : $_POST['ajax_page'];

if (!isset($axhandler) || !os_not_null($axhandler) || !is_file(_MODULES.'ajax/'.$axhandler.'.php'))
	die('***ERROR*** Ajax page "'.$axhandler.'" not define or not exist!!!');

if(is_file(DIR_WS_LANGUAGES.$_SESSION['language'].'/'.$axhandler.'.php'))
	require(DIR_WS_LANGUAGES.$_SESSION['language'].'/'.$axhandler.'.php');

require(_MODULES.'ajax/'.$axhandler.'.php');
exit;
?>