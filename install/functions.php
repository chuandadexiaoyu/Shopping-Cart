<?php
/*
*---------------------------------------------------------
*
*	CartET - Open Source Shopping Cart Software
*	http://www.cartet.org
*
*---------------------------------------------------------
*/

function is_ajax_request()
{
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']))
    {
	    return false;
    }
    return $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
}

function display($template_name, $data = array())
{
    extract($data);
    ob_start();
    include PATH . "themes/{$template_name}.php";
    return ob_get_clean();
}

function run_step($step, $is_submit = false)
{
    require PATH . "pages/{$step['id']}.php";
    $result = step($is_submit);
    return $result;
}

function text_status($value, $condition)
{
    if ($condition)
        return '<span class="positive">'.$value.'</span>';
    else
        return '<span class="negative">'.$value.'</span>';
}

function get_langs()
{
    $dir = PATH . 'languages';
    $dir_context = opendir($dir);

    $list = array();

    while ($next = readdir($dir_context))
    {
        if (in_array($next, array('.', '..'))){ continue; }
        if (strpos($next, '.') === 0){ continue; }
        if (!is_dir($dir.'/'.$next)) { continue; }

        $list[] = $next;
    }

    return $list;
}

function t($l)
{
	global $language;
	return $language[$l];
}

function os_db_connect_installer($server, $username, $password, $link = 'db_link')
{
	global $$link, $db_error;
	$db_error = false;

	$$link = @mysql_connect($server, $username, $password) or $db_error = mysql_error();

	@mysql_query("SET SQL_MODE= ''");
	@mysql_query("SET CHARACTER SET utf8");
	@mysql_query("SET NAMES utf8");
	@mysql_query("SET COLLATION utf8_general_ci");

	return $$link;
}

function os_db_select_db($database)
{
	return mysql_select_db($database);
}

function os_db_query($query, $link = 'db_link')
{
	global $$link;
	global $query_counts;

	$query_counts++;

	$result = mysql_query($query, $$link) or os_db_error($query, mysql_errno(), mysql_error());

	if (!$result)
	{
		os_db_error($query, mysql_errno(), mysql_error());
	}

	return $result;
}
function os_db_error($query, $errno, $error)
{
	return $query."\n".$error;
}

function copy_folder($d1, $d2)
{
	if (is_dir($d1))
	{
		$d = dir( $d1 );
		while (false !== ($entry = $d->read()))
		{
			if ($entry != '.' && $entry != '..')
				@copy_folder("$d1/$entry", "$d2/$entry");
		}
		$d->close();
	}
	else
	{
		$ok = @copy($d1, $d2);
	}
}

function os_get_country_list($name, $selected = '', $parameters = '')
{
	$countries_array = array();
//    Probleme mit register_globals=off -> erstmal nur auskommentiert. Kann u.U. gelС†scht werden.
	$countries = os_get_countriesList();

	for ($i=0, $n=sizeof($countries); $i<$n; $i++) {
		$countries_array[] = array('id' => $countries[$i]['countries_id'], 'text' => $countries[$i]['countries_name']);
	}
	//if (is_array($name)) return os_draw_pull_down_menuNote($name, $countries_array, $selected, $parameters);
	return os_draw_pull_down_menu($name, $countries_array, $selected, $parameters);
}

function os_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
	$field = '<select name="' . os_parse_input_field_data($name, array('"' => '&quot;')) . '"';

	if (os_not_null($parameters)) $field .= ' ' . $parameters;

	$field .= '>';

	if (empty($default) && isset($GLOBALS[$name])) $default = $GLOBALS[$name];

	for ($i=0, $n=sizeof($values); $i<$n; $i++) {
		$field .= '<option value="' . os_parse_input_field_data($values[$i]['id'], array('"' => '&quot;')) . '"';
		if ($default == $values[$i]['id']) {
			$field .= ' selected="selected"';
		}

		$field .= '>' . os_parse_input_field_data($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
	}
	$field .= '</select>';

	if ($required == true) $field .= TEXT_FIELD_REQUIRED;

	return $field;
}

function os_get_countriesList($countries_id = '', $with_iso_codes = false)
{
	$countries_array = array();
	if (os_not_null($countries_id)) {
		if ($with_iso_codes == true) {
			$countries = os_db_query("select countries_name, countries_iso_code_2, countries_iso_code_3 from ".DB_PREFIX."countries where countries_id = '" . $countries_id . "' and status = '1' order by countries_name");
			$countries_values = os_db_fetch_array($countries);
			$countries_array = array('countries_name' => $countries_values['countries_name'],
				'countries_iso_code_2' => $countries_values['countries_iso_code_2'],
				'countries_iso_code_3' => $countries_values['countries_iso_code_3']);
		} else {
			$countries = os_db_query("select countries_name from ".DB_PREFIX."countries where countries_id = '" . $countries_id . "' and status = '1'");
			$countries_values = os_db_fetch_array($countries);
			$countries_array = array('countries_name' => $countries_values['countries_name']);
		}
	} else {
		$countries = os_db_query("select countries_id, countries_name from ".DB_PREFIX."countries where status = '1' order by countries_name");

		while ($countries_values = os_db_fetch_array($countries)) {
			$countries_array[] = array('countries_id' => $countries_values['countries_id'],
				'countries_name' => $countries_values['countries_name']);
		}
	}

	return $countries_array;
}

function os_not_null($value)
{
	if (is_array($value))
	{
		if (sizeof($value) > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{
		if (($value != '') && ($value != 'NULL') && (strlen(trim($value)) > 0))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

function os_db_fetch_array(&$db_query,$cq=false)
{
	if (is_array($db_query))
	{
		$curr = current($db_query);
		next($db_query);
		return $curr;
	}

	return mysql_fetch_assoc($db_query);
}

function os_parse_input_field_data($data, $parse)
{
	return strtr(trim($data), $parse);
}

function os_db_prepare_input($string)
{
	if (is_string($string))
	{
		return trim(stripslashes($string));
	}
	elseif (is_array($string))
	{
		reset($string);
		while (list($key, $value) = each($string))
		{
			$string[$key] = os_db_prepare_input($value);
		}
		return $string;
	}
	else
	{
		return $string;
	}
}

function os_db_num_rows($db_query,$cq=false)
{
	if (DB_CACHE=='true' && $cq)
	{
		if (!count($db_query)) return false;
		return count($db_query);
	}
	else
	{
		if (!is_array($db_query)) return mysql_num_rows($db_query);
	}
}

function os_draw_pull_down_menuNote($data, $values, $default = '', $parameters = '', $required = false) {
	$field = '<select name="' . os_parse_input_field_data($data['name'], array('"' => '&quot;')) . '"';

	if (os_not_null($parameters)) $field .= ' ' . $parameters;

	$field .= '>';

	if (empty($default) && isset($GLOBALS[$data['name']])) $default = $GLOBALS[$data['name']];

	for ($i=0, $n=sizeof($values); $i<$n; $i++) {
		$field .= '<option value="' . os_parse_input_field_data($values[$i]['id'], array('"' => '&quot;')) . '"';
		if ($default == $values[$i]['id']) {
			$field .= ' selected="selected"';
		}

		$field .= '>' . os_parse_input_field_data($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
	}
	$field .= '</select>'.$data['text'];

	if ($required == true) $field .= TEXT_FIELD_REQUIRED;

	return $field;
}

function os_encrypt_password($plain)
{
	$password=md5($plain);
	return $password;
}

function os_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link')
{
	reset($data);

	if ($action == 'insert') {
		$query = 'insert into ' . $table . ' (';
		while (list($columns, ) = each($data)) {
			$query .= $columns . ', ';
		}
		$query = substr($query, 0, -2) . ') values (';
		reset($data);
		while (list(, $value) = each($data)) {
			$value = (is_Float($value)) ? sprintf("%.F",$value) : (string)($value);
			switch ($value) {
				case 'now()':
					$query .= 'now(), ';
					break;
				case 'null':
					$query .= 'null, ';
					break;
				default:
					$query .= '\'' . os_db_input($value) . '\', ';
					break;
			}
		}
		$query = substr($query, 0, -2) . ')';
	} elseif ($action == 'update') {
		$query = 'update ' . $table . ' set ';
		while (list($columns, $value) = each($data)) {
			$value = (is_Float($value)) ? sprintf("%.F",$value) : (string)($value);
			switch ($value) {
				case 'now()':
					$query .= $columns . ' = now(), ';
					break;
				case 'null':
					$query .= $columns .= ' = null, ';
					break;
				default:
					$query .= $columns . ' = \'' . os_db_input($value) . '\', ';
					break;
			}
		}
		$query = substr($query, 0, -2) . ' where ' . $parameters;
	}

	return os_db_query($query, $link);
}

function os_db_input($string, $link = 'db_link')
{
	global $$link;

	if (function_exists('mysql_real_escape_string'))
	{
		return mysql_real_escape_string($string, $$link);
	}
	elseif (function_exists('mysql_escape_string'))
	{
		return mysql_escape_string($string);
	}
	return addslashes($string);
}