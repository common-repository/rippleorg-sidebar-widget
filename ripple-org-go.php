<?php
	require_once('../../../wp-config.php');
	global  $wpdb, $table_prefix;
	$url = "http://www.ripple.org/give.php?p=";
	// set table name	
	$wp_ripple_db = $table_prefix."RIPPLE_CLICKS";
	
	$category=$_GET['cat'];
	if (isset($category)) {
		$date = date('Y-m-d H:i:s');
		$query_update = sprintf("update %s set clicks = clicks + 1, lastclicked = '%s' where category = '%s'",
			$wpdb->escape($wp_ripple_db),
			$date,
			$wpdb->escape(strtolower($category)));
		$wpdb->query($query_update);
		
		//echo $query_update;
			
		$location = 'Location: ' . $url . strtolower($category);
		header($location);
		exit();
	} else {
		echo "Invalid link!";
	}
?>