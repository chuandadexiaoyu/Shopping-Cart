<?php
/*
*---------------------------------------------------------
*
*	CartET - Open Source Shopping Cart Software
*	http://www.cartet.org
*
*---------------------------------------------------------
*/

    if (STORE_PAGE_PARSE_TIME == 'true') {
        $time_start = explode(' ', PAGE_PARSE_START_TIME);
        $time_end = explode(' ', microtime());
        $parse_time = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);
        error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' - ' . getenv('REQUEST_URI') . ' (' . $parse_time . 's)' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);

    }

    if (DISPLAY_PAGE_PARSE_TIME == 'true') {
	    global $cartet;
        $time_start = explode(' ', PAGE_PARSE_START_TIME);
        $time_end = explode(' ', microtime());
        $parse_time = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);
        echo '<center><font color="red">'.PARSE_TIME.' <b>'. $parse_time . '</b></font>, <font color="green">'.QUERIES.': <b>' . $query_counts . '</b></font>, <font color="blue">cache: <b>' . $cartet->cache->query_count . '</b></font></center>';
    }

    if ((GZIP_COMPRESSION == 'true') && ($ext_zlib_loaded == true) && ($ini_zlib_output_compression < 1)) 
    {
        if ((PHP_VERSION < '4.0.4') && (PHP_VERSION >= '4')) 
        {
            os_gzip_output(GZIP_LEVEL);
        }
    }

    if (DISPLAY_MEMORY_USAGE == 'true')
    {
        if ( function_exists('memory_get_usage') )
        {
            echo  '<center>'.TEXT_MEMORY_USAGE.round(memory_get_usage()/1024/1024, 2) . 'MB</center>';
        }
    }

    if (DISPLAY_DB_QUERY == 'true')
    {
        echo "<CENTER><div style='overflow: scroll; width: 60%; height: 200px; text-align: left;border: 1px dotted blue;'>";

        arsort ($db_query);

        $_db_query = array();

        if ( count($db_query) > 0 )
        {
            foreach ($db_query as $v1 => $v2)
            {
                $v1 = str_replace('select', '<font color="#ff1493">select</font>', $v1 );
                $v1 = str_replace('from', '<font color="#ff1493">from</font>', $v1 );
                $v1 = str_replace('where', '<font color="#ff1493">where</font>', $v1 );
                $_db_query[ $v1 ] = $v2; 
            }
        }


        foreach ($_db_query as $v1 => $v2)
        {
            echo '<font color="green">('.$v2['num'].')</font> <font color="red">('.$v2['time'].')</font> <font color="blue">'.$v1."</font><br />";
        }

        echo "</div></center><br /><br />";
    }

?>