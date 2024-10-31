<?php

global $wpdb;	
$wp_ripple_db = $wpdb->prefix."RIPPLE_CLICKS";
$wp_ripple_root = get_option('siteurl').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/';
$buttons = array ( "Water", "Food", "Education", "Money" );

?>