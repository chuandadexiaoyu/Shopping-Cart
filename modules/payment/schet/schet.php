<?php
/*
*---------------------------------------------------------
*
*	CartET - Open Source Shopping Cart Software
*	http://www.cartet.org
*
*---------------------------------------------------------
*/

class schet extends CartET
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

	function schet()
	{
		$this->code = 'schet';
		$this->title = MODULE_PAYMENT_SCHET_TEXT_TITLE;
		$this->description = MODULE_PAYMENT_SCHET_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_PAYMENT_SCHET_SORT_ORDER;
		$this->info = MODULE_PAYMENT_SCHET_TEXT_INFO;
		$this->enabled = ((MODULE_PAYMENT_SCHET_STATUS == 'True') ? true : false);
		$this->icon = 'icon.png';
		$this->icon_small = 'icon_small.png';

		if ((int) MODULE_PAYMENT_SCHET_ORDER_STATUS_ID > 0)
		{
			$this->order_status = MODULE_PAYMENT_SCHET_ORDER_STATUS_ID;
		}
	}

	function update_status()
	{
		global $order;

		if (($this->enabled == true) && ((int) MODULE_PAYMENT_SCHET_ZONE > 0))
		{
			$check_flag = false;
			$check_query = os_db_query("select zone_id from ".TABLE_ZONES_TO_GEO_ZONES." where geo_zone_id = '".MODULE_PAYMENT_SCHET_ZONE."' and zone_country_id = '".$order->billing['country']['id']."' order by zone_id");
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
		global $order;

		if (os_not_null($this->icon)) $icon = os_image(http_path('payment').$this->code.'/'.$this->icon, $this->title);

		if (isset($_SESSION['customer_id']))
		{
			$getOrderQuery = osDBquery("SELECT orders_id, customers_id FROM ".TABLE_ORDERS." WHERE customers_id = '".$_SESSION['customer_id']."' AND payment_method = 'schet' ORDER BY orders_id DESC LIMIT 1");
			if (os_db_num_rows($getOrderQuery, true) > 0)
			{
				$getOrder = os_db_fetch_array($getOrderQuery);

				$company_query = os_db_query("SELECT * FROM ".TABLE_COMPANIES." WHERE orders_id='".(int)$getOrder['orders_id']."'");
				if (os_db_num_rows($company_query) > 0)
					$company = os_db_fetch_array($company_query);
				else
					$company = array();
			}
		}
		else
			$company = array();

		return array
		(
			'id' => $this->code,
			'icon' => $icon,
			'module' => $this->title,
			'description'=>$this->info,
			'fields' => array
			(
				array(
					'title' => MODULE_PAYMENT_SCHET_J_NAME_TITLE,
					'field' => MODULE_PAYMENT_SCHET_J_NAME_DESC
				),
				array(
					'title' => MODULE_PAYMENT_SCHET_J_NAME,
					'field' => os_draw_input_field('name', $company['name']) . MODULE_PAYMENT_SCHET_J_NAME_IP),
				array(
					'title' => MODULE_PAYMENT_SCHET_J_INN,
					'field' => os_draw_input_field('inn', $company['inn'])),
				array(
					'title' => MODULE_PAYMENT_SCHET_J_KPP,
					'field' => os_draw_input_field('kpp', $company['kpp'])),
				array(
					'title' => MODULE_PAYMENT_SCHET_J_OGRN,
					'field' => os_draw_input_field('ogrn', $company['ogrn'])),
				array(
					'title' => MODULE_PAYMENT_SCHET_J_OKPO,
					'field' => os_draw_input_field('okpo', $company['okpo'])),
				array(
					'title' => MODULE_PAYMENT_SCHET_J_RS,
					'field' => os_draw_input_field('rs', $company['rs'])),
				array(
					'title' => MODULE_PAYMENT_SCHET_J_BANK_NAME,
					'field' => os_draw_input_field('bank_name', $company['bank_name']) . MODULE_PAYMENT_SCHET_J_BANK_NAME_HELP),
				array(
					'title' => MODULE_PAYMENT_SCHET_J_BIK,
					'field' => os_draw_input_field('bik', $company['bik'])),
				array(
					'title' => MODULE_PAYMENT_SCHET_J_KS,
					'field' => os_draw_input_field('ks', $company['ks'])),
				array(
					'title' => MODULE_PAYMENT_SCHET_J_ADDRESS,
					'field' => os_draw_input_field('address', $company['address']) . MODULE_PAYMENT_SCHET_J_ADDRESS_HELP),
				array(
					'title' => MODULE_PAYMENT_SCHET_J_TELEPHONE,
					'field' => os_draw_input_field('phone', $order->customer['telephone']))
			)
		);
	}

	function pre_confirmation_check()
	{
		$this->name = os_db_prepare_input($_POST['name']);
		$this->inn = os_db_prepare_input($_POST['inn']);
		$this->kpp = os_db_prepare_input($_POST['kpp']);
		$this->ogrn = os_db_prepare_input($_POST['ogrn']);
		$this->okpo = os_db_prepare_input($_POST['okpo']);
		$this->rs = os_db_prepare_input($_POST['rs']);
		$this->bank_name = os_db_prepare_input($_POST['bank_name']);
		$this->bik = os_db_prepare_input($_POST['bik']);
		$this->ks = os_db_prepare_input($_POST['ks']);
		$this->address = os_db_prepare_input($_POST['address']);
		$this->yur_address = os_db_prepare_input($_POST['yur_address']);
		$this->fakt_address = os_db_prepare_input($_POST['fakt_address']);
		$this->telephone = os_db_prepare_input($_POST['phone']);
		$this->fax = os_db_prepare_input($_POST['fax']);
		$this->email = os_db_prepare_input($_POST['email']);
		$this->director = os_db_prepare_input($_POST['director']);
		$this->accountant = os_db_prepare_input($_POST['accountant']);
	}

	function confirmation()
	{
		global $_POST;

		return array
		(
			'title' => $this->title.': '.$this->check,
			'fields' => array(
				array('title' => MODULE_PAYMENT_SCHET_TEXT_DESCRIPTION)
			),
			'description' => $this->info
		);
	}

	function process_button()
	{
		return os_draw_hidden_field('name', $this->name).
		os_draw_hidden_field('inn', $this->inn).
		os_draw_hidden_field('kpp', $this->kpp).
		os_draw_hidden_field('ogrn', $this->ogrn).
		os_draw_hidden_field('okpo', $this->okpo).
		os_draw_hidden_field('rs', $this->rs).
		os_draw_hidden_field('bank_name', $this->bank_name).
		os_draw_hidden_field('bik', $this->bik).
		os_draw_hidden_field('ks', $this->ks).
		os_draw_hidden_field('address', $this->address).
		os_draw_hidden_field('yur_address', $this->yur_address).
		os_draw_hidden_field('fakt_address', $this->fakt_address) .
		os_draw_hidden_field('phone', $this->telephone) .
		os_draw_hidden_field('fax', $this->fax) .
		os_draw_hidden_field('email', $this->email) .
		os_draw_hidden_field('director', $this->director) .
		os_draw_hidden_field('accountant', $this->accountant);
	}

	function before_process()
	{
		$this->pre_confirmation_check();
		return false;
	}

	function after_process()
	{
		global $insert_id, $name, $inn, $kpp, $ogrn, $okpo, $rs, $bank_name, $bik, $ks, $address, $yur_address, $fakt_address, $telephone, $fax, $email, $director, $accountant, $checkout_form_action, $checkout_form_submit;
		os_db_query("INSERT INTO ".TABLE_COMPANIES." (orders_id, name, inn, kpp, ogrn, okpo, rs, bank_name, bik, ks, address, yur_address, fakt_address, telephone, fax, email, director, accountant) VALUES ('".os_db_input($insert_id)."', '".os_db_input($this->name)."', '".os_db_input($this->inn)."', '".os_db_input($this->kpp)."', '".os_db_input($this->ogrn)."', '".os_db_input($this->okpo)."', '".os_db_input($this->rs)."', '".os_db_input($this->bank_name)."', '".os_db_input($this->bik)."', '".os_db_input($this->ks)."', '".os_db_input($this->address)."', '".os_db_input($this->yur_address)."', '".os_db_input($this->fakt_address)."', '".os_db_input($this->telephone)."', '".os_db_input($this->fax)."', '".os_db_input($this->email)."', '".os_db_input($this->director)."', '".os_db_input($this->accountant) ."')");

		if ($this->order_status)
			os_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$this->order_status."' WHERE orders_id='".$insert_id."'");
	}

	function output_error()
	{
		return false;
	}

	function check()
	{
		if (!isset ($this->check))
		{
			$check_query = os_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_SCHET_STATUS'");
			$this->check = os_db_num_rows($check_query);
		}
		return $this->check;
	}

	function install()
	{
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SCHET_ALLOWED', '', '6', '0', now())");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SCHET_STATUS', 'True', '6', '3', 'os_cfg_select_option(array(\'True\', \'False\'), ', now());");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SCHET_1', 'ООО \"Рога и копыта\"',  '6', '1', now());");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SCHET_2', 'Россия, 123456, г. Ставрополь, проспект Кулакова 8б, офис 130', '6', '1', now());");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SCHET_3', '(865)1234567',  '6', '1', now());");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SCHET_4', '(865)7654321',  '6', '1', now());");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SCHET_5', '1234567890',  '6', '1', now());");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SCHET_6', 'Росбанк',  '6', '1', now());");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SCHET_7', '0987654321',  '6', '1', now());");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SCHET_8', '123456',  '6', '1', now());");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SCHET_9', '87654321',  '6', '1', now());");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SCHET_10', '222222222',  '6', '1', now());");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SCHET_11', '11111111111111',  '6', '1', now());");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SCHET_12', '222222222222',  '6', '1', now());");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SCHET_SORT_ORDER', '0',  '6', '0', now())");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_SCHET_ZONE', '0',  '6', '2', 'os_get_zone_class_title', 'os_cfg_pull_down_zone_classes(', now())");
		os_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_SCHET_ORDER_STATUS_ID', '0', '6', '0', 'os_cfg_pull_down_order_statuses(', 'os_get_order_status_name', now())");
	}

	function remove()
	{
		os_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key IN ('".implode("', '", $this->keys())."')");
	}

	function keys()
	{
		return array
		(
			'MODULE_PAYMENT_SCHET_STATUS',
			'MODULE_PAYMENT_SCHET_ALLOWED',
			'MODULE_PAYMENT_SCHET_1',
			'MODULE_PAYMENT_SCHET_2',
			'MODULE_PAYMENT_SCHET_3',
			'MODULE_PAYMENT_SCHET_4',
			'MODULE_PAYMENT_SCHET_5',
			'MODULE_PAYMENT_SCHET_6',
			'MODULE_PAYMENT_SCHET_7',
			'MODULE_PAYMENT_SCHET_8',
			'MODULE_PAYMENT_SCHET_9',
			'MODULE_PAYMENT_SCHET_10',
			'MODULE_PAYMENT_SCHET_11',
			'MODULE_PAYMENT_SCHET_12',
			'MODULE_PAYMENT_SCHET_SORT_ORDER',
			'MODULE_PAYMENT_SCHET_ZONE',
			'MODULE_PAYMENT_SCHET_ORDER_STATUS_ID'
		);
	}
}
?>