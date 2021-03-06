<?php
/*
*---------------------------------------------------------
*
*	CartET - Open Source Shopping Cart Software
*	http://www.cartet.org
*
*---------------------------------------------------------
*/

class z_payment extends CartET
{
	/**
	 * Системный идентификатор модуля
	 */
	public $code;

	/**
	 * Название модуля
	 */
	public $title;

	/**
	 * Описание модуля
	 */
	public $description;

	/**
	 * Статус модуля
	 */
	public $enabled;

	/**
	 * Сессионная переменная модуля
	 */
	public $name = 'cart_zpayment_id';

	// class constructor
	function z_payment() 
	{
		global $order;

		$this->code = 'z_payment';
		$this->title = MODULE_PAYMENT_Z_PAYMENT_TEXT_TITLE;
		$this->public_title = MODULE_PAYMENT_Z_PAYMENT_TEXT_PUBLIC_TITLE;
		$this->description = MODULE_PAYMENT_Z_PAYMENT_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_PAYMENT_Z_PAYMENT_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_Z_PAYMENT_STATUS == 'True') ? true : false);
		$this->icon = 'icon.png';
		$this->icon_small = 'icon_small.png';

		if ((int)MODULE_PAYMENT_Z_PAYMENT_PREPARE_ORDER_STATUS_ID > 0)
		{
			$this->order_status = MODULE_PAYMENT_Z_PAYMENT_PREPARE_ORDER_STATUS_ID;
		}

		if (is_object($order))
			$this->update_status();

		$this->form_action_url = 'https://z-payment.com/merchant.php';
	}

	// class methods
	function update_status()
	{
		global $order;

		if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_Z_PAYMENT_ZONE > 0) )
		{
			$check_flag = false;
			$check_query = os_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_Z_PAYMENT_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
			while ($check = os_db_fetch_array($check_query))
			{
				if ($check['zone_id'] < 1)
				{
					$check_flag = true;
					break;
				}
				elseif ($check['zone_id'] == $order->billing['zone_id'])
				{
					$check_flag = true;
					break;
				}
			}

			if ($check_flag == false)
			{
				$this->enabled = false;
			}
		}
	}

	function javascript_validation()
	{
		return false;
	}

	function selection()
	{
		if (isset($_SESSION[$this->name]))
		{
			$order_id = substr($_SESSION[$this->name], strpos($_SESSION[$this->name], '-')+1);

			$check_query = os_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');

			if (os_db_num_rows($check_query) < 1)
			{
				$this->order->deleteOrderById($order_id);

				unset($_SESSION[$this->name]);
			}
		}

		if (os_not_null($this->icon)) $icon = os_image(http_path('payment').$this->code.'/'.$this->icon, $this->title);

		return array(
			'id' => $this->code,
			'icon' => $icon,
			'module' => $this->public_title
		);
	}

	function pre_confirmation_check()
	{
		global $cartID;

		if (empty($_SESSION['cart']->cartID))
		{
			$cartID = $_SESSION['cart']->cartID = $_SESSION['cart']->generate_cart_id();
		}

		if (!isset($_SESSION['cartID']))
		{
			$_SESSION['cartID'] = $cartID;
		}
	}

	function confirmation()
	{
		global $order, $order_total_modules;

		$this->order->confirmation($this->name, $order, $order_total_modules);

		return array('title' => MODULE_PAYMENT_Z_PAYMENT_TEXT_DESCRIPTION);
	}

	function process_button()
	{
		global $order;

		$process_button_string = '';

		$purse = MODULE_PAYMENT_Z_PAYMENT_ID;
		$order_sum = $order->info['total'];

		$order_id = substr($_SESSION[$this->name], strpos($_SESSION[$this->name], '-')+1);

		$process_button_string =
			os_draw_hidden_field('LMI_PAYMENT_NO', $order_id).
			os_draw_hidden_field('LMI_PAYEE_PURSE', $purse).
			os_draw_hidden_field('LMI_PAYMENT_DESC', 'OrderId-'.$order_id).
			os_draw_hidden_field('LMI_PAYMENT_AMOUNT', $order_sum).
			os_draw_hidden_field('CLIENT_MAIL', $order->customer['email_address']);

		return $process_button_string;
	}

	function before_process()
	{
		global $order;

		$order_id = substr($_SESSION[$this->name], strpos($_SESSION[$this->name], '-')+1);

		// Обновляем количество товаров
		for ($i=0, $n=sizeof($order->products); $i<$n; $i++)
		{
			$this->order->updateQuantity($order->products[$i]);
		}

		$this->orders->beforeProcess($order_id, $order);

		$this->after_process();

		require_once(DIR_WS_INCLUDES . 'affiliate_checkout_process.php');

		$_SESSION['cart']->reset(true);

		// unregister session variables used during checkout
		unset($_SESSION['sendto']);
		unset($_SESSION['billto']);
		unset($_SESSION['shipping']);
		unset($_SESSION['payment']);
		unset($_SESSION['comments']);

		unset($_SESSION[$this->name]);

		os_redirect(os_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
	}

	function after_process()
	{
		return false;
	}

	function output_error()
	{
		return false;
	}

	function check()
	{
		if (!isset($this->_check))
		{
			$check_query = os_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_Z_PAYMENT_STATUS'");
			$this->_check = os_db_num_rows($check_query);
		}
		return $this->_check;
	}

	function install()
	{
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_Z_PAYMENT_STATUS', 'True', '6', '3', 'os_cfg_select_option(array(\'True\', \'False\'), ', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_Z_PAYMENT_ALLOWED', '', '6', '4', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_Z_PAYMENT_ID', '', '6', '5', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_Z_PAYMENT_SECRET_KEY', '', '6', '5', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_Z_PAYMENT_SORT_ORDER', '0', '6', '6', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_Z_PAYMENT_ZONE', '0', '6', '7', 'os_get_zone_class_title', 'os_cfg_pull_down_zone_classes(', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_Z_PAYMENT_PREPARE_ORDER_STATUS_ID', '0', '6', '8', 'os_cfg_pull_down_order_statuses(', 'os_get_order_status_name', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_Z_PAYMENT_ORDER_STATUS_ID', '0', '6', '9', 'os_cfg_pull_down_order_statuses(', 'os_get_order_status_name', now())");
	}

	function remove()
	{
		os_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
	}

	function keys()
	{
		return array(
			'MODULE_PAYMENT_Z_PAYMENT_STATUS',
			'MODULE_PAYMENT_Z_PAYMENT_ALLOWED',
			'MODULE_PAYMENT_Z_PAYMENT_ID',
			'MODULE_PAYMENT_Z_PAYMENT_SECRET_KEY',
			'MODULE_PAYMENT_Z_PAYMENT_SORT_ORDER',
			'MODULE_PAYMENT_Z_PAYMENT_ZONE',
			'MODULE_PAYMENT_Z_PAYMENT_PREPARE_ORDER_STATUS_ID',
			'MODULE_PAYMENT_Z_PAYMENT_ORDER_STATUS_ID'
		);
	}
}
?>